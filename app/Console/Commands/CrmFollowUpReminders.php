<?php

namespace App\Console\Commands;

use App\Models\Crm\CrmActivity;
use App\Models\User;
use App\Services\Mail\EmailQueueService;
use Illuminate\Console\Command;

/**
 * Morning email to each user with the CRM follow-ups due today or overdue (one email per user per day, through email_queues).
 * A follow-up stays in the daily email until it is marked done or moved to a later date.
 */
class CrmFollowUpReminders extends Command
{
    protected $signature = 'crm:follow-up-reminders {--dry-run : list what would be sent, send nothing}';

    protected $description = 'Queue the daily CRM follow-up reminder email for every user with follow-ups due';

    public function handle(EmailQueueService $mail): int
    {
        // times are the company's local clock: a company gets its email once its day has reached 07:00 (the command runs hourly)
        $due = CrmActivity::withoutGlobalScopes()
            ->with(['action' => fn($q) => $q->withoutGlobalScopes()->select('id', 'name', 'category')])
            ->whereNull('deleted_at')
            ->whereNotNull('follow_up_at')->whereNull('follow_up_done_at')
            ->whereNotNull('assigned_to')
            ->where('follow_up_at', '<=', now()->addDays(2))
            ->orderBy('follow_up_at')
            ->get()
            ->filter(function ($a) {
                $now = CrmActivity::localNowNaive((int) $a->company_id);
                if (!$this->option('dry-run') && $now->hour < 7) {
                    return false;
                }
                $today = $now->copy()->startOfDay();

                return $a->follow_up_at->lte($now->copy()->endOfDay())
                    && (!$a->reminder_sent_at || $a->reminder_sent_at->lt($today));
            });

        if ($due->isEmpty()) {
            $this->info('No follow-ups due.');

            return self::SUCCESS;
        }

        $names = CrmActivity::subjectNames($due);
        $users = User::whereIn('id', $due->pluck('assigned_to')->unique())->get(['id', 'name', 'email'])->keyBy('id');
        $sent = 0;

        // one email per user (a user belongs to one company)
        foreach ($due->groupBy('assigned_to') as $userId => $items) {
            $user = $users->get($userId);
            if (!$user || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $rows = $items->map(fn($a) => [
                'when' => $a->follow_up_at->format('d-m-Y H:i'),
                'overdue' => $a->follow_up_at->lt(CrmActivity::localNowNaive((int) $a->company_id)->startOfDay()),
                'kind' => CrmActivity::subjectLabel($a->subject_type),
                'no' => $names[$a->subject_type][$a->subject_id]['no'] ?? '',
                'name' => $names[$a->subject_type][$a->subject_id]['name'] ?? '',
                'todo' => trim(($a->action ? $a->action->name : '') . ($a->notes ? ': ' . \Illuminate\Support\Str::limit($a->notes, 120) : ''), ': '),
            ])->values()->all();
            $overdue = collect($rows)->where('overdue', true)->count();

            $this->line(sprintf('%s <%s>: %d follow-up(s), %d overdue', $user->name, $user->email, count($rows), $overdue));
            if ($this->option('dry-run')) {
                continue;
            }

            $mail->enqueue(
                $user->email,
                $overdue ? "Follow-ups today: {$overdue} overdue, " . (count($rows) - $overdue) . ' due' : 'Follow-ups today: ' . count($rows),
                'emails.crm.follow-up-reminder',
                ['name' => $user->name, 'rows' => $rows, 'url' => url('/sales/follow-ups')],
                ['type' => 'crm-follow-up', 'company_id' => $items->first()->company_id]
            );
            CrmActivity::withoutGlobalScopes()->whereIn('id', $items->pluck('id'))->update(['reminder_sent_at' => CrmActivity::localNowNaive((int) $items->first()->company_id)]);
            $sent++;
        }

        $this->info("Queued {$sent} reminder email(s).");

        return self::SUCCESS;
    }
}

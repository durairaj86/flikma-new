<?php

namespace App\Http\Controllers;

use App\Models\Billing\AiUsage;
use App\Models\Master\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiUsageController extends Controller
{
    public function index(Request $request): View
    {
        $company = Company::currentFresh();

        // Default view is "this month so far" — an unbounded list would dump the
        // entire history on first load, which isn't a useful starting point.
        $filters = [
            'from' => $request->filled('from') ? $request->input('from') : now()->startOfMonth()->toDateString(),
            'to' => $request->filled('to') ? $request->input('to') : now()->toDateString(),
            'user_id' => $request->input('user_id'),
        ];

        $query = AiUsage::where('company_id', companyId());

        if ($filters['from']) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if ($filters['to']) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        if ($filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        $filteredTotalTokens = (clone $query)->sum('total_tokens');

        $logs = $query->with('user:id,name')->latest()->paginate(30)->withQueryString();

        $tokenLimit = $company?->ai_token_limit;
        $tokensUsed = (int) ($company?->ai_tokens_used ?? 0);

        // null or 0 both mean "no usable limit"
        $hasNoLimit = $tokenLimit === null || $tokenLimit <= 0;
        $tokensRemaining = $hasNoLimit ? null : max(0, $tokenLimit - $tokensUsed);

        // "Close to running out" — within 10% of the limit.
        $isLowRemaining = !$hasNoLimit
            && $tokensRemaining > 0
            && $tokensRemaining <= max(1, (int) ceil($tokenLimit * 0.1));

        // Only users who've actually used AI; User is company-scoped, so this can
        // never surface another company's users.
        $userIds = AiUsage::where('company_id', companyId())->distinct()->pluck('user_id');
        $users = User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']);

        return view('billing.ai-usage.index', compact(
            'logs', 'tokenLimit', 'tokensUsed', 'tokensRemaining', 'filters', 'users',
            'filteredTotalTokens', 'hasNoLimit', 'isLowRemaining'
        ));
    }
}

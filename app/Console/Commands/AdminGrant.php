<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/** Give (or take away) access to the /admin panel. */
class AdminGrant extends Command
{
    protected $signature = 'admin:grant {email : The user to change} {--revoke : Remove admin access instead}';

    protected $description = 'Give a user access to the platform admin panel (/admin), or remove it with --revoke';

    public function handle(): int
    {
        $user = User::withoutGlobalScopes()->where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('No user with that email.');
            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => !$this->option('revoke')])->save();
        $this->info($user->is_admin ? "{$user->email} can now sign in at /admin." : "{$user->email} no longer has admin access.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Auth;

use App\Scopes\CompanyScope;
use Illuminate\Auth\EloquentUserProvider;

/**
 * Finds the signed-in / signing-in user without the company filter. User carries CompanyScope (session company_id),
 * and a session can still hold a company while nobody is signed in (e.g. after "Login as" was left without a
 * full logout): the forgot-password, reset and login lookups then only saw that company's users and answered
 * "We can't find a user with that email address" for everyone else.
 */
class UnscopedEloquentUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)->withoutGlobalScope(CompanyScope::class);
    }
}

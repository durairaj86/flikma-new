<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Portal\PortalUser;
use App\Services\Mail\EmailQueueService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Staff side of the customer portal: invite a customer's people, send the invite again, disable / enable a login. */
class PortalUserController extends Controller
{
    public function invite(Request $request, $customerId, EmailQueueService $mail)
    {
        $customer = Customer::findOrFail($customerId);
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            // one portal login per email address, whichever company it belongs to (the email is the login name)
            'email' => ['required', 'email', 'max:191', Rule::unique('customer_portal_users', 'email')],
        ], ['email.unique' => __('This email already has a portal login.')], ['name' => __('Name'), 'email' => __('Email')]);

        $user = new PortalUser();
        $user->company_id = companyId();
        $user->customer_id = $customer->id;
        $user->user_id = auth()->id();
        $user->name = trim($request->input('name'));
        $user->email = $request->input('email');
        $user->is_active = true;
        $user->save();

        $this->sendInvite($user, $customer, $mail);

        return response()->json(['status' => 'success', 'message' => __('Invitation sent to :email', ['email' => $user->email])]);
    }

    public function resend($id, EmailQueueService $mail)
    {
        $user = $this->find($id);
        if (!$user->is_active) {
            return response()->json(['status' => 'error', 'message' => __('Enable this login first.')], 422);
        }
        $this->sendInvite($user, $user->customer, $mail);

        return response()->json(['status' => 'success', 'message' => __('Invitation sent to :email', ['email' => $user->email])]);
    }

    public function updateStatus($id, $status)
    {
        $user = $this->find($id);
        $user->is_active = (bool) $status;
        if (!$user->is_active) {
            // a disabled login is signed out everywhere (remember-me included) and its open links stop working
            $user->remember_token = null;
            $user->invite_token = null;
            $user->invite_expires_at = null;
        }
        $user->save();

        return response()->json(['status' => 'success', 'message' => $user->is_active ? __('Portal login enabled') : __('Portal login disabled')]);
    }

    private function find($id): PortalUser
    {
        // only this company's logins
        return PortalUser::where('company_id', companyId())->findOrFail($id);
    }

    private function sendInvite(PortalUser $user, Customer $customer, EmailQueueService $mail): void
    {
        $token = $user->issueToken(PortalUser::INVITE_HOURS);
        $company = companyName();
        $mail->enqueue($user->email, "Your {$company} customer portal login", 'emails.generic', [
            'body' => "Hello {$user->name},\n\n{$company} has opened a customer portal for {$customer->name_en}. There you can track your shipments, see your invoices and statement, and answer quotations.\n\nUse the button below to choose your password. The link works for " . PortalUser::INVITE_HOURS . " hours.\n\nAfter that, sign in at " . url('/portal/login') . " with this email address.",
            'action_url' => url('/portal/password/' . $token),
            'action_label' => 'Choose your password',
        ], ['type' => 'portal-invite', 'company_id' => $user->company_id, 'sensitive' => true, 'expires_at' => now()->addHours(PortalUser::INVITE_HOURS)]);
    }
}

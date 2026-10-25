<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\Admin\Setting;
use Illuminate\Http\Request;

/**
 * Platform settings (same sections as fatoora). "Admin email" is used: website contact-form messages go there.
 * SMTP, payment and social values are stored for reference; sending still uses the MAIL_* values in .env.
 */
class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'settings' => Setting::getGroup('general'),
            'smtp' => Setting::getGroup('smtp'),
            'payment' => Setting::getGroup('payment'),
            'social' => Setting::getGroup('social'),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'nullable|url|max:255',
            'admin_email' => 'nullable|email|max:255',
            'app_description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|mimes:png,ico|max:1024',
        ]);
        foreach ($request->only(['app_name', 'app_url', 'admin_email', 'app_description']) as $key => $value) {
            Setting::set($key, $value, 'general');
        }
        foreach (['logo', 'favicon'] as $file) {
            if ($request->hasFile($file)) {
                Setting::set($file, $request->file($file)->store('settings', 'public'), 'general');
            }
        }
        ActivityLog::log('updated', 'settings', null, 'Updated general settings');

        return back()->with('success', 'General settings saved.');
    }

    public function updateSmtp(Request $request)
    {
        $keys = ['mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];
        $request->validate([
            'mail_mailer' => 'required|string|max:50',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:20',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);
        foreach ($request->only($keys) as $key => $value) {
            // an empty password box keeps the saved one
            if ($key === 'mail_password' && blank($value)) continue;
            Setting::set($key, $value, 'smtp');
        }
        ActivityLog::log('updated', 'settings', null, 'Updated SMTP settings');

        return back()->with('success', 'SMTP settings saved.');
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'payment_gateway' => 'required|in:stripe,paypal,manual',
            'stripe_key' => 'nullable|string|max:255',
            'stripe_secret' => 'nullable|string|max:255',
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_secret' => 'nullable|string|max:255',
        ]);
        foreach ($request->only(['payment_gateway', 'stripe_key', 'stripe_secret', 'paypal_client_id', 'paypal_secret']) as $key => $value) {
            if (in_array($key, Setting::SECRET, true) && blank($value)) continue;
            Setting::set($key, $value, 'payment');
        }
        ActivityLog::log('updated', 'settings', null, 'Updated payment settings');

        return back()->with('success', 'Payment settings saved.');
    }

    public function updateSocial(Request $request)
    {
        $keys = ['facebook', 'twitter', 'linkedin', 'instagram', 'youtube'];
        $request->validate(array_fill_keys($keys, 'nullable|url|max:255'));
        foreach ($request->only($keys) as $key => $value) {
            Setting::set($key, $value, 'social');
        }
        ActivityLog::log('updated', 'settings', null, 'Updated social media links');

        return back()->with('success', 'Social links saved.');
    }
}

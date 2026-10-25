<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\ContactMessage;
use App\Models\EmailQueue;
use App\Models\Master\Company;
use App\Services\Mail\EmailQueueService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [];
        $data['totalCompanies'] = Company::count();
        $data['companiesThisYear'] = Company::whereYear('created_at', now()->year)->count();
        $data['companiesThisMonth'] = Company::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $data['companiesLastMonth'] = Company::whereYear('created_at', now()->subMonthNoOverflow()->year)
            ->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();

        // paid access = an end date that has not passed; trial = no end date yet, trial running
        $data['paidActive'] = Company::whereNotNull('subscription_expiry_date')->whereDate('subscription_expiry_date', '>=', today())->count();
        $data['paidExpired'] = Company::whereNotNull('subscription_expiry_date')->whereDate('subscription_expiry_date', '<', today())->count();
        $data['onTrial'] = Company::whereNull('subscription_expiry_date')->where('trial_ends_at', '>=', today())->count();
        $data['totalRevenue'] = (float) DB::table('subscriptions')->where('payment_status', 'paid')->sum('amount');

        $data['pendingEnquiries'] = ContactMessage::where('is_read', false)->whereNull('archived_at')->count();
        $data['enquiriesThisMonth'] = ContactMessage::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $data['enquiriesLastMonth'] = ContactMessage::whereYear('created_at', now()->subMonthNoOverflow()->year)
            ->whereMonth('created_at', now()->subMonthNoOverflow()->month)->count();

        // users who signed in to the app today (users.last_login), not counting admins
        $data['loginsToday'] = DB::table('users')->whereDate('last_login', today())->where('is_admin', false)->count();
        $data['failedEmails'] = EmailQueue::where('status', 'failed')->where('attempts', '>=', EmailQueueService::MAX_ATTEMPTS)->count();

        $data['recentActivities'] = ActivityLog::with('admin:id,name')->latest()->take(10)->get();
        $data['recentCompanies'] = Company::latest()->take(5)->get();

        $data['monthlyCompanyRegistrations'] = Company::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', now()->year)->groupBy('month')->pluck('total', 'month');
        $data['monthlyRevenue'] = DB::table('subscriptions')->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as total'))
            ->where('payment_status', 'paid')->whereYear('created_at', now()->year)->groupBy('month')->pluck('total', 'month');

        return view('admin.dashboard.index', $data);
    }
}

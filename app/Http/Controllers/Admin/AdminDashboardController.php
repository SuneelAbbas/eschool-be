<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Institute;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalInstitutes = Institute::count();
        $activeInstitutes = Institute::where('status', 'approved')->count();
        $pendingInstitutes = Institute::where('status', 'pending')->count();
        $rejectedInstitutes = Institute::where('status', 'rejected')->count();

        $totalUsers = User::count();
        $usersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalStudents = Student::count();

        $totalTeachers = Teacher::count();
        $teachersThisMonth = Teacher::whereMonth('join_date', now()->month)
            ->whereYear('join_date', now()->year)
            ->count();

        $revenueThisYear = DB::table('fee_payments')
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $institutes = Institute::with('adminUser', 'plan')
            ->latest()
            ->get()
            ->map(function ($institute) {
                $statusColor = match ($institute->status) {
                    'approved' => 'emerald',
                    'pending' => 'amber',
                    'rejected' => 'red',
                    default => 'stone',
                };
                return (object) [
                    'id' => $institute->id,
                    'initials' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $institute->name), 0, 2)),
                    'name' => $institute->name,
                    'city' => $institute->city,
                    'admin_name' => $institute->adminUser?->first_name . ' ' . $institute->adminUser?->last_name,
                    'plan_name' => $institute->plan?->name ?? '-',
                    'status' => $institute->status,
                    'status_color' => $statusColor,
                    'created_at' => $institute->created_at->format('M j, Y'),
                ];
            });

        $pendingList = Institute::where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn($i) => (object) [
                'id' => $i->id,
                'name' => $i->name,
                'city' => $i->city,
                'created_at' => $i->created_at->diffForHumans(),
            ]);

        $recentMessages = ContactMessage::latest()
            ->take(3)
            ->get()
            ->map(fn($m) => (object) [
                'name' => $m->name,
                'subject' => $m->subject,
                'time' => $m->created_at->diffForHumans(),
            ]);

        $activeSessions = DB::table('sessions')->count();
        $queuedJobs = DB::table('jobs')->count();
        $failedJobs24h = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subDay())
            ->count();
        $phpVersion = phpversion();

        return view('admin.dashboard.index', compact(
            'totalInstitutes', 'activeInstitutes', 'pendingInstitutes', 'rejectedInstitutes',
            'totalUsers', 'usersThisMonth',
            'totalStudents',
            'totalTeachers', 'teachersThisMonth',
            'revenueThisYear',
            'institutes',
            'pendingList',
            'recentMessages',
            'activeSessions', 'queuedJobs', 'failedJobs24h', 'phpVersion',
        ));
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Console\Command;

class CheckInstitute extends Command
{
    protected $signature = 'check:institute {id?}';
    protected $description = 'Check institute details';

    public function handle(): int
    {
        $id = $this->argument('id') ?? 2;

        $institute = Institute::find($id);

        if (!$institute) {
            $this->error("Institute with ID {$id} not found.");
            return 1;
        }

        $this->info("=== Institute (ID: {$id}) ===");
        $this->line("Name: {$institute->name}");
        $this->line("Email: " . ($institute->contact_email ?? 'N/A'));
        $this->line("Phone: " . ($institute->contact_phone ?? 'N/A'));
        $this->line("Address: " . ($institute->address ?? 'N/A'));
        $this->line("Status: {$institute->status}");
        $this->line("Admin User ID: " . ($institute->admin_user_id ?? 'null'));

        if ($institute->admin_user_id) {
            $user = User::find($institute->admin_user_id);

            if ($user) {
                $this->info("\n=== Admin User ===");
                $this->line("ID: {$user->id}");
                $this->line("Name: {$user->first_name} {$user->last_name}");
                $this->line("Email: {$user->email}");
                $this->line("Role: {$user->user_type}");
            }
        }

        return 0;
    }
}

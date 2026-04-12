<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateTeachers extends Command
{
    protected $signature = 'app:clean-duplicate-teachers';

    protected $description = 'Delete duplicate teachers keeping the oldest record';

    public function handle(): int
    {
        $duplicates = DB::table('teachers')
            ->select('institute_id', 'cnic_number', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id) as all_ids'))
            ->groupBy('institute_id', 'cnic_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate teachers found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} sets of duplicate teachers.");

        $deletedCount = 0;
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup->all_ids);
            $keepId = (int) $dup->keep_id;
            $toDelete = array_diff($ids, [(string) $keepId]);

            $deleted = Teacher::whereIn('id', $toDelete)->delete();
            $deletedCount += $deleted;

            $teacher = Teacher::find($keepId);
            $this->warn("Deleted " . count($toDelete) . " duplicates. Kept ID {$keepId} ({$teacher->first_name} {$teacher->last_name})");
        }

        $this->info("Total deleted: {$deletedCount} duplicate teachers.");
        return Command::SUCCESS;
    }
}

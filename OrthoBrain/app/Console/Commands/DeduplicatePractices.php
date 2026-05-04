<?php

namespace App\Console\Commands;

use App\Models\Practice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeduplicatePractices extends Command
{
    protected $signature = 'practices:deduplicate';

    protected $description = 'Find and soft-delete duplicate practices (same name+city), keeping the oldest record.';

    public function handle()
    {
        $this->info('Finding duplicate practices (same name+city)...');

        $duplicates = Practice::query()
            ->select('name', 'city_id', DB::raw('COUNT(*) as cnt'))
            ->whereNull('deleted_at')
            ->groupBy('name', 'city_id')
            ->having('cnt', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} duplicate groups.");

        $totalDeleted = 0;
        $totalSkipped = 0;

        foreach ($duplicates as $dup) {
            $name = $dup->name;
            $cityId = $dup->city_id;

            $group = Practice::where('name', $name)
                ->where('city_id', $cityId)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get();

            if ($group->count() <= 1) {
                continue;
            }

            $this->line("  Name: \"$name\" (city_id: $cityId) — {$group->count()} copies");

            // Keep the best record: ACTIVE over INACTIVE, more members over fewer, oldest ID as tiebreaker.
            $keepRecord = $group->sortBy(function ($p) {
                $members = DB::table('doctor_practice')->where('practice_id', $p->id)->count();
                return [($p->status === 'ACTIVE' ? 0 : 1), -$members, $p->id];
            })->first();
            $toDelete = $group->reject(fn ($p) => $p->id === $keepRecord->id);

            foreach ($toDelete as $record) {
                $hasMembers = DB::table('doctor_practice')
                    ->where('practice_id', $record->id)
                    ->exists();

                if ($hasMembers) {
                    $this->warn(
                        "    → ID {$record->id} has {$record->doctors()->count()} linked doctor(s) — SKIPPING"
                    );
                    $totalSkipped++;
                } else {
                    $record->delete();
                    $this->line("    → ID {$record->id} deleted (no members)");
                    $totalDeleted++;
                }
            }
        }

        $this->info("Deleted: $totalDeleted, Skipped: $totalSkipped");
        return Command::SUCCESS;
    }
}

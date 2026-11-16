<?php

namespace App\Console\Commands;

use App\Models\AbstractSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateAbstractFilesToPrivateDisk extends Command
{
    protected $signature = 'abstracts:migrate-public-files-to-private {--dry-run : Show files that would be moved without changing storage}';

    protected $description = 'Move legacy public abstract files to the private local disk';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;
        $skipped = 0;
        $failed = 0;

        AbstractSubmission::query()->whereNotNull('file_abstrak')->orderBy('id_abstract')->chunk(100, function ($abstracts) use ($public, $private, &$moved, &$skipped, &$failed): void {
            foreach ($abstracts as $abstract) {
                $path = ltrim(str_replace('\\', '/', (string) $abstract->file_abstrak), '/');
                if (! str_starts_with($path, 'abstracts/') || str_contains($path, '../')) {
                    $this->warn("Skipped abstract {$abstract->id_abstract}: unsupported stored path.");
                    $skipped++;

                    continue;
                }

                if ($private->exists($path)) {
                    if ($public->exists($path) && ! $this->option('dry-run')) {
                        $public->delete($path);
                    }
                    $skipped++;

                    continue;
                }

                if (! $public->exists($path)) {
                    $this->warn("Missing legacy file for abstract {$abstract->id_abstract}.");
                    $failed++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("Would move {$path}");
                    $moved++;

                    continue;
                }

                $contents = $public->get($path);
                if ($private->put($path, $contents) && $private->exists($path)) {
                    $public->delete($path);
                    $moved++;
                } else {
                    $this->error("Failed to copy legacy file for abstract {$abstract->id_abstract}.");
                    $failed++;
                }
            }
        });

        $this->info("Moved: {$moved}; skipped: {$skipped}; failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}

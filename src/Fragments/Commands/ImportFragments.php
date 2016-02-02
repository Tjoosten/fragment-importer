<?php

namespace Spatie\FragmentImporter\Commands;

use Spatie\FragmentImporter\Importer;
use Artisan;
use Cache;
use Illuminate\Console\Command;

class ImportFragments extends Command
{
    /** @var string */
    protected $signature = 'fragments:import {--update : Update the existing fragments}';

    /** @var string */
    protected $description = 'Import fragments from the excel file.';

    public function handle(Importer $importer)
    {
        if ($this->option('update')) {
            Artisan::call('backup:run', ['--only-db' => true]);
            $this->comment('Database dumped');
            $importer->updateExistingFragments();
        }

        $latestExcelFile = $this->getLastestFragmentExcel();

        $this->comment("Importing fragments from {$latestExcelFile}");
        $importer->import($latestExcelFile);

        Cache::flush();
        $this->comment('Cache cleared');

        $this->info('Imported '.($this->option('update') ? 'all' : 'only the new').' fragments!');
    }

    public function getLastestFragmentExcel() : string
    {
        $directory = database_path('seeds/data');

        $files = collect(glob("{$directory}/fragments*.xlsx"));

        if ($files->isEmpty()) {
            throw new \Exception("could not find any fragment files in directory `{$directory}`");
        }

        return $files->last();
    }
}

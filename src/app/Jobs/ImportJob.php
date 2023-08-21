<?php

namespace App\Jobs;

use App\Models\Import;
use App\Service\ImportContactService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $imported;

    /**
     * Create a new job instance.
     *
     * @param string importId
     */
    public function __construct(string $imported)
    {
        $this->imported = $imported;
        //$this->onQueue('default');
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(ImportContactService $importContactService): void
    {
        $import = Import::where('id', $this->imported)
            ->where('status', 0)
            ->first();
        if (!$import) return;
        
        $file = download_from_url(asset('assets/file/import/'.$import->path));
        $import->status = 1;
        $import->save();
        if($import->mime == 'text/csv'){
        
            $importContactService->importContactFormCsv($import, $file, $importContactService->getCsv($file));
        }
        else{
            $importContactService->importContactFormExel($import, $file);
        }
    }
}

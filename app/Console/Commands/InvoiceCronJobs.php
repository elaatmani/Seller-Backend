<?php

namespace App\Console\Commands;

use App\Models\Factorisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InvoiceCronJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Factorisation::where([['close',false],['type','seller']])->update(['close' => true]);
    }
}

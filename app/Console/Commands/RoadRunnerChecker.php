<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RoadRunnerRequest;
use Carbon\Carbon;
use App\Models\Notifications;

class RoadRunnerChecker extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:roadrunner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check 24h road runner requests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        
        $requestsCount = RoadRunnerRequest::where('created_at', '>=', $twentyFourHoursAgo)->count();
        
        if ($requestsCount === 0) {
           Notifications::create([
             'user_id' => 1,
             'title' => 'RoadRunner requests',
             'message' => 'No road runner requests created in the last 24 hours. There might be a problem.',
             'type' => 'Request RoadRunner',
             'priority' => 'high'
           ]);
        }
                   
        return 0;
    }
}

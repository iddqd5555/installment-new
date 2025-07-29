<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserLocationLog;
use Carbon\Carbon;

class ClearOldLocationLogs extends Command
{
    protected $signature = 'logs:clear-location-7days';
    protected $description = 'Delete user_location_logs older than 7 days';

    public function handle()
    {
        $days = 7;
        $count = UserLocationLog::where('created_at', '<', Carbon::now()->subDays($days))->delete();
        $this->info("Cleared $count user_location_logs older than $days days.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RoundTimerLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'round:process-loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run round timer in a loop (every second) - Background process';

    /**
     * Execute the console command.
     * Chạy liên tục mỗi giây để xử lý round timer ở server-side
     */
    public function handle()
    {
        $this->info('Round timer loop started - Running every second');
        
        while (true) {
            try {
                $this->call('round:process');
            } catch (\Exception $e) {
                $this->error('Error in round:process: ' . $e->getMessage());
            }
            
            // Wait 1 second before next iteration
            sleep(1);
        }
    }
}

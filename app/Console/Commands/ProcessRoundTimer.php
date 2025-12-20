<?php

namespace App\Console\Commands;

use App\Models\Round;
use App\Http\Controllers\ExploreController;
use Illuminate\Console\Command;

class ProcessRoundTimer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'round:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process round timer - update current second and random result';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current active round
        $round = Round::getCurrentRound();
        
        // If round is finished, create new one
        if ($round->status === 'finished') {
            $round = Round::getCurrentRound();
        }
        
        // If round is pending, start it
        if ($round->status === 'pending') {
            $round->start();
        }
        
        // If round is running, update second
        if ($round->status === 'running') {
            $currentSecond = $round->current_second;
            
            // If we're at second 60, finish the round
            if ($currentSecond >= 60) {
                $round->finish($round->current_result);
                $this->info("Round {$round->round_number} finished with result: {$round->final_result}");
            } else {
                // Increment second and random result
                $newSecond = $currentSecond + 1;
                $randomResult = ExploreController::randomGemType();
                
                $round->updateSecond($newSecond, $randomResult);
                
                $this->info("Round {$round->round_number} - Second {$newSecond}: {$randomResult}");
            }
        }
        
        return 0;
    }
}

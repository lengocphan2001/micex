<?php

namespace App\Console\Commands;

use App\Models\Round;
use App\Http\Controllers\ExploreController;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessRoundTimer extends Command
{
    /**
     * Base time để tính round number (phải giống client)
     * Mặc định: 2025-01-01 00:00:00 UTC
     */
    const BASE_TIME = '2025-01-01 00:00:00';
    const ROUND_DURATION = 60; // 60 giây mỗi round
    
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
     * Calculate round number based on BASE_TIME
     * Round duration: 60 giây
     * Break time: 10 giây
     * Total cycle: 70 giây (60 + 10)
     */
    private function calculateRoundNumber()
    {
        $baseTime = Carbon::parse(self::BASE_TIME)->timestamp;
        $now = now()->timestamp;
        $elapsed = $now - $baseTime;
        $breakTime = 10; // 10 giây break time
        $totalCycle = self::ROUND_DURATION + $breakTime; // 70 giây mỗi cycle
        return floor($elapsed / $totalCycle) + 1;
    }
    
    /**
     * Calculate round deadline
     * Round duration: 60 giây
     * Break time: 10 giây
     * Total cycle: 70 giây (60 + 10)
     */
    private function calculateRoundDeadline($roundNumber)
    {
        $baseTime = Carbon::parse(self::BASE_TIME)->timestamp;
        $breakTime = 10; // 10 giây break time
        $totalCycle = self::ROUND_DURATION + $breakTime; // 70 giây mỗi cycle
        
        // Round start time = baseTime + (roundNumber - 1) * totalCycle
        $roundStartTime = $baseTime + (($roundNumber - 1) * $totalCycle);
        
        // Deadline = roundStartTime + ROUND_DURATION (60 giây)
        return Carbon::createFromTimestamp($roundStartTime + self::ROUND_DURATION);
    }
    
    /**
     * Calculate current second from deadline
     */
    private function calculateCurrentSecond($roundNumber)
    {
        $deadline = $this->calculateRoundDeadline($roundNumber);
        $now = now();
        
        // Tính countdown: deadline - now (số giây còn lại)
        $countdown = max(0, (int) floor(($deadline->timestamp - $now->timestamp)));
        
        if ($countdown > 0 && $countdown <= self::ROUND_DURATION) {
            // Round đang chạy: currentSecond = 60 - countdown + 1
            return self::ROUND_DURATION - $countdown + 1;
        }
        
        // Round đã finish hoặc chưa bắt đầu
        return 0;
    }

    /**
     * Execute the console command.
     * Chạy mỗi giây để xử lý round timer ở server-side
     * Tự động chạy background, không cần user truy cập page
     */
    public function handle()
    {
        // Tính round number từ BASE_TIME (giống client)
        $currentRoundNumber = $this->calculateRoundNumber();
        
        // Get or create round với round_number này
        $round = Round::getOrCreateRoundByNumber($currentRoundNumber);
        
        // Tính current second từ deadline
        $currentSecond = $this->calculateCurrentSecond($currentRoundNumber);
        
        // Nếu round chưa start và đã đến thời gian (currentSecond > 0)
        if ($round->status === 'pending' && $currentSecond > 0) {
            $round->start();
            $this->info("Round {$round->round_number} started");
            return 0;
        }
        
        // Nếu round đang running
        if ($round->status === 'running') {
            // Tính deadline để check xem round đã finish chưa
            $deadline = $this->calculateRoundDeadline($currentRoundNumber);
            $now = now();
            $countdown = max(0, (int) floor(($deadline->timestamp - $now->timestamp)));
            
            // Nếu đã đến giây 60 hoặc quá deadline (countdown = 0), finish round
            if ($currentSecond >= 60 || $countdown <= 0) {
                // Chỉ finish nếu chưa finish (tránh finish nhiều lần)
                if ($round->status === 'running') {
                    // Calculate final result based on seed
                    $finalResult = $this->getGemForSecond($round->seed, 60);
                    
                    // If admin set result, use that
                    if ($round->admin_set_result) {
                        $finalResult = $round->admin_set_result;
                    }
                    
                    // Finish the round (this will process bets and update commission)
                    $round->finish($finalResult);
                    $this->info("Round {$round->round_number} finished with result: {$finalResult}");
                    
                    // Refresh round để đảm bảo có final_result
                    $round->refresh();
                    
                    // Log để debug
                    \Log::info("Round {$round->round_number} finished", [
                        'round_id' => $round->id,
                        'final_result' => $round->final_result,
                        'status' => $round->status,
                    ]);
                }
            } else if ($currentSecond > 0 && $currentSecond < 60) {
                // Update current second and result based on seed
                $randomResult = $this->getGemForSecond($round->seed, $currentSecond);
                
                $round->update([
                    'current_second' => $currentSecond,
                    'current_result' => $randomResult,
                ]);
            }
        }
        
        // Nếu round đã finish nhưng chưa process bets (safety check)
        if ($round->status === 'finished' && $round->final_result) {
            // Kiểm tra xem còn bets pending không
            $pendingBets = $round->bets()->where('status', 'pending')->count();
            if ($pendingBets > 0) {
                // Nếu còn bets pending, process lại
                $this->warn("Round {$round->round_number} has {$pendingBets} pending bets, processing...");
                $round->processBets();
            }
        }
        
        // Nếu round hiện tại đã finish và đã đến round mới
        // Round mới đã được tạo ở đầu function (dòng 99), chỉ cần refresh và start
        if ($round->round_number < $currentRoundNumber) {
            // Refresh để lấy round mới (đã được tạo ở đầu function)
            $round = Round::getOrCreateRoundByNumber($currentRoundNumber);
            if ($round->status === 'pending' && $currentSecond > 0) {
                $round->start();
                $this->info("Round {$round->round_number} started");
            }
        }
        
        return 0;
    }
    
    /**
     * Get gem type for a specific second based on seed
     * Must match client-side logic exactly
     */
    private function getGemForSecond($seed, $second)
    {
        if (!$seed) return 'thachanh';
        
        // Improved hash function with better distribution
        $string = $seed . '_' . $second;
        $hash = 0;
        for ($i = 0; $i < strlen($string); $i++) {
            $char = ord($string[$i]);
            $hash = (($hash << 5) - $hash) + $char;
            $hash = $hash & 0x7FFFFFFF; // Convert to 32bit integer
        }
        
        // Add second to hash for better variation
        $hash = ($hash * 31 + $second * 17) & 0x7FFFFFFF;
        
        // Convert to 1-100 range with better distribution
        $rand = (abs($hash) % 10000) % 100 + 1;
        
        $rates = [
            ['type' => 'thachanh', 'rate' => 30],
            ['type' => 'thachanhtim', 'rate' => 25],
            ['type' => 'ngusac', 'rate' => 20],
            ['type' => 'daquy', 'rate' => 15],
            ['type' => 'cuoc', 'rate' => 7],
            ['type' => 'kimcuong', 'rate' => 3],
        ];
        
        $cumulative = 0;
        foreach ($rates as $item) {
            $cumulative += $item['rate'];
            if ($rand <= $cumulative) {
                return $item['type'];
            }
        }
        
        return 'thachanh';
    }
}

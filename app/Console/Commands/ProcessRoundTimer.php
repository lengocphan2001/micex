<?php

namespace App\Console\Commands;

use App\Models\Round;
use App\Models\SystemSetting;
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
     * IMPORTANT: Use UTC timezone to match client-side calculation
     */
    private function calculateRoundNumber()
    {
        // Parse BASE_TIME as UTC (matching client: '2025-01-01T00:00:00Z')
        $baseTime = Carbon::parse(self::BASE_TIME . ' UTC')->timestamp;
        // Use UTC for now() to match client-side Date.now()
        $now = Carbon::now('UTC')->timestamp;
        $elapsed = $now - $baseTime;
        $totalCycle = self::ROUND_DURATION; // 60 giây mỗi cycle
        return floor($elapsed / $totalCycle) + 1;
    }
    
    /**
     * Calculate round deadline
     * Round duration: 60 giây
     */
    private function calculateRoundDeadline($roundNumber)
    {
        // Parse BASE_TIME as UTC (matching client)
        $baseTime = Carbon::parse(self::BASE_TIME . ' UTC')->timestamp;
        $totalCycle = self::ROUND_DURATION; // 60 giây mỗi cycle
        
        // Round start time = baseTime + (roundNumber - 1) * totalCycle
        $roundStartTime = $baseTime + (($roundNumber - 1) * $totalCycle);
        
        // Deadline = roundStartTime + ROUND_DURATION (60 giây)
        // Use UTC timezone to match client
        return Carbon::createFromTimestamp($roundStartTime + self::ROUND_DURATION, 'UTC');
    }
    
    /**
     * Calculate current second from deadline
     */
    private function calculateCurrentSecond($roundNumber)
    {
        $deadline = $this->calculateRoundDeadline($roundNumber);
        // Use UTC to match client-side calculation
        $now = Carbon::now('UTC');
        
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
        // Process independently per game (each game has its own round stream)
        $games = ['khaithac', 'xanhdo'];

        // Tính round number từ BASE_TIME (giống client) - shared timeline
        $currentRoundNumber = $this->calculateRoundNumber();
        $currentSecond = $this->calculateCurrentSecond($currentRoundNumber);

        foreach ($games as $gameKey) {
            // Get or create round cho từng game
            $round = Round::getOrCreateRoundByNumber($currentRoundNumber, $gameKey);

            // Nếu round chưa start và đã đến thời gian (currentSecond > 0)
            if ($round->status === 'pending' && $currentSecond > 0) {
                $round->start();
                $this->info("[{$gameKey}] Round {$round->round_number} started");
                continue;
            }

            // Nếu round đang running
            if ($round->status === 'running') {
                // Tính deadline để check xem round đã finish chưa
                $deadline = $this->calculateRoundDeadline($currentRoundNumber);
                // Use UTC to match client-side calculation
                $now = Carbon::now('UTC');
                $countdown = max(0, (int) floor(($deadline->timestamp - $now->timestamp)));

                // Nếu đã đến giây 60 hoặc quá deadline (countdown = 0), finish round
                if ($currentSecond >= 60 || $countdown <= 0) {
                    // Refresh round để lấy admin_set_result mới nhất từ database
                    $round->refresh();

                    // Ưu tiên admin_set_result nếu có, nếu không thì random dựa vào tổng tiền đặt cược
                    $finalResult = null;
                    if ($round->admin_set_result) {
                        $finalResult = $round->admin_set_result;
                        $this->info("[{$gameKey}] Round {$round->round_number} using admin_set_result: {$finalResult}");
                    } else {
                        $finalResult = $round->randomResultBasedOnBets();
                        $this->info("[{$gameKey}] Round {$round->round_number} using random result based on bets: {$finalResult}");
                    }

                    $round->finish($finalResult);
                    $this->info("[{$gameKey}] Round {$round->round_number} finished with result: {$finalResult}");

                    $round->refresh();
                    \Log::info("[{$gameKey}] Round {$round->round_number} finished", [
                        'round_id' => $round->id,
                        'final_result' => $round->final_result,
                        'admin_set_result' => $round->admin_set_result,
                        'status' => $round->status,
                    ]);
                } else if ($currentSecond > 0 && $currentSecond < 60) {
                    $randomResult = $this->getGemForSecond($round->seed, $currentSecond);
                    $round->update([
                        'current_second' => $currentSecond,
                        'current_result' => $randomResult,
                    ]);
                }
            }

            // Safety: nếu round đã finish nhưng vẫn còn bets pending
            if ($round->status === 'finished' && $round->final_result) {
                $pendingBets = $round->bets()->where('status', 'pending')->count();
                if ($pendingBets > 0) {
                    $this->warn("[{$gameKey}] Round {$round->round_number} has {$pendingBets} pending bets, processing...");
                    $round->processBets();
                }
            }
        }
        
        // Lưu ý: Round mới đã được tạo ở đầu function (dòng 99) với getOrCreateRoundByNumber
        // Không cần tạo lại ở đây vì getOrCreateRoundByNumber đã có lock và unique constraint
        // Nếu round hiện tại đã finish và đã đến round mới, round mới đã được tạo ở đầu function
        // Logic start round mới đã được xử lý ở dòng 105-108, không cần xử lý lại ở đây
        
        return 0;
    }
    
    /**
     * Get payout rates from database or use defaults
     */
    private function getPayoutRates()
    {
        return [
            'kcxanh' => (float) SystemSetting::getValue('gem_payout_rate_kcxanh', SystemSetting::getValue('gem_payout_rate_thachanh', 1.95)),
            'daquy' => (float) SystemSetting::getValue('gem_payout_rate_daquy', 5.95),
            'kcdo' => (float) SystemSetting::getValue('gem_payout_rate_kcdo', SystemSetting::getValue('gem_payout_rate_kimcuong', 1.95)),
        ];
    }
    
    /**
     * Calculate random rates based on payout rates
     * Đảm bảo: payout rate thấp → random rate cao, payout rate cao → random rate thấp
     * Tổng random rates = 100
     */
    private function calculateRandomRates()
    {
        $payoutRates = $this->getPayoutRates();
        
        // Tính nghịch đảo của payout rate (payout cao → giá trị thấp, payout thấp → giá trị cao)
        $inverseValues = [];
        $totalInverse = 0;
        
        foreach ($payoutRates as $type => $payoutRate) {
            // Nghịch đảo: 1 / payout_rate
            // Payout rate càng cao → inverse càng thấp → random rate càng thấp
            $inverseValues[$type] = 1 / $payoutRate;
            $totalInverse += $inverseValues[$type];
        }
        
        // Chuyển đổi thành tỉ lệ phần trăm (tổng = 100)
        $randomRates = [];
        foreach ($inverseValues as $type => $inverseValue) {
            $randomRates[$type] = round(($inverseValue / $totalInverse) * 100, 2);
        }
        
        // Đảm bảo tổng = 100 (điều chỉnh giá trị cuối cùng nếu cần)
        $sum = array_sum($randomRates);
        if ($sum != 100) {
            $diff = 100 - $sum;
            // Điều chỉnh giá trị đầu tiên
            $firstType = array_key_first($randomRates);
            $randomRates[$firstType] += $diff;
        }
        
        return $randomRates;
    }
    
    /**
     * Get gem type for a specific second based on seed
     * Must match client-side logic exactly
     */
    private function getGemForSecond($seed, $second)
    {
        if (!$seed) return 'kcxanh';
        
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
        
        // Sử dụng random rates được tính từ payout rates
        $randomRates = $this->calculateRandomRates();
        $cumulative = 0;
        foreach ($randomRates as $type => $rate) {
            $cumulative += $rate;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        return 'kcxanh';
    }
}

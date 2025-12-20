<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateTransferCodesForUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-transfer-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate transfer codes for users that do not have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('transfer_code')->get();
        
        if ($users->isEmpty()) {
            $this->info('All users already have transfer codes.');
            return 0;
        }

        $this->info("Generating transfer codes for {$users->count()} users...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            // Generate unique transfer code
            do {
                $transferCode = '0x' . substr(md5(uniqid(rand(), true)), 0, 12);
            } while (User::where('transfer_code', $transferCode)->exists());

            $user->transfer_code = $transferCode;
            $user->save();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Transfer codes generated successfully!');

        return 0;
    }
}

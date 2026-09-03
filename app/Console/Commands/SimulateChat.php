<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class SimulateChat extends Command
{
    protected $signature = 'chat:simulate';
    protected $description = 'Run AI agents E2E chat simulation over WebSockets';

    public function handle()
    {
        $this->info('Starting AI Agents Chat Simulation...');

        // تشغيل Dusk Test
        $exitCode = $this->call('dusk', [
            '--filter' => 'ChatSimulationTest'
        ]);

        if ($exitCode === 0) {
            $this->info('Chat simulation completed successfully!');
        } else {
            $this->error('Simulation failed. Check Dusk screenshots.');
        }
    }
}

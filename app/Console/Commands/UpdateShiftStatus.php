<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShiftStatusService;

class UpdateShiftStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động cập nhật trạng thái ca làm việc (scheduled -> active -> completed)';

    /**
     * Execute the console command.
     */
    protected $shiftStatusService;

    public function __construct(ShiftStatusService $shiftStatusService)
    {
        parent::__construct();
        $this->shiftStatusService = $shiftStatusService;
    }

    public function handle()
    {
        $result = $this->shiftStatusService->updateShiftStatuses();
        
        if ($result['activated'] > 0 || $result['completed'] > 0) {
            $this->info("Đã cập nhật {$result['activated']} ca sang 'active' và {$result['completed']} ca sang 'completed'.");
        } else {
            $this->info("Không có ca nào cần cập nhật.");
        }

        return 0;
    }
}

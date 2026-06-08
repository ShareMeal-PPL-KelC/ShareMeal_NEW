<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CheckOrderDelays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sharemeal:check-order-delays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mendeteksi pesanan yang diam di status diproses selama lebih dari 5 menit dan menandainya sebagai delay.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = now()->subMinutes(5);
        $orders = Order::where('status', 'processing')
            ->where('is_delayed', false)
            ->where('updated_at', '<=', $cutoff)
            ->get();

        foreach ($orders as $order) {
            $order->update([
                'is_delayed' => true,
                'delayed_at' => now(),
            ]);
            $this->info("Pesanan #{$order->orderId} ditandai delay.");
        }

        $this->info("Pengecekan delay selesai. Jumlah pesanan baru ditandai delay: " . $orders->count());
    }
}

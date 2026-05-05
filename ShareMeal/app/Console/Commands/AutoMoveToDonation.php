<?php

namespace App\Console\Commands;

use App\Services\AutoDonationService;
use Illuminate\Console\Command;

class AutoMoveToDonation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sharemeal:auto-donate {--mitra-id= : Batasi auto-donasi untuk satu Mitra}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis memindahkan produk yang akan kedaluwarsa dalam 2 jam ke daftar donasi.';

    /**
     * Execute the console command.
     */
    public function handle(AutoDonationService $autoDonationService)
    {
        $mitraId = $this->option('mitra-id') ? (int) $this->option('mitra-id') : null;
        $result = $autoDonationService->processProducts($mitraId);

        $this->info("Berhasil menandai {$result['expired']} produk expired.");
        $this->info("Berhasil memindahkan {$result['donated']} produk ke daftar donasi.");
    }
}

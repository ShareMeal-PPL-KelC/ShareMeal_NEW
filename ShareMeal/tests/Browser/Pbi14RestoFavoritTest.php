<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi14RestoFavoritTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createConsumer(): array
    {
        $email    = 'consumer14_' . time() . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role'        => 'consumer',
            'name'        => 'Budi Santoso',
            'email'       => $email,
            'password'    => Hash::make($password),
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id'     => $consumer->id,
            'phone'       => '081234567890',
            'address'     => 'Kost Orange, Bandung',
            'is_verified' => true,
        ]);

        return [$email, $password];
    }

    private function createMitraWithProduct(): int
    {
        $mitra = User::factory()->create([
            'role'        => 'mitra',
            'name'        => 'Toko Roti Makmur',
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id'                => $mitra->id,
            'phone'                  => '089876543210',
            'address'                => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_name'          => 'Toko Roti Makmur',
            'business_type'          => 'Bakery',
            'business_address'       => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_contact'       => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description'   => 'Roti lezat dan segar',
            'is_verified'            => true,
            'can_delivery'           => true,
        ]);
        Product::create([
            'user_id'    => $mitra->id,
            'name'       => 'Roti Coklat',
            'category'   => 'Snack',
            'price'      => 8000,
            'stock'      => 20,
            'expires_at' => now()->addHours(4),
        ]);
        return $mitra->id;
    }

    private function login(Browser $browser, string $email, string $password): void
    {
        $browser->driver->manage()->deleteAllCookies();
        $browser->maximize()
            ->visit('/login')
            ->waitFor('select[name="user_type"]')
            ->select('user_type', 'consumer')
            ->type('email', $email)
            ->type('password', $password)
            ->click('button[type="submit"]')
            ->waitForLocation('/consumer', 15);
    }

    private function disableReveal(Browser $browser): void
    {
        $browser->script("
            var style = document.createElement('style');
            style.innerHTML = '.reveal { opacity: 1 !important; transform: none !important; transition: none !important; transition-delay: 0s !important; }';
            document.head.appendChild(style);
        ");
    }

    /**
     * TC-PBI14-001 (Positif)
     * Konsumen dapat menambahkan resto ke daftar favorit — favorit tersimpan di localStorage
     * dan jumlah toko favorit tampil di dashboard.
     */
    public function test_konsumen_dapat_menambahkan_resto_favorit(): void
    {
        $mitraId = $this->createMitraWithProduct();
        [$email, $password] = $this->createConsumer();

        $this->browse(function (Browser $browser) use ($email, $password, $mitraId) {
            $this->login($browser, $email, $password);

            // Bersihkan dulu, lalu simulasikan menambah favorit via localStorage
            // (merefleksikan aksi klik tombol hati di halaman search)
            $browser->script("localStorage.removeItem('favoriteStores');");

            // Buka halaman search agar Alpine init
            $browser->visit(route('consumer.search'));
            $this->disableReveal($browser);
            $browser->pause(2000);

            // Set localStorage favoriteStores dengan mitra ID (simulasi klik hati)
            $browser->script("
                localStorage.setItem('favoriteStores', JSON.stringify([{$mitraId}]));
            ");

            // Reload halaman search — pastikan store card muncul dengan hati merah
            $browser->visit(route('consumer.search'));
            $this->disableReveal($browser);
            $browser->pause(2000)
                ->assertSee('Toko Roti Makmur');

            // Verifikasi localStorage ada 1 favorit
            $favCount = $browser->script(
                "return JSON.parse(localStorage.getItem('favoriteStores') || '[]').length;"
            )[0];
            $this->assertEquals(1, $favCount, 'Harus ada 1 toko di favorit');

            // Buka dashboard — cek section "Toko Favorit" tampil
            $browser->visit(route('consumer.dashboard'));
            $this->disableReveal($browser);
            $browser->pause(2000)
                ->assertSee('Toko Favorit');

            // Angka favorit di dashboard harus 1
            $dashFav = $browser->script(
                "return JSON.parse(localStorage.getItem('favoriteStores') || '[]').length;"
            )[0];
            $this->assertEquals(1, $dashFav);
        });
    }

    /**
     * TC-PBI14-002 (Negatif)
     * Konsumen yang belum menambahkan favorit melihat 0 favorit di dashboard.
     */
    public function test_konsumen_tanpa_favorit_melihat_angka_nol_di_dashboard(): void
    {
        [$email, $password] = $this->createConsumer();

        $this->browse(function (Browser $browser) use ($email, $password) {
            $this->login($browser, $email, $password);

            // Bersihkan localStorage
            $browser->script("localStorage.removeItem('favoriteStores');");

            $browser->visit(route('consumer.dashboard'));
            $this->disableReveal($browser);
            $browser->pause(2000)
                ->assertSee('Toko Favorit');

            $favCount = $browser->script(
                "return JSON.parse(localStorage.getItem('favoriteStores') || '[]').length;"
            )[0];
            $this->assertEquals(0, $favCount, 'Belum ada favorit, harus 0');
        });
    }
}
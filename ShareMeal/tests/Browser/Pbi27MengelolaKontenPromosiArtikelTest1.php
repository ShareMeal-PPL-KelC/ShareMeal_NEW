<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi27MengelolaKontenPromosiArtikelTest1 extends DuskTestCase
{
    /**
     * Test that an admin can create a new article/promotion.
     */
    public function test_admin_can_create_article()
    {
        $this->browse(function (Browser $browser) {
            // Unique title to avoid collisions
            $title = 'Dusk Test Artikel ' . now()->format('YmdHis');

            // 1. Visit login page and authenticate as admin
            $browser->visit('/login')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    // Ensure admin role is selected – if the selector exists it will be set, otherwise ignored
                    ->whenAvailable('select[name="user_type"]', function ($select) {
                        $select->select('admin');
                    })
                    ->press('Masuk')
                    ->assertPathIs('/admin');

            // 2. Go to the admin education (article) management page
            $browser->visit('/admin/education')
                    ->assertPathIs('/admin/education');

            // 3. Open the "Buat Edukasi Baru" modal
            $browser->clickLink('Buat Edukasi Baru');

            // 4. Fill in the form fields
            $browser->type('title', $title)
                    ->select('category', 'Artikel')
                    ->select('status', 'Draft')
                    ->type('content', 'Konten artikel yang dibuat oleh Laravel Dusk untuk PBI 27.')
                    // Submit the form – button text changes based on mode, for creation it is "Terbitkan Edukasi"
                    ->press('Terbitkan Edukasi')
                    // Wait for the page to refresh / modal to close
                    ->pause(1000);

            // 5. Verify that the newly created article appears in the list
            $browser->assertSee($title);
        });
    }
}

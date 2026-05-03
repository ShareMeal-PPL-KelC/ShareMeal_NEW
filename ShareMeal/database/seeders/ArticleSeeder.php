<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => '5 Cara Mengurangi Food Waste di Rumah',
                'category' => 'Tips',
                'status' => 'Published',
                'published_on' => now(),
                'author' => 'Admin ShareMeal',
                'content' => 'Berikut adalah 5 cara efektif mengurangi sampah makanan: 1. Buat daftar belanja, 2. Simpan makanan dengan benar, 3. Pahami label kedaluwarsa, 4. Olah sisa makanan (Recycling food), 5. Berbagi dengan sesama.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800',
                'read_time' => '3 min read',
            ],
            [
                'title' => 'Dampak Sampah Makanan terhadap Lingkungan',
                'category' => 'Edukasi',
                'status' => 'Published',
                'published_on' => now()->subDays(2),
                'author' => 'Lestari Hijau',
                'content' => 'Sampah makanan yang membusuk di TPA menghasilkan gas metana yang 25 kali lebih berbahaya dari CO2 bagi pemanasan global. Selain itu, membuang makanan berarti membuang sumber daya air dan lahan yang digunakan untuk produksinya.',
                'image' => 'https://images.unsplash.com/photo-1593113702251-272b1bc414a9?auto=format&fit=crop&q=80&w=800',
                'read_time' => '5 min read',
            ],
            [
                'title' => 'Panduan Lengkap Donasi Makanan Aman',
                'category' => 'Panduan',
                'status' => 'Published',
                'published_on' => now()->subDays(5),
                'author' => 'ShareMeal Team',
                'content' => 'Cara donasi makanan yang aman: Pastikan makanan belum basi, gunakan kemasan yang bersih, sertakan informasi alergen, dan serahkan kepada lembaga sosial melalui platform ShareMeal.',
                'image' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&q=80&w=800',
                'read_time' => '4 min read',
            ],
            [
                'title' => 'Mengapa Donasi Lebih Baik Daripada Membuang?',
                'category' => 'Artikel',
                'status' => 'Published',
                'published_on' => now()->subDays(10),
                'author' => 'Budi Santoso',
                'content' => 'Donasi makanan tidak hanya membantu mengurangi lapar, tetapi juga menumbuhkan rasa kepedulian sosial di masyarakat.',
                'image' => 'https://images.unsplash.com/photo-1599059813005-11265ba4b4ce?auto=format&fit=crop&q=80&w=800',
                'read_time' => '2 min read',
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(['title' => $article['title']], $article);
        }
    }
}

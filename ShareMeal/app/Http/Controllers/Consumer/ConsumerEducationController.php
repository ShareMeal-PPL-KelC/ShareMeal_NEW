<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Support\ShareMealState;
use Illuminate\View\View;

class ConsumerEducationController extends Controller
{
    public function education(): View
    {
        $articles = collect(ShareMealState::get('articles'))
            ->filter(fn($a) => strtolower($a['status']) === 'published')
            ->values()
            ->map(fn($a) => [
                'id'       => $a['id'],
                'title'    => $a['title'],
                'category' => $a['category'],
                'readTime' => $a['read_time'] ?? '4 min read',
                'date'     => $a['date'],
                'author'   => $a['author'],
                'image'    => $a['image'] ?? '',
                'content'  => $a['content'],
            ])
            ->values();

        $categories = array_values(array_unique(
            array_merge(['Semua'], $articles->pluck('category')->toArray())
        ));

        $stats = (object) [
            'readCount' => 12,
            'level'     => 'Eco Warrior',
            'points'    => 450,
        ];

        return view('consumer.education', compact('articles', 'categories', 'stats'));
    }

    public function showArticle($id): View
    {
        $allArticles = collect(ShareMealState::get('articles'))
            ->filter(fn($a) => strtolower($a['status']) === 'published');

        $raw = $allArticles->firstWhere('id', (int) $id);

        if (!$raw) {
            abort(404);
        }

        $article = (object) [
            'id'       => $raw['id'],
            'title'    => $raw['title'],
            'category' => $raw['category'],
            'readTime' => $raw['read_time'] ?? '4 min read',
            'date'     => $raw['date'],
            'author'   => $raw['author'],
            'image'    => $raw['image'],
            'content'  => $raw['content'],
        ];

        $relatedArticles = $allArticles
            ->filter(fn($a) => $a['id'] !== (int) $id)
            ->take(2)
            ->map(fn($a) => (object) $a);

        return view('consumer.article', compact('article', 'relatedArticles'));
    }
}

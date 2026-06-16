<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Article;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminEducationController extends Controller
{
    public function adminEducation(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $tab = (string) $request->query('tab', 'all');
        $articles = collect(ShareMealState::get('articles'))->filter(function ($article) use ($search, $tab) {
            $matchesSearch = $search === '' || str_contains(strtolower($article['title']), strtolower($search)) || str_contains(strtolower($article['category']), strtolower($search));
            $matchesTab = $tab === 'all' || strtolower($article['status']) === $tab;
            return $matchesSearch && $matchesTab;
        })->values();

        return view('pages.admin.education', $this->dashboardData('admin', 'Edukasi Lingkungan', 'Kelola artikel, tips, dan panduan edukasi seputar food waste') + [
            'articles' => $articles,
            'allArticles' => ShareMealState::get('articles'),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function adminEducationStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'status'   => ['required', 'string'],
            'content'  => ['required', 'string'],
            'image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('articles', 'public');
        }

        ShareMealState::saveArticle($data);

        AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'education_create',
            'details' => 'Membuat artikel edukasi baru: "' . $data['title'] . '"',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function adminEducationUpdate(Request $request, int $articleId): RedirectResponse
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'status'   => ['required', 'string'],
            'content'  => ['required', 'string'],
            'image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $oldArticle = Article::find($articleId);
            if ($oldArticle && $oldArticle->image && Storage::disk('public')->exists($oldArticle->image)) {
                Storage::disk('public')->delete($oldArticle->image);
            }
            $data['image_path'] = $request->file('image')->store('articles', 'public');
        }

        ShareMealState::saveArticle($data, $articleId);

        AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'education_update',
            'target_id' => $articleId,
            'details' => 'Memperbarui artikel edukasi: "' . $data['title'] . '"',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function adminEducationDelete(int $articleId): RedirectResponse
    {
        $article = Article::find($articleId);
        $title = $article ? $article->title : 'Artikel #' . $articleId;
        ShareMealState::deleteArticle($articleId);

        AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
            'action' => 'education_delete',
            'target_id' => $articleId,
            'details' => 'Menghapus artikel edukasi: "' . $title . '"',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}

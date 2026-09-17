<?php

namespace App\Http\Controllers;

use App\Models\EducationalStage;
use App\Models\Event;
use App\Models\Feature;
use App\Models\GalleryItem;
use App\Models\HomepageSetting;
use App\Models\NewsPost;
use App\Models\SiteSetting;
use App\Models\Statistic;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $newsPosts = NewsPost::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('sort_order')
            ->orderByRaw('published_at IS NULL DESC')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $events = Event::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('sort_order')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->limit(4)
            ->get();

        $stages = EducationalStage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $statistics = Statistic::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $features = Feature::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $galleryItems = GalleryItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (GalleryItem $item) => $item->imageUrl() !== null)
            ->take(5)
            ->values();

        return view('pages.home', [
            'siteSettings' => SiteSetting::current(),
            'homepageSettings' => HomepageSetting::current(),
            'newsPosts' => $newsPosts,
            'events' => $events,
            'stages' => $stages,
            'features' => $features,
            'galleryItems' => $galleryItems,
            'statistics' => $statistics,
        ]);
    }
}

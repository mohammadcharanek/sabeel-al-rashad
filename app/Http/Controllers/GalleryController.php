<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\HomepageSetting;
use App\Models\SiteSetting;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleryItems = GalleryItem::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (GalleryItem $item) => $item->imageUrl() !== null)
            ->values();

        return view('pages.gallery', [
            'siteSettings' => SiteSetting::current(),
            'homepageSettings' => HomepageSetting::current(),
            'galleryItems' => $galleryItems,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\HomepageSetting;
use App\Models\MediaFolder;
use App\Models\SiteSetting;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return $this->gallery();
    }

    public function show(MediaFolder $folder): View
    {
        abort_unless($folder->is_published && $folder->media_type === 'photo', 404);

        return $this->gallery($folder);
    }

    private function gallery(?MediaFolder $folder = null): View
    {
        $galleryItems = GalleryItem::query()
            ->published()
            ->where('media_folder_id', $folder?->id)
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
            'folder' => $folder,
            'folders' => MediaFolder::query()->published()->where('media_type', 'photo')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}

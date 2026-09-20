<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;
use App\Models\MediaFolder;
use App\Models\SiteSetting;
use App\Models\Video;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        return $this->gallery();
    }

    public function show(MediaFolder $folder): View
    {
        abort_unless($folder->is_published && $folder->media_type === 'video', 404);

        return $this->gallery($folder);
    }

    private function gallery(?MediaFolder $folder = null): View
    {
        return view('pages.videos', [
            'siteSettings' => SiteSetting::current(),
            'homepageSettings' => HomepageSetting::current(),
            'folder' => $folder,
            'folders' => MediaFolder::query()->published()->where('media_type', 'video')->orderBy('sort_order')->orderBy('id')->get(),
            'videos' => Video::query()->published()->where('media_folder_id', $folder?->id)
                ->orderBy('sort_order')->orderBy('id')->get()
                ->filter(fn (Video $video): bool => $video->videoUrl() !== null)->values(),
        ]);
    }
}

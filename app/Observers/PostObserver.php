<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PostObserver
{
    public function creating(Post $post): void
    {
        if (empty($post->user_id)) {
            $post->user_id = Auth::user()->uuid;
        }
    }

    public function updating(Post $post): void
    {
        if ($post->isDirty('image')) {
            $originalimage = $post->getOriginal('image');

            if ($originalimage && Storage::disk('public')->exists($originalimage)) {
                Storage::disk('public')->delete($originalimage);
            }
        }
    }

    public function deleting(Post $post): void
    {
        if ($post->image) {
            if (Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\CategorySlugChange;

class CategoryObserver
{
    /**
     * Handle the Category "updating" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function updating(Category $category)
    {
        // Cek jika slug kategori mengalami perubahan
        if ($category->isDirty('slug')) {
            // Ambil slug lama dan baru
            $oldSlug = $category->getOriginal('slug');
            $newSlug = $category->slug;

            // Simpan perubahan slug ke tabel category_slug_changes
            CategorySlugChange::create([
                'category_id' => $category->id,
                'old_slug' => $oldSlug,
                'new_slug' => $newSlug,
            ]);
        }
    }
}


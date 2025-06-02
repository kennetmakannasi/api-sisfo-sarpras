<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\ItemSkuChange;

class ItemObserver
{
    /**
     * Handle the Item "updating" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function updating(Item $item)
    {
        // Cek apakah SKU item berubah
        if ($item->isDirty('sku')) {
            // Dapatkan SKU lama dan SKU baru
            $oldSku = $item->getOriginal('sku');
            $newSku = $item->sku;

            // Catat perubahan SKU (bisa menggunakan log atau menyimpannya ke dalam tabel lain)
            ItemSkuChange::create([
                'item_id' => $item->id,
                'old_sku' => $oldSku,
                'new_sku' => $newSku,
            ]);

            // Opsional: Memperbarui kolom updated_at jika SKU berubah
            $item->updated_at = now();
        }
    }
}

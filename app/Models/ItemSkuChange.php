<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSkuChange extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak menggunakan plural dari model secara otomatis
    protected $table = 'item_sku_changes';

    // Tentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'item_id',
        'old_sku',
        'new_sku',
    ];

    /**
     * Relasi dengan model Item.
     * Setiap perubahan SKU terkait dengan satu item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

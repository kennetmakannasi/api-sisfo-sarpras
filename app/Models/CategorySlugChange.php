<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorySlugChange extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak menggunakan konvensi Laravel
    protected $table = 'category_slug_changes';

    // Tentukan kolom yang bisa diisi secara mass-assignment
    protected $fillable = [
        'category_id',
        'old_slug',
        'new_slug',
    ];

    // Relasi dengan model Category (optional, jika ingin relasi)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}


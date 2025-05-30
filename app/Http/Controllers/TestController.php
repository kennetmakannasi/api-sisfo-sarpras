<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Shuchkin\SimpleXLSXGen;
use App\Models\User;

class TestController extends Controller
{
    public function exportSimpleData()
{
    $users = User::all(['username', 'created_at']); // Ambil kolom yang diinginkan

        // Header kolom Excel
        $data = [
            ['Nama', 'Dibuat Pada']
        ];

        // Loop isi data dari DB
        foreach ($users as $user) {
            $data[] = [
                $user->username,
                $user->created_at->format('Y-m-d H:i:s'),
            ];
        }

    $filename = 'data_user.xlsx';
    $xlsx = SimpleXLSXGen::fromArray($data);
    $filePath = public_path($filename); // Path lengkap ke folder public
    $xlsx->saveAs($filePath);

    return redirect(asset($filename));
}
}

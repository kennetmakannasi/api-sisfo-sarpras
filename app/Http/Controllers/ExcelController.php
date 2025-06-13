<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Borrowing;
use App\Models\Returning;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Storage;

class ExcelController extends Controller
{
    public function exportUsers()
    {
        $users = User::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Users');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->fromArray(['ID', 'Name', 'Created At'], null, 'A2');
        $sheet->getColumnDimension('A')->setWidth(5); 
        $sheet->getColumnDimension('B')->setWidth(25); 
        $sheet->getColumnDimension('C')->setWidth(25); 

        $row = 3;
        foreach ($users as $user) {
            $sheet->setCellValue("A$row", $user->id);
            $sheet->setCellValue("B$row", $user->username);
            $sheet->setCellValue("C$row", $user->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("A2:C$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $filename = 'data_users_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/private/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function exportItems()
    {
        $items = Item::with(['categories:id,name'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Items');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->fromArray(['ID', 'SKU', 'Name', 'Image Url', 'Stock', 'Category Names'], null, 'A2');

        $sheet->getColumnDimension('A')->setWidth(5); 
        $sheet->getColumnDimension('B')->setWidth(25); 
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);  
        $sheet->getColumnDimension('E')->setWidth(10); 
        $sheet->getColumnDimension('F')->setWidth(25); 

        $row = 3;

        foreach ($items as $item) {
            $categoryNames = $item->categories->pluck('name')->implode(', ');
            $sheet->setCellValue("A$row", $item->id);
            $sheet->setCellValue("B$row", $item->sku);
            $sheet->setCellValue("C$row", $item->name);
            $sheet->setCellValue("D$row", $item->image_url);
            $sheet->setCellValue("E$row", $item->stock);
            $sheet->setCellValue("F$row", $categoryNames);
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("A2:F$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $filename = 'data_items_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/private/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function exportCategories()
    {
        $categories = Category::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $sheet->setCellValue('A1', 'Categories');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->fromArray(['ID', 'Slug', 'Name'], null, 'A2');
        $sheet->getColumnDimension('A')->setWidth(5); 
        $sheet->getColumnDimension('B')->setWidth(25); 
        $sheet->getColumnDimension('C')->setWidth(25); 

        $row = 3;
        foreach ($categories as $category) {
            $sheet->setCellValue("A$row", $category->id);
            $sheet->setCellValue("B$row", $category->slug);
            $sheet->setCellValue("C$row", $category->name);
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("A2:C$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $filename = 'data_categories_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/private/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function exportBorrows()
    {
        $borrows = Borrowing::with(['user:id,username', 'item:id,name'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Borrows');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->fromArray(['ID', 'Item', 'User', 'Quantity', 'Status', 'Created At'], null, 'A2');
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25); 
        $sheet->getColumnDimension('C')->setWidth(25); 
        $sheet->getColumnDimension('D')->setWidth(10); 
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20); 

        $row = 3;
        foreach ($borrows as $borrow) {
            $sheet->setCellValue("A$row", $borrow->id);
            $sheet->setCellValue("B$row", $borrow->item->name ?? 'N/A');
            $sheet->setCellValue("C$row", $borrow->user->username ?? 'N/A');
            $sheet->setCellValue("D$row", $borrow->quantity);
            $sheet->setCellValue("E$row", $borrow->status);
            $sheet->setCellValue("F$row", $borrow->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("A2:F$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $filename = 'borrowing_data_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/private/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

   public function exportReturns()
{
    $returns = Returning::with([
        'borrowing.item:id,name',
        'borrowing.user:id,username'
    ])->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // 👇 Tambahkan judul di baris 1
    $sheet->setCellValue('A1', 'Returns');
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // 👇 Header kolom pindah ke baris 2
    $sheet->fromArray(['ID', 'Borrow ID', 'Item', 'User', 'Returned Quantity', 'Status'], null, 'A2');

    // Set lebar kolom
    $sheet->getColumnDimension('A')->setWidth(5); 
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(25); 
    $sheet->getColumnDimension('D')->setWidth(25); 
    $sheet->getColumnDimension('E')->setWidth(18); 
    $sheet->getColumnDimension('F')->setWidth(15); 

    // 👇 Mulai data dari baris 3
    $row = 3;
    foreach ($returns as $return) {
        $borrow = $return->borrowing;
        $itemName = $borrow->item->name ?? 'N/A';
        $username = $borrow->user->username ?? 'N/A';
        $status = $borrow->status ?? 'N/A';

        $sheet->setCellValue("A$row", $return->id);
        $sheet->setCellValue("B$row", $return->borrow_id);
        $sheet->setCellValue("C$row", $itemName);
        $sheet->setCellValue("D$row", $username);
        $sheet->setCellValue("E$row", $return->returned_quantity);
        $sheet->setCellValue("F$row", $status);
        $row++;
    }

    $lastRow = $row - 1;

    // 👇 Tambahkan border ke semua sel dari A2 sampai F...
    $sheet->getStyle("A2:F$lastRow")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ]);

    // 👇 Warnai header baris (baris 2) dengan kuning
    $sheet->getStyle("A1:F$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

    $filename = 'returning_data_' . date('Ymd_His') . '.xlsx';
    $filePath = storage_path('app/private/' . $filename);

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    return response()->download($filePath)->deleteFileAfterSend(true);
}
}

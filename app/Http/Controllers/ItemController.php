<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Utility\ApiResponse;
use App\Utility\Formatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Item::with("categories");

        if(request()->get('sort') === 'asc'){
            $query->orderBy('id', 'asc');
        }

        if(request()->get('sort') === 'desc'){
            $query->orderBy('id', 'desc');
        }

        if (request()->filled('category')) {
        $query->whereHas('categories', function($query) {
            $query->where('name', 'LIKE', '%' . request()->category . '%');
        });
    }

        $items = $query->get();

        return ApiResponse::send(200, "Item list retrieved", null, $items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "stock" => "required|integer",
            "image" => "sometimes|image",
            "category_slugs" => "sometimes|string"
        ]);

        if ($validator->fails()) {
            return ApiResponse::send(422, "Validation failed", $validator->errors()->all());
        }

        $sku = Formatter::removeVowels(str_replace(" ", "-", $request->name));
        if (Item::query()->where("sku", $sku)->exists()) {
            return ApiResponse::send(403, "Item already existed");
        }

        $image_url = null;

        if ($request->hasFile("image")) {
            $imageFile = $request->file("image");
            $path = "item-images";
            $fileName = $sku . "." . $imageFile->getClientOriginalExtension();
            $storedPath =$imageFile->storeAs($path, $fileName, "public");
            $image_url = url(Storage::url($storedPath));
        }

        $newItem = Item::query()->create([
            "sku" => $sku,
            "name" => $request->name,
            "image_url" => $image_url,
            "stock" => $request->stock
        ]);

        if ($request->has("category_slugs")) {
            foreach (explode(",", $request->category_slugs) as $category_slug) {
                $category_id = Category::query()->where("slug", $category_slug)->first()->id;
                if (is_null($category_id)) {
                    break;
                }
                ItemCategory::query()->create([
                    "category_id" => $category_id,
                    "item_id" => $newItem->id
                ]);
            }
        }

        return ApiResponse::send(200, "Item created", null, $newItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $sku)
    {
        $item = Item::query()->with("categories")->where("sku", $sku)->first();

        if (is_null($item)) {
            return ApiResponse::send(404, "Item not found");
        }

        return ApiResponse::send(200, "Item retrieved", null, $item);
    }

    /**
     * Update the specified resource in storage.
    */
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $sku)
    {
        $item = Item::query()->where("sku", $sku)->first();

        if (is_null($item)) {
            return ApiResponse::send(404, "Item not found");
        }

        $item->delete();

        return ApiResponse::send(200, "Item removed");
    }

    public function updateItemData(Request $request, string $sku)
{
    $item = Item::query()->where("sku", $sku)->first();

    if (is_null($item)) {
        return ApiResponse::send(404, "Item not found");
    }

    // Validasi gabungan
    $validator = Validator::make($request->all(), [
        "name" => "nullable",
        "stock" => "nullable",
        "category_slugs" => "nullable",
        "image" => "nullable", // ⬅ tambahkan validasi image opsional
    ]);

    if ($validator->fails()) {
        return ApiResponse::send(422, "Validation failed", $validator->errors()->all());
    }

    $newStock = $item->stock;
    if ($request->has("stock")) {
        $newStock = $request->stock;
    }

    $newSku = $item->sku;
    if ($request->has("name")) {
        $newSku = Formatter::removeVowels(str_replace(" ", "-", $request->name));

        if ($item->sku == $newSku && $item->stock == $newStock && !$request->hasFile("image")) {
            return ApiResponse::send(200, "Nothing changes lah");
        }

        if (Item::query()->where("sku", $newSku)->exists() && $newSku !== $item->sku) {
            return ApiResponse::send(403, "Item already existed");
        }
    }

    // Proses relasi kategori
    if ($request->has("category_slugs")) {
        foreach (explode(",", $request->category_slugs) as $category_slug) {
            $category = Category::query()->where("slug", $category_slug)->first();
            if ($category) {
                ItemCategory::query()->updateOrCreate([
                    "category_id" => $category->id,
                    "item_id" => $item->id
                ]);
            }
        }
    }

    $cred = $validator->validated();
    $cred["sku"] = $newSku;

    // Proses upload image jika ada
    if ($request->hasFile("image")) {
        $imageFile = $request->file("image");
        $path = "item-images";
        $fileName = $newSku . "." . $imageFile->getClientOriginalExtension(); // pakai new SKU agar konsisten
        $storedPath = $imageFile->storeAs($path, $fileName, "public");
        $image_url = url(Storage::url($storedPath));
        $cred["image_url"] = $image_url;
    }

    // Update item
    $item->update($cred);

    return ApiResponse::send(200, "Item updated", null, $item);
}

}

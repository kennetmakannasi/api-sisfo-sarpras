<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->group(function () {
   Route::post("login", [\App\Http\Controllers\authController::class, "login"]);
   Route::post("register", [\App\Http\Controllers\authController::class, "register"]);
   Route::get("logout", [\App\Http\Controllers\authController::class, "logout"])->middleware("need-token");
   Route::get("self", [\App\Http\Controllers\authController::class, "self"])->middleware("need-token");
});

Route::middleware("need-token")->group(function () {
    Route::middleware("admin-only")->group(function(){
        Route::prefix("admin")->group(function () {
            Route::prefix("dashboard")->group(function(){
                Route::get("",[\App\Http\Controllers\DashboardController::class,"main"]);
                Route::get("borrowing", [\App\Http\Controllers\DashboardController::class, "borrowing"]);    
                Route::get("returning",[\App\Http\Controllers\DashboardController::class, "returning"]);
                Route::get("borrowingByTime",[\App\Http\Controllers\DashboardController::class, "borrowingByTime"]);
                Route::get("categoryItemCount",[\App\Http\Controllers\DashboardController::class, "CategoryItemCount"]);
            });
            // Route::get("dl", [\App\Http\Controllers\TestController::class, "exportSimpleData"]);

            Route::apiResource("users", \App\Http\Controllers\UserController::class);
            Route::apiResource("categories", \App\Http\Controllers\CategoryController::class);

            Route::apiResource("items", \App\Http\Controllers\ItemController::class);
            Route::post("items/{sku}/change-image", [\App\Http\Controllers\ItemController::class, "changeImage"]);

            Route::apiResource("borrows", \App\Http\Controllers\BorrowingController::class)->only(["index","show"]);
            Route::apiResource("returns", \App\Http\Controllers\ReturningController::class)->only(["index","show"]);

            Route::patch("borrows/{id}/approve", [\App\Http\Controllers\BorrowingController::class, "approve"]);
            Route::patch("borrows/{id}/reject", [\App\Http\Controllers\BorrowingController::class, "reject"]);

            Route::patch("returns/{id}/approve", [\App\Http\Controllers\ReturningController::class, "approve"]);
            Route::patch("returns/{id}/reject", [\App\Http\Controllers\ReturningController::class, "reject"]);
        });
    });
    
    Route::prefix("user")->group(function () {
        Route::get("items", [\App\Http\Controllers\ItemController::class, "index"]);
        Route::get("categories", [\App\Http\Controllers\CategoryController::class, "index"]);
        Route::get("borrow-history", [\App\Http\Controllers\BorrowingController::class, "userBorrowHistory"]);
        Route::post("borrow-request", [\App\Http\Controllers\BorrowingController::class, "borrowRequest"]);

        Route::get("return-history", [\App\Http\Controllers\ReturningController::class, "userReturnHistory"]);
        Route::post("return-request", [\App\Http\Controllers\ReturningController::class, "returnRequest"]);
    });
});


Route::fallback(function () {
    return \App\Utility\ApiResponse::send(404, "Route not found");
});

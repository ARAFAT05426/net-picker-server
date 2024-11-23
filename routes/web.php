<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/suggestions', [ProductController::class, 'searchSuggestions']);

Route::get('/products/{id}', [ProductController::class, 'show']);

Route::post('/contact', [ContactController::class, 'sendMail']);

Route::post('/log-visitor', [VisitorController::class, 'logVisitor']);

// Route to get visitor stats
Route::get('/visitor-stats', [VisitorController::class, 'getVisitorStats']);

// Blog routes (CRUD)
Route::resource('blogs', BlogsController::class);

Route::get('/banners', [BannerController::class, 'index']);

Route::post('/banners', [BannerController::class, 'store']);

Route::put('/banners/{id}', [BannerController::class, 'update']);

Route::delete('/banners/{id}', [BannerController::class, 'destroy']);

Route::post('/upload-image', [ImageUploadController::class, 'store']);

require __DIR__ . '/auth.php';

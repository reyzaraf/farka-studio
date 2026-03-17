<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [WebController::class, 'index']);

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Custom Admin Panel Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('key-people', \App\Http\Controllers\Admin\KeyPersonController::class);
    
    // Contact Settings (Single row)
    Route::get('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'edit'])->name('contact-settings.edit');
    Route::put('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'update'])->name('contact-settings.update');
    
    // Protected by Spatie Super Admin role
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    });
});

// require __DIR__.'/auth.php';

Auth::routes();

Route::redirect('/home', '/admin')->name('home');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [WebController::class, 'index']);

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Custom Admin Panel Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Self-service profile (available to every authenticated user, not just super admins)
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Bulk / custom routes must be declared BEFORE their resource so paths like
    // "projects/bulk-destroy" don't get captured by "projects/{project}".
    Route::post('projects/reorder', [\App\Http\Controllers\Admin\ProjectController::class, 'reorder'])->name('projects.reorder');
    Route::delete('projects/bulk-destroy', [\App\Http\Controllers\Admin\ProjectController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class)->except(['show']);

    Route::delete('categories/bulk-destroy', [\App\Http\Controllers\Admin\CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    Route::post('key-people/reorder', [\App\Http\Controllers\Admin\KeyPersonController::class, 'reorder'])->name('key-people.reorder');
    Route::delete('key-people/bulk-destroy', [\App\Http\Controllers\Admin\KeyPersonController::class, 'bulkDestroy'])->name('key-people.bulk-destroy');
    Route::resource('key-people', \App\Http\Controllers\Admin\KeyPersonController::class);

    // Page / Contact Settings (single row)
    Route::get('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'edit'])->name('contact-settings.edit');
    Route::put('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'update'])->name('contact-settings.update');

    // Protected by Spatie Super Admin role
    Route::middleware(['role:super_admin'])->group(function () {
        Route::delete('users/bulk-destroy', [\App\Http\Controllers\Admin\UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        Route::delete('roles/bulk-destroy', [\App\Http\Controllers\Admin\RoleController::class, 'bulkDestroy'])->name('roles.bulk-destroy');
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    });
});

// require __DIR__.'/auth.php';

// Public self-registration is disabled — accounts are created by a super admin only.
Auth::routes(['register' => false]);

Route::redirect('/home', '/admin')->name('home');

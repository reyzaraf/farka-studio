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

// Public budget calculator
Route::get('/budget-estimator', [\App\Http\Controllers\BudgetCalculatorController::class, 'show'])->name('kalkulator.show');
Route::post('/budget-estimator/calculate', [\App\Http\Controllers\BudgetCalculatorController::class, 'calculate'])->middleware('throttle:60,1')->name('kalkulator.calculate');
Route::post('/budget-estimator/pdf', [\App\Http\Controllers\BudgetCalculatorController::class, 'pdf'])->middleware('throttle:60,1')->name('kalkulator.pdf');

// Custom Admin Panel Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Self-service profile (available to every authenticated user, not just super admins)
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Global quick-search (returns JSON for the topbar search box)
    Route::get('search', \App\Http\Controllers\Admin\SearchController::class)->name('search');

    // Bulk / custom routes must be declared BEFORE their resource so paths like
    // "projects/bulk-destroy" don't get captured by "projects/{project}".
    Route::post('projects/reorder', [\App\Http\Controllers\Admin\ProjectController::class, 'reorder'])->name('projects.reorder');
    Route::delete('projects/bulk-destroy', [\App\Http\Controllers\Admin\ProjectController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class)->except(['show']);

    Route::post('categories/reorder', [\App\Http\Controllers\Admin\CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::delete('categories/bulk-destroy', [\App\Http\Controllers\Admin\CategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    Route::post('key-people/reorder', [\App\Http\Controllers\Admin\KeyPersonController::class, 'reorder'])->name('key-people.reorder');
    Route::delete('key-people/bulk-destroy', [\App\Http\Controllers\Admin\KeyPersonController::class, 'bulkDestroy'])->name('key-people.bulk-destroy');
    Route::resource('key-people', \App\Http\Controllers\Admin\KeyPersonController::class);

    // Page / Contact Settings (single row)
    Route::get('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'edit'])->name('contact-settings.edit');
    Route::put('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'update'])->name('contact-settings.update');

    // Budget Calculator reference data
    Route::name('calc.')->prefix('calculator')->group(function () {
        Route::resource('rooms', \App\Http\Controllers\Admin\Calc\RoomController::class)->except('show');
        Route::resource('zonasi', \App\Http\Controllers\Admin\Calc\ZonasiController::class)->except('show');
        Route::resource('building-types', \App\Http\Controllers\Admin\Calc\BuildingTypeController::class)->except('show');
        Route::resource('factor-groups', \App\Http\Controllers\Admin\Calc\FactorGroupController::class)->except('show');
        Route::resource('allocations', \App\Http\Controllers\Admin\Calc\AllocationController::class)->except('show');
        Route::resource('components', \App\Http\Controllers\Admin\Calc\ComponentController::class)->except('show');
        Route::resource('size-tiers', \App\Http\Controllers\Admin\Calc\SizeTierController::class)->except('show');
        Route::get('settings', [\App\Http\Controllers\Admin\Calc\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [\App\Http\Controllers\Admin\Calc\SettingController::class, 'update'])->name('settings.update');
    });

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

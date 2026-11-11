<?php

use App\Http\Controllers\AcademicCatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('active')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportsController::class, 'export'])->name('reports.export');

        Route::middleware('admin')->group(function () {
            foreach (['departments', 'programs', 'subjects', 'instructors', 'sections', 'payment-methods'] as $catalog) {
                Route::prefix($catalog)->name($catalog.'.')->controller(AcademicCatalogController::class)->group(function () use ($catalog): void {
                    Route::get('/', 'index')->name('index')->defaults('catalog', $catalog);
                    Route::get('create', 'create')->name('create')->defaults('catalog', $catalog);
                    Route::post('/', 'store')->name('store')->defaults('catalog', $catalog);
                    Route::get('{record}', 'show')->name('show')->defaults('catalog', $catalog);
                    Route::get('{record}/edit', 'edit')->name('edit')->defaults('catalog', $catalog);
                    Route::put('{record}', 'update')->name('update')->defaults('catalog', $catalog);
                    Route::delete('{record}', 'destroy')->name('destroy')->defaults('catalog', $catalog);
                });
            }

            Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
            Route::patch('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        });

        Route::get('students', [AcademicCatalogController::class, 'index'])->name('students.index')->defaults('catalog', 'students');
        Route::get('students/create', [AcademicCatalogController::class, 'create'])->name('students.create')->defaults('catalog', 'students');
        Route::post('students', [AcademicCatalogController::class, 'store'])->name('students.store')->defaults('catalog', 'students');
        Route::get('students/{record}', [AcademicCatalogController::class, 'show'])->name('students.show')->defaults('catalog', 'students');
        Route::get('students/{record}/edit', [AcademicCatalogController::class, 'edit'])->name('students.edit')->defaults('catalog', 'students');
        Route::put('students/{record}', [AcademicCatalogController::class, 'update'])->name('students.update')->defaults('catalog', 'students');
        Route::delete('students/{record}', [AcademicCatalogController::class, 'destroy'])->name('students.destroy')->defaults('catalog', 'students');

        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'create', 'store']);
        Route::post('enrollments/{enrollment}/drop', [EnrollmentController::class, 'drop'])->name('enrollments.drop');
        Route::post('enrollments/{enrollment}/drop-add', [EnrollmentController::class, 'dropAdd'])->name('enrollments.drop-add');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    });
});

require __DIR__.'/settings.php';

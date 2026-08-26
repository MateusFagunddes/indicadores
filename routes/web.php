<?php

use App\Http\Controllers\IndicadorDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'create'])->name('login');
	Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
	Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
	Route::get('/', [IndicadorDashboardController::class, 'index'])->name('dashboard');

	Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
		Route::get('/users', [AdminController::class, 'users'])->name('users');
		Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
		Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
		Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
		Route::get('/cursos', [AdminController::class, 'cursos'])->name('cursos');
		Route::post('/cursos', [AdminController::class, 'storeCurso'])->name('cursos.store');
		Route::put('/cursos/{curso}', [AdminController::class, 'updateCurso'])->name('cursos.update');
		Route::delete('/cursos/{curso}', [AdminController::class, 'destroyCurso'])->name('cursos.destroy');
		Route::get('/indicadores', [AdminController::class, 'indicadores'])->name('indicadores');
		Route::post('/indicadores', [AdminController::class, 'storeIndicador'])->name('indicadores.store');
		Route::put('/indicadores/{indicador}', [AdminController::class, 'updateIndicador'])->name('indicadores.update');
		Route::delete('/indicadores/{indicador}', [AdminController::class, 'destroyIndicador'])->name('indicadores.destroy');
	});
});

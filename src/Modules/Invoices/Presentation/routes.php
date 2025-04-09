<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceController;

Route::get('/invoices/{invoice}', [InvoiceController::class, 'view'])->name('invoice.view');
Route::post('/invoices', [InvoiceController::class, 'create'])->name('invoice.create');
Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoice.send');

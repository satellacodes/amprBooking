<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

// Halaman Depan
Route::get('/', [BookingController::class, 'index'])->name('home');

// Proses Booking
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

// Halaman Tiket Sukses
Route::get('/ticket/{booking:booking_code}', [BookingController::class, 'showTicket'])->name('booking.ticket');

// DOWNLOAD PDF (BARU)
Route::get('/ticket/{booking:booking_code}/pdf', [BookingController::class, 'downloadPdf'])->name('booking.pdf');

// MENU TIKET SAYA
Route::get('/my-tickets', [BookingController::class, 'myTickets'])->name('booking.list');
Route::post('/my-tickets', [BookingController::class, 'checkTickets'])->name('booking.check');

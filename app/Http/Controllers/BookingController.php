<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckTicketRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * PAGE: Halaman Utama (Jadwal)
     */
    public function index()
    {
        // PENTING: Gunakan Timezone Jakarta
        $today = Carbon::now('Asia/Jakarta');

        // --- 1. FITUR DYNAMIC DATE (1 BULAN) ---
        // Menghitung range dari hari ini sampai tanggal yang sama bulan depan.
        // Contoh: 18 Des -> 18 Jan. Carbon otomatis tahu jika ada tgl 31 atau Feb 28.
        $endDate = $today->copy()->addMonth();
        $daysDifference = $today->diffInDays($endDate);

        $dates = collect(range(0, $daysDifference))->map(function ($days) use ($today) {
            $date = $today->copy()->addDays($days);

            return [
                'full_date' => $date->format('Y-m-d'),
                'day_name' => $date->locale('id')->isoFormat('ddd'), // Sen, Sel
                'date_num' => $date->format('d'),
                'month_name' => $date->locale('id')->isoFormat('MMM'), // Des
                'is_today' => $date->isToday(),
            ];
        });

        // 2. Ambil Data Slot Terisi (Format Ringan untuk Frontend)
        $rawBookings = Booking::active()
            ->whereDate('start_time', '>=', $today->format('Y-m-d'))
            ->whereDate('start_time', '<=', $endDate->format('Y-m-d'))
            ->get(['start_time']);

        $bookedSlots = $rawBookings->map(function ($b) {
            return [
                'date' => $b->start_time->format('Y-m-d'),
                'hour' => (int) $b->start_time->format('H'),
            ];
        });

        // 3. Logic Tanggal Penuh (16 Slot per hari)
        $bookingsPerDate = $bookedSlots->groupBy('date')->map->count();
        $fullyBookedDates = $bookingsPerDate->filter(fn ($count) => $count >= 16)->keys()->toArray();

        // 4. Data Unit untuk Dropdown
        $units = Unit::orderBy('unit_number', 'asc')->pluck('unit_number');

        return Inertia::render('booking/Home', [
            'dates' => $dates,
            'bookedSlots' => $bookedSlots,
            'units' => $units,
            'fullyBookedDates' => $fullyBookedDates,
        ]);
    }

    /**
     * ACTION: Proses Booking (Logic Validasi Lengkap)
     */
    public function store(StoreBookingRequest $request)
    {
        // Setup Waktu (Timezone Jakarta)
        $startTime = Carbon::createFromFormat('Y-m-d H', "$request->date $request->hour", 'Asia/Jakarta');
        $endTime = $startTime->copy()->addHour();

        // --- VALIDASI 3: CONCURRENCY (ATOMIC LOCK) ---
        // Kunci slot ini selama 5 detik berdasarkan waktu.
        // Jika ada orang lain klik di detik yang sama, dia akan ditolak.
        $lockKey = "booking_lock_{$startTime->timestamp}";
        $lock = Cache::lock($lockKey, 5); // 5 detik

        if (! $lock->get()) {
            throw ValidationException::withMessages(['slot' => 'Slot sedang diproses oleh penghuni lain. Silakan coba lagi.']);
        }

        try {
            return DB::transaction(function () use ($request, $startTime, $endTime) {

                // 1. Cek Unit & PIN (Auth Manual)
                $unit = Unit::where('unit_number', $request->unit_number)->first();
                if (! $unit || ! Hash::check($request->access_code, $unit->access_code)) {
                    throw ValidationException::withMessages(['access_code' => 'PIN Akses salah atau Unit tidak ditemukan.']);
                }

                // --- VALIDASI 1: SANKSI (BANNED) ---
                // Cek apakah kolom banned terisi DAN tanggalnya masih di masa depan
                if ($unit->is_banned_until && now() < $unit->is_banned_until) {
                    $dateEnd = Carbon::parse($unit->is_banned_until)->format('d M Y');
                    throw ValidationException::withMessages([
                        'unit_number' => "Unit ini sedang disanksi (Banned) hingga {$dateEnd}. Tidak dapat melakukan booking.",
                    ]);
                }

                // --- VALIDASI 2: KUOTA MINGGUAN ---
                // Hitung jumlah booking unit ini di minggu berjalan (Senin - Minggu)
                $startOfWeek = now()->startOfWeek();
                $endOfWeek = now()->endOfWeek();

                $weeklyUsage = Booking::active()
                    ->where('unit_id', $unit->id)
                    ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
                    ->count();

                // Batas Max 2 Slot per minggu
                if ($weeklyUsage >= 2) {
                    throw ValidationException::withMessages([
                        'unit_number' => 'Kuota mingguan habis. Maksimal 2 jam per minggu.',
                    ]);
                }

                // 2. Cek Ketersediaan Slot (Double Check Database)
                $isBooked = Booking::active()
                    ->where('start_time', $startTime->format('Y-m-d H:i:s'))
                    ->exists();

                if ($isBooked) {
                    throw ValidationException::withMessages(['slot' => 'Maaf, slot ini baru saja diambil orang lain.']);
                }

                // 3. Simpan Booking
                $booking = Booking::create([
                    'unit_id' => $unit->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'player_names' => [$request->player_names],
                    'status' => 'booked',
                ]);

                // Generate Booking Code jika Observer macet
                if (! $booking->booking_code) {
                    $booking->booking_code = 'TN-'.strtoupper(uniqid());
                    $booking->save();
                }

                // Update Counter Unit
                $unit->increment('quota_usage');

                // 4. Redirect ke Halaman Sukses
                return to_route('booking.ticket', $booking->booking_code);
            });

        } finally {
            $lock->release(); // Lepas kunci
        }
    }

    public function showTicket(Booking $booking)
    {
        $booking->load('unit');
        $qrCode = (new QRCode)->render($booking->booking_code);

        return Inertia::render('booking/Success', [
            'booking' => $booking,
            'qrCode' => $qrCode,
        ]);
    }

    public function downloadPdf(Booking $booking)
    {
        $booking->load('unit');

        // --- KONFIGURASI QR CODE KHUSUS PDF (PNG) ---
        // Kita gunakan PNG karena DomPDF lebih stabil merender PNG daripada SVG
        $options = new \chillerlan\QRCode\QROptions([
            'version' => 5,
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => true, // Agar langsung keluar string "data:image/png;base64,..."
        ]);

        // Generate QR
        $qrCode = (new QRCode($options))->render($booking->booking_code);

        // Render PDF
        $pdf = Pdf::loadView('pdf.ticket', [
            'booking' => $booking,
            'qrCode' => $qrCode,
        ]);

        // Setup Kertas A5 (Ukuran Tiket)
        $pdf->setPaper('A5', 'portrait');
        $pdf->setWarnings(false);

        return $pdf->download('e-ticket-'.$booking->booking_code.'.pdf');
    }

    public function myTickets()
    {
        return Inertia::render('booking/MyTickets');
    }

    /**
     * ACTION: Proses Cek Tiket (Refactored)
     */
    public function checkTickets(CheckTicketRequest $request)
    {
        // 1. Ambil Unit (Pakai Scope dari Model Unit)
        $unit = Unit::byNumber($request->unit_number)->first();

        // 2. Validasi Kredensial (Unit Ada & PIN Cocok)
        if (! $unit || ! Hash::check($request->access_code, $unit->access_code)) {
            throw ValidationException::withMessages([
                'access_code' => 'Kombinasi Unit dan PIN tidak cocok.',
            ]);
        }

        // 3. Ambil Data (Pakai Scope dari Model Booking)
        // Baca: "Booking -> Upcoming For Unit -> Get"
        $bookings = Booking::upcomingForUnit($unit->id)
            ->with('unit')
            ->get();

        // 4. Return ke Vue
        return Inertia::render('booking/MyTickets', [
            'bookings' => $bookings,
            'searchedUnit' => $unit->unit_number,
        ]);

    }
}

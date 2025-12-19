<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

// Props dari Controller
const props = defineProps({
    bookings: Array, // Hasil pencarian (bisa null jika belum cari)
    searchedUnit: String, // Unit yang sedang dicari
});

const form = useForm({
    unit_number: '',
    access_code: '',
});

const submitCheck = () => {
    form.post('/my-tickets', {
        onFinish: () => form.reset('access_code'),
    });
};

// Helper Format Tanggal
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
    });
};

const formatTime = (start, end) => {
    const s = new Date(start).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
    const e = new Date(end).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
    return `${s} - ${e}`;
};
</script>

<template>
    <Head title="Tiket Saya" />

    <div
        class="flex h-[100dvh] justify-center overflow-hidden bg-gradient-to-br from-[#F4E7FB] via-[#FFF0F0] to-[#E8EAF6] font-sans text-[#5A4D61] antialiased"
    >
        <div
            class="relative flex h-full w-full max-w-[480px] flex-col overflow-hidden border-x border-white/40 bg-white/60 shadow-2xl backdrop-blur-md"
        >
            <!-- BAGIAN 1: HEADER -->
            <div class="z-20 flex-none px-6 pt-8 pb-4">
                <h1 class="text-2xl font-black tracking-tight text-[#5A4D61]">
                    Tiket Saya <span class="text-[#FF9973]">.</span>
                </h1>
                <p class="mt-1 text-xs font-medium text-[#9D8CA6]">
                    Cek jadwal main aktif unit Anda.
                </p>
            </div>

            <!-- BAGIAN 2: KONTEN UTAMA -->
            <main class="no-scrollbar flex-1 overflow-y-auto px-6 pt-2 pb-24">
                <!-- KONDISI A: BELUM ADA HASIL (Tampilkan Form Login) -->
                <div
                    v-if="!bookings"
                    class="flex h-[60vh] flex-col justify-center"
                >
                    <div
                        class="rounded-[2rem] border border-white bg-white/80 p-6 shadow-lg backdrop-blur-xl"
                    >
                        <div class="mb-6 text-center">
                            <div
                                class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-[#F4E7FB] text-[#C8A8E9]"
                            >
                                <svg
                                    class="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.542 6.356 15 7zm0 0v2a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"
                                    ></path>
                                </svg>
                            </div>
                            <h3 class="font-bold text-[#5A4D61]">
                                Akses Data Booking
                            </h3>
                            <p class="text-xs text-[#9D8CA6]">
                                Masukkan PIN Unit untuk melihat tiket.
                            </p>
                        </div>

                        <form @submit.prevent="submitCheck" class="space-y-4">
                            <div>
                                <label
                                    class="mb-1 ml-1 block text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                                    >Unit</label
                                >
                                <input
                                    v-model="form.unit_number"
                                    type="text"
                                    placeholder="TWR-A-0101"
                                    class="w-full rounded-2xl border-none bg-[#F9F9F9] py-3 pl-4 font-bold text-[#5A4D61] uppercase transition focus:ring-2 focus:ring-[#C8A8E9]"
                                />
                                <p
                                    v-if="form.errors.unit_number"
                                    class="mt-1 ml-1 text-[10px] font-bold text-[#FF9973]"
                                >
                                    {{ form.errors.unit_number }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1 ml-1 block text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                                    >PIN</label
                                >
                                <input
                                    v-model="form.access_code"
                                    type="password"
                                    placeholder="••••••"
                                    class="w-full rounded-2xl border-none bg-[#F9F9F9] py-3 pl-4 font-bold tracking-widest text-[#5A4D61] transition focus:ring-2 focus:ring-[#C8A8E9]"
                                />
                                <p
                                    v-if="form.errors.access_code"
                                    class="mt-1 ml-1 text-[10px] font-bold text-[#FF9973]"
                                >
                                    {{ form.errors.access_code }}
                                </p>
                            </div>
                            <button
                                :disabled="form.processing"
                                type="submit"
                                class="mt-2 w-full rounded-2xl bg-[#0A0D25] py-3.5 font-bold text-white shadow-lg transition hover:opacity-90"
                            >
                                {{
                                    form.processing
                                        ? 'Memeriksa...'
                                        : 'Cek Tiket'
                                }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KONDISI B: SUDAH ADA DATA (Tampilkan List) -->
                <div v-else>
                    <div class="mb-4 flex items-center justify-between">
                        <span
                            class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#7ECFE0]"
                            >Unit: {{ searchedUnit }}</span
                        >
                        <a
                            href="/my-tickets"
                            class="text-xs font-bold text-[#FF9973] hover:underline"
                            >Keluar</a
                        >
                    </div>

                    <!-- KONDISI C: DATA KOSONG (Tidak ada booking) -->
                    <div
                        v-if="bookings.length === 0"
                        class="py-20 text-center opacity-60"
                    >
                        <svg
                            class="mx-auto mb-2 h-16 w-16 text-[#C4B2AE]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                            ></path>
                        </svg>
                        <p class="text-sm font-bold">Belum ada jadwal main.</p>
                        <Link
                            href="/"
                            class="mt-2 inline-block text-xs text-[#7ECFE0]"
                            >Buat Booking Baru</Link
                        >
                    </div>

                    <!-- LIST CARD -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="book in bookings"
                            :key="book.id"
                            class="group relative overflow-hidden rounded-[1.5rem] border border-white bg-white p-5 shadow-sm transition hover:shadow-md"
                        >
                            <!-- Garis Indikator -->
                            <div
                                class="absolute top-0 bottom-0 left-0 w-2 bg-[#7ECFE0]"
                            ></div>

                            <div class="flex items-start justify-between pl-2">
                                <div>
                                    <p
                                        class="mb-0.5 text-[10px] font-bold tracking-wider text-[#9D8CA6] uppercase"
                                    >
                                        Tanggal Main
                                    </p>
                                    <h4
                                        class="text-lg font-black text-[#5A4D61]"
                                    >
                                        {{ formatDate(book.start_time) }}
                                    </h4>
                                    <p
                                        class="mt-1 text-sm font-bold text-[#7ECFE0]"
                                    >
                                        {{
                                            formatTime(
                                                book.start_time,
                                                book.end_time,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="rounded-lg bg-[#F4E7FB] px-2 py-1 text-[10px] font-bold text-[#C8A8E9]"
                                        >CONFIRMED</span
                                    >
                                </div>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between border-t border-dashed border-slate-100 pt-3 pl-2"
                            >
                                <span
                                    class="font-mono text-[10px] text-[#C4B2AE]"
                                    >{{ book.booking_code }}</span
                                >
                                <a
                                    :href="`/ticket/${book.booking_code}/pdf`"
                                    target="_blank"
                                    class="flex items-center gap-1 text-xs font-bold text-[#FF9973] hover:text-[#FF865B]"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        ></path>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- BAGIAN 3: BOTTOM NAVIGATION (Sama seperti Home) -->
            <div class="absolute right-6 bottom-6 left-6 z-30">
                <nav
                    class="flex items-center justify-between rounded-[2rem] border border-white/50 bg-white/90 px-6 py-3 shadow-xl shadow-[#C8A8E9]/20 backdrop-blur-lg"
                >
                    <!-- HOME -->
                    <Link
                        href="/"
                        class="flex w-12 flex-col items-center gap-1 text-[#9D8CA6] transition hover:text-[#FF9973]"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            ></path>
                        </svg>
                    </Link>

                    <!-- TIKET (Active) -->
                    <Link
                        href="/my-tickets"
                        class="flex w-12 flex-col items-center gap-1 text-[#7ECFE0]"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                            ></path>
                        </svg>
                        <span class="text-[8px] font-bold tracking-wide"
                            >Tiket</span
                        >
                    </Link>

                    <!-- INFO -->
                    <button
                        class="flex w-12 flex-col items-center gap-1 text-[#9D8CA6] transition hover:text-[#FF9973]"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

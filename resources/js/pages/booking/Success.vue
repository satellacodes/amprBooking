<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    booking: Object,
    qrCode: String,
});

const showQrModal = ref(false);

const formattedDate = computed(() => {
    return new Date(props.booking.start_time).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
});

const formattedTime = computed(() => {
    const s = new Date(props.booking.start_time);
    const e = new Date(props.booking.end_time);
    const opt = { hour: '2-digit', minute: '2-digit', hour12: false };
    return `${s.toLocaleTimeString('id-ID', opt)} - ${e.toLocaleTimeString('id-ID', opt)}`;
});
</script>

<template>
    <Head title="Booking Berhasil" />

    <!-- LAYOUT UTAMA -->
    <div
        class="flex min-h-[100dvh] justify-center overflow-y-auto bg-gradient-to-br from-[#F4E7FB] via-[#FFF0F0] to-[#E8EAF6] font-sans text-[#5A4D61] antialiased"
    >
        <!-- MOBILE CONTAINER -->
        <div
            class="relative flex min-h-[100dvh] w-full max-w-[480px] flex-col items-center justify-center overflow-hidden border-x border-white/50 bg-white/60 p-6 shadow-2xl backdrop-blur-xl"
        >
            <!-- 1. Success Icon (Warna Ungu Gradasi) -->
            <div
                class="relative mb-6 flex h-24 w-24 items-center justify-center"
            >
                <div
                    class="absolute h-full w-full animate-ping rounded-full bg-[#C8A8E9]/30"
                ></div>
                <!-- Perbaikan Warna: Gradasi Ungu -->
                <div
                    class="relative flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-[#C8A8E9] to-[#E3AADD] text-white shadow-lg shadow-[#C8A8E9]/40"
                >
                    <svg
                        class="h-10 w-10"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M5 13l4 4L19 7"
                        ></path>
                    </svg>
                </div>
            </div>

            <h1 class="mb-2 text-2xl font-black text-[#5A4D61]">
                Booking Berhasil!
            </h1>
            <p class="mb-8 text-center text-sm font-medium text-[#9D8CA6]">
                Jadwal lapangan tenis telah diamankan.<br />Silakan simpan tiket
                Anda.
            </p>

            <!-- 2. Ticket Card -->
            <div
                class="w-full overflow-hidden rounded-[2rem] border border-white bg-white/80 shadow-xl backdrop-blur-md"
            >
                <!-- Header Card -->
                <div
                    class="flex items-center justify-between border-b border-[#F4E7FB] bg-white/50 p-6"
                >
                    <div>
                        <div
                            class="text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                        >
                            Kode Booking
                        </div>
                        <div
                            class="mt-1 font-mono text-2xl font-black tracking-tight text-[#FF9973]"
                        >
                            {{
                                booking.booking_code
                                    .substring(0, 8)
                                    .toUpperCase()
                            }}
                        </div>
                    </div>
                    <!-- QR Button -->
                    <button
                        @click="showQrModal = true"
                        class="rounded-xl border border-white bg-white p-1.5 shadow-sm transition hover:scale-105 active:scale-95"
                    >
                        <img :src="qrCode" class="h-12 w-12" alt="QR" />
                    </button>
                </div>

                <!-- Detail Grid -->
                <!-- Perbaikan Layout: pb-8 agar konten bawah tidak mepet -->
                <div
                    class="grid grid-cols-2 gap-x-4 gap-y-6 p-6 pb-8 text-left"
                >
                    <!-- Tanggal -->
                    <div class="col-span-2">
                        <div class="mb-1 flex items-center gap-2">
                            <svg
                                class="h-4 w-4 flex-shrink-0 text-[#C8A8E9]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                ></path>
                            </svg>
                            <span
                                class="text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                                >Tanggal Main</span
                            >
                        </div>
                        <div class="pl-6 text-base font-bold text-[#5A4D61]">
                            {{ formattedDate }}
                        </div>
                    </div>

                    <!-- Jam -->
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <svg
                                class="h-4 w-4 flex-shrink-0 text-[#C8A8E9]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <span
                                class="text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                                >Jam</span
                            >
                        </div>
                        <div class="pl-6 text-sm font-bold text-[#5A4D61]">
                            {{ formattedTime }}
                        </div>
                    </div>

                    <!-- Unit (Perbaikan Icon Kepotong) -->
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <!-- flex-shrink-0 agar icon tidak gepeng -->
                            <svg
                                class="h-4 w-4 flex-shrink-0 text-[#C8A8E9]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 11-4 0 2 2 0 014 0z"
                                ></path>
                            </svg>
                            <span
                                class="text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                                >Unit</span
                            >
                        </div>
                        <div class="pl-6 text-sm font-bold text-[#5A4D61]">
                            {{ booking.unit?.unit_number }}
                        </div>
                    </div>

                    <!-- Pemain (Perbaikan Nama Kepotong) -->
                    <div class="col-span-2 mt-2 border-t border-[#F4E7FB] pt-4">
                        <div class="mb-1 flex items-center gap-2">
                            <svg
                                class="h-4 w-4 flex-shrink-0 text-[#C8A8E9]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                ></path>
                            </svg>
                            <span
                                class="text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                                >Pemain</span
                            >
                        </div>
                        <!-- break-words agar nama panjang turun ke bawah -->
                        <div
                            class="pl-6 text-sm font-bold break-words text-[#5A4D61]"
                        >
                            {{
                                Array.isArray(booking.player_names)
                                    ? booking.player_names[0]
                                    : booking.player_names
                            }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Action Buttons (Warna Ungu Gradasi) -->
            <a
                :href="`/ticket/${booking.booking_code}/pdf`"
                target="_blank"
                class="mt-8 flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#C8A8E9] to-[#E3AADD] py-4 font-bold text-white shadow-xl shadow-[#C8A8E9]/30 transition hover:opacity-90 active:scale-95"
            >
                <svg
                    class="h-5 w-5"
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
                Download E-Ticket
            </a>

            <Link
                href="/"
                class="mt-4 py-2 text-sm font-bold text-[#9D8CA6] transition hover:text-[#5A4D61]"
            >
                Booking Slot Lain
            </Link>
        </div>

        <!-- MODAL QR -->
        <div
            v-if="showQrModal"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-[#5A4D61]/60 p-6 backdrop-blur-sm transition-opacity"
            @click="showQrModal = false"
        >
            <div
                class="w-full max-w-xs scale-100 transform rounded-[2rem] bg-white p-8 text-center shadow-2xl transition-all"
                @click.stop
            >
                <div
                    class="mb-6 inline-block rounded-2xl border border-[#F4E7FB] bg-[#F9F9F9] p-4 shadow-inner"
                >
                    <img
                        :src="qrCode"
                        class="h-56 w-56 mix-blend-multiply"
                        alt="QR Full"
                    />
                </div>
                <h3 class="mb-2 text-lg font-bold text-[#5A4D61]">Scan Me</h3>
                <p class="text-sm font-medium text-[#9D8CA6]">
                    Tunjukkan QR Code ini kepada petugas keamanan untuk akses
                    masuk.
                </p>
                <button
                    @click="showQrModal = false"
                    class="mt-6 w-full rounded-xl bg-gradient-to-r from-[#C8A8E9] to-[#E3AADD] py-3 text-sm font-bold text-[#ffff] shadow-[#C8A8E9]/30 transition hover:opacity-90 active:scale-95"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

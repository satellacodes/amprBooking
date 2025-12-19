<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// --- 1. LOGIC (TETAP SAMA 100%) ---
const props = defineProps({
    dates: { type: Array, default: () => [] },
    bookedSlots: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
    fullyBookedDates: { type: Array, default: () => [] },
});

const todayStr = new Date().toISOString().split('T')[0];
const selectedDate = ref(
    props.dates.length > 0 ? props.dates[0].full_date : todayStr,
);
const showModal = ref(false);
const selectedSlot = ref(null);
const unitSearch = ref('');
const showUnitDropdown = ref(false);

const form = useForm({
    unit_number: '',
    access_code: '',
    player_names: '',
    date: '',
    hour: '',
    agree_terms: false,
});

const filteredUnits = computed(() => {
    if (!unitSearch.value) return props.units;
    return props.units.filter((u) =>
        u.toLowerCase().includes(unitSearch.value.toLowerCase()),
    );
});

const morningSlots = [6, 7, 8, 9, 10, 11];
const eveningSlots = [15, 16, 17, 18, 19, 20, 21];

const getStatus = (hour) => {
    const slots = props.bookedSlots || [];
    const isBooked = slots.some(
        (slot) => slot.date === selectedDate.value && slot.hour === hour,
    );
    if (isBooked) return 'booked';
    const now = new Date();
    const localToday = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .split('T')[0];
    const currentHour = now.getHours();
    if (selectedDate.value < localToday) return 'past';
    if (selectedDate.value === localToday && hour <= currentHour) return 'past';
    return 'available';
};

const openBooking = (hour) => {
    if (getStatus(hour) !== 'available') return;
    const start = String(hour).padStart(2, '0') + ':00';
    const end = String(hour + 1).padStart(2, '0') + ':00';
    selectedSlot.value = { hour, label: `${start} - ${end}` };
    form.date = selectedDate.value;
    form.hour = hour;
    form.unit_number = '';
    form.access_code = '';
    form.agree_terms = false;
    unitSearch.value = '';
    form.clearErrors();
    showModal.value = true;
};

const selectUnit = (unit) => {
    form.unit_number = unit;
    unitSearch.value = unit;
    showUnitDropdown.value = false;
};

const submitBooking = () => {
    form.post('/booking', {
        onSuccess: () => (showModal.value = false),
        onFinish: () => form.reset('access_code'),
    });
};

const formatDateDisplay = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
};
</script>

<template>
    <Head title="Booking Lapangan" />

    <!-- LAYOUT UTAMA: Pastel Gradient Background -->
    <div
        class="flex h-[100dvh] justify-center overflow-hidden bg-gradient-to-br from-[#F4E7FB] via-[#FFF0F0] to-[#E8EAF6] font-sans text-[#5A4D61] antialiased"
    >
        <!-- MOBILE CONTAINER -->
        <div
            class="relative flex h-full w-full max-w-[480px] flex-col overflow-hidden border-x border-white/40 bg-white/60 shadow-2xl backdrop-blur-md"
        >
            <!-- BAGIAN 1: HEADER (Sesuai Screenshot Baru) -->
            <div class="z-20 flex-none px-6 pt-8 pb-2">
                <!-- Top Bar: Lokasi & Notif -->
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p
                            class="mb-1 text-[10px] font-bold tracking-widest text-[#9D8CA6] uppercase"
                        >
                            LOKASI
                        </p>
                        <div class="flex items-center gap-1.5 text-[#2D2D2D]">
                            <svg
                                class="h-4 w-4 text-[#FF844B]"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                            <h1 class="text-base font-bold text-[#5A4D61]">
                                Mediterania Palace
                            </h1>
                        </div>
                    </div>
                    <!-- Profile / Notif Avatar -->
                    <div
                        class="h-10 w-10 rounded-full bg-gradient-to-tr from-[#C8A8E9] to-[#E3AADD] p-[2px]"
                    >
                        <div
                            class="flex h-full w-full items-center justify-center rounded-full bg-white"
                        >
                            <svg
                                class="h-5 w-5 text-[#C8A8E9]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Hero Card (Visa Style but for Info) -->
                <div
                    class="group relative overflow-hidden rounded-[1.8rem] p-5 text-white shadow-lg shadow-[#E3AADD]/30"
                >
                    <!-- Gradient Background -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-[#F6BCBA] to-[#E3AADD]"
                    ></div>
                    <!-- Abstract Shapes -->
                    <div
                        class="absolute -top-10 -right-6 h-32 w-32 rounded-full bg-white/20 blur-xl"
                    ></div>
                    <div
                        class="absolute -bottom-10 -left-6 h-32 w-32 rounded-full bg-[#C8A8E9]/40 blur-xl"
                    ></div>

                    <div
                        class="relative z-10 flex items-center justify-between"
                    >
                        <!-- Kiri: Judul -->
                        <div>
                            <h2
                                class="mb-1 flex items-center gap-2 text-xl font-bold tracking-wide"
                            >
                                AMPR Tennis
                                <span class="text-2xl">🎾</span>
                            </h2>
                            <p
                                class="text-xs font-bold font-medium text-[#5A4D61]"
                            >
                                Booking Anytime
                            </p>
                        </div>

                        <!-- Kanan: Kuota Box (Glass Effect) -->
                        <div
                            class="min-w-[70px] rounded-2xl border border-white/5 bg-white/10 p-3 text-center backdrop-blur-md"
                        >
                            <span
                                class="mb-0.5 block text-[9px] font-bold tracking-wider text-[#5A4D61] uppercase"
                                >KUOTA</span
                            >
                            <span class="text-sm font-bold">2 Slot</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: CONTENT (Scrollable) -->
            <main class="no-scrollbar flex-1 overflow-y-auto px-6 pt-2 pb-24">
                <!-- Date Picker -->
                <div class="mb-8">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-[#5A4D61]">
                            Pilih Tanggal
                        </h3>
                        <span
                            class="rounded-lg bg-white px-2 py-1 text-xs font-medium text-[#C8A8E9]"
                            >{{
                                new Date(selectedDate).toLocaleDateString(
                                    'id-ID',
                                    { month: 'long' },
                                )
                            }}</span
                        >
                    </div>
                    <div
                        class="no-scrollbar flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2"
                    >
                        <button
                            v-for="day in dates"
                            :key="day.full_date"
                            @click="selectedDate = day.full_date"
                            class="relative flex h-[75px] min-w-[60px] snap-center flex-col items-center justify-center rounded-[1.5rem] border border-transparent transition-all duration-300"
                            :class="[
                                selectedDate === day.full_date
                                    ? 'scale-105 bg-[#C8A8E9] text-white shadow-lg shadow-[#C8A8E9]/40'
                                    : 'bg-white text-[#9D8CA6] hover:bg-white/80',
                                fullyBookedDates.includes(day.full_date) &&
                                selectedDate !== day.full_date
                                    ? 'opacity-50 grayscale'
                                    : '',
                            ]"
                        >
                            <span
                                class="mb-1 text-[10px] font-bold tracking-widest uppercase"
                                >{{ day.day_name }}</span
                            >
                            <span class="text-lg font-black">{{
                                day.date_num
                            }}</span>
                            <!-- Dot -->
                            <div
                                v-if="fullyBookedDates.includes(day.full_date)"
                                class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-[#F6BCBA]"
                            ></div>
                        </button>
                    </div>
                </div>

                <!-- Slot List -->
                <div class="space-y-6">
                    <!-- Pagi -->
                    <div>
                        <div class="mb-4 flex items-center gap-2">
                            <span
                                class="h-2 w-2 rounded-full bg-[#F6BCBA]"
                            ></span>
                            <span
                                class="text-xs font-bold tracking-widest text-[#9D8CA6] uppercase"
                                >Pagi (06:00 - 11:00)</span
                            >
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="hour in morningSlots"
                                :key="hour"
                                @click="openBooking(hour)"
                                class="group relative flex items-center justify-between rounded-[1.2rem] bg-white p-4 transition-all duration-300"
                                :class="[
                                    getStatus(hour) === 'available'
                                        ? 'cursor-pointer hover:shadow-lg hover:shadow-[#C8A8E9]/10'
                                        : 'cursor-not-allowed bg-[#F2F0F4] opacity-50',
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold"
                                        :class="
                                            getStatus(hour) === 'available'
                                                ? 'bg-[#F4E7FB] text-[#C8A8E9]'
                                                : 'bg-gray-100 text-gray-400'
                                        "
                                    >
                                        {{ String(hour).padStart(2, '0') }}
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-bold text-[#5A4D61]"
                                        >
                                            {{
                                                String(hour).padStart(2, '0')
                                            }}:00 -
                                            {{
                                                String(hour + 1).padStart(
                                                    2,
                                                    '0',
                                                )
                                            }}:00
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] font-bold uppercase"
                                            :class="
                                                getStatus(hour) === 'available'
                                                    ? 'text-[#C8A8E9]'
                                                    : 'text-[#F6BCBA]'
                                            "
                                        >
                                            {{
                                                getStatus(hour) === 'available'
                                                    ? 'Available'
                                                    : 'Unavailable'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Add Button -->
                                <button
                                    class="flex h-8 w-8 items-center justify-center rounded-full transition-all duration-300"
                                    :class="
                                        getStatus(hour) === 'available'
                                            ? 'bg-[#C8A8E9] text-white shadow-md shadow-[#C8A8E9]/30 group-hover:scale-110'
                                            : 'bg-transparent text-gray-300'
                                    "
                                >
                                    <span
                                        v-if="getStatus(hour) === 'available'"
                                        class="mb-0.5 text-lg font-bold"
                                        >+</span
                                    >
                                    <svg
                                        v-else
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sore -->
                    <div>
                        <div class="mb-4 flex items-center gap-2">
                            <span
                                class="h-2 w-2 rounded-full bg-[#C3C7F4]"
                            ></span>
                            <span
                                class="text-xs font-bold tracking-widest text-[#9D8CA6] uppercase"
                                >Sore (15:00 - 21:00)</span
                            >
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="hour in eveningSlots"
                                :key="hour"
                                @click="openBooking(hour)"
                                class="group relative flex items-center justify-between rounded-[1.2rem] bg-white p-4 transition-all duration-300"
                                :class="[
                                    getStatus(hour) === 'available'
                                        ? 'cursor-pointer hover:shadow-lg hover:shadow-[#C8A8E9]/10'
                                        : 'cursor-not-allowed bg-[#F2F0F4] opacity-50',
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold"
                                        :class="
                                            getStatus(hour) === 'available'
                                                ? 'bg-[#EAEBFF] text-[#C3C7F4]'
                                                : 'bg-gray-100 text-gray-400'
                                        "
                                    >
                                        {{ String(hour).padStart(2, '0') }}
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-bold text-[#5A4D61]"
                                        >
                                            {{
                                                String(hour).padStart(2, '0')
                                            }}:00 -
                                            {{
                                                String(hour + 1).padStart(
                                                    2,
                                                    '0',
                                                )
                                            }}:00
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] font-bold uppercase"
                                            :class="
                                                getStatus(hour) === 'available'
                                                    ? 'text-[#C3C7F4]'
                                                    : 'text-[#F6BCBA]'
                                            "
                                        >
                                            {{
                                                getStatus(hour) === 'available'
                                                    ? 'Available'
                                                    : 'Unavailable'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    class="flex h-8 w-8 items-center justify-center rounded-full transition-all duration-300"
                                    :class="
                                        getStatus(hour) === 'available'
                                            ? 'bg-[#C3C7F4] text-white shadow-md shadow-[#C3C7F4]/30 group-hover:scale-110'
                                            : 'bg-transparent text-gray-300'
                                    "
                                >
                                    <span
                                        v-if="getStatus(hour) === 'available'"
                                        class="mb-0.5 text-lg font-bold"
                                        >+</span
                                    >
                                    <svg
                                        v-else
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- BAGIAN 3: BOTTOM NAVIGATION (Floating Glass) -->
            <div class="absolute right-6 bottom-6 left-6 z-30">
                <nav
                    class="flex items-center justify-between rounded-[2rem] border border-white/50 bg-gradient-to-br from-[#F4E7FB] via-[#FFF0F0] px-6 py-3 shadow-xl shadow-[#C8A8E9]/20 backdrop-blur-lg"
                >
                    <!-- HOME (Active) -->
                    <button
                        class="flex w-12 flex-col items-center gap-1 text-[#C8A8E9]"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                            ></path>
                        </svg>
                        <span class="text-[8px] font-bold tracking-wide"
                            >Home</span
                        >
                    </button>

                    <!-- TIKET -->

                    <Link
                        href="/my-tickets"
                        class="flex w-12 flex-col items-center gap-1 text-[#9D8CA6] transition hover:text-[#7ECFE0]"
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
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                            ></path>
                        </svg>
                        <span class="text-[10px] font-bold tracking-wide">
                            E-Ticket
                        </span>
                    </Link>

                    <!-- INFO -->
                    <button
                        class="flex w-12 flex-col items-center gap-1 text-[#9D8CA6] transition hover:text-[#C3C7F4]"
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
                        <span class="text-[8px] font-bold tracking-wide"
                            >Info</span
                        >
                    </button>
                </nav>
            </div>

            <!-- MODAL KONFIRMASI -->
            <div
                v-if="showModal"
                class="fixed inset-0 z-[60] flex items-end justify-center p-0 sm:items-center sm:p-4"
            >
                <div
                    @click="showModal = false"
                    class="absolute inset-0 bg-[#5A4D61]/40 backdrop-blur-sm transition-opacity"
                ></div>
                <div
                    class="animate-slide-up relative z-10 max-h-[90vh] w-full max-w-[480px] overflow-y-auto rounded-t-[2.5rem] bg-white p-8 shadow-2xl sm:rounded-[2.5rem]"
                >
                    <div
                        class="mx-auto mb-8 h-1.5 w-12 rounded-full bg-[#F2F0F4]"
                    ></div>

                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-[#5A4D61]">
                                Booking
                            </h2>
                            <p class="mt-1 text-sm font-medium text-[#9D8CA6]">
                                {{ formatDateDisplay(selectedDate) }} •
                                {{ selectedSlot?.label }}
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submitBooking" class="space-y-5">
                        <!-- Input Unit -->
                        <div class="group relative">
                            <label
                                class="mb-2 ml-1 block text-xs font-bold tracking-wider text-[#9D8CA6] uppercase"
                                >Unit Apartemen</label
                            >
                            <input
                                type="text"
                                v-model="unitSearch"
                                @focus="showUnitDropdown = true"
                                placeholder="Cth: TWR-A-0101"
                                class="w-full rounded-2xl border-none bg-[#F4E7FB]/50 py-4 pl-5 font-bold text-[#5A4D61] uppercase placeholder-[#C8A8E9]/50 transition focus:bg-[#F4E7FB] focus:ring-2 focus:ring-[#C8A8E9]"
                            />
                            <div
                                v-if="
                                    showUnitDropdown && filteredUnits.length > 0
                                "
                                class="absolute z-50 mt-2 max-h-48 w-full overflow-y-auto rounded-2xl border border-[#F4E7FB] bg-white p-2 shadow-xl"
                            >
                                <div
                                    v-for="unit in filteredUnits"
                                    :key="unit"
                                    @click="selectUnit(unit)"
                                    class="cursor-pointer rounded-xl px-4 py-3 text-sm font-bold text-[#5A4D61] hover:bg-[#F4E7FB]"
                                >
                                    {{ unit }}
                                </div>
                            </div>
                            <p
                                v-if="form.errors.unit_number"
                                class="mt-2 ml-1 text-xs font-bold text-[#F6BCBA]"
                            >
                                {{ form.errors.unit_number }}
                            </p>
                        </div>

                        <!-- Input PIN -->
                        <div>
                            <label
                                class="mb-2 ml-1 block text-xs font-bold tracking-wider text-[#9D8CA6] uppercase"
                                >PIN Akses</label
                            >
                            <input
                                v-model="form.access_code"
                                type="password"
                                placeholder=""
                                class="[#C8A8E9]/50 w-full rounded-2xl border-none bg-[#F4E7FB]/50 py-4 pl-5 font-bold tracking-widest text-[#5A4D61] placeholder-[#C8A8E9]/50 transition focus:bg-[#F4E7FB] focus:ring-2 focus:ring-[#C8A8E9]"
                            />
                            <p
                                v-if="form.errors.access_code"
                                class="mt-2 ml-1 text-xs font-bold text-[#F6BCBA]"
                            >
                                {{ form.errors.access_code }}
                            </p>
                        </div>

                        <!-- Input Nama -->
                        <div>
                            <label
                                class="mb-2 ml-1 block text-xs font-bold tracking-wider text-[#9D8CA6] uppercase"
                                >Nama Pemain</label
                            >
                            <input
                                v-model="form.player_names"
                                type="text"
                                placeholder="Budi & Keluarga"
                                class="w-full rounded-2xl border-none bg-[#F4E7FB]/50 py-4 pl-5 font-medium text-[#5A4D61] placeholder-[#C8A8E9]/50 transition focus:bg-[#F4E7FB] focus:ring-2 focus:ring-[#C8A8E9]"
                            />
                        </div>

                        <!-- Terms -->
                        <div
                            class="flex items-start gap-3 rounded-2xl border border-[#F2F0F4] bg-[#FFF] p-4"
                            :class="{
                                'border-[#F6BCBA]': form.errors.agree_terms,
                            }"
                        >
                            <input
                                id="terms"
                                type="checkbox"
                                v-model="form.agree_terms"
                                class="mt-1 h-5 w-5 rounded-md border-[#C8A8E9] text-[#C8A8E9] focus:ring-[#C8A8E9]"
                            />
                            <label
                                for="terms"
                                class="text-xs leading-relaxed font-medium text-[#9D8CA6]"
                            >
                                Saya setuju dengan tata tertib. Keterlambatan
                                >15 menit sanksi
                                <span class="font-bold text-[#F6BCBA]"
                                    >banned 1 minggu.</span
                                >
                            </label>
                        </div>

                        <!-- Button -->
                        <button
                            :disabled="form.processing"
                            type="submit"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#C8A8E9] to-[#E3AADD] py-4 font-bold text-white shadow-xl shadow-[#C8A8E9]/30 transition hover:opacity-90 disabled:opacity-70"
                        >
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else>Booking Sekarang</span>
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
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                                ></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
@keyframes slide-up {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
.animate-slide-up {
    animation: slide-up 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>

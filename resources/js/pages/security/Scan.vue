<script setup>
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Html5QrcodeScanner, Html5QrcodeScanType } from 'html5-qrcode';
import { Shield } from 'lucide-vue-next';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

const manualCode = ref('');
const scanner = ref(null);
const scanResult = ref(null);
const scanStatus = ref('');
const showModal = ref(false);

onMounted(() => {
    nextTick(() => {
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            formatsToSupport: [0], // Hanya QR Code
            facingMode: 'environment', // Kamera Belakang
            supportedScanTypes: [
                Html5QrcodeScanType.SCAN_TYPE_CAMERA,
                Html5QrcodeScanType.SCAN_TYPE_FILE,
            ],
        };

        // false di belakang untuk mematikan log error merah bawaan
        scanner.value = new Html5QrcodeScanner('reader', config, false);

        scanner.value.render(onScanSuccess, (errorMessage) => {
            /* Sembunyikan error gagal baca kotak */
        });
    });
});

onUnmounted(() => {
    if (scanner.value) {
        try {
            scanner.value.clear();
        } catch (e) {
            console.error('Gagal mematikan scanner', e);
        }
    }
});

const onScanSuccess = (decodedText) => {
    if (scanner.value) scanner.value.pause(); // Jeda kamera
    validateTicket(decodedText);
};

const validateTicket = async (code) => {
    if (!code) return;
    try {
        const response = await axios.post(route('security.validate'), {
            booking_code: code,
        });
        scanResult.value = response.data.data;
        scanStatus.value = response.data.status;
        showModal.value = true;
    } catch (error) {
        scanStatus.value = 'error';
        scanResult.value = {
            message: error.response?.data?.message || 'Tiket Tidak Ditemukan',
        };
        showModal.value = true;
    }
};

const processCheckIn = () => {
    router.post(
        route('security.checkin'),
        { booking_code: scanResult.value.booking_code },
        {
            onSuccess: () => {
                showModal.value = false;
                alert('Check-In Berhasil!');
                if (scanner.value) scanner.value.resume(); // Nyalakan kamera lagi
            },
        },
    );
};

const closeModal = () => {
    showModal.value = false;
    manualCode.value = '';
    if (scanner.value) scanner.value.resume(); // Nyalakan kamera lagi
};
</script>

<template>
    <Head title="Security Scanner" />

    <!-- BACKGROUND DESKTOP (Gelap) -->
    <div
        class="flex min-h-screen w-full items-center justify-center bg-gray-900 font-sans"
    >
        <!-- CONTAINER APLIKASI (Mobile Layout) -->
        <div
            class="relative flex h-[100dvh] w-full max-w-[480px] flex-col overflow-hidden bg-[#EAEFF5] text-[#1A5F7A] shadow-2xl"
        >
            <!-- 1. HEADER -->
            <div class="relative z-20 flex-none p-6 pb-2">
                <div
                    class="flex w-full items-center justify-between rounded-[2rem] border border-white bg-white p-4 shadow-xl shadow-[#1A5F7A]/5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[#1A5F7A] text-white shadow-md"
                        >
                            <Shield class="h-6 w-6" />
                        </div>
                        <div>
                            <h1
                                class="text-lg leading-none font-black tracking-tight text-[#1A5F7A]"
                            >
                                Security Access
                            </h1>
                            <p
                                class="mt-1 inline-block rounded-md bg-[#1A5F7A] px-2 py-0.5 text-[10px] font-bold tracking-widest text-[#BEF264] uppercase"
                            >
                                Mediterania Court
                            </p>
                        </div>
                    </div>
                    <button
                        @click="router.post('/logout')"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FFE5E5] text-[#EF583D] transition-colors hover:bg-[#ffcccc]"
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
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- 2. SCANNER AREA (Flexible) -->
            <div class="relative flex min-h-0 flex-1 flex-col px-6 pb-2">
                <div
                    class="relative h-full w-full overflow-hidden rounded-[2.5rem] border-4 border-white bg-black shadow-2xl"
                >
                    <!-- CONTAINER LIBRARY -->
                    <div id="reader" class="h-full w-full bg-[#EAEFF5]"></div>

                    <!-- OVERLAY DEKORASI -->
                    <div
                        class="scan-overlay pointer-events-none absolute inset-0 z-10 -mt-20 flex flex-col items-center justify-center"
                    >
                        <div
                            class="relative h-64 w-64 rounded-[2rem] border-2 border-white/30"
                        >
                            <!-- Pojok-pojok -->
                            <div
                                class="absolute top-0 left-0 h-8 w-8 rounded-tl-xl border-t-4 border-l-4 border-[#BEF264]"
                            ></div>
                            <div
                                class="absolute top-0 right-0 h-8 w-8 rounded-tr-xl border-t-4 border-r-4 border-[#BEF264]"
                            ></div>
                            <div
                                class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-xl border-b-4 border-l-4 border-[#BEF264]"
                            ></div>
                            <div
                                class="absolute right-0 bottom-0 h-8 w-8 rounded-br-xl border-r-4 border-b-4 border-[#BEF264]"
                            ></div>
                            <!-- Laser Scan -->
                            <div
                                class="animate-scan absolute top-0 left-0 h-1 w-full bg-[#BEF264] shadow-[0_0_20px_rgba(190,242,100,0.8)]"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. INPUT MANUAL (Bottom) -->
            <div class="relative z-20 flex-none p-6 pt-2 pb-8">
                <div
                    class="flex w-full gap-2 rounded-[2rem] border border-white bg-white p-2 shadow-xl shadow-[#1A5F7A]/10"
                >
                    <div class="relative flex-1">
                        <div
                            class="absolute top-1/2 left-4 -translate-y-1/2 text-slate-300"
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
                                    d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                ></path>
                            </svg>
                        </div>
                        <input
                            v-model="manualCode"
                            type="text"
                            placeholder="Input Kode Manual..."
                            class="h-14 w-full rounded-[1.5rem] border-none bg-[#F1F5F9] pr-4 pl-12 font-bold tracking-widest text-[#1A5F7A] placeholder-slate-400 transition-all focus:ring-2 focus:ring-[#BEF264]"
                            @keyup.enter="validateTicket(manualCode)"
                        />
                    </div>
                    <button
                        @click="validateTicket(manualCode)"
                        class="flex h-14 w-14 items-center justify-center rounded-[1.5rem] bg-[#1A5F7A] text-[#BEF264] shadow-lg transition hover:bg-[#154c61] active:scale-95"
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
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- MODAL RESULT -->
            <transition name="modal">
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-6"
                >
                    <div
                        class="absolute inset-0 flex justify-center bg-gray-900/80 backdrop-blur-sm"
                        @click="closeModal"
                    >
                        <div class="h-full w-full max-w-[480px]"></div>
                    </div>

                    <div
                        class="animate-pop-up relative z-50 w-full max-w-xs overflow-hidden rounded-[2.5rem] bg-white shadow-2xl"
                    >
                        <div
                            class="p-8 pb-6 text-center"
                            :class="{
                                'bg-[#F0FDF4]': scanStatus === 'success',
                                'bg-[#FFF7ED]': scanStatus === 'warning',
                                'bg-[#FEF2F2]': scanStatus === 'error',
                            }"
                        >
                            <div
                                class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-white shadow-sm"
                            >
                                <svg
                                    v-if="scanStatus === 'success'"
                                    class="h-10 w-10 text-green-500"
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
                                <svg
                                    v-else-if="scanStatus === 'warning'"
                                    class="h-10 w-10 text-orange-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    ></path>
                                </svg>
                                <svg
                                    v-else
                                    class="h-10 w-10 text-red-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M6 18L18 6M6 6l12 12"
                                    ></path>
                                </svg>
                            </div>
                            <h2
                                class="text-2xl font-black tracking-tight"
                                :class="{
                                    'text-green-700': scanStatus === 'success',
                                    'text-orange-600': scanStatus === 'warning',
                                    'text-red-600': scanStatus === 'error',
                                }"
                            >
                                {{
                                    scanStatus === 'success'
                                        ? 'ACCESS GRANTED'
                                        : scanStatus === 'warning'
                                          ? 'WARNING'
                                          : 'ACCESS DENIED'
                                }}
                            </h2>
                            <p
                                class="mt-1 text-xs font-bold tracking-wide text-slate-500 uppercase"
                            >
                                {{ scanResult?.message }}
                            </p>
                        </div>
                        <div
                            v-if="scanStatus !== 'error'"
                            class="space-y-3 p-6"
                        >
                            <div
                                class="flex items-center justify-between rounded-2xl border border-slate-100 bg-[#F8FAFC] p-4"
                            >
                                <span
                                    class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                                    >UNIT</span
                                >
                                <span
                                    class="text-xl font-black text-[#1A5F7A]"
                                    >{{ scanResult.unit }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between rounded-2xl border border-slate-100 bg-[#F8FAFC] p-4"
                            >
                                <span
                                    class="text-xs font-bold tracking-widest text-slate-400 uppercase"
                                    >TIME</span
                                >
                                <span
                                    class="text-lg font-black text-[#1A5F7A]"
                                    >{{ scanResult.time }}</span
                                >
                            </div>
                            <button
                                v-if="scanStatus === 'success'"
                                @click="processCheckIn"
                                class="mt-2 w-full rounded-2xl bg-[#1A5F7A] py-4 text-lg font-bold text-white shadow-xl shadow-[#1A5F7A]/30 transition-all hover:bg-[#154c61] active:scale-95"
                            >
                                CONFIRM CHECK-IN
                            </button>
                        </div>
                        <div class="p-6 pt-0">
                            <button
                                @click="closeModal"
                                class="w-full rounded-2xl border-2 border-slate-100 bg-white py-4 font-bold text-slate-400 transition-colors hover:border-[#1A5F7A] hover:text-[#1A5F7A]"
                            >
                                CLOSE / SCAN NEXT
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<style>
/* CSS HTML5-QRCODE HACKS */
#reader {
    display: flex !important;
    flex-direction: column;
    height: 100%;
    width: 100%;
    border: none !important;
    background-color: black;
}

#reader__dashboard {
    order: 2;
    padding: 10px;
    background-color: #eaeff5;
    border-top: 1px solid white;
    z-index: 50;
    text-align: center;
}

#reader__scan_region {
    order: 1;
    flex: 1;
    min-height: 0;
    overflow: hidden;
    position: relative;
    background-color: black;
}

#reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 0 !important;
}

#html5-qrcode-button-camera-permission {
    display: inline-block;
    padding: 10px 20px;
    background-color: #1a5f7a;
    color: white;
    font-weight: bold;
    font-size: 12px;
    border-radius: 1rem;
    border: none;
    box-shadow: 0 4px 10px rgba(26, 95, 122, 0.3);
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 5px;
}

#html5-qrcode-button-camera-permission:hover {
    background-color: #154c61;
}

#reader__dashboard_section_swaplink {
    display: block;
    margin-top: 5px;
    font-size: 11px;
    font-weight: bold;
    color: #1a5f7a;
    text-decoration: none;
    opacity: 0.8;
}

#reader__dashboard_section_csr span {
    display: none !important;
}
#reader__scan_region img {
    display: none;
}

@keyframes scan {
    0% {
        top: 0;
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        top: 100%;
        opacity: 0;
    }
}
.animate-scan {
    animation: scan 2.5s linear infinite;
}
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
@keyframes popUp {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
.animate-pop-up {
    animation: popUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>

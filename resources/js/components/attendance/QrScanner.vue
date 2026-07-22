<script setup>
import { ref, onUnmounted } from 'vue'
import { Html5Qrcode } from 'html5-qrcode'
import { CameraIcon, ArrowPathIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'

const emit = defineEmits(['scan', 'error'])
const scanning = ref(false)
const cameraReady = ref(false)
const scanError = ref(null)
let html5QrCode = null

async function start() {
  scanning.value = true
  scanError.value = null

  try {
    html5QrCode = new Html5Qrcode('qr-reader')
    const cameras = await Html5Qrcode.getCameras()
    if (!cameras.length) {
      scanError.value = 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera.'
      scanning.value = false
      emit('error', scanError.value)
      return
    }
    const backCamera = cameras.find((c) => c.id.toLowerCase().includes('back')) || cameras[0]
    await html5QrCode.start(
      backCamera.id,
      { fps: 10, qrbox: { width: 250, height: 250 } },
      (decodedText) => {
        stopScanner()
        emit('scan', decodedText)
      },
      () => {}
    )
    cameraReady.value = true
  } catch (err) {
    scanError.value = err.message || 'Gagal mengakses kamera. Periksa izin kamera.'
    scanning.value = false
    emit('error', scanError.value)
  }
}

function stopScanner() {
  if (html5QrCode) {
    html5QrCode.stop().catch(() => {})
    html5QrCode = null
  }
  scanning.value = false
  cameraReady.value = false
}

defineExpose({ start, stopScanner })
onUnmounted(() => { stopScanner() })
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div id="qr-reader" class="w-full [&>video]:rounded-t-2xl" />

    <div v-if="!scanning && !cameraReady && !scanError" class="p-12 text-center animate-fade-in">
      <div class="mx-auto w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4">
        <CameraIcon class="w-10 h-10 text-blue-500" />
      </div>
      <p class="text-slate-800 font-semibold mb-1">Arahkan QR Code ke Kamera</p>
      <p class="text-sm text-slate-500 mb-6">Pastikan QR Code terlihat jelas dan cukup cahaya</p>
      <button
        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition-all shadow-sm hover:shadow-md"
        @click="start">
        <CameraIcon class="w-5 h-5" />
        Mulai Scan QR
      </button>
    </div>

    <div v-if="scanError" class="p-6 bg-amber-50 text-center border-t border-amber-100">
      <div class="flex items-center justify-center gap-2 text-amber-700 mb-3">
        <ExclamationCircleIcon class="w-5 h-5" />
        <span class="font-semibold">{{ scanError }}</span>
      </div>
      <button class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm transition-colors" @click="start">
        Coba Lagi
      </button>
    </div>

    <div v-if="scanning" class="p-3 flex justify-center border-t bg-slate-50">
      <button
        class="flex items-center gap-2 px-4 py-2 text-sm text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
        @click="stopScanner">
        <ArrowPathIcon class="w-4 h-4" />
        Hentikan Scanner
      </button>
    </div>
  </div>
</template>

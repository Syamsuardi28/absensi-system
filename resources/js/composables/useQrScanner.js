import { ref, onUnmounted } from 'vue'
import { Html5Qrcode } from 'html5-qrcode'

export function useQrScanner() {
  const isScanning = ref(false)
  const scanResult = ref(null)
  const scanError = ref(null)
  let html5QrCode = null

  async function startScanner(elementId = 'qr-reader') {
    try {
      html5QrCode = new Html5Qrcode(elementId)
      isScanning.value = true
      scanError.value = null

      const cameras = await Html5Qrcode.getCameras()

      let cameraId = null
      if (cameras && cameras.length) {
        const backCam = cameras.find(c => c.label.toLowerCase().includes('back'))
        cameraId = backCam ? backCam.id : cameras[0].id
      }

      await html5QrCode.start(
        cameraId || { facingMode: 'environment' },
        {
          fps: 10,
          qrbox: { width: 250, height: 250 },
        },
        (decodedText) => {
          scanResult.value = decodedText
          stopScanner()
        },
        () => {}
      )
    } catch (err) {
      scanError.value = err.message || 'Gagal mengakses kamera'
      isScanning.value = false
    }
  }

  async function stopScanner() {
    if (html5QrCode && isScanning.value) {
      try {
        await html5QrCode.stop()
      } catch {}
      html5QrCode = null
      isScanning.value = false
    }
  }

  function resetResult() {
    scanResult.value = null
    scanError.value = null
  }

  onUnmounted(() => {
    stopScanner()
  })

  return { isScanning, scanResult, scanError, startScanner, stopScanner, resetResult }
}

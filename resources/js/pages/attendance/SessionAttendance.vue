<script setup>
import { ref, onMounted, watch } from 'vue'
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import schedulesApi from '../../api/modules/schedules'
import studentsApi from '../../api/modules/students'
import attendanceApi from '../../api/modules/attendance'
import StatusBadge from '../../components/common/StatusBadge.vue'
import { AcademicCapIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'

let watchToken = 0

const schedules = ref([])
const students = ref([])
const attendances = ref([])
const loading = ref(false)
const loadingSchedules = ref(false)
const loadingStudents = ref(false)
const saving = ref(false)
const error = ref('')
const success = ref('')
const selectedScheduleId = ref('')
const selectedDate = ref(new Date().toISOString().slice(0, 10))

const attendanceStatuses = [
  { value: 'hadir', label: 'Hadir', color: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
  { value: 'izin', label: 'Izin', color: 'text-blue-600 bg-blue-50 border-blue-200' },
  { value: 'sakit', label: 'Sakit', color: 'text-violet-600 bg-violet-50 border-violet-200' },
  { value: 'alpa', label: 'Alpa', color: 'text-rose-600 bg-rose-50 border-rose-200' },
]

const studentStatuses = ref({})

onMounted(fetchSchedules)

async function fetchSchedules() {
  loadingSchedules.value = true
  try {
    const { data } = await schedulesApi.list()
    schedules.value = data.data?.data ?? []
  } catch {
    schedules.value = []
  } finally {
    loadingSchedules.value = false
  }
}

watch(selectedScheduleId, async (scheduleId) => {
  if (!scheduleId) {
    students.value = []
    attendances.value = []
    studentStatuses.value = {}
    return
  }
  const token = ++watchToken
  await Promise.all([fetchStudents(scheduleId, token), fetchExistingAttendances(scheduleId, token)])
})

async function fetchStudents(scheduleId, token = null) {
  loadingStudents.value = true
  try {
    const schedule = schedules.value.find((s) => s.id == scheduleId)
    const classId = schedule?.class_id
    if (!classId) {
      students.value = []
      return
    }
    const { data } = await studentsApi.list({ class_id: classId })
    if (token !== null && token !== watchToken) return
    students.value = data.data?.data ?? []
    studentStatuses.value = {}
    students.value.forEach((s) => {
      studentStatuses.value[s.id] = 'hadir'
    })
  } catch {
    students.value = []
  } finally {
    if (token === null || token === watchToken) {
      loadingStudents.value = false
    }
  }
}

async function fetchExistingAttendances(scheduleId, token = null) {
  try {
    const { data } = await attendanceApi.report({
      schedule_id: scheduleId,
      date: selectedDate.value,
    })
    if (token !== null && token !== watchToken) return
    const existing = data.data?.data ?? []
    attendances.value = existing

    existing.forEach((a) => {
      const studentId = a.student_id || a.user_id
      if (studentId && a.status) {
        studentStatuses.value[studentId] = a.status
      }
    })
  } catch {
    attendances.value = []
  }
}

function setStatus(studentId, status) {
  studentStatuses.value[studentId] = status
}

function onDateChange() {
  if (selectedScheduleId.value) {
    fetchExistingAttendances(selectedScheduleId.value)
  }
}

function getStatusClass(status) {
  return attendanceStatuses.find((s) => s.value === status)?.color || attendanceStatuses[0].color
}

async function saveAll() {
  saving.value = true
  error.value = ''
  success.value = ''
  try {
    const payload = {
      schedule_id: selectedScheduleId.value,
      date: selectedDate.value,
      attendances: Object.entries(studentStatuses.value).map(([studentId, status]) => {
        const student = students.value.find((s) => s.id == studentId)
        return {
          user_id: student?.user_id,
          status,
        }
      }),
    }
    await attendanceApi.session(payload)
    success.value = 'Absensi berhasil disimpan.'
    await fetchExistingAttendances(selectedScheduleId.value)
  } catch {
    error.value = 'Gagal menyimpan absensi.'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <TeacherLayout>
    <div class="mb-6">
      <h1 class="font-display text-2xl text-slate-900">Absensi per Sesi</h1>
      <p class="text-sm text-slate-500 mt-1">Kelola absensi siswa per sesi pelajaran</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="w-full sm:w-64">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Pilih Jadwal</label>
          <select
            v-model="selectedScheduleId"
            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
            :disabled="loadingSchedules"
          >
            <option value="">-- Pilih Jadwal --</option>
            <option v-for="s in schedules" :key="s.id" :value="s.id">
              {{ s.subject?.name }} &middot; {{ s.class?.name }} &middot; {{ s.day }} ({{ s.start_time }} - {{ s.end_time }})
            </option>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label>
          <input
            v-model="selectedDate"
            type="date"
            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
            @change="onDateChange"
          />
        </div>
      </div>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>
    <div v-if="success" class="p-3.5 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl mb-4">{{ success }}</div>

    <div v-if="!selectedScheduleId" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
      <AcademicCapIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <p class="text-slate-500 font-medium">Pilih jadwal terlebih dahulu</p>
      <p class="text-sm text-slate-400 mt-1">Silakan pilih jadwal pelajaran untuk melihat daftar siswa.</p>
    </div>

    <div v-else-if="loadingStudents" class="flex items-center justify-center py-16">
      <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
    </div>

    <div v-else-if="!students.length" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
      <p class="text-slate-500 font-medium">Tidak ada siswa</p>
      <p class="text-sm text-slate-400 mt-1">Kelas ini belum memiliki siswa terdaftar.</p>
    </div>

    <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase w-12">No</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Sudah Tercatat</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(student, idx) in students" :key="student.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-3 text-sm text-slate-400">{{ idx + 1 }}</td>
              <td class="px-6 py-3 text-sm text-slate-900 font-medium">{{ student.user?.name }}</td>
              <td class="px-6 py-3 text-sm text-slate-500">{{ student.nis }}</td>
              <td class="px-6 py-3">
                <div class="flex gap-1.5 flex-wrap">
                  <button
                    v-for="st in attendanceStatuses"
                    :key="st.value"
                    @click="setStatus(student.id, st.value)"
                    :class="[
                      'px-2.5 py-1 rounded-lg text-xs font-medium border transition-colors',
                      studentStatuses[student.id] === st.value
                        ? st.color + ' border-current'
                        : 'bg-white text-slate-400 border-slate-200 hover:border-slate-300',
                    ]"
                  >
                    {{ st.label }}
                  </button>
                </div>
              </td>
              <td class="px-6 py-3">
                <StatusBadge
                  v-if="studentStatuses[student.id]"
                  :variant="studentStatuses[student.id]"
                >
                  {{ attendanceStatuses.find((s) => s.value === studentStatuses[student.id])?.label }}
                </StatusBadge>
                <span v-else class="text-sm text-slate-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-slate-200 flex justify-end">
        <button
          @click="saveAll"
          :disabled="saving || !students.length"
          class="rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all bg-blue-600 text-white disabled:opacity-50 text-sm inline-flex items-center gap-2"
        >
          <svg v-if="saving" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          <CheckCircleIcon v-else class="w-4 h-4" />
          {{ saving ? 'Menyimpan...' : 'Simpan Absensi' }}
        </button>
      </div>
    </div>
  </TeacherLayout>
</template>

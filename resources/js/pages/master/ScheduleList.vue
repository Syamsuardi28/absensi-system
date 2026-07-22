<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import schedulesApi from '../../api/modules/schedules'
import classesApi from '../../api/modules/classes'
import subjectsApi from '../../api/modules/subjects'
import teachersApi from '../../api/modules/teachers'
import BaseModal from '../../components/common/BaseModal.vue'
import { PencilSquareIcon, TrashIcon, PlusIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline'

const schedules = ref([])
const classes = ref([])
const subjects = ref([])
const teachers = ref([])
const loading = ref(false)
const error = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ class_id: '', subject_id: '', teacher_id: '', day: 'senin', start_time: '', end_time: '' })
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']

onMounted(fetchData)

async function fetchData() {
  loading.value = true
  try {
    const [{ data: sc }, { data: c }, { data: sb }, { data: t }] = await Promise.all([
      schedulesApi.list(),
      classesApi.list(),
      subjectsApi.list(),
      teachersApi.list(),
    ])
    schedules.value = sc.data?.data ?? []
    classes.value = c.data?.data ?? []
    subjects.value = sb.data?.data ?? []
    teachers.value = t.data?.data ?? []
  } catch {
    schedules.value = []
    classes.value = []
    subjects.value = []
    teachers.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { class_id: '', subject_id: '', teacher_id: '', day: 'senin', start_time: '', end_time: '' }
  showModal.value = true
}

function openEdit(s) {
  editing.value = s
  form.value = {
    class_id: s.class_id,
    subject_id: s.subject_id,
    teacher_id: s.teacher_id,
    day: s.day,
    start_time: s.start_time,
    end_time: s.end_time,
  }
  showModal.value = true
}

async function handleSave() {
  error.value = ''
  try {
    if (editing.value) {
      await schedulesApi.update(editing.value.id, form.value)
    } else {
      await schedulesApi.store(form.value)
    }
    showModal.value = false
    await fetchData()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menyimpan'
  }
}

async function handleDelete(s) {
  if (!confirm('Hapus jadwal ini?')) return
  error.value = ''
  try {
    await schedulesApi.delete(s.id)
    await fetchData()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menghapus'
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="font-display text-2xl text-slate-900">Jadwal Pelajaran</h1>
        <p class="text-sm text-slate-500">Kelola jadwal per kelas</p>
      </div>
      <button @click="openCreate" class="bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all inline-flex items-center gap-2 hover:bg-blue-700">
        <PlusIcon class="w-5 h-5" /> Tambah Jadwal
      </button>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Mapel</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Guru</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Hari</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Jam</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading">
              <td colspan="6" class="px-5 py-3.5">
                <div class="flex items-center justify-center py-16">
                  <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-200 border-t-blue-600"></div>
                  <span class="ml-3 text-slate-400 text-sm">Memuat data...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!schedules.length">
              <td colspan="6" class="px-5 py-3.5">
                <div class="flex flex-col items-center justify-center py-16">
                  <CalendarDaysIcon class="w-12 h-12 text-slate-300" />
                  <p class="mt-3 text-slate-400 text-sm">Belum ada data jadwal</p>
                </div>
              </td>
            </tr>
            <tr v-for="s in schedules" :key="s.id" class="hover:bg-slate-50">
              <td class="px-5 py-3.5 font-medium text-sm text-slate-900">{{ s.class?.name }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ s.subject?.name }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ s.teacher?.user?.name }}</td>
              <td class="px-5 py-3.5 text-sm capitalize text-slate-500">{{ s.day_label }}</td>
              <td class="px-5 py-3.5 text-sm font-mono text-slate-500">{{ s.start_time }} - {{ s.end_time }}</td>
              <td class="px-5 py-3.5">
                <div class="flex gap-1">
                  <button @click="openEdit(s)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button @click="handleDelete(s)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <BaseModal :show="showModal" :title="editing ? 'Edit Jadwal' : 'Tambah Jadwal'" @close="showModal = false">
      <form @submit.prevent="handleSave" class="space-y-3">
        <div>
          <select v-model="form.class_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="">Pilih Kelas</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <select v-model="form.subject_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="">Pilih Mata Pelajaran</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <select v-model="form.teacher_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="">Pilih Guru</option>
            <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.user?.name }}</option>
          </select>
        </div>
        <div>
          <select v-model="form.day" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none capitalize">
            <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <input v-model="form.start_time" type="time" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
          </div>
          <div>
            <input v-model="form.end_time" type="time" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
          </div>
        </div>
        <div class="flex gap-3 pt-3">
          <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md hover:bg-blue-700 transition-all">Simpan</button>
          <button type="button" @click="showModal = false" class="px-6 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 font-medium text-sm text-slate-600 shadow-sm hover:shadow-md transition-all">Batal</button>
        </div>
      </form>
    </BaseModal>
  </AdminLayout>
</template>

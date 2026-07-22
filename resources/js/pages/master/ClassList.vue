<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import classesApi from '../../api/modules/classes'
import schoolYearsApi from '../../api/modules/schoolYears'
import teachersApi from '../../api/modules/teachers'
import BaseModal from '../../components/common/BaseModal.vue'
import { PencilSquareIcon, TrashIcon, PlusIcon, FolderOpenIcon } from '@heroicons/vue/24/outline'

const classes = ref([])
const schoolYears = ref([])
const teachers = ref([])
const loading = ref(false)
const error = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', school_year_id: '', homeroom_teacher_id: '' })

onMounted(fetchData)

async function fetchData() {
  loading.value = true
  try {
    const [{ data: c }, { data: sy }, { data: t }] = await Promise.all([
      classesApi.list(),
      schoolYearsApi.list(),
      teachersApi.list(),
    ])
    classes.value = c.data?.data ?? []
    schoolYears.value = sy.data?.data ?? []
    teachers.value = t.data?.data ?? []
  } catch {
    classes.value = []
    schoolYears.value = []
    teachers.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', school_year_id: '', homeroom_teacher_id: '' }
  showModal.value = true
}

function openEdit(cls) {
  editing.value = cls
  form.value = {
    name: cls.name,
    school_year_id: cls.school_year_id,
    homeroom_teacher_id: cls.homeroom_teacher_id || '',
  }
  showModal.value = true
}

async function handleSave() {
  error.value = ''
  try {
    if (editing.value) {
      await classesApi.update(editing.value.id, form.value)
    } else {
      await classesApi.store(form.value)
    }
    showModal.value = false
    await fetchData()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menyimpan'
  }
}

async function handleDelete(cls) {
  if (!confirm(`Hapus ${cls.name}?`)) return
  error.value = ''
  try {
    await classesApi.delete(cls.id)
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
        <h1 class="font-display text-2xl text-slate-900">Data Kelas</h1>
        <p class="text-sm text-slate-500">Kelola kelas dan wali kelas</p>
      </div>
      <button @click="openCreate" class="bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all inline-flex items-center gap-2 hover:bg-blue-700">
        <PlusIcon class="w-5 h-5" /> Tambah Kelas
      </button>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Tahun Ajaran</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Wali Kelas</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Siswa</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading">
              <td colspan="5" class="px-5 py-3.5">
                <div class="flex items-center justify-center py-16">
                  <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-200 border-t-blue-600"></div>
                  <span class="ml-3 text-slate-400 text-sm">Memuat data...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!classes.length">
              <td colspan="5" class="px-5 py-3.5">
                <div class="flex flex-col items-center justify-center py-16">
                  <FolderOpenIcon class="w-12 h-12 text-slate-300" />
                  <p class="mt-3 text-slate-400 text-sm">Belum ada data kelas</p>
                </div>
              </td>
            </tr>
            <tr v-for="c in classes" :key="c.id" class="hover:bg-slate-50">
              <td class="px-5 py-3.5 font-medium text-sm text-slate-900">{{ c.name }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ c.school_year?.name || '-' }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ c.homeroom_teacher?.user?.name || '-' }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ c.students_count ?? 0 }}</td>
              <td class="px-5 py-3.5">
                <div class="flex gap-1">
                  <button @click="openEdit(c)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button @click="handleDelete(c)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <BaseModal :show="showModal" :title="editing ? 'Edit Kelas' : 'Tambah Kelas'" @close="showModal = false">
      <form @submit.prevent="handleSave" class="space-y-3">
        <div>
          <input v-model="form.name" placeholder="Nama Kelas" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div>
          <select v-model="form.school_year_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="">Pilih Tahun Ajaran</option>
            <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">{{ sy.name }}</option>
          </select>
        </div>
        <div>
          <select v-model="form.homeroom_teacher_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
            <option value="">Pilih Wali Kelas (opsional)</option>
            <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.user?.name }}</option>
          </select>
        </div>
        <div class="flex gap-3 pt-3">
          <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md hover:bg-blue-700 transition-all">Simpan</button>
          <button type="button" @click="showModal = false" class="px-6 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 font-medium text-sm text-slate-600 shadow-sm hover:shadow-md transition-all">Batal</button>
        </div>
      </form>
    </BaseModal>
  </AdminLayout>
</template>

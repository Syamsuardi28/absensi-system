<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import teachersApi from '../../api/modules/teachers'
import subjectsApi from '../../api/modules/subjects'
import BaseModal from '../../components/common/BaseModal.vue'
import { PencilSquareIcon, TrashIcon, PlusIcon, AcademicCapIcon } from '@heroicons/vue/24/outline'

const teachers = ref([])
const subjects = ref([])
const loading = ref(false)
const error = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', email: '', nip: '', phone: '', password: '', subject_ids: [] })

onMounted(() => { fetchTeachers(); fetchSubjects() })

async function fetchTeachers() {
  loading.value = true
  try {
    const { data } = await teachersApi.list()
    teachers.value = data.data?.data ?? []
  } catch {
    teachers.value = []
  } finally {
    loading.value = false
  }
}

async function fetchSubjects() {
  try {
    const { data } = await subjectsApi.list()
    subjects.value = data.data?.data ?? []
  } catch {
    subjects.value = []
  }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', email: '', nip: '', phone: '', password: '', subject_ids: [] }
  showModal.value = true
}

function openEdit(t) {
  editing.value = t
  form.value = {
    name: t.user?.name || '',
    email: t.user?.email || '',
    nip: t.nip,
    phone: t.user?.phone || '',
    password: '',
    subject_ids: t.subjects?.map(s => s.id) || [],
  }
  showModal.value = true
}

function toggleSubject(id) {
  const idx = form.value.subject_ids.indexOf(id)
  if (idx > -1) {
    form.value.subject_ids.splice(idx, 1)
  } else {
    form.value.subject_ids.push(id)
  }
}

async function handleSave() {
  error.value = ''
  try {
    if (editing.value) {
      await teachersApi.update(editing.value.id, { ...form.value, password: form.value.password || undefined })
    } else {
      await teachersApi.store(form.value)
    }
    showModal.value = false
    await fetchTeachers()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menyimpan'
  }
}

async function handleDelete(t) {
  if (!confirm(`Hapus ${t.user?.name}?`)) return
  error.value = ''
  try {
    await teachersApi.delete(t.id)
    await fetchTeachers()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menghapus'
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="font-display text-2xl text-slate-900">Data Guru</h1>
        <p class="text-sm text-slate-500">Kelola data guru dan mata pelajaran yang diampu</p>
      </div>
      <button @click="openCreate" class="bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all inline-flex items-center gap-2 hover:bg-blue-700">
        <PlusIcon class="w-5 h-5" /> Tambah Guru
      </button>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">NIP</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Mapel</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
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
            <tr v-else-if="!teachers.length">
              <td colspan="5" class="px-5 py-3.5">
                <div class="flex flex-col items-center justify-center py-16">
                  <AcademicCapIcon class="w-12 h-12 text-slate-300" />
                  <p class="mt-3 text-slate-400 text-sm">Belum ada data guru</p>
                </div>
              </td>
            </tr>
            <tr v-for="t in teachers" :key="t.id" class="hover:bg-slate-50">
              <td class="px-5 py-3.5 font-medium text-sm text-slate-900">{{ t.user?.name }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500 font-mono">{{ t.nip }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ t.subjects?.map(s => s.name).join(', ') || '-' }}</td>
              <td class="px-5 py-3.5 text-sm text-slate-500">{{ t.user?.email }}</td>
              <td class="px-5 py-3.5">
                <div class="flex gap-1">
                  <button @click="openEdit(t)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button @click="handleDelete(t)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <BaseModal :show="showModal" :title="editing ? 'Edit Guru' : 'Tambah Guru'" @close="showModal = false">
      <form @submit.prevent="handleSave" class="space-y-3">
        <div>
          <input v-model="form.name" placeholder="Nama" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div>
          <input v-model="form.email" type="email" placeholder="Email" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div>
          <input v-model="form.nip" placeholder="NIP" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div>
          <input v-model="form.phone" placeholder="Telepon" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div class="text-sm">
          <p class="mb-2 font-medium text-slate-700">Mata Pelajaran:</p>
          <div class="grid grid-cols-2 gap-1 max-h-32 overflow-auto p-2 border border-slate-200 rounded-xl">
            <label v-for="s in subjects" :key="s.id" class="flex items-center gap-1.5 text-xs py-1 text-slate-600 cursor-pointer">
              <input type="checkbox" :checked="form.subject_ids.includes(s.id)" @change="toggleSubject(s.id)" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20" />
              {{ s.name }}
            </label>
          </div>
        </div>
        <div>
          <input v-model="form.password" type="password" placeholder="Password" :required="!editing" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div class="flex gap-3 pt-3">
          <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md hover:bg-blue-700 transition-all">Simpan</button>
          <button type="button" @click="showModal = false" class="px-6 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 font-medium text-sm text-slate-600 shadow-sm hover:shadow-md transition-all">Batal</button>
        </div>
      </form>
    </BaseModal>
  </AdminLayout>
</template>

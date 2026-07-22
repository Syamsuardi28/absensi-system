<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import subjectsApi from '../../api/modules/subjects'
import BaseModal from '../../components/common/BaseModal.vue'
import { PencilSquareIcon, TrashIcon, PlusIcon, BookOpenIcon } from '@heroicons/vue/24/outline'

const subjects = ref([])
const loading = ref(false)
const error = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', code: '' })

onMounted(fetchSubjects)

async function fetchSubjects() {
  loading.value = true
  try {
    const { data } = await subjectsApi.list()
    subjects.value = data.data?.data ?? []
  } catch {
    subjects.value = []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', code: '' }
  showModal.value = true
}

function openEdit(s) {
  editing.value = s
  form.value = { name: s.name, code: s.code }
  showModal.value = true
}

async function handleSave() {
  error.value = ''
  try {
    if (editing.value) {
      await subjectsApi.update(editing.value.id, form.value)
    } else {
      await subjectsApi.store(form.value)
    }
    showModal.value = false
    await fetchSubjects()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menyimpan'
  }
}

async function handleDelete(s) {
  if (!confirm(`Hapus ${s.name}?`)) return
  error.value = ''
  try {
    await subjectsApi.delete(s.id)
    await fetchSubjects()
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menghapus'
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="font-display text-2xl text-slate-900">Mata Pelajaran</h1>
        <p class="text-sm text-slate-500">Kelola mata pelajaran</p>
      </div>
      <button @click="openCreate" class="bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md transition-all inline-flex items-center gap-2 hover:bg-blue-700">
        <PlusIcon class="w-5 h-5" /> Tambah Mapel
      </button>
    </div>

    <div v-if="error" class="p-3.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl mb-4">{{ error }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Kode</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading">
              <td colspan="3" class="px-5 py-3.5">
                <div class="flex items-center justify-center py-16">
                  <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-200 border-t-blue-600"></div>
                  <span class="ml-3 text-slate-400 text-sm">Memuat data...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="!subjects.length">
              <td colspan="3" class="px-5 py-3.5">
                <div class="flex flex-col items-center justify-center py-16">
                  <BookOpenIcon class="w-12 h-12 text-slate-300" />
                  <p class="mt-3 text-slate-400 text-sm">Belum ada data mata pelajaran</p>
                </div>
              </td>
            </tr>
            <tr v-for="s in subjects" :key="s.id" class="hover:bg-slate-50">
              <td class="px-5 py-3.5 font-medium text-sm text-slate-900">{{ s.name }}</td>
              <td class="px-5 py-3.5 text-sm font-mono text-slate-500">{{ s.code }}</td>
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

    <BaseModal :show="showModal" :title="editing ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'" @close="showModal = false">
      <form @submit.prevent="handleSave" class="space-y-3">
        <div>
          <input v-model="form.name" placeholder="Nama Mata Pelajaran" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div>
          <input v-model="form.code" placeholder="Kode (contoh: MTK)" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
        </div>
        <div class="flex gap-3 pt-3">
          <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl px-4 py-2.5 font-medium shadow-sm hover:shadow-md hover:bg-blue-700 transition-all">Simpan</button>
          <button type="button" @click="showModal = false" class="px-6 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 font-medium text-sm text-slate-600 shadow-sm hover:shadow-md transition-all">Batal</button>
        </div>
      </form>
    </BaseModal>
  </AdminLayout>
</template>

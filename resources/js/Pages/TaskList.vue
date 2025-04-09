<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import TaskForm from './TaskForm.vue'

const tasks = ref([])
const search = ref('')
const status = ref('')
const selectedTask = ref(null)

const getTasks = async () => {
  const res = await axios.get('/api/tasks', {
    params: {
      search: search.value,
      status: status.value
    }
  })
  tasks.value = res.data.data || res.data
}

const editTask = (task) => {
  selectedTask.value = task
}

onMounted(() => {
  getTasks()
})

const toggleStatus = async (task) => {
  await axios.patch(`/api/tasks/${task.id}/toggle`)
  getTasks()
}

const deleteTask = async (task) => {
  await axios.delete(`/api/tasks/${task.id}`)
  getTasks()
}

const restoreTask = async (task) => {
  await axios.patch(`/api/tasks/${task.id}/restore`)
  getTasks()
}

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString()
}

const isOverdue = (task) => {
  if (!task.due_date || task.status === 'completed') return false
  const today = new Date().setHours(0, 0, 0, 0)
  const due = new Date(task.due_date).setHours(0, 0, 0, 0)
  return due < today
}
</script>


<template>
  <div class="max-w-5xl mx-auto px-4 py-10 space-y-10">
    <h1 class="text-4xl font-bold text-center text-indigo-700">📝 My Task Manager</h1>

    <!-- Task Form -->
    <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-200">
      <TaskForm :task="selectedTask" @task-saved="getTasks" />
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row items-center gap-4">
      <input v-model="search" @input="getTasks" type="text" placeholder="🔍 Search tasks..."
        class="w-full md:flex-1 p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none transition" />

      <select v-model="status" @change="getTasks"
        class="w-full md:w-1/3 p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
      </select>
    </div>

    <!-- Task List -->
    <div class="space-y-6">
      <div v-for="task in tasks" :key="task.id"
        class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition border border-gray-200 flex flex-col md:flex-row md:justify-between gap-4">
        <!-- Task Info -->
        <div class="flex-1 space-y-1">
          <h2 class="text-xl font-semibold text-gray-800">{{ task.title }} <span v-if="task.category"
              class="ml-3 text-xs font-medium bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
              {{ task.category.name }}
            </span></h2>


          <p class="text-gray-600">{{ task.description }}</p>
          <div class="flex items-center gap-2 mt-2">
            <span class="text-sm px-2 py-1 rounded-full" :class="{
              'bg-green-100 text-green-700': task.status === 'completed',
              'bg-yellow-100 text-yellow-700': task.status === 'pending'
            }">
              {{ task.status }}
            </span>
            <span class="text-sm" :class="isOverdue(task) ? 'text-red-600 font-medium' : 'text-gray-500'">
              📅 {{ formatDate(task.due_date) }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col items-end gap-2 text-sm">
          <button @click="toggleStatus(task)" class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-100">
            {{ task.status === 'completed' ? '↩️ Mark as Pending' : '✅ Mark as Completed' }}
          </button>

          <button @click="editTask(task)" class="text-blue-600 hover:text-blue-800 font-medium">
            ✏️ Edit
          </button>

          <button v-if="!task.deleted_at" @click="deleteTask(task)" class="text-red-500 hover:text-red-700">
            🗑️ Delete
          </button>

          <button v-else @click="restoreTask(task)" class="text-green-600 hover:text-green-800">
            ♻️ Restore
          </button>
        </div>
      </div>
    </div>

    <div v-if="tasks.length === 0" class="text-center text-gray-400 mt-10 text-lg">
      😕 No tasks found.
    </div>
  </div>
</template>

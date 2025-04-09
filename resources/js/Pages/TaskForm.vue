<template>
    <div class="mb-10 bg-white p-6 rounded-2xl shadow-md border border-gray-200">
        <h2 class="text-2xl font-semibold text-indigo-700 mb-4">
            {{ editing ? "✏️ Edit Task" : "➕ Add New Task" }}
        </h2>

        <form @submit.prevent="submitForm" class="space-y-5">
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input v-model="form.title" type="text" placeholder="Enter task title"
                    class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none transition"
                    required />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" placeholder="Enter task description" rows="3"
                    class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none transition"></textarea>
            </div>

            <!-- Due Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input v-model="form.due_date" type="date"
                    class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none transition" />
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select v-model="form.category_id"
                    class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none transition">
                    <option disabled value="">-- Select Category --</option>
                    <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select v-model="form.status"
                    class="w-full p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none transition">
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl shadow-sm transition">
                    {{ editing ? "Update Task" : "Add Task" }}
                </button>

                <button v-if="editing" @click="resetForm" type="button"
                    class="text-gray-500 hover:text-gray-700 underline text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>


<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

const emit = defineEmits(["task-saved"]);

const props = defineProps({
    task: Object,
});

const editing = ref(false);
const categories = ref([]);

const form = ref({
    title: "",
    description: "",
    due_date: "",
    status: "pending",
    category_id: "",
});

const getCategories = async () => {
    const res = await axios.get("/api/categories");
    categories.value = res.data.data || res.data; // adjust if your API returns a `data` field
};

watch(
  () => props.task,
  (newVal) => {
    if (newVal && loaded.value) {
      editing.value = true;
      form.value = {
        title: newVal.title,
        description: newVal.description,
        due_date: newVal.due_date,
        status: newVal.status,
        category_id: newVal.category_id ? String(newVal.category_id) : "",
      };
    }
  },
  { immediate: true } // important: this ensures the watcher runs even if task is already available
);

const submitForm = async () => {
    if (editing.value) {
        await axios.put(`/api/tasks/${props.task.id}`, form.value);
    } else {
        await axios.post("/api/tasks", form.value);
    }
    emit("task-saved");
    resetForm();
};

const resetForm = () => {
    form.value = {
        title: "",
        description: "",
        due_date: "",
        status: "pending",
        category_id: "",
    };
    editing.value = false;
    emit("task-saved");
};

const loaded = ref(false);
onMounted(() => {
    getCategories();
    loaded.value = true;
});
</script>

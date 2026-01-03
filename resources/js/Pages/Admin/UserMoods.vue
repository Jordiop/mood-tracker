<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import MoodCalendar from '@/Components/MoodCalendar.vue';
import MoodStats from '@/Components/MoodStats.vue';
import axios from 'axios';

const props = defineProps({
    userId: {
        type: [String, Number],
        required: true
    }
});

const currentYear = ref(new Date().getFullYear());
const user = ref(null);
const moods = ref([]);
const loading = ref(false);

const fetchUserMoods = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/admin/users/${props.userId}/moods?year=${currentYear.value}`);
        user.value = response.data.user;
        moods.value = response.data.moods;
    } catch (error) {
        console.error('Error fetching user moods:', error);
    } finally {
        loading.value = false;
    }
};

const changeYear = (delta) => {
    currentYear.value += delta;
    fetchUserMoods();
};

onMounted(() => {
    fetchUserMoods();
});
</script>

<template>
    <Head :title="`Admin - ${user?.name || 'User'} Moods`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link
                        href="/admin/users"
                        class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                    >
                        ← Back to Users
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ user?.name || 'User' }}'s Mood Tracker
                    </h2>
                    <p v-if="user" class="text-sm text-gray-600">{{ user.email }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="changeYear(-1)"
                        class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        ← Previous Year
                    </button>
                    <span class="text-lg font-semibold text-gray-800">{{ currentYear }}</span>
                    <button
                        @click="changeYear(1)"
                        :disabled="currentYear >= new Date().getFullYear()"
                        class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next Year →
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="loading" class="text-center py-12">
                    <div class="text-gray-600">Loading...</div>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Calendar -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <MoodCalendar
                                :moods="moods"
                                :year="currentYear"
                                :readonly="true"
                            />
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="lg:col-span-1">
                        <MoodStats
                            :moods="moods"
                            :year="currentYear"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

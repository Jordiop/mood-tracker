<script setup>
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import MoodCalendar from '@/Components/MoodCalendar.vue';
import MoodEntryModal from '@/Components/MoodEntryModal.vue';
import MoodStats from '@/Components/MoodStats.vue';
import ViewModeSelector from '@/Components/ViewModeSelector.vue';
import { getWeekStart, formatWeekRange, getMonthName } from '@/utils/dateUtils';
import axios from 'axios';
import DailyChart from '@/Components/DailyChart.vue';

const currentYear = ref(new Date().getFullYear());
const moods = ref([]);
const showModal = ref(false);
const selectedDate = ref(null);
const selectedEntry = ref(null);
const loading = ref(false);
const viewMode = ref('year');
const currentMonth = ref(new Date().getMonth());
const currentWeekStart = ref(getWeekStart(new Date()));

const fetchMoods = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/moods?year=${currentYear.value}`);
        moods.value = response.data;
    } catch (error) {
        console.error('Error fetching moods:', error);
    } finally {
        loading.value = false;
    }
};

const handleDayClick = (cell) => {
    selectedDate.value = cell.date;
    selectedEntry.value = cell.mood || null;
    showModal.value = true;
};

const formatDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const handleSave = async (data) => {
    try {
        const payload = {
            date: formatDate(data.date),
            score: data.score,
            comment: data.comment
        };

        if (data.id) {
            await axios.put(`/api/moods/${data.id}`, payload);
        } else {
            await axios.post('/api/moods', payload);
        }

        showModal.value = false;
        await fetchMoods();
    } catch (error) {
        console.error('Error saving mood:', error);
        alert('Error saving mood entry. Please try again.');
    }
};

const handleDelete = async (id) => {
    try {
        await axios.delete(`/api/moods/${id}`);
        showModal.value = false;
        await fetchMoods();
    } catch (error) {
        console.error('Error deleting mood:', error);
    }
};

const handleCloseModal = () => {
    showModal.value = false;
    selectedDate.value = null;
    selectedEntry.value = null;
};

const changeYear = (delta) => {
    currentYear.value += delta;
    fetchMoods();
};

const changeMonth = (delta) => {
    let newMonth = currentMonth.value + delta;
    if (newMonth < 0) {
        newMonth = 11;
        if (currentYear.value > minimumYear) currentYear.value--;
    } else if (newMonth > 11) {
        newMonth = 0;
        currentYear.value++;
    }
    currentMonth.value = newMonth;
    fetchMoods();
};

const changeWeek = (delta) => {
    const newWeekStart = new Date(currentWeekStart.value);
    newWeekStart.setDate(newWeekStart.getDate() + (delta * 7));

    if (newWeekStart.getFullYear() !== currentYear.value) {
        currentYear.value = newWeekStart.getFullYear();
        fetchMoods();
    }

    currentWeekStart.value = newWeekStart;
    currentMonth.value = newWeekStart.getMonth();
};

const handleViewModeChange = (newMode) => {
    viewMode.value = newMode;

    const now = new Date();

    if (newMode === 'year') {
        currentYear.value = now.getFullYear();
    } else if (newMode === 'month') {
        currentYear.value = now.getFullYear();
        currentMonth.value = now.getMonth();
    } else if (newMode === 'week') {
        currentWeekStart.value = getWeekStart(now);
        currentYear.value = now.getFullYear();
        currentMonth.value = now.getMonth();
    }
};

const canGoNext = computed(() => {
    if (viewMode.value === 'year') {
        return currentYear.value < new Date().getFullYear();
    } else if (viewMode.value === 'month') {
        const now = new Date();
        return currentYear.value < now.getFullYear() ||
               (currentYear.value === now.getFullYear() && currentMonth.value < now.getMonth());
    } else if (viewMode.value === 'week') {
        const now = new Date();
        const nowWeekStart = getWeekStart(now);
        return currentWeekStart.value < nowWeekStart;
    }
    return false;
});

const chartData = computed(() => {
    return {
        chart: {
            type: 'spline',
        },
        title: {
            text: ''
        },
        xAxis: {
            type: 'datetime',
        },
        yAxis: {
            title: {
                text: ''
            },
            min: 1,
            max: 10
        },
        series: [{
            name: 'Mood Score',
            data: moods.value.map(entry => [
                new Date(entry.date).getTime(),
                entry.score
            ]),
        }]
    }
})

onMounted(() => {
    fetchMoods();
});
</script>

<template>
    <Head title="Mood Tracker" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between gap-3">
                      <ViewModeSelector
                        :view-mode="viewMode"
                        @update:view-mode="handleViewModeChange"
                    />
                    <div v-if="viewMode === 'year'" class="flex items-center gap-3">
                        <button
                            @click="changeYear(-1)"
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            ← Previous Year
                        </button>
                        <span class="text-lg font-semibold text-gray-800">{{ currentYear }}</span>
                        <button
                            @click="changeYear(1)"
                            :disabled="!canGoNext"
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next Year →
                        </button>
                    </div>

                    <div v-else-if="viewMode === 'month'" class="flex items-center gap-3">
                        <button
                            @click="changeMonth(-1)"
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            ← Previous
                        </button>
                        <span class="text-lg font-semibold text-gray-800">
                            {{ getMonthName(currentMonth) }} {{ currentYear }}
                        </span>
                        <button
                            @click="changeMonth(1)"
                            :disabled="!canGoNext"
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next →
                        </button>
                    </div>

                    <div v-else-if="viewMode === 'week'" class="flex items-center gap-3">
                        <button
                            @click="changeWeek(-1)"
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            ← Previous
                        </button>
                        <span class="text-lg font-semibold text-gray-800">
                            {{ formatWeekRange(currentWeekStart) }}
                        </span>
                        <button
                            @click="changeWeek(1)"
                            :disabled="!canGoNext"
                            class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next →
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="loading" class="text-center py-12">
                    <div class="text-gray-600">
                        <svg class="animate-spin h-8 w-8 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <MoodCalendar
                                :moods="moods"
                                :year="currentYear"
                                :view-mode="viewMode"
                                :current-month="currentMonth"
                                :current-week-start="currentWeekStart"
                                @day-click="handleDayClick"
                            />
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <MoodStats
                            :moods="moods"
                            :year="currentYear"
                            :view-mode="viewMode"
                            :current-month="currentMonth"
                            :current-week-start="currentWeekStart"
                        />
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                            <DailyChart :chartOptions="chartData" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <MoodEntryModal
            :show="showModal"
            :date="selectedDate"
            :existing-entry="selectedEntry"
            @close="handleCloseModal"
            @save="handleSave"
            @delete="handleDelete"
        />
    </AuthenticatedLayout>
</template>

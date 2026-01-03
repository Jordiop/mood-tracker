<script setup>
import { computed } from 'vue';
import { getWeekEnd, getMonthName, getDaysInMonthCount, formatWeekRange } from '@/utils/dateUtils';

const props = defineProps({
    moods: {
        type: Array,
        default: () => []
    },
    year: {
        type: Number,
        required: true
    },
    viewMode: {
        type: String,
        default: 'year'
    },
    currentMonth: {
        type: Number,
        default: 0
    },
    currentWeekStart: {
        type: Date,
        default: null
    }
});

const filteredMoods = computed(() => {
    if (props.viewMode === 'year') {
        return props.moods;
    } else if (props.viewMode === 'month') {
        return props.moods.filter(mood => {
            const moodDate = new Date(mood.date);
            return moodDate.getMonth() === props.currentMonth;
        });
    } else if (props.viewMode === 'week' && props.currentWeekStart) {
        const weekEnd = getWeekEnd(props.currentWeekStart);
        return props.moods.filter(mood => {
            const moodDate = new Date(mood.date);
            return moodDate >= props.currentWeekStart && moodDate <= weekEnd;
        });
    }
    return [];
});

const periodLabel = computed(() => {
    if (props.viewMode === 'year') {
        return `${props.year}`;
    } else if (props.viewMode === 'month') {
        return `${getMonthName(props.currentMonth)} ${props.year}`;
    } else if (props.viewMode === 'week' && props.currentWeekStart) {
        return formatWeekRange(props.currentWeekStart);
    }
    return '';
});

const averageScore = computed(() => {
    if (filteredMoods.value.length === 0) return 0;
    const sum = filteredMoods.value.reduce((acc, mood) => acc + mood.score, 0);
    return (sum / filteredMoods.value.length).toFixed(1);
});

const daysTracked = computed(() => {
    return filteredMoods.value.length;
});

const totalDaysInPeriod = computed(() => {
    if (props.viewMode === 'year') {
        const lastDay = new Date(props.year, 11, 31);
        return Math.floor((lastDay - new Date(props.year, 0, 1)) / (1000 * 60 * 60 * 24)) + 1;
    } else if (props.viewMode === 'month') {
        return getDaysInMonthCount(props.year, props.currentMonth);
    } else if (props.viewMode === 'week') {
        return 7;
    }
    return 0;
});

const trackingRate = computed(() => {
    if (totalDaysInPeriod.value === 0) return 0;
    return ((daysTracked.value / totalDaysInPeriod.value) * 100).toFixed(1);
});

const distribution = computed(() => {
    const dist = {
        excellent: 0,
        great: 0,
        good: 0,
        low: 0,
        poor: 0
    };

    filteredMoods.value.forEach(mood => {
        if (mood.score >= 9) dist.excellent++;
        else if (mood.score >= 7) dist.great++;
        else if (mood.score >= 5) dist.good++;
        else if (mood.score >= 3) dist.low++;
        else dist.poor++;
    });

    return dist;
});

const distributionPercentages = computed(() => {
    const total = daysTracked.value;
    if (total === 0) return {
        excellent: 0,
        great: 0,
        good: 0,
        low: 0,
        poor: 0
    };

    return {
        excellent: ((distribution.value.excellent / total) * 100).toFixed(1),
        great: ((distribution.value.great / total) * 100).toFixed(1),
        good: ((distribution.value.good / total) * 100).toFixed(1),
        low: ((distribution.value.low / total) * 100).toFixed(1),
        poor: ((distribution.value.poor / total) * 100).toFixed(1)
    };
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        <h3 class="text-lg font-semibold text-gray-900">Statistics for {{ periodLabel }}</h3>

        <!-- Key Stats -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ averageScore }}</div>
                <div class="text-sm text-gray-600">Average</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ daysTracked }}</div>
                <div class="text-sm text-gray-600">Days Tracked</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-900">{{ trackingRate }}%</div>
                <div class="text-sm text-gray-600">Tracking Rate</div>
            </div>
        </div>

        <!-- Distribution -->
        <div>
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Mood Distribution</h4>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-green-500"></div>
                        <span class="text-sm text-gray-700">Excellent (9-10)</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ distribution.excellent }} ({{ distributionPercentages.excellent }}%)
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-green-400"></div>
                        <span class="text-sm text-gray-700">Great (7-8.9)</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ distribution.great }} ({{ distributionPercentages.great }}%)
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-yellow-400"></div>
                        <span class="text-sm text-gray-700">Good (5-6.9)</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ distribution.good }} ({{ distributionPercentages.good }}%)
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-orange-500"></div>
                        <span class="text-sm text-gray-700">Low (3-4.9)</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ distribution.low }} ({{ distributionPercentages.low }}%)
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-sm bg-red-500"></div>
                        <span class="text-sm text-gray-700">Poor (1-2.9)</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ distribution.poor }} ({{ distributionPercentages.poor }}%)
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

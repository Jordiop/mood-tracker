<script setup>
import { computed } from 'vue';

const props = defineProps({
    moods: {
        type: Array,
        default: () => []
    }
});

const distribution = computed(() => {
    const dist = {
        excellent: { count: 0, color: 'bg-green-500', label: 'Excellent' },
        great: { count: 0, color: 'bg-green-400', label: 'Great' },
        good: { count: 0, color: 'bg-yellow-400', label: 'Good' },
        low: { count: 0, color: 'bg-orange-500', label: 'Low' },
        poor: { count: 0, color: 'bg-red-500', label: 'Poor' }
    };

    props.moods.forEach(mood => {
        if (mood.score >= 9) dist.excellent.count++;
        else if (mood.score >= 7) dist.great.count++;
        else if (mood.score >= 5) dist.good.count++;
        else if (mood.score >= 3) dist.low.count++;
        else dist.poor.count++;
    });

    return dist;
});

const maxCount = computed(() => {
    return Math.max(...Object.values(distribution.value).map(d => d.count), 1);
});

const getBarWidth = (count) => {
    return (count / maxCount.value) * 100;
};
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(data, key) in distribution"
            :key="key"
            class="flex items-center gap-3"
        >
            <div class="w-20 text-sm text-gray-700">{{ data.label }}</div>
            <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                <div
                    :class="data.color"
                    class="h-full flex items-center justify-end px-2 transition-all duration-300"
                    :style="{ width: `${getBarWidth(data.count)}%` }"
                >
                    <span v-if="data.count > 0" class="text-xs font-medium text-white">
                        {{ data.count }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

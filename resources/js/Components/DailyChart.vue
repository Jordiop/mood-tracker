<template>
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Mood Over Time</h3>
    <div class="chart w-full h-full relative" id="container" />
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import Highcharts from 'highcharts';

const props = defineProps({
    chartOptions: {
        type: Object,
        required: true
    }
});


const chartComponent = ref(null);

const renderChart = () => {
    if (chartComponent.value) {
        chartComponent.value.destroy();
    }
    chartComponent.value = Highcharts.chart('container', props.chartOptions);
};

onMounted(() => {
    renderChart();
});

watch(() => props.chartOptions, (newOptions) => {
    renderChart();
});

</script>
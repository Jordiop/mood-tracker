<script setup>
const props = defineProps({
    viewMode: {
        type: String,
        default: 'year',
        validator: (value) => ['year', 'month', 'week'].includes(value)
    }
});

const emit = defineEmits(['update:viewMode']);

const viewModes = [
    { value: 'year', label: 'Year' },
    { value: 'month', label: 'Month' },
    { value: 'week', label: 'Week' }
];

const selectView = (mode) => {
    emit('update:viewMode', mode);
};

const getButtonClasses = (mode, index) => {
    const isActive = props.viewMode === mode;
    const isFirst = index === 0;
    const isLast = index === viewModes.length - 1;
    
    return [
        'px-4 py-1 text-sm font-medium border',
        {
            'rounded-l-lg': isFirst,
            'rounded-r-lg': isLast,
            'border-t border-b': !isFirst && !isLast,
            'bg-gray-600 text-white border-gray-600': isActive,
            'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !isActive
        }
    ];
};
</script>

<template>
    <div class="inline-flex rounded-md shadow-sm" role="group">
        <button
            v-for="(mode, index) in viewModes"
            :key="mode.value"
            type="button"
            @click="selectView(mode.value)"
            :class="getButtonClasses(mode.value, index)"
        >
            {{ mode.label }}
        </button>
    </div>
</template>

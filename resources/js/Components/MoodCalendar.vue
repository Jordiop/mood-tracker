<script setup>
import { computed } from 'vue';
import { getDaysInMonth, getDaysInWeek, isToday, getDayNames } from '@/utils/dateUtils';

const props = defineProps({
    moods: {
        type: Array,
        default: () => []
    },
    year: {
        type: Number,
        required: true
    },
    readonly: {
        type: Boolean,
        default: false
    },
    viewMode: {
        type: String,
        default: 'year',
        validator: (value) => ['year', 'month', 'week'].includes(value)
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

const emit = defineEmits(['dayClick']);

const months = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

const getMoodColor = (score) => {
    if (!score) return 'bg-gray-200';
    if (score >= 9) return 'bg-green-500';
    if (score >= 7) return 'bg-green-400';
    if (score >= 5) return 'bg-yellow-400';
    if (score >= 3) return 'bg-orange-500';
    return 'bg-red-500';
};

const getMoodForDate = (date) => {
    return props.moods.find(mood => {
        const moodDate = new Date(mood.date);
        return moodDate.toDateString() === date.toDateString();
    });
};

const yearGrid = computed(() => {
    const grid = [];
    const daysInYear = [];

    for (let month = 0; month < 12; month++) {
        const daysInMonth = new Date(props.year, month + 1, 0).getDate();
        for (let day = 1; day <= daysInMonth; day++) {
            daysInYear.push(new Date(props.year, month, day));
        }
    }

    for (let day = 0; day < 31; day++) {
        const row = [];
        for (let month = 0; month < 12; month++) {
            const date = new Date(props.year, month, day + 1);
            const daysInMonth = new Date(props.year, month + 1, 0).getDate();

            if (day + 1 <= daysInMonth) {
                const mood = getMoodForDate(date);
                row.push({
                    date,
                    day: day + 1,
                    month,
                    mood,
                    exists: true
                });
            } else {
                row.push({ exists: false });
            }
        }
        grid.push(row);
    }

    return grid;
});

const monthGrid = computed(() => {
    const weeks = getDaysInMonth(props.year, props.currentMonth);
    return weeks.map(week =>
        week.map(date => {
            const mood = getMoodForDate(date);
            return {
                date,
                day: date.getDate(),
                month: date.getMonth(),
                mood,
                exists: true,
                isCurrentMonth: date.getMonth() === props.currentMonth,
                isToday: isToday(date)
            };
        })
    );
});

const weekGrid = computed(() => {
    if (!props.currentWeekStart || !(props.currentWeekStart instanceof Date)) {
        return [];
    }
    const days = getDaysInWeek(props.currentWeekStart);
    return days.map(date => {
        const mood = getMoodForDate(date);
        return {
            date,
            day: date.getDate(),
            month: date.getMonth(),
            mood,
            exists: true,
            isToday: isToday(date)
        };
    });
});

const handleDayClick = (cell) => {
    if (!props.readonly && cell.exists) {
        emit('dayClick', cell);
    }
};
</script>

<template>
    <div class="calendar-container">
        <!-- YEAR VIEW -->
        <div v-if="viewMode === 'year'">
            <!-- Month headers -->
            <div class="flex mb-2">
                <div class="w-8"></div>
                <div class="flex-1 grid grid-cols-12 gap-1">
                    <div
                        v-for="month in months"
                        :key="month"
                        class="text-xs text-center font-semibold text-gray-600"
                    >
                        {{ month }}
                    </div>
                </div>
            </div>

            <!-- Calendar grid -->
            <div class="space-y-1">
                <div
                    v-for="(row, rowIndex) in yearGrid"
                    :key="rowIndex"
                    class="flex gap-1"
                >
                    <!-- Day number -->
                    <div class="w-8 text-xs text-right text-gray-500 pt-1">
                        {{ rowIndex + 1 }}
                    </div>

                    <!-- Month cells -->
                    <div class="flex-1 grid grid-cols-12 gap-1">
                        <div
                            v-for="(cell, colIndex) in row"
                            :key="colIndex"
                            @click="handleDayClick(cell)"
                            :class="[
                                'aspect-square rounded-sm transition-all',
                                cell.exists ? getMoodColor(cell.mood?.score) : 'bg-transparent',
                                cell.exists && !readonly ? 'cursor-pointer hover:ring-2 hover:ring-blue-400' : '',
                                !cell.exists ? 'invisible' : ''
                            ]"
                            :title="cell.mood ? `${cell.date.toDateString()}: ${cell.mood.score}/10${cell.mood.comment ? ' - ' + cell.mood.comment : ''}` : cell.exists ? cell.date.toDateString() : ''"
                        >
                            <div class="w-full h-full flex items-center justify-center text-xs font-medium text-white">
                                {{ cell.mood?.score || '' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MONTH VIEW -->
        <div v-else-if="viewMode === 'month'">
            <!-- Day of week headers -->
            <div class="grid grid-cols-7 gap-1 md:gap-2 mb-2">
                <div
                    v-for="day in getDayNames()"
                    :key="day"
                    class="text-center text-xs md:text-sm font-semibold text-gray-600 py-2"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Month calendar grid -->
            <div class="space-y-1 md:space-y-2">
                <div
                    v-for="(week, weekIndex) in monthGrid"
                    :key="weekIndex"
                    class="grid grid-cols-7 gap-1 md:gap-2"
                >
                    <div
                        v-for="(cell, dayIndex) in week"
                        :key="dayIndex"
                        @click="handleDayClick(cell)"
                        :class="[
                            'aspect-square rounded-sm transition-all min-h-[40px] md:min-h-[60px]',
                            'flex flex-col items-center justify-center p-1',
                            getMoodColor(cell.mood?.score),
                            !cell.isCurrentMonth ? 'opacity-40' : '',
                            cell.isToday ? 'ring-2 ring-blue-500' : '',
                            !readonly ? 'cursor-pointer hover:ring-2 hover:ring-blue-400' : ''
                        ]"
                        :title="cell.mood ? `${cell.date.toDateString()}: ${cell.mood.score}/10${cell.mood.comment ? ' - ' + cell.mood.comment : ''}` : cell.date.toDateString()"
                    >
                        <div class="text-xs font-medium" :class="cell.mood ? 'text-white' : 'text-gray-700'">
                            {{ cell.day }}
                        </div>
                        <div v-if="cell.mood" class="text-xs font-bold text-white mt-1">
                            {{ cell.mood.score }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WEEK VIEW -->
        <div v-else-if="viewMode === 'week'">
            <!-- Day of week headers -->
            <div class="grid grid-cols-7 gap-2 md:gap-4 mb-2">
                <div
                    v-for="day in getDayNames()"
                    :key="day"
                    class="text-center text-sm md:text-base font-semibold text-gray-600 py-2"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Week grid -->
            <div class="grid grid-cols-7 gap-2 md:gap-4">
                <div
                    v-for="(cell, dayIndex) in weekGrid"
                    :key="dayIndex"
                    @click="handleDayClick(cell)"
                    :class="[
                        'rounded-lg transition-all min-h-[100px] md:min-h-[120px]',
                        'flex flex-col items-center justify-start p-3',
                        getMoodColor(cell.mood?.score),
                        cell.isToday ? 'ring-2 ring-blue-500' : '',
                        !readonly ? 'cursor-pointer hover:ring-2 hover:ring-blue-400' : ''
                    ]"
                    :title="cell.mood ? `${cell.date.toDateString()}: ${cell.mood.score}/10${cell.mood.comment ? ' - ' + cell.mood.comment : ''}` : cell.date.toDateString()"
                >
                    <div class="text-lg font-bold" :class="cell.mood ? 'text-white' : 'text-gray-700'">
                        {{ cell.day }}
                    </div>
                    <div v-if="cell.mood" class="text-2xl font-bold text-white mt-2">
                        {{ cell.mood.score }}
                    </div>
                    <div v-if="cell.mood && cell.mood.comment" class="text-xs text-white mt-2 text-center line-clamp-2">
                        {{ cell.mood.comment }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-gray-200"></div>
                <span class="text-gray-600">No entry</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-red-500"></div>
                <span class="text-gray-600">1-2.9 Poor</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-orange-500"></div>
                <span class="text-gray-600">3-4.9 Low</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-yellow-400"></div>
                <span class="text-gray-600">5-6.9 Good</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-green-400"></div>
                <span class="text-gray-600">7-8.9 Great</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-green-500"></div>
                <span class="text-gray-600">9-10 Excellent</span>
            </div>
        </div>
    </div>
</template>

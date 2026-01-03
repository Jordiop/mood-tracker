<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    date: {
        type: Date,
        default: null
    },
    existingEntry: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'save', 'delete']);

const form = ref({
    score: 5,
    comment: ''
});

const errors = ref({});

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.existingEntry) {
            form.value.score = props.existingEntry.score;
            form.value.comment = props.existingEntry.comment || '';
        } else {
            form.value.score = 5;
            form.value.comment = '';
        }
        errors.value = {};
    }
});

const formatDate = (date) => {
    if (!date) return '';
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!form.value.score || form.value.score < 1 || form.value.score > 10) {
        errors.value.score = 'Score must be between 1 and 10';
        isValid = false;
    }

    return isValid;
};

const handleSave = () => {
    if (validateForm()) {
        emit('save', {
            date: props.date,
            score: parseInt(form.value.score),
            comment: form.value.comment,
            id: props.existingEntry?.id
        });
    }
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this mood entry?')) {
        emit('delete', props.existingEntry.id);
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="handleClose">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ existingEntry ? 'Edit' : 'Add' }} Mood Entry
            </h2>

            <p class="text-sm text-gray-600 mb-6">
                {{ formatDate(date) }}
            </p>

            <div class="space-y-4">

                <div>
                    <InputLabel for="score" value="Mood score (1-10)" />
                    <div class="mt-2 space-y-2">
                        <input
                            id="score"
                            type="range"
                            v-model.number="form.score"
                            min="1"
                            max="10"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                        />
                        <div class="flex justify-between items-center">
                            <TextInput
                                type="number"
                                v-model="form.score"
                                min="1"
                                max="10"
                                class="w-20 text-center"
                            />
                            <div class="text-sm text-gray-600">
                                <span v-if="form.score >= 9" class="text-green-600 font-semibold">Excellent</span>
                                <span v-else-if="form.score >= 7" class="text-green-500 font-semibold">Good</span>
                                <span v-else-if="form.score >= 5" class="text-yellow-500 font-semibold">Average</span>
                                <span v-else-if="form.score >= 3" class="text-orange-500 font-semibold">Low</span>
                                <span v-else class="text-red-500 font-semibold">Bad</span>
                            </div>
                        </div>
                    </div>
                    <InputError :message="errors.score" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="comment" value="Comment (optional)" />
                    <textarea
                        id="comment"
                        v-model="form.comment"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="How are you feeling today?"
                    ></textarea>
                    <InputError :message="errors.comment" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <div>
                    <button
                        v-if="existingEntry"
                        @click="handleDelete"
                        type="button"
                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                    >
                        Delete Entry
                    </button>
                </div>
                <div class="flex gap-3">
                    <SecondaryButton @click="handleClose">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="handleSave">
                        Save
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>

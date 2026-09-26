<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    trialClasses: Array,
    parents: Array,
});

const selectedStudent = ref(null);
const selectedClass = ref(null);
const bookingStatus = ref(null);
const isBooking = ref(false);

function bookTrial() {
    if (!selectedStudent.value || !selectedClass.value) {
        bookingStatus.value = { type: 'error', message: 'Please select both a student and a trial class.' };
        return;
    }

    isBooking.value = true;
    bookingStatus.value = null;

    router.post('/bookings', {
        student_id: selectedStudent.value,
        trial_class_id: selectedClass.value,
    }, {
        onSuccess: () => {
            // Inertia will redirect to bookings.show on success — no extra handling needed
            isBooking.value = false;
        },
        onError: (errors) => {
            isBooking.value = false;
            // Inertia returns validation errors as plain strings (not arrays)
            const msg = errors.student_id || errors.trial_class_id || errors.message || 'Booking failed.';
            bookingStatus.value = { type: 'error', message: msg };
        },
        onFinish: () => {
            // Fallback: ensure isBooking is always reset
            isBooking.value = false;
        },
    });
}

function getStudentOptions() {
    const options = [];
    props.parents.forEach(parent => {
        parent.students.forEach(student => {
            options.push({
                value: student.id,
                label: `${student.name} (${parent.name})`,
            });
        });
    });
    return options;
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <Link href="/" class="text-gray-500 hover:text-gray-700 mb-2 inline-block">← Back to Home</Link>
                    <h1 class="text-3xl font-bold text-gray-900">Available Trial Classes</h1>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Book a Trial Class</h2>

                <div class="grid gap-6 md:grid-cols-3 mb-6">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Choose Child</label>
                        <select
                            v-model="selectedStudent"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select a child...</option>
                            <option
                                v-for="option in getStudentOptions()"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Choose Trial Class</label>
                        <select
                            v-model="selectedClass"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select a class...</option>
                            <option
                                v-for="cls in trialClasses"
                                :key="cls.id"
                                :value="cls.id"
                                :disabled="cls.available_seats === 0"
                            >
                                {{ cls.title }} - {{ cls.available_seats }}/{{ cls.capacity }} seats
                                <span v-if="cls.available_seats === 0" class="text-red-500"> (Full)</span>
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button
                            @click="bookTrial"
                            :disabled="isBooking || !selectedStudent || !selectedClass"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="isBooking" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Booking...
                            </span>
                            <span v-else>Book Trial</span>
                        </button>
                    </div>
                </div>

                <div v-if="bookingStatus" :class="['p-4 rounded-md', bookingStatus.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
                    {{ bookingStatus.message }}
                </div>
            </div>

            <!-- Trial Classes List -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-900">All Trial Classes</h2>
                <div v-for="cls in trialClasses" :key="cls.id" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ cls.title }}</h3>
                            <p class="text-gray-600 mt-1">Starts: {{ new Date(cls.start_at).toLocaleString() }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ cls.available_seats }} / {{ cls.capacity }}</div>
                                <div class="text-sm text-gray-500">Available Seats</div>
                            </div>
                            <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full transition-all"
                                    :class="cls.available_seats === 0 ? 'bg-red-500' : cls.available_seats === 1 ? 'bg-yellow-500' : 'bg-green-500'"
                                    :style="{ width: ((cls.available_seats / cls.capacity) * 100) + '%' }"
                                ></div>
                            </div>
                            <Link
                                :href="`/trial-classes/` + cls.id + `/roster`"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                            >
                                View Roster
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
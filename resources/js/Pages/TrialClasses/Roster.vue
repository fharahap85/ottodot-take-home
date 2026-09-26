<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    trialClass: Object,
    confirmedBookings: Array,
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 py-8">
            <div class="mb-6">
                <Link href="/trial-classes" class="text-gray-500 hover:text-gray-700 mb-2 inline-block">← Back to Classes</Link>
                <h1 class="text-3xl font-bold text-gray-900">Class Roster</h1>
                <p class="text-gray-600 mt-1">{{ trialClass.title }}</p>
            </div>

            <!-- Class Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Start Time</p>
                        <p class="font-medium text-gray-900">{{ new Date(trialClass.start_at).toLocaleString() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Capacity</p>
                        <p class="font-medium text-gray-900">{{ trialClass.capacity }} seats</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Confirmed</p>
                        <p class="font-medium text-green-700">{{ trialClass.confirmed_count }} students</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Available</p>
                        <p class="font-medium" :class="trialClass.available_seats === 0 ? 'text-red-600' : 'text-gray-900'">
                            {{ trialClass.available_seats }} seats
                            <span v-if="trialClass.available_seats === 0" class="text-xs">(Full)</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Roster Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Confirmed Students ({{ confirmedBookings.length }})</h2>
                </div>

                <div v-if="confirmedBookings.length === 0" class="px-6 py-12 text-center">
                    <p class="text-gray-500">No confirmed bookings yet.</p>
                </div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booked At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="entry in confirmedBookings" :key="entry.position" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ entry.position }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ entry.student_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ entry.parent_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(entry.booked_at).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    booking: Object,
});

const isProcessing = ref(false);
const paymentResult = ref(null);

function simulatePayment(result) {
    isProcessing.value = true;
    paymentResult.value = null;

    router.post(`/bookings/${props.booking.id}/payment`, {
        result: result,
    }, {
        onSuccess: () => {
            // Inertia redirects back to this page with refreshed booking data from server
            // booking.status will already reflect the new state via fresh props
            isProcessing.value = false;
            paymentResult.value = props.booking.status === 'confirmed' ? 'success' : 'failed';
        },
        onError: () => {
            isProcessing.value = false;
            paymentResult.value = 'error';
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'confirmed': return 'bg-green-100 text-green-800';
        case 'pending_payment': return 'bg-yellow-100 text-yellow-800';
        case 'payment_failed': return 'bg-red-100 text-red-800';
        case 'cancelled': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function formatStatus(status) {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <Link href="/trial-classes" class="text-gray-500 hover:text-gray-700 mb-4 inline-block">← Back to Classes</Link>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Booking #{{ booking.id }}</h1>
                        <p class="text-gray-600">{{ booking.trial_class.title }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusBadgeClass(booking.status)]">
                        {{ formatStatus(booking.status) }}
                    </span>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Student</p>
                        <p class="font-medium text-gray-900">{{ booking.student.name }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Trial Class</p>
                        <p class="font-medium text-gray-900">{{ booking.trial_class.title }}</p>
                        <p class="text-sm text-gray-500">{{ new Date(booking.trial_class.start_at).toLocaleString() }}</p>
                    </div>
                </div>

                <!-- Payment Simulation -->
                <div v-if="booking.status === 'pending_payment'" class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Simulate Payment</h3>
                    <div class="flex gap-4">
                        <button
                            @click="simulatePayment('success')"
                            :disabled="isProcessing"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="isProcessing" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Processing...
                            </span>
                            <span v-else>Simulate Success</span>
                        </button>
                        <button
                            @click="simulatePayment('failed')"
                            :disabled="isProcessing"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Simulate Failure
                        </button>
                    </div>
                </div>

                <!-- Payment Result Message -->
                <div v-if="paymentResult" class="mt-4 p-4 rounded-lg" :class="paymentResult === 'success' ? 'bg-green-50 text-green-800' : paymentResult === 'failed' ? 'bg-red-50 text-red-800' : 'bg-yellow-50 text-yellow-800'">
                    <div v-if="paymentResult === 'success'" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Payment successful! Booking confirmed.
                    </div>
                    <div v-else-if="paymentResult === 'failed'" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10 7.293 11.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        Payment failed. Booking not confirmed.
                    </div>
                    <div v-else>
                        An error occurred. Please try again.
                    </div>
                </div>

                <!-- Payment Attempts History -->
                <div v-if="booking.payment_attempts && booking.payment_attempts.length > 0" class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h3>
                    <div class="space-y-2">
                        <div v-for="attempt in booking.payment_attempts" :key="attempt.id" class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ attempt.status === 'success' ? 'Successful' : attempt.status === 'failed' ? 'Failed' : 'Pending' }} Payment
                                </p>
                                <p class="text-sm text-gray-500">Amount: ${{ attempt.amount }}</p>
                            </div>
                            <span :class="attempt.status === 'success' ? 'text-green-600' : attempt.status === 'failed' ? 'text-red-600' : 'text-yellow-600'">
                                {{ attempt.paid_at ? new Date(attempt.paid_at).toLocaleString() : 'Processing...' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
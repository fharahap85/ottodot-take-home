<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-16">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Ottodot Trial Booking</h1>
                <p class="text-xl text-gray-600">Book trial classes for your children</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Link
                    href="/trial-classes"
                    class="block p-8 bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow text-center"
                >
                    <div class="text-4xl mb-4">📚</div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">View Trial Classes</h2>
                    <p class="text-gray-600">Browse available trial classes and book a spot for your child</p>
                </Link>

                <div class="p-8 bg-white rounded-lg shadow-sm border border-gray-200 text-center">
                    <div class="text-4xl mb-4">👨‍👩‍👧‍👦</div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">Manage Bookings</h2>
                    <p class="text-gray-600">View booking status, simulate payments, and check confirmed rosters</p>
                </div>
            </div>

            <div class="mt-12 p-6 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">Demo Scenarios (Seeded Data)</h3>
                <ul class="text-sm text-blue-800 space-y-2">
                    <li>• <strong>Math Trial (10:00)</strong> - 0/4 confirmed, 4 seats available</li>
                    <li>• <strong>Science Trial (14:00)</strong> - 3/4 confirmed, <strong>1 seat left</strong> (last-seat test)</li>
                    <li>• <strong>English Trial (16:00)</strong> - 4/4 confirmed, full</li>
                    <li>• <strong>Art Trial (09:00)</strong> - 1/4 confirmed, duplicate booking test</li>
                    <li>• <strong>Music Trial (11:00)</strong> - Failed payment scenario</li>
                    <li>• <strong>History Trial (13:00)</strong> - Pending payment (not counted as confirmed)</li>
                </ul>
            </div>
        </div>
    </div>
</template>
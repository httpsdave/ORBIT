<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend);

// Define props for data that will be passed from the controller
const props = defineProps({
    collegesData: {
        type: Array,
        default: () => [],
    },
    upcomingEvent: {
        type: Object,
        default: null,
    },
    todayEvent: {
        type: Object,
        default: null,
    },
    totalStudentOrgs: {
        type: Number,
        default: 0,
    }
});

// Debugging - Log received data
console.log('Props received:', {
    collegesData: props.collegesData,
    totalStudentOrgs: props.totalStudentOrgs,
    todayEvent: props.todayEvent,
    upcomingEvent: props.upcomingEvent
});

// Colors for the pie chart
const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d', '#ffc658', '#FF6B6B', '#6A7FDB', '#66BB6A'];

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Get the event to display (priority: today's event, then upcoming event)
const displayEvent = props.todayEvent || props.upcomingEvent;

// Prepare chart data in a reactive ref, and update it when props change
const chartData = ref({
    labels: [],
    datasets: [
        {
            backgroundColor: [],
            data: []
        }
    ]
});

// Initialize chart data
onMounted(() => {
    updateChartData();
});

const updateChartData = () => {
    // Filter out colleges with zero student orgs to prevent empty slices
    const collegesWithOrgs = props.collegesData.filter(college => college.student_orgs_count > 0);
    
    // If no organizations, show a placeholder
    if (collegesWithOrgs.length === 0) {
        chartData.value = {
            labels: ['No Organizations'],
            datasets: [{
                backgroundColor: ['#e0e0e0'],
                data: [1]
            }]
        };
        return;
    }
    
    chartData.value = {
        labels: collegesWithOrgs.map(college => college.name),
        datasets: [
            {
                backgroundColor: COLORS.slice(0, collegesWithOrgs.length),
                data: collegesWithOrgs.map(college => college.student_orgs_count)
            }
        ]
    };
};

// Watch for changes in collegesData
watch(() => props.collegesData, () => {
    updateChartData();
}, { deep: true });

// Chart options
const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    return `${label}: ${value} orgs`;
                }
            }
        }
    }
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Debug Information -->
                <div class="bg-yellow-50 p-4 mb-6 rounded-lg shadow" v-if="collegesData.length === 0 || totalStudentOrgs === 0">
                    <h3 class="font-bold text-yellow-800">Debug Information</h3>
                    <p class="text-yellow-700">Colleges found: {{ collegesData.length }}</p>
                    <p class="text-yellow-700">Total organizations: {{ totalStudentOrgs }}</p>
                    <div v-if="collegesData.length > 0">
                        <p class="font-bold mt-2">College Data:</p>
                        <ul class="list-disc pl-5">
                            <li v-for="college in collegesData" :key="college.id">
                                {{ college.name }}: {{ college.student_orgs_count }} orgs
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Stats Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-700">Total Organizations</h4>
                                    <p class="text-3xl font-bold text-gray-800">{{ totalStudentOrgs }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-700">Total Colleges</h4>
                                    <p class="text-3xl font-bold text-gray-800">{{ collegesData.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-700">Event Status</h4>
                                    <p class="text-xl font-medium text-gray-800">
                                        {{ displayEvent ? (props.todayEvent ? 'Event Today' : 'Upcoming') : 'No Events' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Student Organizations Chart -->
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium mb-4 text-gray-800">Student Organizations by College</h3>
                            <div class="h-80">
                                <Pie 
                                    :data="chartData" 
                                    :options="chartOptions" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Event Information -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium mb-4 text-gray-800">
                                {{ props.todayEvent ? "Today's Event" : "Upcoming Event" }}
                            </h3>
                            
                            <div v-if="displayEvent" class="border rounded-lg p-4 bg-blue-50">
                                <h4 class="font-bold text-lg text-blue-800">{{ displayEvent.title }}</h4>
                                <div class="text-sm text-gray-600 mt-2">
                                    <div class="flex items-center mb-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Start: {{ formatDate(displayEvent.start_date) }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>End: {{ formatDate(displayEvent.end_date) }}</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm">{{ displayEvent.description }}</p>
                            </div>
                            
                            <div v-else class="border rounded-lg p-4 bg-gray-50 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-gray-600">No upcoming events</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="bg-blue-50 p-4 rounded-lg shadow">
                        <h4 class="font-medium text-blue-800">User Management</h4>
                        <p class="text-sm text-gray-600 my-2">Manage system users and roles</p>
                        <a :href="route('admin.users')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Manage Users →
                        </a>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg shadow">
                        <h4 class="font-medium text-purple-800">Applications</h4>
                        <p class="text-sm text-gray-600 my-2">Review and approve applications</p>
                        <a :href="route('applications.index')" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            View Applications →
                        </a>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg shadow">
                        <h4 class="font-medium text-green-800">System Settings</h4>
                        <p class="text-sm text-gray-600 my-2">Configure application settings</p>
                        <a href="#" class="text-green-600 hover:text-green-800 text-sm font-medium">
                            Settings →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title } from 'chart.js';
import { Pie, Bar } from 'vue-chartjs';

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title);

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
    },
    // Add new prop for pending applications
    pendingApplications: {
        type: Number,
        default: 0,
    },
    // Add username prop for greeting
    userName: {
        type: String,
        default: '',
    }
});

// Colors for the charts
const COLORS = ['#4F46E5', '#0EA5E9', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#6366F1', '#D946EF'];

// Get the event to display (priority: today's event, then upcoming event)
const displayEvent = props.todayEvent || props.upcomingEvent;

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

// Get greeting based on time of day
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 18) return 'Good Afternoon';
    return 'Good Evening';
});

// Prepare chart data in a reactive ref, and update it when props change
const pieChartData = ref({
    labels: [],
    datasets: [
        {
            backgroundColor: [],
            data: []
        }
    ]
});

const barChartData = ref({
    labels: [],
    datasets: [
        {
            label: 'Number of Organizations',
            backgroundColor: '',
            data: []
        }
    ]
});

// Sort colleges by number of orgs for better visualization
const sortedColleges = computed(() => {
    return [...(props.collegesData || [])].sort((a, b) => 
        (b.student_orgs_count || 0) - (a.student_orgs_count || 0)
    );
});

// Initialize chart data
onMounted(() => {
    updateChartData();
});

const updateChartData = () => {
    // Safety check if collegesData is undefined
    if (!sortedColleges.value || !Array.isArray(sortedColleges.value)) {
        setPlaceholderCharts();
        return;
    }
    
    // Filter out colleges with zero student orgs to prevent empty slices
    const collegesWithOrgs = sortedColleges.value.filter(college => 
        college && college.student_orgs_count && college.student_orgs_count > 0
    );
    
    // If no organizations, show a placeholder
    if (collegesWithOrgs.length === 0) {
        setPlaceholderCharts();
        return;
    }
    
    // Pie chart data
    pieChartData.value = {
        labels: collegesWithOrgs.map(college => college.name || 'Unknown College'),
        datasets: [
            {
                backgroundColor: COLORS.slice(0, collegesWithOrgs.length),
                data: collegesWithOrgs.map(college => college.student_orgs_count || 0)
            }
        ]
    };
    
    // Bar chart data
    barChartData.value = {
        labels: collegesWithOrgs.map(college => college.name || 'Unknown College'),
        datasets: [
            {
                label: 'Number of Organizations',
                backgroundColor: COLORS.map(color => color + 'CC'), // Add transparency
                data: collegesWithOrgs.map(college => college.student_orgs_count || 0),
                borderRadius: 6
            }
        ]
    };
};

// Helper function to set placeholder charts when no data is available
const setPlaceholderCharts = () => {
    const placeholder = {
        labels: ['No Organizations'],
        datasets: [{
            backgroundColor: ['#e0e0e0'],
            data: [1]
        }]
    };
    pieChartData.value = placeholder;
    barChartData.value = {
        labels: ['No Organizations'],
        datasets: [{
            label: 'Number of Organizations',
            backgroundColor: '#e0e0e0',
            data: [0]
        }]
    };
};


// Watch for changes in collegesData
watch(() => props.collegesData, () => {
    updateChartData();
}, { deep: true });

// Chart options
const pieChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right',
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    const percentage = Math.round((value / total) * 100);
                    return `${label}: ${value} orgs (${percentage}%)`;
                }
            }
        }
    }
});

const barChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return `${context.raw} organizations`;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            },
            title: {
                display: true,
                text: 'Number of Organizations'
            }
        },
        y: {
            grid: {
                display: false
            }
        }
    }
});

// Switch between chart types
const activeChart = ref('bar'); // Default to bar chart

// Get total number of colleges with orgs
const collegesWithOrgsCount = computed(() => {
    return (props.collegesData || []).filter(college => 
        college && college.student_orgs_count && college.student_orgs_count > 0
    ).length;
});

// Average orgs per college - keeping for reference but we won't display it
const avgOrgsPerCollege = computed(() => {
    if (collegesWithOrgsCount.value === 0) return 0;
    return ((props.totalStudentOrgs || 0) / collegesWithOrgsCount.value).toFixed(1);
});
</script>

<template>
    <Head title="Admin Dashboard" ></Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
        </template>

                <!-- Greeting Card - New Addition -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-6 bg-gradient-to-r from-indigo-50 to-blue-50">
                        <h2 class="text-2xl font-bold text-gray-800">{{ greeting }}, {{ userName || 'Administrator' }}!</h2>
                        <p class="mt-2 text-gray-600">Welcome to your administration dashboard. Here's an overview of your system.</p>
                    </div>
                </div>

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

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 overflow-hidden shadow-md rounded-lg text-white">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold">Total Organizations</h4>
                                    <p class="text-3xl font-bold">{{ totalStudentOrgs }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow-md rounded-lg text-white">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold">Colleges with Orgs</h4>
                                    <p class="text-3xl font-bold">{{ collegesWithOrgsCount }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 overflow-hidden shadow-md rounded-lg text-white">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold">Pending Applications</h4>
                                    <p class="text-3xl font-bold">{{ pendingApplications }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Student Organizations Chart -->
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium text-gray-800">Student Organizations by College</h3>
                                <div class="flex space-x-2">
                                    <button 
                                        @click="activeChart = 'bar'" 
                                        :class="[
                                            'px-3 py-1 text-sm rounded-md', 
                                            activeChart === 'bar' 
                                                ? 'bg-indigo-600 text-white' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        ]"
                                    >
                                        Bar Chart
                                    </button>
                                    <button 
                                        @click="activeChart = 'pie'" 
                                        :class="[
                                            'px-3 py-1 text-sm rounded-md', 
                                            activeChart === 'pie' 
                                                ? 'bg-indigo-600 text-white' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        ]"
                                    >
                                        Pie Chart
                                    </button>
                                </div>
                            </div>
                            
                            <div class="h-96" v-if="activeChart === 'pie'">
                                <Pie 
                                    :data="pieChartData" 
                                    :options="pieChartOptions" 
                                />
                            </div>
                            
                            <div class="h-96" v-else>
                                <Bar 
                                    :data="barChartData" 
                                    :options="barChartOptions" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Event Information -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-md bg-purple-100 text-purple-600 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">
                                    {{ props.todayEvent ? "Today's Event" : "Upcoming Event" }}
                                </h3>
                            </div>
                            
                            <div v-if="displayEvent" class="border rounded-lg p-4 bg-gradient-to-br from-blue-50 to-purple-50">
                                <h4 class="font-bold text-lg text-indigo-800 mb-3">{{ displayEvent.title }}</h4>
                                <div class="text-sm text-gray-600 mt-2 space-y-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Start: {{ formatDate(displayEvent.start_date) }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>End: {{ formatDate(displayEvent.end_date) }}</span>
                                    </div>
                                </div>
                                <div class="mt-4 border-t border-blue-100 pt-3">
                                    <p class="text-sm text-gray-700">{{ displayEvent.description }}</p>
                                </div>
                            </div>
                            
                            <div v-else class="border rounded-lg p-6 bg-gray-50 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-4 text-gray-600">No upcoming events scheduled</p>
                                <a href="#" class="inline-block mt-3 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                    Create Event
                                </a>
                            </div>
                            
                            <div class="mt-4">
                                <a :href="route('admin.users')" class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                                    Manage Users
                                </a>
                                <a :href="route('applications.index')" class="inline-block ml-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition">
                                    View Applications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
               
          
        
    </AuthenticatedLayout>
</template>
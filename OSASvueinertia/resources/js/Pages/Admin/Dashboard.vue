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
    pendingApplications: {
        type: Number,
        default: 0,
    },
    userName: {
        type: String,
        default: '',
    }
});

// Colors for the charts - using our primary color scheme
const COLORS = ['#3B82F6', '#10B981', '#FBBF24', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#6366F1', '#D946EF', '#A855F7'];
const COLORS_WITH_OPACITY = COLORS.map(color => `${color}CC`);

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
                backgroundColor: COLORS_WITH_OPACITY,
                data: collegesWithOrgs.map(college => college.student_orgs_count || 0),
                borderRadius: 8
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
            labels: {
                boxWidth: 12,
                padding: 15,
                font: {
                    family: 'Inter, sans-serif',
                    size: 12
                }
            }
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
                text: 'Number of Organizations',
                font: {
                    family: 'Inter, sans-serif',
                    size: 12
                }
            }
        },
        y: {
            grid: {
                display: false
            },
            ticks: {
                font: {
                    family: 'Inter, sans-serif',
                    size: 12
                }
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

// Stats card data for cleaner template
const statsCards = computed(() => [
    {
        title: 'Total Organizations',
        value: props.totalStudentOrgs,
        icon: 'users-group',
        color: 'blue'
    },
    {
        title: 'Colleges with Orgs',
        value: collegesWithOrgsCount.value,
        icon: 'building',
        color: 'green'
    },
    {
        title: 'Pending Applications',
        value: props.pendingApplications,
        icon: 'clock',
        color: 'yellow'
    }
]);
</script>

<template>
    <Head title="Admin Dashboard"></Head>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
            </div>
        </template>

        <!-- Colored Banner -->
        <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
            <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
            <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
            <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
            <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
        </div>

        <!-- Greeting Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-semibold text-gray-800">{{ greeting }}, {{ userName || 'Administrator' }}!</h2>
                        <p class="mt-1 text-gray-600">Welcome to Orbit. Here's an overview of your system.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Information (Only shown when no data) -->
        <div class="bg-yellow-50 p-4 mb-6 rounded-lg shadow-sm" v-if="collegesData.length === 0 || totalStudentOrgs === 0">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-medium text-yellow-800">Debug Information</h3>
                    <p class="text-sm text-yellow-700 mt-1">Colleges found: {{ collegesData.length }}</p>
                    <p class="text-sm text-yellow-700">Total organizations: {{ totalStudentOrgs }}</p>
                    <div v-if="collegesData.length > 0" class="mt-2">
                        <p class="text-sm font-medium text-yellow-800">College Data:</p>
                        <ul class="list-disc pl-5 text-sm text-yellow-700 mt-1">
                            <li v-for="college in collegesData" :key="college.id">
                                {{ college.name }}: {{ college.student_orgs_count }} orgs
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div v-for="(card, index) in statsCards" :key="index" 
                 :class="`bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-${card.color}-500 transition hover:shadow-md`">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Users Group Icon -->
                            <div v-if="card.icon === 'users-group'" class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <!-- Building Icon -->
                            <div v-else-if="card.icon === 'building'" class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <!-- Clock Icon -->
                            <div v-else-if="card.icon === 'clock'" class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ card.title }}</p>
                            <p class="text-3xl font-bold text-gray-900">{{ card.value }}</p>
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
                        <div class="inline-flex rounded-md shadow-sm">
                            <button 
                                @click="activeChart = 'bar'" 
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-l-md border border-gray-200', 
                                    activeChart === 'bar' 
                                        ? 'bg-blue-500 text-white border-blue-500' 
                                        : 'bg-white text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                Bar
                            </button>
                            <button 
                                @click="activeChart = 'pie'" 
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-r-md border border-gray-200 border-l-0', 
                                    activeChart === 'pie' 
                                        ? 'bg-blue-500 text-white border-blue-500' 
                                        : 'bg-white text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                Pie
                            </button>
                        </div>
                    </div>
                    
                    <div class="h-80" v-if="activeChart === 'pie'">
                        <Pie 
                            :data="pieChartData" 
                            :options="pieChartOptions" 
                        />
                    </div>
                    
                    <div class="h-80" v-else>
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
                        <div class="p-2 rounded-md bg-blue-100 text-blue-600 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800">
                            {{ props.todayEvent ? "Today's Event" : "Upcoming Event" }}
                        </h3>
                    </div>
                    
                    <div v-if="displayEvent" class="border rounded-lg p-4 bg-white shadow-sm">
                        <h4 class="font-medium text-lg text-gray-800 mb-3">{{ displayEvent.title }}</h4>
                        <div class="text-sm text-gray-600 mt-2 space-y-2">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Start: {{ formatDate(displayEvent.start_date) }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>End: {{ formatDate(displayEvent.end_date) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 border-t border-gray-100 pt-3">
                            <p class="text-sm text-gray-700">{{ displayEvent.description }}</p>
                        </div>
                    </div>
                    
                    <div v-else class="border rounded-lg p-6 bg-gray-50 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-4 text-gray-600">No upcoming events scheduled</p>
                        <button class="inline-block mt-3 px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Create Event
                        </button>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <a :href="route('admin.users')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Manage Users
                        </a>
                        <a :href="route('applications.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            View Applications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

</style>
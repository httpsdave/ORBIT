<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { Pie, Bar } from 'vue-chartjs';
import Modal from '@/Components/Modal.vue';
import { Link } from '@inertiajs/vue3';

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, ChartDataLabels);

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
    },
    advisersData: {
        type: Array,
        default: () => [],
    },
    pastEventsCount: { // <-- Add this prop
        type: Number,
        default: 0,
    }
});

// Colors for the charts - using our primary color scheme
const COLORS = ['#3B82F6', '#10B981', '#FBBF24', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#6366F1', '#D946EF', '#A855F7'];
const COLORS_WITH_OPACITY = COLORS.map(color => `${color}CC`);

// Get the event to display (priority: today's event, then upcoming event)
const displayEvent = props.todayEvent || props.upcomingEvent;

// Real-time clock
const currentDateTime = ref(new Date());
const clockTimer = ref(null);

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

// Format time only for display
const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Format date without time
const formatDateOnly = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

// Format current date time for header
const formatCurrentDateTime = computed(() => {
    return currentDateTime.value.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
});

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

// Remove old barChartData and related logic, and replace with members per organization chart

// Prepare chart data for members per organization
const membersBarChartData = ref({
    labels: [],
    datasets: [
        {
            label: 'Number of Members',
            backgroundColor: COLORS_WITH_OPACITY,
            data: [],
            borderRadius: 8
        }
    ]
});

const updateMembersBarChartData = () => {
    // Filter organizations with members_count > 0
    const orgsWithMembers = (props.advisersData || []).filter(org => org.members_count && org.members_count > 0);
    if (orgsWithMembers.length === 0) {
        membersBarChartData.value = {
            labels: ['No Data'],
            datasets: [{
                label: 'Number of Members',
                backgroundColor: '#e0e0e0',
                data: [0],
                borderRadius: 8,
                datalabels: {
                    color: '#666666',
                    font: {
                        weight: 'bold',
                        size: 20
                    }
                }
            }]
        };
        return;
    }
    membersBarChartData.value = {
        labels: orgsWithMembers.map(org => org.organization || 'Unknown'),
        datasets: [
            {
                label: 'Number of Members',
                backgroundColor: COLORS_WITH_OPACITY,
                data: orgsWithMembers.map(org => org.members_count),
                borderRadius: 8,
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 20
                    },
                    formatter: function(value) {
                        return value;
                    }
                }
            }
        ]
    };
};

// Watch for changes in advisersData
watch(() => props.advisersData, () => {
    updateMembersBarChartData();
}, { deep: true });

// Initialize chart data
onMounted(() => {
    updateChartData(); // for pie
    updateMembersBarChartData(); // for members bar
    
    // Start real-time clock
    clockTimer.value = setInterval(() => {
        currentDateTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (clockTimer.value) {
        clearInterval(clockTimer.value);
    }
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
                data: collegesWithOrgs.map(college => college.student_orgs_count || 0),
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 28
                    }
                }
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
            data: [1],
            datalabels: {
                color: '#666666',
                font: {
                    weight: 'bold',
                    size: 28
                }
            }
        }]
    };
    pieChartData.value = placeholder;
};

// Watch for changes in collegesData
watch(() => props.collegesData, () => {
    updateChartData();
}, { deep: true });

// Restore sortedColleges for pie chart and updateChartData
const sortedColleges = computed(() => {
    return [...(props.collegesData || [])].sort((a, b) => 
        (b.student_orgs_count || 0) - (a.student_orgs_count || 0)
    );
});

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
        },
        datalabels: {
            color: '#ffffff',
            font: {
                weight: 'bold',
                size: 28,
                family: 'Inter, sans-serif'
            },
            formatter: function(value, context) {
                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return percentage + '%';
            },
            textAlign: 'center',
            textBaseline: 'middle'
        }
    }
});

// In the Bar chart options, update y-axis ticks to truncate long org names
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
                    return `${context.raw} members`;
                },
                title: function(context) {
                    // Show full org name in tooltip title
                    return context[0].label;
                }
            }
        },
        datalabels: {
            color: '#ffffff',
            font: {
                weight: 'bold',
                size: 20
            },
            formatter: function(value) {
                return value;
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
                text: 'Number of Members',
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
                },
                callback: function(value, index, ticks) {
                    // Use the label from the chart data
                    const label = this.getLabelForValue ? this.getLabelForValue(value) : value;
                    return typeof label === 'string' && label.length > 20 ? label.slice(0, 20) + '…' : label;
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

// Non-college affiliated organizations count (total orgs minus sum of college orgs)
const nonAffiliatedCount = computed(() => {
    const totalCollegeOrgs = (props.collegesData || []).reduce((sum, c) => sum + (c && c.student_orgs_count ? c.student_orgs_count : 0), 0);
    const total = props.totalStudentOrgs || 0;
    return Math.max(0, total - totalCollegeOrgs);
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
        title: 'Non-College Affiliated Orgs',
        value: nonAffiliatedCount.value,
        icon: 'building',
        color: 'green'
    },
    {
        title: 'Pending Applications',
        value: props.pendingApplications,
        icon: 'clock',
        color: 'yellow'
    }
    // Removed Past Events card
]);

function exportAdvisersToCSV() {
    if (!props.advisersData.length) return;
    const headers = ['Organization', 'Adviser', 'Second Adviser', 'Members Count', 'Officers Count'];
    const rows = props.advisersData.map(row => {
        // Combine adviser name with prefix and suffix
        const adviserFullName = [row.adviser_prefix, row.adviser_name, row.adviser_suffix]
            .filter(Boolean)
            .join(' ') || row.adviser_name || '';
        
        return [
            `"${row.organization || ''}"`,
            `"${adviserFullName}"`,
            `"${row.second_adviser || ''}"`,
            row.members_count ?? '',
            row.officers_count ?? ''
        ];
    });
    const csvContent = [headers, ...rows].map(e => e.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'advisers.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head title="Admin Dashboard"></Head>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin Dashboard</h2>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatCurrentDateTime }}</div>
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
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                         <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ greeting }}, {{ userName || 'Administrator' }}!</h2>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Welcome to ORBIT. Here's an overview of your system.</p>
                    </div>
                </div>
            </div>
        </div>

        

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div v-for="(card, index) in statsCards" :key="index" 
                 :class="`bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-${card.color}-500 transition hover:shadow-md`">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Users Group Icon -->
                            <div v-if="card.icon === 'users-group'" class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <!-- Building Icon -->
                            <div v-else-if="card.icon === 'building'" class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <!-- Clock Icon -->
                            <div v-else-if="card.icon === 'clock'" class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <!-- Calendar Icon for Past Events -->
                            <div v-else-if="card.icon === 'calendar'" class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ card.title }}</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ card.value }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
            <!-- Student Organizations Chart -->
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-3 sm:p-4 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                        <h3 class="text-base sm:text-lg font-medium text-gray-800 dark:text-gray-200">
                            <template v-if="activeChart === 'bar'">Members per Organization</template>
                            <template v-else>Student Organizations by College</template>
                        </h3>
                        <div class="inline-flex rounded-md shadow-sm w-full sm:w-auto">
                            <button 
                                @click="activeChart = 'bar'" 
                                :class="[
                                    'flex-1 sm:flex-none px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-l-xl border transition-all duration-300', 
                                    activeChart === 'bar' 
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white border-blue-500 shadow-md' 
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                                ]"
                            >
                                Bar
                            </button>
                            <button 
                                @click="activeChart = 'pie'" 
                                :class="[
                                    'flex-1 sm:flex-none px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium border border-l-0 transition-all duration-300', 
                                    activeChart === 'pie' 
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white border-blue-500 shadow-md' 
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                                ]"
                            >
                                Pie
                            </button>
                            <button 
                                @click="activeChart = 'advisers'" 
                                :class="[
                                    'flex-1 sm:flex-none px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-r-xl border border-l-0 transition-all duration-300', 
                                    activeChart === 'advisers' 
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white border-blue-500 shadow-md' 
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                                ]"
                            >
                                Advisers
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="activeChart === 'pie'" class="h-64 sm:h-80">
                        <Pie 
                            :data="pieChartData" 
                            :options="pieChartOptions" 
                        />
                    </div>
                    
                    <div v-else-if="activeChart === 'bar'" class="h-64 sm:h-80">
                        <Bar 
                            :data="membersBarChartData" 
                            :options="barChartOptions" 
                        />
                    </div>
                    <div v-else-if="activeChart === 'advisers'" class="overflow-x-auto">
                        <button
                            @click="exportAdvisersToCSV"
                            class="mb-4 inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group disabled:opacity-60 disabled:pointer-events-none disabled:bg-gray-200 disabled:text-gray-400 w-full sm:w-auto"
                            :disabled="!props.advisersData.length"
                            title="Export as CSV"
                            aria-label="Export as CSV"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                            </svg>
                            Export CSV
                        </button>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Organization</th>
                                        <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Adviser</th>
                                        <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Second Adviser</th>
                                        <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Members</th>
                                        <th class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Officers</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="(row, idx) in props.advisersData" :key="idx">
                                        <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                                            <div class="truncate max-w-[120px] sm:max-w-none" :title="row.organization">
                                                {{ row.organization }}
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                                            {{ [row.adviser_prefix, row.adviser_name, row.adviser_suffix].filter(Boolean).join(' ') || row.adviser_name || '—' }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 hidden md:table-cell">{{ row.second_adviser || '—' }}</td>
                                        <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 text-center">{{ row.members_count ?? '—' }}</td>
                                        <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 text-center hidden sm:table-cell">{{ row.officers_count ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-3 sm:p-4 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center mb-4 justify-between space-y-2 sm:space-y-0">
                        <div class="flex items-center">
                            <div :class="props.todayEvent ? 'p-2 rounded-md bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 mr-3' : 'p-2 rounded-md bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 mr-3'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" :stroke="props.todayEvent ? '#16a34a' : '#3b82f6'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-800 dark:text-gray-200">
                                {{ props.todayEvent ? "Today's Event" : "Upcoming Event" }}
                            </h3>
                        </div>
                        <!-- Minimalist Events Held Badge -->
                        <div class="flex items-center space-x-1 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 px-2 sm:px-3 py-1 rounded-full text-xs font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="hidden sm:inline">Events Held: </span>
                            <span class="sm:hidden">Events: </span>
                            <span>{{ props.pastEventsCount }}</span>
                        </div>
                    </div>
                    
                    <div v-if="displayEvent" class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 sm:p-4 bg-white dark:bg-gray-800 shadow-sm cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                      <Link :href="route('calendar')" class="block">
                        <h4 class="font-medium text-base sm:text-lg text-gray-800 dark:text-gray-200 mb-3 truncate line-clamp-2 overflow-hidden">{{ displayEvent.title }}</h4>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-2">
                          <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Start: {{ formatDate(displayEvent.start_date) }}</span>
                          </div>
                          <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>End: {{ formatDate(displayEvent.end_date) }}</span>
                          </div>
                        </div>
                        <div class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-3">
                          <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 line-clamp-3 overflow-hidden">{{ displayEvent.description }}</p>
                        </div>
                      </Link>
                    </div>
                    
                    <div v-else class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 sm:p-6 bg-gray-50 dark:bg-gray-700 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-4 text-sm sm:text-base text-gray-600 dark:text-gray-400">No upcoming events scheduled</p>
                        <a :href="route('calendar')" class="inline-block mt-3 px-3 sm:px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Create Event
                        </a>
                    </div>
                    
                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a :href="route('admin.users')" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="hidden sm:inline">Manage Organizations</span>
                            <span class="sm:hidden">Organizations</span>
                        </a>
                        <a :href="route('applications.index')" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-300 text-xs sm:text-sm font-medium rounded-xl shadow-md hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-800 dark:hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-blue-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="hidden sm:inline">View Applications</span>
                            <span class="sm:hidden">Applications</span>
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

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
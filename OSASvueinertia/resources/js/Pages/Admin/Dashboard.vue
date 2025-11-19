<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, computed, defineAsyncComponent } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import TutorialModal from '@/Components/Tutorial/TutorialModal.vue';

const loadChartModules = (() => {
    let loader;
    return () => {
        if (!loader) {
            loader = Promise.all([
                import('chart.js/auto'),
                import('chartjs-plugin-datalabels'),
                import('vue-chartjs'),
            ]).then(([chartJs, dataLabels, vueChart]) => {
                const Chart = chartJs.default || chartJs.Chart;
                const dataLabelsPlugin = dataLabels.default || dataLabels;

                if (Chart && dataLabelsPlugin) {
                    Chart.register(dataLabelsPlugin);
                }

                return {
                    Pie: vueChart.Pie,
                    Bar: vueChart.Bar,
                };
            });
        }

        return loader;
    };
})();

const Pie = defineAsyncComponent(async () => {
    const modules = await loadChartModules();
    return modules.Pie;
});

const Bar = defineAsyncComponent(async () => {
    const modules = await loadChartModules();
    return modules.Bar;
});

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
    totalSubOrgs: {
        type: Number,
        default: 0,
    },
    pendingApplications: {
        type: Number,
        default: 0,
    },
    approvedApplications: {
        type: Number,
        default: 0,
    },
    rejectedApplications: {
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
    },
    approvedPOAsCount: {
        type: Number,
        default: 0,
    },
    hasSeenTutorial: {
        type: Boolean,
        default: false,
    }
});

// Tutorial state
const showTutorial = ref(false);

// Modal state for viewing all organizations
const showAllOrganizationsModal = ref(false);

// Colors for the charts - using our primary color scheme
const COLORS = ['#3B82F6', '#10B981', '#FBBF24', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#6366F1', '#D946EF', '#A855F7'];
const COLORS_WITH_OPACITY = COLORS.map(color => `${color}CC`);

// Get the event to display (priority: today's event, then upcoming event)
const displayEvent = props.todayEvent || props.upcomingEvent;

// Real-time clock
const currentDateTime = ref(new Date());
const clockTimer = ref(null);

// Application card flip state (0 = pending, 1 = approved, 2 = rejected)
const applicationCardState = ref(0);

const cycleApplicationCard = () => {
    applicationCardState.value = (applicationCardState.value + 1) % 3;
};

const applicationCardData = computed(() => {
    const states = [
        {
            title: 'Pending Applications',
            value: props.pendingApplications,
            icon: 'clock',
            color: 'yellow',
            borderColor: 'border-yellow-500'
        },
        {
            title: 'Approved Applications',
            value: props.approvedApplications,
            icon: 'check-circle',
            color: 'green',
            borderColor: 'border-green-500'
        },
        {
            title: 'Rejected Applications',
            value: props.rejectedApplications,
            icon: 'x-circle',
            color: 'red',
            borderColor: 'border-red-500'
        }
    ];
    return states[applicationCardState.value];
});

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

// Format minimalist date time for mobile
const formatMobileDateTime = computed(() => {
    const date = currentDateTime.value;
    const month = date.toLocaleDateString('en-US', { month: 'short' });
    const day = date.getDate();
    const time = date.toLocaleTimeString('en-US', { 
        hour: 'numeric',
        minute: '2-digit',
        hour12: true 
    });
    return `${month} ${day}, ${time}`;
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

// Get organizations with members, sorted by member count (descending)
const sortedOrgsWithMembers = computed(() => {
    const orgsWithMembers = (props.advisersData || []).filter(org => org.members_count && org.members_count > 0);
    return orgsWithMembers.sort((a, b) => b.members_count - a.members_count);
});

// Get top 5 organizations for the main chart
const top5OrgsWithMembers = computed(() => {
    return sortedOrgsWithMembers.value.slice(0, 5);
});

const updateMembersBarChartData = () => {
    // Use top 5 organizations for the main display
    const orgsToDisplay = top5OrgsWithMembers.value;
    
    if (orgsToDisplay.length === 0) {
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
        labels: orgsToDisplay.map(org => org.organization || 'Unknown'),
        datasets: [
            {
                label: 'Number of Members',
                backgroundColor: COLORS_WITH_OPACITY,
                data: orgsToDisplay.map(org => org.members_count),
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

// Prepare chart data for ALL organizations (for modal)
const allMembersBarChartData = computed(() => {
    const allOrgs = sortedOrgsWithMembers.value;
    
    if (allOrgs.length === 0) {
        return {
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
                        size: 16
                    }
                }
            }]
        };
    }
    
    return {
        labels: allOrgs.map(org => org.organization || 'Unknown'),
        datasets: [
            {
                label: 'Number of Members',
                backgroundColor: COLORS_WITH_OPACITY,
                data: allOrgs.map(org => org.members_count),
                borderRadius: 8,
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 16
                    },
                    formatter: function(value) {
                        return value;
                    }
                }
            }
        ]
    };
});

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

    // Add click handler for adviser filters
    document.addEventListener('click', handleAdviserClickOutside);
    
    // Start mobile carousel auto-advance
    startMobileCarousel();
    
    // Show tutorial if user hasn't seen it yet
    if (!props.hasSeenTutorial) {
        setTimeout(() => {
            showTutorial.value = true;
        }, 1000); // Delay to allow dashboard to load first
    }
});

onUnmounted(() => {
    if (clockTimer.value) {
        clearInterval(clockTimer.value);
    }
    // Remove click handler
    document.removeEventListener('click', handleAdviserClickOutside);
    
    // Stop mobile carousel
    stopMobileCarousel();
});

const closeTutorial = () => {
    showTutorial.value = false;
};

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
                        size: 20
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
                    size: 20
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
                    // Show raw count instead of percentage
                    return `${label}: ${value} orgs`;
                }
            }
        },
        datalabels: {
            color: '#ffffff',
            font: {
                weight: 'bold',
                size: 20,
                family: 'Inter, sans-serif'
            },
            formatter: function(value) {
                // Display the raw count on the slice
                return value;
            },
            textAlign: 'center',
            anchor: 'end', // Position at the outer edge of the slice
            align: 'end', // Align towards the outer edge
            offset: -30, // Move inward by 30 pixels to keep it inside the slice
            clamp: true // Ensure labels stay within chart bounds
        }
    }
});

// In the Bar chart options, update y-axis ticks to truncate long org names
const barChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    onClick: () => {
        showAllOrganizationsModal.value = true;
    },
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
                    // Always show FULL organization name in tooltip
                    return context[0].label;
                }
            },
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 13
            },
            padding: 12,
            displayColors: false
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
                    size: 11
                },
                // Allow text wrapping for long names
                autoSkip: false,
                callback: function(value, index, ticks) {
                    const label = this.getLabelForValue ? this.getLabelForValue(value) : value;
                    if (typeof label === 'string' && label.length > 25) {
                        // Split into multiple lines at word boundaries
                        const words = label.split(' ');
                        const lines = [];
                        let currentLine = '';
                        
                        words.forEach(word => {
                            if ((currentLine + ' ' + word).length > 25) {
                                if (currentLine) lines.push(currentLine);
                                currentLine = word;
                            } else {
                                currentLine = currentLine ? currentLine + ' ' + word : word;
                            }
                        });
                        if (currentLine) lines.push(currentLine);
                        
                        return lines;
                    }
                    return label;
                }
            }
        }
    }
});

// Chart options for the modal (all organizations)
const allOrgsBarChartOptions = ref({
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
                    // Always show FULL organization name in tooltip
                    return context[0].label;
                }
            },
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 13
            },
            padding: 12,
            displayColors: false
        },
        datalabels: {
            color: '#ffffff',
            font: {
                weight: 'bold',
                size: 14
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
                    size: 10
                },
                autoSkip: false,
                callback: function(value, index, ticks) {
                    const label = this.getLabelForValue ? this.getLabelForValue(value) : value;
                    if (typeof label === 'string' && label.length > 30) {
                        // Split into multiple lines for modal (slightly longer per line)
                        const words = label.split(' ');
                        const lines = [];
                        let currentLine = '';
                        
                        words.forEach(word => {
                            if ((currentLine + ' ' + word).length > 30) {
                                if (currentLine) lines.push(currentLine);
                                currentLine = word;
                            } else {
                                currentLine = currentLine ? currentLine + ' ' + word : word;
                            }
                        });
                        if (currentLine) lines.push(currentLine);
                        
                        return lines;
                    }
                    return label;
                }
            }
        }
    }
});

// Switch between chart types
const activeChart = ref('bar'); // Default to bar chart

// Calendar generation for when no events exist
const currentMonth = ref(new Date());

const generateCalendar = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    
    // Get first day of month and last day of month
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Get day of week for first day (0 = Sunday)
    const firstDayOfWeek = firstDay.getDay();
    
    // Calculate total cells needed
    const daysInMonth = lastDay.getDate();
    const weeks = [];
    let currentWeek = [];
    
    // Fill in empty cells before first day
    for (let i = 0; i < firstDayOfWeek; i++) {
        currentWeek.push(null);
    }
    
    // Fill in the days
    for (let day = 1; day <= daysInMonth; day++) {
        currentWeek.push(day);
        
        // If week is complete (7 days) or last day of month
        if (currentWeek.length === 7 || day === daysInMonth) {
            // Fill remaining cells if needed
            while (currentWeek.length < 7) {
                currentWeek.push(null);
            }
            weeks.push([...currentWeek]);
            currentWeek = [];
        }
    }
    
    return {
        weeks,
        monthName: firstDay.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    };
});

const isToday = (day) => {
    if (!day) return false;
    const today = new Date();
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    return today.getDate() === day && 
           today.getMonth() === month && 
           today.getFullYear() === year;
};

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

// College affiliated organizations count (sum of parent organizations only in all colleges)
const collegeAffiliatedCount = computed(() => {
    return (props.collegesData || []).reduce((sum, c) => sum + (c && c.parent_orgs_count ? c.parent_orgs_count : 0), 0);
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
        icon: 'building',
        color: 'green',
        flippable: false
    },
    {
        title: 'Non-College Affiliated Orgs',
        value: nonAffiliatedCount.value,
        icon: 'users-group',
        color: 'blue',
        flippable: false
    },
    {
        ...applicationCardData.value,
        flippable: true
    },
    {
        title: 'Total Activities Conducted',
        value: props.approvedPOAsCount,
        icon: 'document-text',
        color: 'red',
        flippable: false
    },
    {
        title: 'Sub-Organizations',
        value: props.totalSubOrgs,
        icon: 'organization-chart',
        color: 'green',
        flippable: false
    },
    {
        title: 'College Affiliated Orgs',
        value: collegeAffiliatedCount.value,
        icon: 'academic-cap',
        color: 'blue',
        flippable: false
    }
    // Removed Past Events card
]);

// Mobile stats with custom grouping
const mobileStatsCards = computed(() => {
    const cards = statsCards.value;
    // Reorder for mobile: 
    // Page 1: Pending/Approved/Rejected Applications (index 2, flippable) + Total Activities Conducted (index 3)
    // Page 2: Total Organizations (index 0) + Sub-Organizations (index 4)
    // Page 3: College Affiliated Orgs (index 5) + Non-College Affiliated (index 1)
    return [
        cards[2], // Pending/Approved/Rejected Applications (flippable)
        cards[3], // Total Activities Conducted
        cards[0], // Total Organizations
        cards[4], // Sub-Organizations
        cards[5], // College Affiliated Orgs
        cards[1]  // Non-College Affiliated Orgs
    ];
});

function exportAdvisersToCSV() {
    if (!filteredAdvisersData.value.length) return;
    const headers = ['Organization', 'Adviser', 'Second Adviser'];
    const rows = filteredAdvisersData.value.map(row => {
        // Combine adviser name with prefix and suffix
        const adviserFullName = [row.adviser_prefix, row.adviser_name, row.adviser_suffix]
            .filter(Boolean)
            .join(' ') || row.adviser_name || '';
        
        return [
            `"${row.organization || ''}"`,
            `"${adviserFullName}"`,
            `"${row.second_adviser || ''}"`
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

// Adviser table columns configuration
const adviserTableColumns = [
    { key: 'organization', label: 'Organization', type: 'text' },
    { key: 'adviser', label: 'Adviser', type: 'text' },
    { key: 'second_adviser', label: 'Second Adviser', type: 'text' }
];

// Filter operators by type
const filterOperators = {
    text: [
        { value: 'contains', label: 'Contains' },
        { value: 'equals', label: 'Equals' },
        { value: 'starts', label: 'Starts with' },
        { value: 'ends', label: 'Ends with' }
    ]
};

// Create default column filters for advisers table
const createDefaultAdviserFilters = () => {
    return adviserTableColumns.reduce((acc, column) => {
        acc[column.key] = {
            operator: filterOperators[column.type][0].value,
            value: ''
        };
        return acc;
    }, {});
};

const adviserColumnFilters = ref(createDefaultAdviserFilters());
const activeAdviserFilterDropdown = ref(null);
const adviserSortState = ref({ column: null, direction: null });
const adviserFilterDropdownStyle = ref({});

// Filter advisers data based on column filters
const filteredAdvisersData = computed(() => {
    let filtered = [...(props.advisersData || [])];

    // Apply column filters
    adviserTableColumns.forEach(column => {
        const filter = adviserColumnFilters.value[column.key];
        if (!filter || !filter.value) return;

        const filterValue = filter.value.toLowerCase();
        
        filtered = filtered.filter(row => {
            let cellValue = '';
            
            // Get cell value based on column
            if (column.key === 'organization') {
                cellValue = (row.organization || '').toLowerCase();
            } else if (column.key === 'adviser') {
                cellValue = [row.adviser_prefix, row.adviser_name, row.adviser_suffix]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase() || (row.adviser_name || '').toLowerCase();
            } else if (column.key === 'second_adviser') {
                cellValue = (row.second_adviser || '').toLowerCase();
            }

            // Apply filter operator
            switch (filter.operator) {
                case 'contains':
                    return cellValue.includes(filterValue);
                case 'equals':
                    return cellValue === filterValue;
                case 'starts':
                    return cellValue.startsWith(filterValue);
                case 'ends':
                    return cellValue.endsWith(filterValue);
                default:
                    return true;
            }
        });
    });

    // Apply sorting
    if (adviserSortState.value.column) {
        const column = adviserSortState.value.column;
        const direction = adviserSortState.value.direction;

        filtered.sort((a, b) => {
            let aVal, bVal;

            if (column === 'organization') {
                aVal = (a.organization || '').toLowerCase();
                bVal = (b.organization || '').toLowerCase();
            } else if (column === 'adviser') {
                aVal = [a.adviser_prefix, a.adviser_name, a.adviser_suffix]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();
                bVal = [b.adviser_prefix, b.adviser_name, b.adviser_suffix]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();
            } else if (column === 'second_adviser') {
                aVal = (a.second_adviser || '').toLowerCase();
                bVal = (b.second_adviser || '').toLowerCase();
            }

            const result = aVal.localeCompare(bVal);
            return direction === 'asc' ? result : -result;
        });
    }

    return filtered;
});

// Helper functions for adviser table
const hasActiveAdviserColumnFilter = (columnKey) => {
    const filter = adviserColumnFilters.value[columnKey];
    return filter && filter.value !== '';
};

const toggleAdviserFilterDropdown = (columnKey, event) => {
    if (activeAdviserFilterDropdown.value === columnKey) {
        activeAdviserFilterDropdown.value = null;
        adviserFilterDropdownStyle.value = {};
    } else {
        activeAdviserFilterDropdown.value = columnKey;
        
        // Calculate position based on button click
        const button = event.currentTarget;
        const rect = button.getBoundingClientRect();
        const dropdownWidth = 256; // w-64 = 16rem = 256px
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Determine if dropdown should open to the left or right
        let left = rect.left;
        if (left + dropdownWidth > viewportWidth) {
            left = rect.right - dropdownWidth;
        }
        
        // Determine if dropdown should open above or below
        let top = rect.bottom + 8; // 8px gap
        const estimatedDropdownHeight = 300;
        if (top + estimatedDropdownHeight > viewportHeight) {
            top = rect.top - estimatedDropdownHeight - 8;
        }
        
        adviserFilterDropdownStyle.value = {
            top: `${top}px`,
            left: `${left}px`,
        };
    }
};

const closeAdviserFilterDropdown = () => {
    activeAdviserFilterDropdown.value = null;
};

const clearAdviserColumnFilter = (columnKey) => {
    adviserColumnFilters.value[columnKey].value = '';
    adviserColumnFilters.value[columnKey].operator = filterOperators.text[0].value;
};

const updateAdviserSort = (columnKey, direction) => {
    adviserSortState.value = {
        column: columnKey,
        direction: direction
    };
};

const isAdviserColumnSorted = (columnKey, direction) => {
    return adviserSortState.value.column === columnKey && adviserSortState.value.direction === direction;
};

const handleAdviserClickOutside = (event) => {
    if (!event.target.closest('.adviser-column-filter-wrapper')) {
        closeAdviserFilterDropdown();
    }
};

// Mobile stats carousel state
const currentMobileStatIndex = ref(0);
const mobileStatsPerView = 2; // Show 2 cards at a time on mobile
const mobileCarouselPaused = ref(false); // Track if auto-advance is paused
const mobileCarouselTimer = ref(null); // Timer for auto-advance

const nextMobileStats = () => {
    if (currentMobileStatIndex.value < mobileStatsCards.value.length - mobileStatsPerView) {
        currentMobileStatIndex.value += mobileStatsPerView;
    } else {
        // Loop back to the beginning
        currentMobileStatIndex.value = 0;
    }
};

const prevMobileStats = () => {
    if (currentMobileStatIndex.value > 0) {
        currentMobileStatIndex.value -= mobileStatsPerView;
    } else {
        // Loop to the end
        currentMobileStatIndex.value = Math.floor((mobileStatsCards.value.length - 1) / mobileStatsPerView) * mobileStatsPerView;
    }
};

const goToMobileStatPage = (pageIndex) => {
    currentMobileStatIndex.value = pageIndex * mobileStatsPerView;
};

// User interaction handlers that pause auto-advance
const handleMobileStatNext = () => {
    mobileCarouselPaused.value = true;
    stopMobileCarousel();
    nextMobileStats();
};

const handleMobileStatPrev = () => {
    mobileCarouselPaused.value = true;
    stopMobileCarousel();
    prevMobileStats();
};

const handleMobileStatPageClick = (pageIndex) => {
    mobileCarouselPaused.value = true;
    stopMobileCarousel();
    goToMobileStatPage(pageIndex);
};

// Handle card click - pause carousel and cycle if flippable
const handleMobileCardClick = (card) => {
    mobileCarouselPaused.value = true;
    stopMobileCarousel();
    if (card.flippable) {
        cycleApplicationCard();
    }
};

const totalMobilePages = computed(() => {
    return Math.ceil(mobileStatsCards.value.length / mobileStatsPerView);
});

const currentMobilePage = computed(() => {
    return Math.floor(currentMobileStatIndex.value / mobileStatsPerView);
});

const visibleMobileStats = computed(() => {
    return mobileStatsCards.value.slice(
        currentMobileStatIndex.value,
        currentMobileStatIndex.value + mobileStatsPerView
    );
});

// Auto-advance carousel
const startMobileCarousel = () => {
    if (!mobileCarouselPaused.value) {
        mobileCarouselTimer.value = setInterval(() => {
            nextMobileStats();
        }, 5000); // Auto-advance every 5 seconds
    }
};

const stopMobileCarousel = () => {
    if (mobileCarouselTimer.value) {
        clearInterval(mobileCarouselTimer.value);
        mobileCarouselTimer.value = null;
    }
};
</script>

<template>
    <Head title="Admin Dashboard"></Head>

    <AuthenticatedLayout>
        <!-- Tutorial Modal -->
        <TutorialModal 
            :show="showTutorial" 
            :isAdmin="true"
            @close="closeTutorial"
        />
        
        <template #header>
            <div class="flex items-center justify-between select-none">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin Dashboard</h2>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="hidden md:inline">{{ formatCurrentDateTime }}</span>
                    <span class="md:hidden">{{ formatMobileDateTime }}</span>
                </div>
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
                    <div class="ml-4 select-none">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ greeting }}, {{ userName || 'Administrator' }}!</h2>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Welcome to ORBIT. Here's an overview of your system.</p>
                    </div>
                </div>
            </div>
        </div>

        

    <!-- Stats Cards - Mobile Carousel (< md screens) -->
    <div class="md:hidden mb-6">
        <div class="relative">
            <!-- Carousel Container -->
            <div class="overflow-hidden">
                <div class="grid grid-cols-2 gap-3 px-1 transition-all duration-300">
                    <div v-for="(card, index) in visibleMobileStats" :key="currentMobileStatIndex + index" 
                         :class="[
                             'bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 transition-all duration-300',
                             card.borderColor || `border-${card.color}-500`,
                             card.flippable ? 'cursor-pointer active:scale-95' : ''
                         ]"
                         @click="handleMobileCardClick(card)">
                        <div class="p-3">
                            <div class="flex flex-col space-y-2">
                                <!-- Icon -->
                                <div class="flex items-center justify-between">
                                    <div class="flex-shrink-0">
                                        <!-- Users Group Icon -->
                                        <div v-if="card.icon === 'users-group'" class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/50">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <!-- Building Icon -->
                                        <div v-else-if="card.icon === 'building'" class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <!-- Clock Icon -->
                                        <div v-else-if="card.icon === 'clock'" class="w-10 h-10 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/50">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <!-- Document Text Icon -->
                                        <div v-else-if="card.icon === 'document-text'" class="w-10 h-10 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/50">
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <!-- Organization Chart Icon -->
                                        <div v-else-if="card.icon === 'organization-chart'" class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 16H4V9h5v1c0 .55.45 1 1 1h4c.55 0 1-.45 1-1V9h5v11z"/>
                                            </svg>
                                        </div>
                                        <!-- Academic Cap Icon -->
                                        <div v-else-if="card.icon === 'academic-cap'" class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/50">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                            </svg>
                                        </div>
                                        <!-- Check Circle Icon -->
                                        <div v-else-if="card.icon === 'check-circle'" class="w-10 h-10 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <!-- X Circle Icon -->
                                        <div v-else-if="card.icon === 'x-circle'" class="w-10 h-10 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/50">
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Flip indicator -->
                                    <div v-if="card.flippable" class="flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Title and Value -->
                                <div class="select-none">
                                    <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 line-clamp-2 min-h-[2rem]">{{ card.title }}</h3>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ card.value }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Dots -->
            <div class="flex justify-center items-center space-x-2 mt-4">
                <button 
                    @click="handleMobileStatPrev"
                    class="p-1.5 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                >
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <div class="flex space-x-1.5">
                    <button
                        v-for="pageIndex in totalMobilePages"
                        :key="pageIndex"
                        @click="handleMobileStatPageClick(pageIndex - 1)"
                        :class="[
                            'transition-all duration-300',
                            currentMobilePage === pageIndex - 1
                                ? 'w-6 h-2 bg-blue-600 dark:bg-blue-500 rounded-full'
                                : 'w-2 h-2 bg-gray-300 dark:bg-gray-600 rounded-full hover:bg-gray-400 dark:hover:bg-gray-500'
                        ]"
                    />
                </div>
                
                <button 
                    @click="handleMobileStatNext"
                    class="p-1.5 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                >
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Desktop Grid (md+ screens) -->
    <div class="hidden md:grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-6">
            <div v-for="(card, index) in statsCards" :key="index" 
                 :class="[
                     'bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 transition-all duration-300',
                     card.borderColor || `border-${card.color}-500`,
                     card.flippable ? 'cursor-pointer hover:shadow-lg hover:scale-105' : 'hover:shadow-md'
                 ]"
                 @click="card.flippable ? cycleApplicationCard() : null">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
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
                            <!-- Document Text Icon for POAs -->
                            <div v-else-if="card.icon === 'document-text'" class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <!-- Custom Icon for Sub-Organizations (uses currentColor) -->
                            <div v-else-if="card.icon === 'organization-chart'" class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 dark:text-green-400" viewBox="0 -960 960 960" fill="currentColor" width="24px" height="24px">
                                    <path d="M600-120v-120H440v-400h-80v120H80v-320h280v120h240v-120h280v320H600v-120h-80v320h80v-120h280v320H600ZM160-760v160-160Zm520 400v160-160Zm0-400v160-160Zm0 160h120v-160H680v160Zm0 400h120v-160H680v160ZM160-600h120v-160H160v160Z" />
                                </svg>
                            </div>
                            <!-- Academic Cap Icon for College Affiliated Orgs -->
                            <div v-else-if="card.icon === 'academic-cap'" class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                </svg>
                            </div>
                            <!-- Check Circle Icon for Approved Applications -->
                            <div v-else-if="card.icon === 'check-circle'" class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <!-- X Circle Icon for Rejected Applications -->
                            <div v-else-if="card.icon === 'x-circle'" class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1 select-none">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ card.title }}</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ card.value }}</p>
                        </div>
                        </div>
                        <!-- Flip indicator icon (only for flippable cards) -->
                        <div v-if="card.flippable" class="flex-shrink-0 ml-2 relative group/tooltip">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute bottom-full right-0 mb-2 hidden group-hover/tooltip:block z-10 pointer-events-none">
                                <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg py-2 px-3 whitespace-nowrap shadow-lg">
                                    Click to flip card
                                    <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                </div>
                            </div>
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
                        <div class="flex items-center justify-between w-full sm:w-auto">
                            <h3 class="text-base sm:text-lg font-medium text-gray-800 dark:text-gray-200 select-none">
                                <template v-if="activeChart === 'bar'">Members per Organization</template>
                                <template v-else>Student Organizations by College</template>
                            </h3>
                            <button 
                                v-if="activeChart === 'bar' && sortedOrgsWithMembers.length > 5"
                                @click="showAllOrganizationsModal = true"
                                class="ml-3 text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium flex items-center gap-1 transition-colors">
                                View All ({{ sortedOrgsWithMembers.length }})
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
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
                                <span class="hidden sm:inline">Members</span>
                                <span class="sm:hidden inline-flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                        <path d="M350-63q-46 0-82.5-24T211-153q-16 21-40.5 32.5T120-109q-51 0-85.5-35T0-229q0-43 28-77.5T99-346q-14-20-21.5-42.5T70-436q0-40 20.5-75t57.5-57q5 18 13.5 38.5T181-494q-14 11-22 26.5t-8 32.5q0 56 46 69t87 21l19 32q-11 32-19 54.5t-8 40.5q0 30 21.5 52.5T350-143q38 0 63-34t41-80q16-46 24.5-93t13.5-72l78 21q-9 45-22 103t-36.5 110.5Q488-135 449.5-99T350-63ZM120-189q17 0 28.5-11.5T160-229q0-17-11.5-28.5T120-269q-17 0-28.5 11.5T80-229q0 17 11.5 28.5T120-189Zm284-158q-46-41-83.5-76.5t-64.5-69q-27-33.5-41.5-67T200-629q0-65 44.5-109.5T354-783q4 0 7 .5t7 .5q-4-10-6-20t-2-21q0-50 35-85t85-35q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h14q60 0 102 38.5t50 95.5q-18-3-40.5-3t-41.5 2q-7-23-25.5-38T606-703q-35 0-54.5 20.5T498-623h-37q-35-41-54.5-60.5T354-703q-32 0-53 21t-21 53q0 23 13 47.5t36.5 52q23.5 27.5 57 58.5t74.5 67l-57 57Zm76-436q17 0 28.5-11.5T520-823q0-17-11.5-28.5T480-863q-17 0-28.5 11.5T440-823q0 17 11.5 28.5T480-783ZM609-63q-22 0-43.5-6T524-88q11-14 22-33t20-35q11 7 22 10t22 3q32 0 53.5-22.5T685-219q0-19-8-41t-19-54l19-32q42-8 87.5-21t45.5-69q0-40-29.5-58T716-512q-42 0-98 16t-131 41l-21-78q78-25 139-42t112-17q69 0 121 41t52 115q0 25-7.5 47.5T861-346q43 5 71 39.5t28 77.5q0 50-34.5 85T840-109q-26 0-50.5-11.5T749-153q-20 42-56.5 66T609-63Zm232-126q17 0 28-11.5t11-28.5q0-17-11.5-29T840-270q-17 0-28.5 11.5T800-230q0 17 12 29t29 12Zm-721-40Zm360-594Zm360 593Z"/>
                                    </svg>
                                </span>
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
                                <span class="hidden sm:inline">Colleges</span>
                                <span class="sm:hidden inline-flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                        <path d="M480-120 200-272v-240L40-600l440-240 440 240v320h-80v-276l-80 44v240L480-120Zm0-332 274-148-274-148-274 148 274 148Zm0 241 200-108v-151L480-360 280-470v151l200 108Zm0-241Zm0 90Zm0 0Z"/>
                                    </svg>
                                </span>
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
                                <span class="hidden sm:inline">Advisers</span>
                                <span class="sm:hidden inline-flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                                        <path d="M412-168q45-91 120-121.5T660-320q23 0 45 4t43 10q24-38 38-82t14-92q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 45 11.5 86t34.5 76q41-20 85-31t89-11q32 0 61.5 5.5T500-340q-23 12-43.5 28T418-278q-12-2-20.5-2H380q-32 0-63.5 7T256-252q32 32 71.5 53.5T412-168Zm68 88q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80ZM380-420q-58 0-99-41t-41-99q0-58 41-99t99-41q58 0 99 41t41 99q0 58-41 99t-99 41Zm0-80q25 0 42.5-17.5T440-560q0-25-17.5-42.5T380-620q-25 0-42.5 17.5T320-560q0 25 17.5 42.5T380-500Zm280 120q-42 0-71-29t-29-71q0-42 29-71t71-29q42 0 71 29t29 71q0 42-29 71t-71 29ZM480-480Z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="activeChart === 'pie'" class="h-64 sm:h-80">
                        <Pie 
                            :data="pieChartData" 
                            :options="pieChartOptions" 
                        />
                    </div>
                    
                    <div v-else-if="activeChart === 'bar'" class="h-64 sm:h-80 relative">
                        <Bar 
                            :data="membersBarChartData" 
                            :options="barChartOptions" 
                            class="cursor-pointer"
                        />
                        <div v-if="sortedOrgsWithMembers.length > 5" 
                             class="absolute bottom-2 right-2 text-xs text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-2 py-1 rounded shadow">
                            Showing top 5 of {{ sortedOrgsWithMembers.length }} • Click to view all
                        </div>
                    </div>
                    <div v-else-if="activeChart === 'advisers'">
                        <!-- Export Button - Only show when data exists -->
                        <button
                            v-if="props.advisersData && props.advisersData.length > 0"
                            @click="exportAdvisersToCSV"
                            class="mb-4 inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group disabled:opacity-60 disabled:pointer-events-none disabled:bg-gray-200 disabled:text-gray-400 w-full sm:w-auto"
                            title="Export as CSV"
                            aria-label="Export as CSV"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                            </svg>
                            Export CSV
                        </button>

                        <!-- Table - Always show header to allow filter clearing -->
                        <div v-if="props.advisersData && props.advisersData.length > 0">
                            <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th v-for="column in adviserTableColumns" :key="column.key" 
                                            class="px-2 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            <div class="flex items-center justify-between gap-2">
                                                <span>{{ column.label }}</span>
                                                <div class="flex items-center gap-1">
                                                    <!-- Sort Button -->
                                                    <button
                                                        type="button"
                                                        @click="updateAdviserSort(column.key, isAdviserColumnSorted(column.key, 'asc') ? 'desc' : 'asc')"
                                                        class="inline-flex items-center p-1 rounded-md transition-colors duration-150"
                                                        :class="adviserSortState.column === column.key ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300' : 'text-gray-400 dark:text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                                        :aria-label="`Sort by ${column.label}`"
                                                    >
                                                        <!-- Ascending Icon -->
                                                        <svg v-if="isAdviserColumnSorted(column.key, 'asc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M3 6h4M3 12h8M3 18h12M17 8l-4 4 4 4" />
                                                        </svg>
                                                        <!-- Descending Icon -->
                                                        <svg v-else-if="isAdviserColumnSorted(column.key, 'desc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M3 18h4M3 12h8M3 6h12M17 16l-4-4 4-4" />
                                                        </svg>
                                                        <!-- Unsorted Icon -->
                                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M3 6h18M3 12h18M3 18h18" />
                                                        </svg>
                                                    </button>

                                                    <!-- Filter Button -->
                                                    <div class="relative adviser-column-filter-wrapper">
                                                        <button
                                                            type="button"
                                                            ref="filterButton"
                                                            class="inline-flex items-center justify-center p-1 rounded-md transition-colors duration-150"
                                                            :class="hasActiveAdviserColumnFilter(column.key) ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                                            @click.stop="toggleAdviserFilterDropdown(column.key, $event)"
                                                            :aria-expanded="activeAdviserFilterDropdown === column.key"
                                                            :aria-label="`Filter ${column.label}`"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                                            </svg>
                                                        </button>

                                                        <!-- Filter Dropdown with Teleport -->
                                                        <Teleport to="body">
                                                            <transition name="fade">
                                                                <div
                                                                    v-if="activeAdviserFilterDropdown === column.key"
                                                                    class="fixed w-64 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 p-4"
                                                                    :style="adviserFilterDropdownStyle"
                                                                    @click.stop
                                                                >
                                                                <div class="flex items-start justify-between gap-2 mb-3">
                                                                    <div>
                                                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Filter {{ column.label }}</p>
                                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Apply a condition to this column</p>
                                                                    </div>
                                                                    <button
                                                                        type="button"
                                                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                                                        @click="clearAdviserColumnFilter(column.key)"
                                                                    >
                                                                        Clear
                                                                    </button>
                                                                </div>

                                                                <div class="space-y-4">
                                                                    <div>
                                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Condition</label>
                                                                        <select
                                                                            v-model="adviserColumnFilters[column.key].operator"
                                                                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                        >
                                                                            <option
                                                                                v-for="option in filterOperators[column.type]"
                                                                                :key="option.value"
                                                                                :value="option.value"
                                                                            >
                                                                                {{ option.label }}
                                                                            </option>
                                                                        </select>
                                                                    </div>

                                                                    <div>
                                                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Value</label>
                                                                        <input
                                                                            type="text"
                                                                            v-model="adviserColumnFilters[column.key].value"
                                                                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                            placeholder="Enter text"
                                                                        />
                                                                    </div>

                                                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                                                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Quick Sort</p>
                                                                        <div class="flex flex-col gap-1">
                                                                            <button
                                                                                type="button"
                                                                                class="inline-flex items-center justify-between w-full text-left text-xs px-2 py-1 rounded-md transition-colors"
                                                                                :class="isAdviserColumnSorted(column.key, 'asc') ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"
                                                                                @click="updateAdviserSort(column.key, 'asc'); closeAdviserFilterDropdown();"
                                                                            >
                                                                                <span class="flex items-center gap-2">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                                                        <path d="M7 18h3v-8h3L8 4 3 10h3v8zm10-6h-3v8h-3l5 6 5-6h-3v-8z" />
                                                                                    </svg>
                                                                                    Sort Ascending
                                                                                </span>
                                                                                <svg v-if="isAdviserColumnSorted(column.key, 'asc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.147 7.146a1 1 0 01-1.415 0L3.296 9.01a1 1 0 011.415-1.414L9 11.884l6.296-6.295a1 1 0 011.408-.3z" clip-rule="evenodd" />
                                                                                </svg>
                                                                            </button>
                                                                            <button
                                                                                type="button"
                                                                                class="inline-flex items-center justify-between w-full text-left text-xs px-2 py-1 rounded-md transition-colors"
                                                                                :class="isAdviserColumnSorted(column.key, 'desc') ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"
                                                                                @click="updateAdviserSort(column.key, 'desc'); closeAdviserFilterDropdown();"
                                                                            >
                                                                                <span class="flex items-center gap-2">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                                                        <path d="M7 6h3v8h3L8 20l-5-6h3V6zm10 12h-3v-8h-3l5-6 5 6h-3v8z" />
                                                                                    </svg>
                                                                                    Sort Descending
                                                                                </span>
                                                                                <svg v-if="isAdviserColumnSorted(column.key, 'desc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.147 7.146a1 1 0 01-1.415 0L3.296 9.01a1 1 0 011.415-1.414L9 11.884l6.296-6.295a1 1 0 011.408-.3z" clip-rule="evenodd" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </transition>
                                                        </Teleport>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    <!-- Data rows when results exist -->
                                    <template v-if="filteredAdvisersData && filteredAdvisersData.length > 0">
                                        <tr v-for="(row, idx) in filteredAdvisersData" :key="idx">
                                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                                                <div class="truncate max-w-[120px] sm:max-w-none" :title="row.organization">
                                                    {{ row.organization }}
                                                </div>
                                            </td>
                                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                                                {{ [row.adviser_prefix, row.adviser_name, row.adviser_suffix].filter(Boolean).join(' ') || row.adviser_name || '—' }}
                                            </td>
                                            <td class="px-2 sm:px-4 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                                                {{ row.second_adviser || '—' }}
                                            </td>
                                        </tr>
                                    </template>
                                    <!-- No results row when filtered -->
                                    <tr v-else>
                                        <td colspan="3" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No Results Found</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-4">
                                                    No advisers match your current filter criteria. Try adjusting your filters above.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <!-- Empty State - Only when NO data exists at all -->
                        <div v-else class="flex flex-col items-center justify-center py-12 px-4 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No Data Available</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                There are currently no adviser records to display. Check back later or contact your administrator.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-3 sm:p-4 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center mb-4 justify-between space-y-2 sm:space-y-0">
                        <div class="flex items-center select-none">
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
                        <div class="flex items-center space-x-1 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 px-2 sm:px-3 py-1 rounded-full text-xs font-semibold select-none">
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
                    
                    <div v-else class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <!-- Mini Calendar View -->
                        <div class="p-4 select-none">
                            <div class="text-center mb-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ generateCalendar.monthName }}</h4>
                            </div>
                            
                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                <!-- Day headers -->
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">Su</div>
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">Mo</div>
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">Tu</div>
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">We</div>
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">Th</div>
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">Fr</div>
                                <div class="font-semibold text-gray-600 dark:text-gray-400 py-1">Sa</div>
                                
                                <!-- Calendar days -->
                                <template v-for="(week, weekIndex) in generateCalendar.weeks" :key="`week-${weekIndex}`">
                                    <div 
                                        v-for="(day, dayIndex) in week" 
                                        :key="`day-${weekIndex}-${dayIndex}`"
                                        :class="[
                                            'py-1 rounded',
                                            day ? 'text-gray-700 dark:text-gray-300' : 'text-transparent',
                                            isToday(day) ? 'bg-blue-500 text-white font-bold' : '',
                                            day && !isToday(day) ? 'hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer' : ''
                                        ]"
                                    >
                                        {{ day || '-' }}
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- No events message and action button -->
                        <div class="p-4 border-t border-gray-200 dark:border-gray-600 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">No upcoming events scheduled</p>
                            <a :href="route('calendar')" class="inline-block px-3 sm:px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
                                <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                                Create Event
                            </a>
                        </div>
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

        <!-- All Organizations Modal -->
        <div v-if="showAllOrganizationsModal" 
             @click="showAllOrganizationsModal = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4">
            <div @click.stop 
                 class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">
                        All Organizations - Members Count
                    </h3>
                    <button 
                        @click="showAllOrganizationsModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        aria-label="Close modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Content - Scrollable Chart -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                    <div v-if="sortedOrgsWithMembers.length === 0" 
                         class="text-center text-gray-500 dark:text-gray-400 py-12">
                        <p class="text-lg">No organizations with members found.</p>
                    </div>
                    <div v-else :style="{ height: Math.max(400, sortedOrgsWithMembers.length * 50) + 'px' }">
                        <Bar :data="allMembersBarChartData" :options="allOrgsBarChartOptions" />
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
                        <span>Total Organizations: <strong class="text-gray-900 dark:text-gray-100">{{ sortedOrgsWithMembers.length }}</strong></span>
                        <button 
                            @click="showAllOrganizationsModal = false"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors font-medium">
                            Close
                        </button>
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

.animate-spin-slow {
    animation: spin 3s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
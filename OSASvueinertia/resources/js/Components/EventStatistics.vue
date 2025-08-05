<template>
  <div class="p-2 sm:p-4 lg:p-6 w-full max-w-full overflow-x-hidden min-h-screen">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center h-96">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading statistics...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError" class="flex items-center justify-center h-96">
      <div class="text-center">
        <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Unable to load statistics</h3>
        <p class="text-gray-600 mb-4">There was an error loading the event statistics.</p>
        <button 
          @click="refreshData" 
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200"
        >
          Try Again
        </button>
      </div>
    </div>

    <!-- Statistics Content -->
    <div v-else class="space-y-3 sm:space-y-4 lg:space-y-6 w-full max-w-full">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Events Report</h2>
          <p class="text-gray-600 mt-1 text-sm sm:text-base">Analytics and insights for administrative reporting</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
          <!-- Time Range Selector -->
          <div class="relative w-full sm:w-auto">
            <button
              @click="showTimeRangeDropdown = !showTimeRangeDropdown"
              class="inline-flex items-center justify-center px-4 py-2 bg-white border border-blue-500 text-blue-700 text-sm font-medium rounded-xl shadow-md hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
              type="button"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z" />
              </svg>
              {{
                selectedTimeRange === '7' ? 'Last 7 days' :
                selectedTimeRange === '30' ? 'Last 30 days' :
                selectedTimeRange === '90' ? 'Last 3 months' :
                selectedTimeRange === '365' ? 'Last year' :
                'All time'
              }}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div
              v-if="showTimeRangeDropdown"
              class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-50"
            >
              <ul class="py-1">
                <li>
                  <button
                    @click="selectedTimeRange = '7'; updateChartData(); showTimeRangeDropdown = false"
                    :class="['w-full text-left px-4 py-2 text-sm transition', selectedTimeRange === '7' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700']"
                  >
                    Last 7 days
                  </button>
                </li>
                <li>
                  <button
                    @click="selectedTimeRange = '30'; updateChartData(); showTimeRangeDropdown = false"
                    :class="['w-full text-left px-4 py-2 text-sm transition', selectedTimeRange === '30' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700']"
                  >
                    Last 30 days
                  </button>
                </li>
                <li>
                  <button
                    @click="selectedTimeRange = '90'; updateChartData(); showTimeRangeDropdown = false"
                    :class="['w-full text-left px-4 py-2 text-sm transition', selectedTimeRange === '90' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700']"
                  >
                    Last 3 months
                  </button>
                </li>
                <li>
                  <button
                    @click="selectedTimeRange = '365'; updateChartData(); showTimeRangeDropdown = false"
                    :class="['w-full text-left px-4 py-2 text-sm transition', selectedTimeRange === '365' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700']"
                  >
                    Last year
                  </button>
                </li>
                <li>
                  <button
                    @click="selectedTimeRange = 'all'; updateChartData(); showTimeRangeDropdown = false"
                    :class="['w-full text-left px-4 py-2 text-sm transition', selectedTimeRange === 'all' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700']"
                  >
                    All time
                  </button>
                </li>
              </ul>
            </div>
            <!-- Click outside to close -->
            <div v-if="showTimeRangeDropdown" class="fixed inset-0 z-40" @click="showTimeRangeDropdown = false"></div>
          </div>
          
          <!-- Export Button -->
          <button
            @click="exportStatistics"
            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
            </svg>
            <span class="hidden sm:inline relative z-10">Export</span>
            <span class="sm:hidden relative z-10">CSV</span>
          </button>
        </div>
      </div>

      <!-- Statistics Cards - Responsive Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 w-full">
        <!-- Total Events -->
        <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Events</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ totalEvents }}</p>
            </div>
          </div>
        </div>

        <!-- Completed Events -->
        <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Completed</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ completedEvents }}</p>
            </div>
          </div>
        </div>

        <!-- Cancelled Events -->
        <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Cancelled</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ cancelledEvents }}</p>
            </div>
          </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Upcoming</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ upcomingEvents }}</p>
            </div>
          </div>
        </div>

        <!-- Completion Rate -->
        <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Rate</p>
              <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ completionRate }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart Container - Responsive -->
      <div class="hidden sm:block bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Event Trends</h3>
          <div class="mt-2 sm:mt-0">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span>Events over time</span>
            </div>
          </div>
        </div>

        <!-- Chart Area -->
        <div class="relative h-64 sm:h-80 w-full">
          <canvas ref="chartCanvas" class="w-full h-full"></canvas>
        </div>

        <!-- No Data State -->
        <div v-if="chartData.labels.length === 0" class="flex items-center justify-center h-64 sm:h-80">
          <div class="text-center">
            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No data available</h3>
            <p class="text-sm sm:text-base text-gray-600">No events found for the selected time range.</p>
          </div>
        </div>
      </div>

      <!-- Mobile Summary (replaces chart on small screens) -->
      <div class="block sm:hidden bg-white rounded-lg border border-gray-200 p-4 shadow-sm w-full">
        <h3 class="text-base font-semibold text-gray-900 mb-3">Quick Summary</h3>
        <div class="grid grid-cols-2 gap-3">
          <div class="text-center bg-gray-50 rounded-lg p-3">
            <p class="text-xl font-bold text-blue-600">{{ totalEvents }}</p>
            <p class="text-xs text-gray-600">Total Events</p>
          </div>
          <div class="text-center bg-gray-50 rounded-lg p-3">
            <p class="text-xl font-bold text-green-600">{{ completedEvents }}</p>
            <p class="text-xs text-gray-600">Completed</p>
          </div>
          <div class="text-center bg-gray-50 rounded-lg p-3">
            <p class="text-xl font-bold text-red-600">{{ cancelledEvents }}</p>
            <p class="text-xs text-gray-600">Cancelled</p>
          </div>
          <div class="text-center bg-gray-50 rounded-lg p-3">
            <p class="text-xl font-bold text-yellow-600">{{ upcomingEvents }}</p>
            <p class="text-xs text-gray-600">Upcoming</p>
          </div>
        </div>
      </div>

      <!-- Event Distribution -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Monthly Distribution -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Monthly Distribution</h3>
          <div class="space-y-3 max-h-48 sm:max-h-64 overflow-y-auto pr-2 custom-scrollbar">
            <div v-for="month in monthlyDistribution" :key="month.month" class="flex items-center">
              <div class="w-16 sm:w-20 text-xs sm:text-sm text-gray-600 flex-shrink-0">{{ month.month }}</div>
              <div class="flex-1 mx-2 sm:mx-3">
                <div class="bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                    :style="{ width: month.percentage + '%' }"
                  ></div>
                </div>
              </div>
              <div class="w-6 sm:w-8 text-xs sm:text-sm text-gray-900 font-medium flex-shrink-0">{{ month.count }}</div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
          <div class="space-y-3">
            <div v-for="activity in recentActivity.slice(0, 3)" :key="activity.id" class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ activity.title }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(activity.start_date, 'MMM DD, YYYY') }}</p>
              </div>
            </div>
            <div v-if="recentActivity.length === 0" class="text-center py-4">
              <p class="text-sm text-gray-500">No recent activity</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import dayjs from 'dayjs';
import Chart from 'chart.js/auto';

export default {
  name: 'EventStatistics',
  
  props: {
    events: {
      type: Array,
      required: true
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },

  setup(props) {
    const chartCanvas = ref(null);
    const chart = ref(null);
    const selectedTimeRange = ref('30');
    const hasError = ref(false);
    const showTimeRangeDropdown = ref(false);

    // Computed statistics
    const totalEvents = computed(() => props.events.length);
    
    const completedEvents = computed(() => {
      const now = new Date();
      return props.events.filter(event => {
        const endDate = event.end_date ? new Date(event.end_date) : new Date(event.start_date);
        return endDate < now && event.status !== 'cancelled';
      }).length;
    });

    const cancelledEvents = computed(() => {
      return props.events.filter(event => event.status === 'cancelled').length;
    });

    const upcomingEvents = computed(() => {
      const now = new Date();
      return props.events.filter(event => {
        const startDate = new Date(event.start_date);
        return startDate > now && event.status !== 'cancelled';
      }).length;
    });

    const completionRate = computed(() => {
      if (totalEvents.value === 0) return 0;
      return Math.round((completedEvents.value / totalEvents.value) * 100);
    });

    const averagePerMonth = computed(() => {
      if (props.events.length === 0) return 0;
      
      const oldestEvent = props.events.reduce((oldest, event) => {
        const eventDate = new Date(event.start_date);
        return eventDate < oldest ? eventDate : oldest;
      }, new Date());
      
      const monthsDiff = dayjs().diff(dayjs(oldestEvent), 'month') + 1;
      return Math.round(props.events.length / monthsDiff * 10) / 10;
    });

    // Chart data
    const chartData = ref({
      labels: [],
      datasets: []
    });

    // Monthly distribution
    const monthlyDistribution = computed(() => {
      const months = {};
      const maxCount = ref(0);

      props.events.forEach(event => {
        const month = dayjs(event.start_date).format('MMM YYYY');
        months[month] = (months[month] || 0) + 1;
        maxCount.value = Math.max(maxCount.value, months[month]);
      });

      return Object.entries(months)
        .map(([month, count]) => ({
          month,
          count,
          percentage: maxCount.value > 0 ? (count / maxCount.value) * 100 : 0
        }))
        .sort((a, b) => dayjs(a.month).valueOf() - dayjs(b.month).valueOf())
        .slice(-6); // Last 6 months
    });

    // Recent activity (limit to 3)
    const recentActivity = computed(() => {
      return [...props.events]
        .sort((a, b) => new Date(b.start_date) - new Date(a.start_date))
        .slice(0, 3);
    });

    // Update chart data based on selected time range
    function updateChartData() {
      try {
        const now = dayjs();
        let startDate;

        switch (selectedTimeRange.value) {
          case '7':
            startDate = now.subtract(7, 'day');
            break;
          case '30':
            startDate = now.subtract(30, 'day');
            break;
          case '90':
            startDate = now.subtract(90, 'day');
            break;
          case '365':
            startDate = now.subtract(365, 'day');
            break;
          default:
            startDate = null;
        }

        const filteredEvents = startDate 
          ? props.events.filter(event => dayjs(event.start_date).isAfter(startDate))
          : props.events;

        // Group events by date
        const eventsByDate = {};
        const completedByDate = {};

        filteredEvents.forEach(event => {
          const date = dayjs(event.start_date).format('YYYY-MM-DD');
          eventsByDate[date] = (eventsByDate[date] || 0) + 1;

          const endDate = event.end_date ? new Date(event.end_date) : new Date(event.start_date);
          if (endDate < new Date()) {
            completedByDate[date] = (completedByDate[date] || 0) + 1;
          }
        });

        // Generate labels and data
        const labels = [];
        const eventsData = [];
        const completedData = [];

        const days = selectedTimeRange.value === 'all' ? 30 : parseInt(selectedTimeRange.value);
        const interval = days > 90 ? 'month' : 'day';
        const format = interval === 'month' ? 'MMM YYYY' : 'MMM DD';

        for (let i = days - 1; i >= 0; i--) {
          const date = now.subtract(i, interval);
          const dateKey = date.format('YYYY-MM-DD');
          const label = date.format(format);

          labels.push(label);
          eventsData.push(eventsByDate[dateKey] || 0);
          completedData.push(completedByDate[dateKey] || 0);
        }

        chartData.value = {
          labels,
          datasets: [
            {
              label: 'Events',
              data: eventsData,
              borderColor: 'rgb(34, 197, 94)', // Green-500
              backgroundColor: 'transparent',
              borderWidth: 2,
              tension: 0, // No smoothing - sharp point to point
              fill: false,
              pointBackgroundColor: 'rgb(34, 197, 94)',
              pointBorderColor: 'rgb(34, 197, 94)',
              pointRadius: 5,
              pointHoverRadius: 8,
              pointBorderWidth: 2
            }
          ]
        };

        nextTick(() => {
          renderChart();
        });
      } catch (error) {
        console.error('Error updating chart data:', error);
        hasError.value = true;
      }
    }

    // Render chart
    function renderChart() {
      if (!chartCanvas.value) return;

      if (chart.value) {
        chart.value.destroy();
      }

      const ctx = chartCanvas.value.getContext('2d');
      
      chart.value = new Chart(ctx, {
        type: 'line',
        data: chartData.value,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(255, 255, 255, 0.95)',
              titleColor: '#374151',
              bodyColor: '#374151',
              borderColor: '#E5E7EB',
              borderWidth: 1,
              cornerRadius: 8,
              displayColors: false,
              titleFont: {
                size: 12,
                weight: '600'
              },
              bodyFont: {
                size: 12
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              border: {
                display: false
              },
              ticks: {
                color: '#9CA3AF',
                font: {
                  size: 11
                },
                maxTicksLimit: 8
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(156, 163, 175, 0.2)',
                drawBorder: false
              },
              border: {
                display: false
              },
              ticks: {
                color: '#9CA3AF',
                font: {
                  size: 11
                },
                stepSize: 1,
                padding: 8
              }
            }
          },
          elements: {
            point: {
              radius: 3,
              hoverRadius: 5,
              borderWidth: 0
            },
            line: {
              borderWidth: 2
            }
          },
          layout: {
            padding: {
              top: 10,
              bottom: 10
            }
          }
        }
      });
    }

    // Export statistics
    function exportStatistics() {
      try {
        const csvData = [
          ['Date', 'Event Title', 'Status', 'Description'],
          ...props.events.map(event => [
            dayjs(event.start_date).format('YYYY-MM-DD'),
            event.title || '',
            new Date(event.end_date || event.start_date) < new Date() ? 'Completed' : 'Upcoming',
            event.description || ''
          ])
        ];

        const csvContent = csvData.map(row => 
          row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `event-statistics-${dayjs().format('YYYY-MM-DD')}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      } catch (error) {
        console.error('Error exporting statistics:', error);
        alert('Failed to export statistics. Please try again.');
      }
    }

    // Refresh data
    function refreshData() {
      hasError.value = false;
      updateChartData();
    }

    // Format date
    function formatDate(dateString, format) {
      return dayjs(dateString).format(format);
    }

    // Watch for events changes
    watch(() => props.events, () => {
      updateChartData();
    }, { deep: true });

    // Initialize
    onMounted(() => {
      updateChartData();
    });

    // Cleanup
    onUnmounted(() => {
      if (chart.value) {
        chart.value.destroy();
      }
    });

    return {
      chartCanvas,
      selectedTimeRange,
      hasError,
      totalEvents,
      completedEvents,
      cancelledEvents,
      upcomingEvents,
      completionRate,
      averagePerMonth,
      chartData,
      monthlyDistribution,
      recentActivity,
      updateChartData,
      exportStatistics,
      refreshData,
      formatDate,
      showTimeRangeDropdown
    };
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar,
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track,
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb,
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover,
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animation for cards */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.bg-gradient-to-r {
  animation: fadeInUp 0.6s ease-out;
}

.bg-gradient-to-r:nth-child(2) {
  animation-delay: 0.1s;
}

.bg-gradient-to-r:nth-child(3) {
  animation-delay: 0.2s;
}

.bg-gradient-to-r:nth-child(4) {
  animation-delay: 0.3s;
}
</style>

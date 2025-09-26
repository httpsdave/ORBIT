<template>
  <div class="w-full pb-4 sm:pb-8 px-1 sm:px-0">
  
  <!-- Status Banner -->
  <StatusBanner
    :show="showStatusBanner"
    :message="statusMessage"
    :type="statusType"
    @close="showStatusBanner = false"
  />
  
  <!-- Colored banner -->
  <div class="flex w-full mb-4 overflow-hidden rounded-lg shadow-sm">
    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
  </div>
  
  <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-800 dark:text-gray-100 px-1 sm:px-0">Event Calendar</h1>
  
  <div class="mb-4 sm:mb-6 grid gap-4 sm:gap-6 md:grid-cols-2 px-1 sm:px-0">
    <!-- Left panel - only visible to admins -->
    <div v-if="isAdmin" class="md:col-span-1">
        <div v-if="isAdmin" class="md:col-span-1">
          <FileUploadComponent 
            @file-processed="handleFileProcessed"
            @create-new-event="createNewEvent"
          />
        </div>
    </div>
    
   <TodayUpcomingEvents 
  :displayed-events="displayedEvents"
  :is-admin="isAdmin"
  @edit-event="editEvent"
  @delete-event="deleteEvent"
  @cancel-event="cancelEvent"
  @view-event-details="viewEventDetails"
/>
    
</div>

<!-- View Toggle and Actions - Mobile Optimized -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-3 sm:gap-4 px-1 sm:px-0">
  <!-- View Toggle Switch -->
  <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
    <button 
      @click="currentView = 'calendar'"
      :class="[
        'flex items-center space-x-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-200',
        currentView === 'calendar' 
          ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm' 
          : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100'
      ]"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <span>Calendar</span>
    </button>
    <button 
      @click="currentView = 'statistics'"
      :class="[
        'flex items-center space-x-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-200',
        currentView === 'statistics' 
          ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm' 
          : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100'
      ]"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <span>Statistics</span>
    </button>
  </div>
  
  <!-- Event History Button -->
  <button 
    @click="showPastEventsModal = true" 
    class="flex items-center space-x-2 px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
  >
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
    </svg>
    <span class="text-sm font-medium">View Past Events</span>
  </button>
</div>

  <!-- Main Content Container with 3D Flip Animation - Mobile Optimized -->
  <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden min-h-96 mx-1 sm:mx-0" style="perspective: 1000px;">
    <!-- Calendar View -->
    <div 
      :class="[
        'transition-transform duration-300 ease-out transform-style-preserve-3d',
        currentView === 'statistics' ? 'rotate-y-180' : 'rotate-y-0'
      ]"
      style="transform-style: preserve-3d; will-change: transform;"
    >
             <!-- Front Side - Calendar -->
       <div 
         :class="[
           'w-full backface-hidden',
           currentView === 'statistics' ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'
         ]"
         style="backface-visibility: hidden; will-change: opacity;"
       >
        <div class="p-2 sm:p-5">
          <!-- Custom calendar header with your color scheme -->
          <div class="mb-4 sm:mb-6">
            <!-- Colored banner for the calendar -->
            <div class="flex w-full mb-4 overflow-hidden rounded-lg">
              <div class="w-1/4 h-1.5 bg-blue-500"></div>
              <div class="w-1/4 h-1.5 bg-green-500"></div>
              <div class="w-1/4 h-1.5 bg-yellow-500"></div>
              <div class="w-1/4 h-1.5 bg-red-500"></div>
            </div>
            
            <!-- Date Picker for Quick Navigation - Mobile Optimized -->
            <div class="mb-4 space-y-3">
              <!-- Mobile: Stack everything vertically -->
              <div class="block sm:hidden space-y-3">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  Quick Jump:
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <select
                    v-model="selectedMonth"
                    @change="navigateToDate"
                    class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 appearance-none"
                  >
                    <option v-for="(month, index) in months" :key="index" :value="index">
                      {{ month }}
                    </option>
                  </select>
                  <select
                    v-model="selectedYear"
                    @change="navigateToDate"
                    class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 appearance-none"
                  >
                    <option v-for="year in yearOptions" :key="year" :value="year">
                      {{ year }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Desktop: Horizontal layout -->
              <div class="hidden sm:flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    Quick Jump:
                  </label>
                  <div class="flex items-center gap-2">
                    <select
                      v-model="selectedMonth"
                      @change="navigateToDate"
                      class="px-3 py-1.5 pr-8 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 min-w-0 appearance-none"
                      style="min-width: 110px;"
                    >
                      <option v-for="(month, index) in months" :key="index" :value="index">
                        {{ month }}
                      </option>
                    </select>
                    <select
                      v-model="selectedYear"
                      @change="navigateToDate"
                      class="px-3 py-1.5 pr-8 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 min-w-0 appearance-none"
                      style="min-width: 80px;"
                    >
                      <option v-for="year in yearOptions" :key="year" :value="year">
                        {{ year }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <FullCalendar
            ref="fullCalendar"
            :options="calendarOptions"
            :class="['full-calendar-custom', { 'calendar-admin': isAdmin }]"
          />
        </div>
      </div>
      
             <!-- Back Side - Statistics -->
       <div 
         :class="[
           'w-full backface-hidden rotate-y-180',
           currentView === 'calendar' ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'
         ]"
         style="backface-visibility: hidden; transform: rotateY(180deg); will-change: opacity;"
       >
        <EventStatistics 
          :events="events" 
          :is-admin="isAdmin"
          :key="'statistics-' + events.length"
        />
      </div>
    </div>
  </div>
  
  <!-- Event Form Modal -->
  <Transition name="modal">
    <div 
      v-if="extractedData || isEditing" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="cancelEdit"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 sm:p-6 w-full max-w-xs sm:max-w-md max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
          {{ isEditing ? 'Edit Event' : 'Create New Event' }}
        </h2>
        <button @click="cancelEdit" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="space-y-4">
        <div>
          <div class="relative group">
            <input 
              v-model="eventForm.title" 
              type="text" 
              id="event-title"
              :class="[
                'peer w-full rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                formErrors.title 
                  ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                  : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
              ]"
              placeholder="Event Title"
              @input="formErrors.title = ''"
            />
            <label 
              for="event-title"
              class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
              :class="eventForm.title ? 'floating-label-active' : ''"
            >
              Event Title
            </label>
          </div>
          <p v-if="formErrors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.title }}</p>
        </div>
        
        <!-- Date Fields Section -->
        <div>
          <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Event Dates
          </h4>
          <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <div>
              <div class="relative group">
                <input 
                  v-model="eventForm.date" 
                  type="date" 
                  id="event-start-date"
                  :class="[
                    'peer w-full rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                    formErrors.start_date 
                      ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                      : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
                  ]"
                  placeholder="Start Date"
                  @input="formErrors.start_date = ''"
                />
                <label 
                  for="event-start-date"
                  class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
                  :class="eventForm.date ? 'floating-label-active' : ''"
                >
                  Start Date
                </label>
              </div>
              <p v-if="formErrors.start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.start_date }}</p>
            </div>
            <div>
              <div class="relative group">
                <input 
                  v-model="eventForm.end_date" 
                  type="date" 
                  id="event-end-date"
                  :min="eventForm.date"
                  :class="[
                    'peer w-full rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                    formErrors.end_date 
                      ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                      : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
                  ]"
                  placeholder="End Date"
                  @input="formErrors.end_date = ''"
                />
                <label 
                  for="event-end-date"
                  class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
                  :class="eventForm.end_date ? 'floating-label-active' : ''"
                >
                  End Date
                </label>
              </div>
              <p v-if="formErrors.end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.end_date }}</p>
            </div>
          </div>
        </div>
        
        <!-- Time Fields Section -->
        <div>
          <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Event Times
          </h4>
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="flex-shrink-0">
              <div class="relative group">
                <input 
                  v-model="eventForm.start_time" 
                  type="time" 
                  id="event-start-time"
                  :class="[
                    'peer w-28 sm:w-36 rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                    formErrors.start_time 
                      ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                      : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
                  ]"
                  placeholder="Start Time"
                  @input="formErrors.start_time = ''"
                />
                <label 
                  for="event-start-time"
                  class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
                  :class="eventForm.start_time ? 'floating-label-active' : ''"
                >
                  Start Time
                </label>
              </div>
              <p v-if="formErrors.start_time" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.start_time }}</p>
            </div>
            
            <div class="flex-shrink-0 mt-6">
              <span class="text-gray-500 dark:text-gray-400 font-medium text-lg">—</span>
            </div>
            
            <div class="flex-shrink-0">
              <div class="relative group">
                <input 
                  v-model="eventForm.end_time" 
                  type="time" 
                  id="event-end-time"
                  :class="[
                    'peer w-28 sm:w-36 rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                    formErrors.end_time 
                      ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                      : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
                  ]"
                  placeholder="End Time"
                  @input="formErrors.end_time = ''"
                />
                <label 
                  for="event-end-time"
                  class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
                  :class="eventForm.end_time ? 'floating-label-active' : ''"
                >
                  End Time
                </label>
              </div>
              <p v-if="formErrors.end_time" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.end_time }}</p>
            </div>
          </div>
        </div>
        <div>
          <div class="relative group">
            <textarea 
              v-model="eventForm.description" 
              rows="3" 
              id="event-description"
              :class="[
                'peer w-full rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                formErrors.description 
                  ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                  : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
              ]"
              placeholder="Description"
              @input="formErrors.description = ''"
            ></textarea>
            <label 
              for="event-description"
              class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
              :class="eventForm.description ? 'floating-label-active' : ''"
            >
              Description
            </label>
          </div>
          <p v-if="formErrors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.description }}</p>
        </div>
        <div>
          <div class="relative group">
            <input 
              v-model="eventForm.location" 
              type="text" 
              id="event-location"
              :class="[
                'peer w-full rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                formErrors.location 
                  ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                  : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
              ]"
              placeholder="Location"
              @input="formErrors.location = ''"
            />
            <label 
              for="event-location"
              class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
              :class="eventForm.location ? 'floating-label-active' : ''"
            >
              Location
            </label>
          </div>
          <p v-if="formErrors.location" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.location }}</p>
        </div>
        <div>
          <div class="relative group">
            <input 
              v-model="eventForm.organization" 
              type="text" 
              id="event-organization"
              :class="[
                'peer w-full rounded-md border shadow-sm focus:ring-2 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 sm:py-2.5 text-sm sm:text-base placeholder-transparent transition-all duration-300',
                formErrors.organization 
                  ? 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-200 dark:focus:ring-red-600' 
                  : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-200 dark:focus:ring-blue-600'
              ]"
              placeholder="Organization"
              @input="formErrors.organization = ''"
            />
            <label 
              for="event-organization"
              class="floating-label absolute left-3 top-2 sm:top-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
              :class="eventForm.organization ? 'floating-label-active' : ''"
            >
              Organization
            </label>
          </div>
          <p v-if="formErrors.organization" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ formErrors.organization }}</p>
        </div>
        <div class="pt-4 flex justify-end space-x-3">
          <button 
            @click="cancelEdit" 
            class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-gray-100 dark:bg-gray-600 rounded-full group-hover:w-96 group-hover:h-96 opacity-20"></span>
            <span class="relative z-10">Cancel</span>
          </button>
          <button 
            @click="isEditing ? updateEvent() : saveEvent()" 
            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <span class="relative z-10">{{ isEditing ? 'Update Event' : 'Save Event' }}</span>
          </button>
        </div>
      </div>
      </div>
    </div>
  </Transition>
  
  <!-- Event Details Modal for non-admin users -->
  <Transition name="modal">
    <div 
      v-if="showEventDetailsModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeEventDetailsModal"
    >
      <div class="bg-white dark:bg-gray-800 dark:border-gray-700 border border-gray-100 rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 break-words overflow-wrap-anywhere max-w-[90%]">{{selectedEvent.title}}</h3>
        <button @click="closeEventDetailsModal" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <!-- Colored banner -->
      <div class="flex w-full mb-4 overflow-hidden rounded-md">
        <div class="w-1/4 h-1 bg-blue-500"></div>
        <div class="w-1/4 h-1 bg-green-500"></div>
        <div class="w-1/4 h-1 bg-yellow-500"></div>
        <div class="w-1/4 h-1 bg-red-500"></div>
      </div>
      
      <div class="space-y-4">
        <div class="flex space-x-4">
          <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900 rounded-lg p-3 text-center border-l-4 border-blue-500">
            <span class="text-sm font-medium text-blue-500 dark:text-blue-200">{{ formatDate(selectedEvent.start_date, 'MMM') }}</span>
            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ formatDate(selectedEvent.start_date, 'DD') }}</p>
          </div>
          <div>
            <p class="font-medium text-gray-700 dark:text-gray-100">
              <template v-if="selectedEvent.end_date && formatDate(selectedEvent.start_date, 'YYYY-MM-DD') !== formatDate(selectedEvent.end_date, 'YYYY-MM-DD')">
                {{ formatDate(selectedEvent.start_date, 'dddd, MMMM D, YYYY') }}<br>
                <span class="text-gray-600 dark:text-gray-300">{{ formatDate(selectedEvent.start_date, 'h:mm A') }} - </span>
                <br>
                {{ formatDate(selectedEvent.end_date, 'dddd, MMMM D, YYYY') }}<br>
                <span class="text-gray-600 dark:text-gray-300">{{ formatDate(selectedEvent.end_date, 'h:mm A') }}</span>
              </template>
              <template v-else>
                {{ formatDate(selectedEvent.start_date, 'dddd, MMMM D, YYYY') }}<br>
                <span class="text-gray-600 dark:text-gray-300">{{ formatDate(selectedEvent.start_date, 'h:mm A') }} - {{ formatDate(selectedEvent.end_date || selectedEvent.start_date, 'h:mm A') }}</span>
              </template>
            </p>
          </div>
        </div>
        <div v-if="selectedEvent.description" class="mt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg max-h-40 overflow-y-auto">
          <p class="text-sm text-gray-600 dark:text-gray-300 font-medium mb-2">Description:</p>
          <p class="text-gray-700 dark:text-gray-100 whitespace-pre-line break-words overflow-wrap-anywhere">{{selectedEvent.description}}</p>
        </div>
        <div v-if="selectedEvent.location" class="mt-4 bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
          <p class="text-sm text-blue-600 dark:text-blue-200 font-medium mb-2">Location:</p>
          <p class="text-gray-700 dark:text-gray-100 whitespace-pre-line break-words overflow-wrap-anywhere">{{selectedEvent.location}}</p>
        </div>
        <div v-if="selectedEvent.organization" class="mt-4 bg-green-50 dark:bg-green-900 p-4 rounded-lg">
          <p class="text-sm text-green-600 dark:text-green-200 font-medium mb-2">Organization:</p>
          <p class="text-gray-700 dark:text-gray-100 whitespace-pre-line break-words overflow-wrap-anywhere">{{selectedEvent.organization}}</p>
        </div>
        <div v-if="isAdmin" class="flex justify-end space-x-2 mt-4">
          <button @click="editEvent(selectedEvent)" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span class="relative z-10">Edit</span>
          </button>
          <button v-if="selectedEvent.status !== 'cancelled'" @click="cancelEvent(selectedEvent.id)" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="relative z-10">Cancel</span>
          </button>
          <button @click="deleteEvent(selectedEvent.id)" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="relative z-10">Delete</span>
          </button>
        </div>
      </div>
      </div>
    </div>
  </Transition>

  <EventHistoryModal 
  :show-modal="showPastEventsModal"
  :events="events"
  :is-admin="isAdmin"
  @close="closePastEventsModal"
  @view-event-details="viewEventDetails"
  @event-deleted="handleEventDeleted"
  @export-csv="exportPastEventsCsv"
  @delete-past-event="handleDeletePastEvent"
/>

  <!-- Delete Confirmation Modal -->
  <ConfirmationModal
    :show="showDeleteConfirmation"
    title="Delete Event"
    :message="`Are you sure you want to delete '${eventToDelete?.title}'? This action cannot be undone.`"
    type="danger"
    confirm-text="Delete"
    cancel-text="Cancel"
    @confirm="confirmDeleteEvent"
    @cancel="cancelDeleteEvent"
  />

  <!-- Cancel Confirmation Modal -->
  <ConfirmationModal
    :show="showCancelConfirmation"
    title="Cancel Event"
    :message="`Are you sure you want to cancel '${eventToCancel?.title}'? You can reactivate it later by editing the event.`"
    type="warning"
    confirm-text="Cancel Event"
    cancel-text="Keep Event"
    @confirm="confirmCancelEvent"
    @cancel="cancelCancelEvent"
  />

  <!-- Past Date Confirmation Modal -->
  <ConfirmationModal
    :show="showPastDateConfirmation"
    title="Past Date Warning"
    :message="pastDateMessage"
    type="warning"
    confirm-text="Continue"
    cancel-text="Cancel"
    @confirm="confirmPastDate"
    @cancel="cancelPastDate"
  />

  <!-- First-time tooltip modal -->
  <Transition name="modal">
    <div 
      v-if="showFirstClickTooltip" 
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="dismissTooltip"
    >
      <Transition name="modal-content" appear>
        <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 border border-gray-100">
          <div class="flex items-start space-x-3">
            <!-- Icon -->
            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            
            <!-- Content -->
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Calendar Tips</h3>
              <p class="text-sm text-gray-600 mb-4">
                <span class="font-medium">Click</span> any date to create a single-day event, or 
                <span class="font-medium">drag</span> across multiple dates to create a multi-day event.
              </p>
              
              <!-- Actions -->
              <div class="flex flex-col space-y-2">
                <button 
                  @click="dismissTooltip"
                  class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  Got it!
                </button>
                <button 
                  @click="dismissTooltipPermanently"
                  class="w-full px-4 py-1.5 text-xs text-gray-500 hover:text-gray-700 transition-colors duration-200"
                >
                  Don't show again
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
  
  <!-- Status Banner -->
  <StatusBanner
    v-if="showStatusBanner"
    :message="statusMessage"
    :type="statusType"
  />
  
  </div>
</template>
<style scoped>
    /* Custom dropdown arrow styles to prevent overlap */
    select {
      background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
      background-position: right 8px center;
      background-repeat: no-repeat;
      background-size: 16px 16px;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
    }

    /* Dark mode dropdown arrow */
    .dark select {
      background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    }

    /* Ensure proper spacing for dropdown content */
    select option {
      padding: 8px 12px;
    }

    /* Ensure active (selected) calendar view button has white text on blue background in both light and dark mode */
    :deep(.fc .fc-button.fc-button-active),
    :deep(.fc .fc-button:active),
    :deep(.fc .fc-button-primary.fc-button-active),
    :deep(.fc .fc-button-primary:active) {
      background-color: #3B82F6 !important;
      border-color: #3B82F6 !important;
      color: #fff !important;
    }

    .dark :deep(.fc .fc-button.fc-button-active),
    .dark :deep(.fc .fc-button:active),
    .dark :deep(.fc .fc-button-primary.fc-button-active),
    .dark :deep(.fc .fc-button-primary:active) {
      background-color: #3B82F6 !important;
      border-color: #3B82F6 !important;
      color: #fff !important;
    }
    /* Custom FullCalendar styling to match your color scheme */
    :deep(.full-calendar-custom) {
      --fc-border-color: #e5e7eb; /* gray-200 */
      --fc-button-bg-color: #ffffff; /* white */
      --fc-button-border-color: #d1d5db; /* gray-300 */
      --fc-button-text-color: #374151; /* gray-700 */
      --fc-button-hover-bg-color: #f9fafb; /* gray-50 */
      --fc-button-hover-border-color: #9ca3af; /* gray-400 */
      --fc-button-active-bg-color: #3B82F6; /* blue-500 */
      --fc-button-active-border-color: #3B82F6; /* blue-500 */
      --fc-button-active-text-color: #ffffff; /* white */
      --fc-event-bg-color: #3B82F6; /* blue-500 */
      --fc-event-border-color: #3B82F6; /* blue-500 */
      --fc-today-bg-color: #EFF6FF; /* blue-50 */
      --fc-highlight-color: #F3F4F6; /* gray-100 */
      --fc-list-event-hover-bg-color: #F3F4F6; /* gray-100 */
      --fc-neutral-bg-color: #ffffff; /* light mode background */
      --fc-neutral-text-color: #374151; /* light mode text */
    }

    /* Dark mode FullCalendar styling */
    .dark :deep(.full-calendar-custom) {
      --fc-border-color: #4b5563 !important; /* gray-600 */
      --fc-button-bg-color: #374151 !important; /* gray-700 */
      --fc-button-border-color: #4b5563 !important; /* gray-600 */
      --fc-button-text-color: #f3f4f6 !important; /* gray-100 */
      --fc-button-hover-bg-color: #4b5563 !important; /* gray-600 */
      --fc-button-hover-border-color: #6b7280 !important; /* gray-500 */
      --fc-button-active-bg-color: #3B82F6 !important; /* blue-500 */
      --fc-button-active-border-color: #3B82F6 !important; /* blue-500 */
      --fc-button-active-text-color: #ffffff !important; /* white */
      --fc-event-bg-color: #3B82F6 !important; /* blue-500 */
      --fc-event-border-color: #3B82F6 !important; /* blue-500 */
      --fc-today-bg-color: rgba(59, 130, 246, 0.15) !important; /* blue with opacity for dark mode */
      --fc-highlight-color: #374151 !important; /* gray-700 */
      --fc-list-event-hover-bg-color: #374151 !important; /* gray-700 */
      --fc-neutral-bg-color: #1f2937 !important; /* dark mode background */
      --fc-neutral-text-color: #f3f4f6 !important; /* dark mode text */
      background-color: #1f2937 !important; /* Force dark background */
      color: #f3f4f6 !important; /* Force light text */
    }
    
    :deep(.fc .fc-button) {
      font-weight: 500;
      border-radius: 0.5rem;
      padding: 0.5rem 1rem;
      transition: all 0.2s ease;
      border: 1px solid #3B82F6 !important;
      font-size: 0.875rem;
      box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      background-color: #3B82F6 !important;
      color: #fff !important;
    }
    
    :deep(.fc .fc-button:hover) {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(59, 130, 246, 0.15);
    }
    
    :deep(.fc .fc-button:active) {
      transform: translateY(0);
      box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
    }
    
    :deep(.fc .fc-toolbar-title) {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1F2937; /* gray-800 */
    }

    /* Light mode calendar text styling */
    :deep(.fc) {
      color: #374151; /* gray-700 */
    }

    :deep(.fc-daygrid-day-number) {
      color: #374151; /* gray-700 */
      font-weight: 500;
    }

    :deep(.fc-col-header-cell-cushion) {
      color: #6b7280; /* gray-500 */
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
    }

    :deep(.fc-daygrid-day-top) {
      color: #374151; /* gray-700 */
    }

    /* Dark mode calendar text styling - More specific selectors with !important */
    .dark :deep(.fc) {
      color: #f3f4f6 !important; /* gray-100 */
    }

    .dark :deep(.fc-daygrid-day-number) {
      color: #f3f4f6 !important; /* gray-100 */
      font-weight: 500 !important;
    }

    .dark :deep(.fc-col-header-cell-cushion) {
      color: #d1d5db !important; /* gray-300 */
      font-weight: 600 !important;
      text-transform: uppercase;
      font-size: 0.75rem;
    }

    .dark :deep(.fc-daygrid-day-top) {
      color: #f3f4f6 !important; /* gray-100 */
    }

    .dark :deep(.fc .fc-toolbar-title) {
      color: #f3f4f6 !important; /* gray-100 */
    }

    .dark :deep(.fc-col-header-cell) {
      color: #d1d5db !important; /* gray-300 */
    }

    .dark :deep(.fc-daygrid-day-frame) {
      color: #f3f4f6 !important; /* gray-100 */
    }

    .dark :deep(.fc-daygrid-day) {
      color: #f3f4f6 !important; /* gray-100 */
    }

    .dark :deep(.fc-button-primary) {
      background-color: #3B82F6 !important;
      border-color: #3B82F6 !important;
      color: #ffffff !important;
    }

    .dark :deep(.fc-button-primary:not(:disabled):active),
    .dark :deep(.fc-button-primary:not(:disabled).fc-button-active) {
      background-color: #1D4ED8 !important;
      border-color: #1D4ED8 !important;
    }

    /* Additional dark mode fixes for calendar borders and cells */
    .dark :deep(.fc-theme-standard td, .fc-theme-standard th) {
      border-color: #4b5563 !important; /* gray-600 */
    }

    .dark :deep(.fc-scrollgrid) {
      border-color: #4b5563 !important; /* gray-600 */
    }

    .dark :deep(.fc-daygrid-day) {
      background-color: transparent !important;
      color: #f3f4f6 !important; /* gray-100 */
    }

    .dark :deep(.fc-daygrid-day:hover) {
      background-color: rgba(59, 130, 246, 0.1) !important; /* subtle blue hover */
    }

    /* More specific selectors for all calendar text elements */
    .dark :deep(.fc-daygrid-day-number),
    .dark :deep(.fc-daygrid-day a),
    .dark :deep(.fc-col-header-cell a),
    .dark :deep(.fc-daygrid-day-top a) {
      color: #f3f4f6 !important; /* gray-100 */
      text-decoration: none !important;
    }

    /* Ensure all borders in dark mode are visible */
    .dark :deep(.fc-scrollgrid-section table),
    .dark :deep(.fc-scrollgrid-section tbody),
    .dark :deep(.fc-scrollgrid-section tr),
    .dark :deep(.fc-scrollgrid-section td),
    .dark :deep(.fc-scrollgrid-section th) {
      border-color: #4b5563 !important; /* gray-600 */
    }

    /* Fix event text contrast in dark mode */
    .dark :deep(.fc-event) {
      background-color: #3B82F6 !important;
      border-color: #3B82F6 !important;
      color: #ffffff !important;
    }

    .dark :deep(.fc-event-title) {
      color: #ffffff !important;
    }

    .dark :deep(.fc-event-time) {
      color: #ffffff !important;
    }

    /* Dark mode past event styling */
    .dark :deep(.fc-past-event) {
      background: rgba(59, 130, 246, 0.3) !important; /* More transparent in dark mode */
      color: rgba(255, 255, 255, 0.7) !important; /* More transparent text in dark mode */
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.05) !important; /* Very light shadow */
    }

    .dark :deep(.fc-past-event:hover) {
      background: rgba(37, 99, 235, 0.4) !important; /* Slightly more opaque on hover */
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.1) !important; /* Lighter hover shadow */
    }

    /* Dark mode cancelled event styling */
    .dark :deep(.fc-cancelled-event) {
      background: rgba(239, 68, 68, 0.3) !important; /* More transparent red in dark mode */
      color: rgba(255, 255, 255, 0.8) !important; /* More transparent text in dark mode */
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08) !important; /* Very light red shadow */
      text-decoration: line-through !important; /* Strike-through to indicate cancellation */
    }

    .dark :deep(.fc-cancelled-event:hover) {
      background: rgba(220, 38, 38, 0.4) !important; /* Slightly more opaque on hover */
      box-shadow: 0 4px 16px rgba(220, 38, 38, 0.15) !important; /* Lighter hover shadow */
    }

    /* Force override for stubborn FullCalendar default styles */
    .dark :deep(.fc-theme-standard .fc-scrollgrid) {
      border-color: #4b5563 !important;
      background-color: #1f2937 !important;
    }

    .dark :deep(.fc-theme-standard .fc-scrollgrid td) {
      border-color: #4b5563 !important;
      background-color: transparent !important;
    }

    .dark :deep(.fc-theme-standard .fc-scrollgrid th) {
      border-color: #4b5563 !important;
      background-color: #374151 !important;
    }

    /* Target the specific day number elements more aggressively */
    .dark :deep(.fc-daygrid-day-number),
    .dark :deep(.fc-daygrid-day .fc-daygrid-day-number) {
      color: #f3f4f6 !important;
      opacity: 1 !important;
      font-weight: 500 !important;
    }

    /* Ensure header text is visible */
    .dark :deep(.fc-col-header-cell),
    .dark :deep(.fc-col-header-cell .fc-col-header-cell-cushion) {
      color: #d1d5db !important;
      background-color: #374151 !important;
    }
    
    :deep(.fc .fc-daygrid-day.fc-day-today) {
      background-color: var(--fc-today-bg-color);
    }

    /* Enhanced today highlighting for dark mode */
    .dark :deep(.fc .fc-daygrid-day.fc-day-today) {
      background-color: rgba(59, 130, 246, 0.15) !important; /* blue with opacity */
      border: 1px solid rgba(59, 130, 246, 0.3) !important; /* subtle blue border */
    }

    .dark :deep(.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number) {
      color: #93c5fd !important; /* blue-300 for today's number */
      font-weight: 600 !important;
    }
    
    :deep(.fc .fc-col-header-cell-cushion) {
      font-weight: 600;
    }
    
    :deep(.fc-event) {
      border-radius: 0.25rem;
      font-size: 0.875rem;
    }
    
    :deep(.fc .fc-daygrid-day-number) {
      font-weight: 500;
      color: #4B5563; /* gray-600 */
    }

    :deep(.fc-custom-event) {
      background: #3B82F6;
      color: #fff;
      border-radius: 0.5rem;
      padding: 0.25rem 0.5rem;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
      cursor: pointer;
      transition: box-shadow 0.2s, background 0.2s;
      font-size: 0.95em;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      width: 100%;
      max-width: 100%;
      overflow: hidden;
    }
    
    :deep(.fc-custom-event .event-title-truncated) {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      width: 100%;
      max-width: 100%;
      display: block;
    }
    :deep(.fc-custom-event:hover) {
      background: #2563EB;
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.25);
    }

    /* Past event styling with transparency */
    :deep(.fc-past-event) {
      background: rgba(59, 130, 246, 0.4) !important; /* More transparent blue */
      color: rgba(255, 255, 255, 0.8) !important; /* Slightly transparent text */
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08) !important; /* Lighter shadow */
    }

    :deep(.fc-past-event:hover) {
      background: rgba(37, 99, 235, 0.5) !important; /* Slightly more opaque on hover */
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.15) !important; /* Lighter hover shadow */
    }

    /* Cancelled event styling with light red */
    :deep(.fc-cancelled-event) {
      background: rgba(239, 68, 68, 0.4) !important; /* Light red with transparency */
      color: rgba(255, 255, 255, 0.9) !important; /* Slightly transparent white text */
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1) !important; /* Light red shadow */
      text-decoration: line-through !important; /* Strike-through to indicate cancellation */
    }

    :deep(.fc-cancelled-event:hover) {
      background: rgba(220, 38, 38, 0.5) !important; /* Slightly more opaque red on hover */
      box-shadow: 0 4px 16px rgba(220, 38, 38, 0.2) !important; /* More visible hover shadow */
    }

    /* Calendar selection styling for admins */
    :deep(.fc-highlight) {
      background: rgba(59, 130, 246, 0.12) !important;
      border: 2px solid #3B82F6 !important;
      border-radius: 6px !important;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Admin interactive calendar styling */
    .calendar-admin :deep(.fc-daygrid-day) {
      position: relative;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .calendar-admin :deep(.fc-daygrid-day:hover) {
      background-color: rgba(59, 130, 246, 0.06);
      cursor: pointer;
      transform: translateY(-1px);
    }

    /* Floating label animations for modal inputs */
    .floating-label {
      transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
      transform-origin: left top;
      will-change: transform, color, background-color;
    }

    .floating-label-active {
      transform: translateY(-1.25rem) translateX(-0.5rem) scale(0.75) !important;
      color: #3B82F6 !important;
      background-color: rgba(255, 255, 255, 0.9) !important;
      padding: 0.125rem 0.25rem !important;
      border-radius: 0.25rem !important;
    }

    /* Dark mode floating label background */
    .dark .floating-label-active {
      background-color: rgba(31, 41, 55, 0.9) !important;
      color: #60A5FA !important;
    }

    /* Peer-based animations for smooth transitions */
    .peer:focus ~ .floating-label {
      transform: translateY(-1.25rem) translateX(-0.5rem) scale(0.75);
      color: #3B82F6;
      background-color: rgba(255, 255, 255, 0.9);
      padding: 0.125rem 0.25rem;
      border-radius: 0.25rem;
    }

    .dark .peer:focus ~ .floating-label {
      background-color: rgba(31, 41, 55, 0.9);
      color: #60A5FA;
    }

    .peer:not(:placeholder-shown) ~ .floating-label {
      transform: translateY(-1.25rem) translateX(-0.5rem) scale(0.75);
      color: #3B82F6;
      background-color: rgba(255, 255, 255, 0.9);
      padding: 0.125rem 0.25rem;
      border-radius: 0.25rem;
    }

    .dark .peer:not(:placeholder-shown) ~ .floating-label {
      background-color: rgba(31, 41, 55, 0.9);
      color: #60A5FA;
    }

    /* Responsive adjustments */
    @media (min-width: 640px) {
      .floating-label-active {
        transform: translateY(-1.375rem) translateX(-0.5rem) scale(0.75) !important;
      }
      
      .peer:focus ~ .floating-label,
      .peer:not(:placeholder-shown) ~ .floating-label {
        transform: translateY(-1.375rem) translateX(-0.5rem) scale(0.75);
      }
    }

    /* Subtle indicator for admin clickable dates */
    .calendar-admin :deep(.fc-daygrid-day::before) {
      content: '';
      position: absolute;
      top: 4px;
      right: 4px;
      width: 3px;
      height: 3px;
      background-color: #3B82F6;
      border-radius: 50%;
      opacity: 0.4;
      transition: opacity 0.2s ease;
    }

    .calendar-admin :deep(.fc-daygrid-day:hover::before) {
      opacity: 0.8;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      :deep(.fc .fc-toolbar) {
        flex-direction: column;
        gap: 0.75rem;
      }
      
      :deep(.fc .fc-toolbar-title) {
        font-size: 1rem;
      }
    }
    .break-words {
      word-break: break-word;
    }
    .overflow-wrap-anywhere {
      overflow-wrap: anywhere;
    }
    </style>
<script>
import { ref, reactive, onMounted, computed, watch, onUnmounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import FileUploadComponent from './FileUploadComponent.vue';
import TodayUpcomingEvents from './TodayUpcomingEvents.vue';
import EventHistoryModal from './EventHistoryModal.vue';
import EventStatistics from './EventStatistics.vue';
import ConfirmationModal from './ConfirmationModal.vue';
import StatusBanner from './StatusBanner.vue';
import axios from 'axios';
import dayjs from 'dayjs';

export default {
  components: {
    FullCalendar,
    FileUploadComponent,
    TodayUpcomingEvents,
    EventHistoryModal,
    EventStatistics,
    ConfirmationModal,
    StatusBanner
  },
  
  props: {
    initialEvents: Array,
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  
  setup(props) {
    const events = ref(props.initialEvents || []);
    const displayedEvents = ref([]);
    
    // Status banner state
    const showStatusBanner = ref(false);
    const statusMessage = ref('');
    const statusType = ref('success');
    
    // Calendar refs and navigation
    const fullCalendar = ref(null);
    const selectedMonth = ref(new Date().getMonth());
    const selectedYear = ref(new Date().getFullYear());
    
    // Month names for the dropdown
    const months = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    // Generate year options (past years up to current year only)
    const yearOptions = computed(() => {
      const currentYear = new Date().getFullYear();
      const years = [];
      // Show years from 10 years ago up to current year only
      for (let year = currentYear - 10; year <= currentYear; year++) {
        years.push(year);
      }
      return years.reverse(); // Show newest years first
    });
  
    const extractedData = ref(null);
    
    const isEditing = ref(false);
    const currentEditId = ref(null);
  // Track whether we came from viewing an event so we can return there on cancel
  const wasViewingBeforeEdit = ref(false);
    const checkEventsTimer = ref(null);
    const showPastEventsModal = ref(false);
    
    // For event details modal (non-admin users)
    const showEventDetailsModal = ref(false);
    const selectedEvent = ref({});
    
    // View toggle state
    const currentView = ref('calendar');
    
    // Confirmation modals
    const showDeleteConfirmation = ref(false);
    const showCancelConfirmation = ref(false);
    const showPastDateConfirmation = ref(false);
    const eventToDelete = ref(null);
    const eventToCancel = ref(null);
    const deleteFromPastEvents = ref(false);
    const pastDateCallback = ref(null);
    const pastDateMessage = ref('');
    
    // First-time user guidance
    const showFirstClickTooltip = ref(false);
    const hasSeenTooltip = ref(false);
    const pendingClickInfo = ref(null);
    const pendingSelectInfo = ref(null);
    
    const eventForm = reactive({
      title: '',
      date: '',       // Start date
      end_date: '',   // New field for end date
      start_time: '',
      end_time: '',
      description: '',
      location: '',
      organization: ''
    });
    
    const formErrors = reactive({
      title: '',
      start_date: '',
      end_date: '',
      start_time: '',
      end_time: '',
      description: '',
      location: '',
      organization: ''
    });
    
    // Clear form errors
    const clearFormErrors = () => {
      Object.keys(formErrors).forEach(key => {
        formErrors[key] = '';
      });
    };

    // Prevent background scrolling when any modal is open
    const isModalOpen = computed(() => {
      // Modal is considered open when any of these are truthy:
      // - extractedData (create modal)
      // - isEditing (edit modal) 
      // - showEventDetailsModal (view details modal)
      // - showDeleteConfirmation (delete confirmation modal)
      // - showCancelConfirmation (cancel event confirmation modal)
      // - showPastDateConfirmation (past date warning modal)
      return !!(extractedData.value || isEditing.value || showEventDetailsModal.value || 
                showDeleteConfirmation.value || showCancelConfirmation.value || showPastDateConfirmation.value);
    });

    // Strong body lock: save scroll position, set body fixed to prevent background scroll/overscroll
    let _savedBodyScroll = null;
    const lockBodyScroll = () => {
      try {
        // Save current scroll position
        _savedBodyScroll = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
        // Set fixed positioning to prevent scroll (keeps visual position)
        document.body.style.position = 'fixed';
        document.body.style.top = `-${_savedBodyScroll}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';
        // Improve mobile behavior
        document.documentElement.style.touchAction = 'none';
        document.documentElement.style.overscrollBehavior = 'none';
      } catch (e) {
        // ignore server-side or non-DOM environments
      }
    };

    const unlockBodyScroll = () => {
      try {
        // Restore styles
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.body.style.overflow = '';
        document.documentElement.style.touchAction = '';
        document.documentElement.style.overscrollBehavior = '';

        // Restore scroll position
        if (_savedBodyScroll !== null) {
          window.scrollTo(0, _savedBodyScroll);
          _savedBodyScroll = null;
        }
      } catch (e) {
        // ignore
      }
    };

    // Watch modal state and toggle body overflow
    watch(isModalOpen, (open) => {
      if (open) lockBodyScroll(); else unlockBodyScroll();
    }, { immediate: true });

    // Ensure body scroll is restored if component unmounts while modal is open
    onUnmounted(() => {
      unlockBodyScroll();
    });
    
    // Filter out expired and cancelled events for display
    const filterExpiredEvents = () => {
      // Show all events including cancelled ones - just filter by styling instead
      displayedEvents.value = events.value.filter(event => {
        // Keep all events (past, present, future, and cancelled)
        return true;
      });
    };
    
    // Apply initial filter
    watch(events, () => {
      filterExpiredEvents();
    }, { immediate: true });
    
    // Check if user has seen the tooltip before
    onMounted(() => {
      // Check localStorage for tooltip preference
      const tooltipSeen = localStorage.getItem('calendar-tooltip-seen');
      const cooldownExpiry = localStorage.getItem('calendar-tooltip-cooldown');
      
      if (tooltipSeen === 'true') {
        hasSeenTooltip.value = true;
      } else if (cooldownExpiry) {
        // Check if cooldown period has expired
        const cooldownTime = parseInt(cooldownExpiry);
        const now = new Date().getTime();
        if (now < cooldownTime) {
          hasSeenTooltip.value = true; // Still in cooldown period
        }
      }
      
      checkEventsTimer.value = setInterval(() => {
        filterExpiredEvents();
      }, 60000); // Check every minute
      
      // Initial events loading
      if (!props.initialEvents) {
        axios.get('/api/events')
          .then(response => {
            events.value = response.data;
            filterExpiredEvents();
          })
          .catch(error => {
            console.error('Error fetching events:', error);
          });
      } else {
        filterExpiredEvents();
      }
    });
    
    // Clean up timer on component unmount
    onUnmounted(() => {
      if (checkEventsTimer.value) {
        clearInterval(checkEventsTimer.value);
      }
    });
    
    const calendarOptions = reactive({
      plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: computed(() => {
        return displayedEvents.value.map(event => ({
          id: event.id,
          title: event.title,
          start: event.start_date,
          end: event.end_date,
          description: event.description
        }));
      }),
      eventContent: function(arg) {
        // Custom rendering for calendar events with responsive text truncation
        const title = arg.event.title;
        const today = dayjs();
        
        // Get the full event object to check status
        const eventId = parseInt(arg.event.id);
        const fullEvent = events.value.find(e => e.id === eventId);
        
        // Check event status and date - use the original event's start_date for more reliable parsing
        const isCancelledEvent = fullEvent?.status === 'cancelled';
        const eventDate = fullEvent ? dayjs(fullEvent.start_date) : dayjs(arg.event.start);
        const isPastEvent = eventDate.isBefore(today, 'day');
        
        // Determine truncation length based on viewport width
        let maxLength = 20; // Default for desktop
        if (window.innerWidth < 640) {
          maxLength = 8; // Very small screens
        } else if (window.innerWidth < 768) {
          maxLength = 12; // Mobile screens
        } else if (window.innerWidth < 1024) {
          maxLength = 16; // Tablet screens
        }
        
        const truncatedTitle = title.length > maxLength ? title.substring(0, maxLength) + '...' : title;
        
        // Add conditional classes for different event types
        let eventClass = 'fc-custom-event';
        if (isCancelledEvent) {
          eventClass += ' fc-cancelled-event';
        } else if (isPastEvent) {
          eventClass += ' fc-past-event';
        }
        
        return {
          html: `
            <div class="${eventClass}">
              <span class="font-bold event-title-truncated" title="${title}">${truncatedTitle}</span>
              <span class="block text-xs">${dayjs(arg.event.start).format('h:mm A')}</span>
            </div>
          `
        };
      },
      eventClick: info => {
        const eventId = parseInt(info.event.id);
        const event = events.value.find(e => e.id === eventId);
        if (event) {
          viewEventDetails(event); // Always show modal
        }
      },
      editable: false, // Disable dragging events
      selectable: props.isAdmin, // Enable date selection for admins only
      selectMirror: true, // Show selection preview
      selectAllow: () => props.isAdmin, // Additional check for selection
      dateClick: handleDateClick,
      select: handleDateSelect,
      datesSet: updateDatePickerFromCalendar, // Keep date picker in sync
      // eventDrop: handleEventDrop (no longer needed)
    });
    
    const upcomingEvents = computed(() => {
      const now = new Date();
      return displayedEvents.value
        .filter(event => new Date(event.start_date) >= now)
        .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
        .slice(0, 5);
    });

    function handleEventDeleted(eventId) {
      events.value = events.value.filter(event => event.id !== eventId);
      filterExpiredEvents();
    }

    function handleDeletePastEvent(eventId) {
      const event = events.value.find(e => e.id === eventId);
      if (event) {
        eventToDelete.value = event;
        deleteFromPastEvents.value = true;
        // If the event details modal is open, close it before showing confirmation
        if (showEventDetailsModal.value) {
          showEventDetailsModal.value = false;
        }
        showDeleteConfirmation.value = true;
        // Close the past events modal while showing confirmation
        showPastEventsModal.value = false;
      }
    }
    
    function saveEvent() {
      if (!props.isAdmin) return; // Safety check
      
      // Clear previous errors
      clearFormErrors();
      
      // Client-side validation
      let hasErrors = false;
      
      if (!eventForm.title.trim()) {
        formErrors.title = 'Event title is required';
        hasErrors = true;
      }
      
      if (!eventForm.date) {
        formErrors.start_date = 'Start date is required';
        hasErrors = true;
      }
      
      // Stop if there are validation errors
      if (hasErrors) {
        return;
      }
      
      // Make sure end_date has a value, defaulting to start date if not provided
      const endDateStr = eventForm.end_date || eventForm.date;
      
      const startDate = `${eventForm.date}T${eventForm.start_time || '00:00'}:00`;
      const endDate = `${endDateStr}T${eventForm.end_time || '23:59'}:00`;
      
      axios.post('/api/events', {
        title: eventForm.title,
        start_date: startDate,
        end_date: endDate, // Always include end_date
        description: eventForm.description,
        location: eventForm.location,
        organization: eventForm.organization
      })
        .then(response => {
          // Add the new event to the list
          events.value.push(response.data);
          filterExpiredEvents();
          
          // Dispatch event for calendar badge update
          if (typeof window !== 'undefined') {
            window.dispatchEvent(new Event('calendar-event-added'));
          }
          
          // Reset the form
          resetForm();
          statusMessage.value = 'Event saved successfully!';
          showStatusBanner.value = true;
          statusType.value = 'success';
          
          // Auto-hide after 5 seconds
          setTimeout(() => {
            showStatusBanner.value = false;
          }, 5000);
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
          } else if (error.response && error.response.status === 422) {
            // Handle validation errors
            clearFormErrors();
            if (error.response.data && error.response.data.errors) {
              // Laravel validation errors format
              Object.keys(error.response.data.errors).forEach(field => {
                if (formErrors.hasOwnProperty(field)) {
                  formErrors[field] = error.response.data.errors[field][0]; // Get first error message
                }
              });
            } else if (error.response.data && error.response.data.message) {
              // Single error message
              statusMessage.value = error.response.data.message;
              showStatusBanner.value = true;
              statusType.value = 'error';
              setTimeout(() => {
                showStatusBanner.value = false;
              }, 5000);
            }
          } else {
            // Handle other errors without console logging
            statusMessage.value = 'Failed to save event. Please check your connection and try again.';
            showStatusBanner.value = true;
            statusType.value = 'error';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
              showStatusBanner.value = false;
            }, 5000);
          }
        });
    }
    
    function editEvent(event) {
      if (!props.isAdmin) return; // Safety check
      // Track if the user was viewing the event details so we can reopen on cancel
      wasViewingBeforeEdit.value = !!showEventDetailsModal.value;
      // Close the event details modal if open
      if (showEventDetailsModal.value) closeEventDetailsModal();
      // Switch to edit mode
      isEditing.value = true;
      currentEditId.value = event.id;
      extractedData.value = null;
      // Parse the date and times from the event
      const eventDate = dayjs(event.start_date);
      const eventEndDate = event.end_date ? dayjs(event.end_date) : null;
      // Fill the form with the event data
      eventForm.title = event.title;
      eventForm.date = eventDate.format('YYYY-MM-DD');
      eventForm.start_time = eventDate.format('HH:mm');
      if (eventEndDate) {
        eventForm.end_date = eventEndDate.format('YYYY-MM-DD'); // Default to same day
        eventForm.end_time = eventEndDate.format('HH:mm');
      } else {
        eventForm.end_date = eventDate.format('YYYY-MM-DD');
        eventForm.end_time = '';
      }
      eventForm.description = event.description || '';
      eventForm.location = event.location || '';
      eventForm.organization = event.organization || '';
    }
    
    function updateEvent() {
      if (!props.isAdmin) return; // Safety check
      
      // Clear previous errors
      clearFormErrors();
      
      // Client-side validation
      let hasErrors = false;
      
      if (!eventForm.title.trim()) {
        formErrors.title = 'Event title is required';
        hasErrors = true;
      }
      
      if (!eventForm.date) {
        formErrors.start_date = 'Start date is required';
        hasErrors = true;
      }
      
      // Stop if there are validation errors
      if (hasErrors) {
        return;
      }
      
      // Make sure end_date has a value, defaulting to start date if not provided
      const endDateStr = eventForm.end_date || eventForm.date;
      
      const startDate = `${eventForm.date}T${eventForm.start_time || '00:00'}:00`;
      const endDate = `${endDateStr}T${eventForm.end_time || '23:59'}:00`;
      
      axios.put(`/api/events/${currentEditId.value}`, {
        title: eventForm.title,
        start_date: startDate,
        end_date: endDate, // Always include end_date
        description: eventForm.description,
        location: eventForm.location,
        organization: eventForm.organization
      })
        .then(response => {
          // Update the event in our local state
          const index = events.value.findIndex(e => e.id === currentEditId.value);
          if (index !== -1) {
            events.value[index] = response.data;
            filterExpiredEvents();
          }
          
                      // Reset the form and exit edit mode
            resetForm();
            statusMessage.value = 'Event updated successfully!';
            showStatusBanner.value = true;
            statusType.value = 'success';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
              showStatusBanner.value = false;
            }, 5000);
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
          } else if (error.response && error.response.status === 422) {
            // Handle validation errors
            clearFormErrors();
            if (error.response.data && error.response.data.errors) {
              // Laravel validation errors format
              Object.keys(error.response.data.errors).forEach(field => {
                if (formErrors.hasOwnProperty(field)) {
                  formErrors[field] = error.response.data.errors[field][0]; // Get first error message
                }
              });
            } else if (error.response.data && error.response.data.message) {
              // Single error message
              statusMessage.value = error.response.data.message;
              showStatusBanner.value = true;
              statusType.value = 'error';
              setTimeout(() => {
                showStatusBanner.value = false;
              }, 5000);
            }
          } else {
            // Handle other errors without console logging
            statusMessage.value = 'Failed to update event. Please check your connection and try again.';
            showStatusBanner.value = true;
            statusType.value = 'error';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
              showStatusBanner.value = false;
              }, 5000);
            }
        });
    }
    
    function cancelEdit() {
      // If editing was started from viewing the event, after cancel we should return to view modal
      const reopenView = wasViewingBeforeEdit.value && selectedEvent.value;
      resetForm();
      clearFormErrors();
      wasViewingBeforeEdit.value = false;
      if (reopenView) {
        selectedEvent.value = events.value.find(e => e.id === currentEditId.value) || selectedEvent.value;
        showEventDetailsModal.value = true;
      }
    }
    
    function resetForm() {
      extractedData.value = null;
      isEditing.value = false;
      currentEditId.value = null;
      eventForm.title = '';
      eventForm.date = '';
      eventForm.end_date = ''; // Reset end date
      eventForm.start_time = '';
      eventForm.end_time = '';
      eventForm.description = '';
      eventForm.location = '';
      eventForm.organization = '';
    }
    
    function deleteEvent(eventId) {
      if (!props.isAdmin) return; // Safety check
      
      const event = events.value.find(e => e.id === eventId);
      if (event) {
        eventToDelete.value = event;
        // If the event details modal is open, close it before showing confirmation
        if (showEventDetailsModal.value) {
          showEventDetailsModal.value = false;
        }
        showDeleteConfirmation.value = true;
      }
    }
    
    function confirmDeleteEvent() {
      if (!eventToDelete.value) return;
      
      axios.delete(`/api/events/${eventToDelete.value.id}`)
        .then(() => {
          // Remove the event from the list
          events.value = events.value.filter(event => event.id !== eventToDelete.value.id);
          filterExpiredEvents();
          
          // Dispatch event for calendar badge update
          if (typeof window !== 'undefined') {
            window.dispatchEvent(new Event('calendar-event-deleted'));
          }
          
          // If we were editing this event, reset the form
          if (currentEditId.value === eventToDelete.value.id) {
            resetForm();
          }
          // Close the event details modal if open
          closeEventDetailsModal();
          
          // If deletion was from past events modal, reopen it
          if (deleteFromPastEvents.value) {
            showPastEventsModal.value = true;
            deleteFromPastEvents.value = false;
          }
          
          // Show success notification
          statusMessage.value = 'Event deleted successfully!';
          showStatusBanner.value = true;
          statusType.value = 'success';
          
          // Auto-hide after 5 seconds
          setTimeout(() => {
            showStatusBanner.value = false;
          }, 5000);
          
          // Reset confirmation modal
          showDeleteConfirmation.value = false;
          eventToDelete.value = null;
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
                      } else {
              console.error('Error deleting event:', error);
              statusMessage.value = 'Failed to delete event. Please try again.';
              showStatusBanner.value = true;
              statusType.value = 'error';
              
              // Auto-hide after 5 seconds
              setTimeout(() => {
                showStatusBanner.value = false;
              }, 5000);
            }
          showDeleteConfirmation.value = false;
          eventToDelete.value = null;
          deleteFromPastEvents.value = false;
        });
    }
    
    function cancelDeleteEvent() {
      // If deletion was from past events modal, reopen it
      if (deleteFromPastEvents.value) {
        showPastEventsModal.value = true;
        deleteFromPastEvents.value = false;
      }
      // Close delete confirmation and, if there was a selectedEvent, reopen the view details modal
      showDeleteConfirmation.value = false;
      const reopenedEvent = eventToDelete.value || selectedEvent.value;
      eventToDelete.value = null;
      if (reopenedEvent) {
        selectedEvent.value = reopenedEvent;
        showEventDetailsModal.value = true;
      }
    }
    
    function showPastDateWarning(message, callback) {
      pastDateMessage.value = message;
      pastDateCallback.value = callback;
      showPastDateConfirmation.value = true;
    }
    
    function confirmPastDate() {
      if (pastDateCallback.value) {
        pastDateCallback.value();
      }
      showPastDateConfirmation.value = false;
      pastDateCallback.value = null;
      pastDateMessage.value = '';
    }
    
    function cancelPastDate() {
      showPastDateConfirmation.value = false;
      pastDateCallback.value = null;
      pastDateMessage.value = '';
    }
    
    function cancelEvent(eventId) {
      if (!props.isAdmin) return; // Safety check
      
      const event = events.value.find(e => e.id === eventId);
      if (event) {
        eventToCancel.value = event;
        // If the event details modal is open, close it before showing confirmation
        if (showEventDetailsModal.value) {
          showEventDetailsModal.value = false;
        }
        showCancelConfirmation.value = true;
      }
    }
    
    function confirmCancelEvent() {
      if (!eventToCancel.value) return;
      
      axios.patch(`/api/events/${eventToCancel.value.id}/cancel`)
        .then(response => {
          // Update the event in our local state
          const index = events.value.findIndex(e => e.id === eventToCancel.value.id);
          if (index !== -1) {
            events.value[index] = response.data;
            filterExpiredEvents();
          }
          
          // Close the event details modal if open
          closeEventDetailsModal();
          
          // Dispatch event for calendar badge update
          if (typeof window !== 'undefined') {
            window.dispatchEvent(new Event('calendar-event-cancelled'));
          }
          
          // Reset confirmation modal
          showCancelConfirmation.value = false;
          eventToCancel.value = null;
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
                      } else {
              console.error('Error cancelling event:', error);
              statusMessage.value = 'Failed to cancel event. Please try again.';
              showStatusBanner.value = true;
              statusType.value = 'error';
              
              // Auto-hide after 5 seconds
              setTimeout(() => {
                showStatusBanner.value = false;
              }, 5000);
            }
          showCancelConfirmation.value = false;
          eventToCancel.value = null;
        });
    }
    
    function cancelCancelEvent() {
      // Close cancel confirmation and, if there was a selectedEvent, reopen the event details modal
      showCancelConfirmation.value = false;
      const reopenedEvent = eventToCancel.value || selectedEvent.value;
      eventToCancel.value = null;
      if (reopenedEvent) {
        selectedEvent.value = reopenedEvent;
        // Reopen view modal
        showEventDetailsModal.value = true;
      }
    }

    
        function closePastEventsModal() {
      showPastEventsModal.value = false;
    }
    
    function handleEventDrop(info) {
      if (!props.isAdmin) {
        info.revert();
        return;
      }
      
      const eventId = info.event.id;
      const newStartDate = info.event.start;
      const newEndDate = info.event.end || info.event.start; // Default to start date if no end date
      
      axios.put(`/api/events/${eventId}`, {
        start_date: newStartDate,
        end_date: newEndDate // Always include end_date
      })
        .then(() => {
          // Update the event in our local state
          const index = events.value.findIndex(e => e.id === parseInt(eventId));
          if (index !== -1) {
            events.value[index].start_date = newStartDate;
            events.value[index].end_date = newEndDate;
            filterExpiredEvents();
          }
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
          } else {
            console.error('Error updating event date:', error);
          }
          info.revert(); // Revert the drag if there was an error
        });
    }
    
    function formatDate(dateString, format) {
      return dayjs(dateString).format(format);
    }
    
    // Date navigation functions
    function navigateToDate() {
      if (fullCalendar.value) {
        const targetDate = new Date(selectedYear.value, selectedMonth.value, 1);
        fullCalendar.value.getApi().gotoDate(targetDate);
      }
    }
    
    function goToToday() {
      if (fullCalendar.value) {
        const today = new Date();
        selectedMonth.value = today.getMonth();
        selectedYear.value = today.getFullYear();
        fullCalendar.value.getApi().today();
      }
    }
    
    function updateDatePickerFromCalendar() {
      if (fullCalendar.value) {
        const currentDate = fullCalendar.value.getApi().getDate();
        selectedMonth.value = currentDate.getMonth();
        selectedYear.value = currentDate.getFullYear();
      }
    }
    
    // Functions for non-admin event details modal
    function viewEventDetails(event) {
      selectedEvent.value = event;
      showEventDetailsModal.value = true;
    }
    
    function closeEventDetailsModal() {
      showEventDetailsModal.value = false;
    }
    
    function createNewEvent() {
      isEditing.value = false;
      currentEditId.value = null;
      extractedData.value = {};
      
      // Set default values for the form
      const today = dayjs().format('YYYY-MM-DD');
      eventForm.title = '';
      eventForm.date = today;
      eventForm.end_date = today; // Default end date to same day
      eventForm.start_time = '';
      eventForm.end_time = '';
      eventForm.description = '';
      eventForm.location = '';
      eventForm.organization = '';
    }

    function createEventForDate(clickedDate, info) {
      // Set up new event with clicked date
      isEditing.value = false;
      currentEditId.value = null;
      extractedData.value = {};
      
      eventForm.title = '';
      eventForm.date = clickedDate;
      eventForm.end_date = clickedDate;
      eventForm.start_time = '';
      eventForm.end_time = '';
      eventForm.description = '';
      eventForm.location = '';
      eventForm.organization = '';
      
      // Add smooth visual feedback with modern animation
      if (info.dayEl) {
        info.dayEl.style.transform = 'scale(1.05)';
        info.dayEl.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
        info.dayEl.style.transition = 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)';
        
        // Reset animation after modal opens
        setTimeout(() => {
          if (info.dayEl) {
            info.dayEl.style.transform = '';
            info.dayEl.style.backgroundColor = '';
            info.dayEl.style.transition = '';
          }
        }, 300);
      }
      
      console.log(`Event creation initiated for date: ${clickedDate}`);
    }
    
    function createEventForDateRange(startDate, endDate, info) {
      // Set up new event with selected date range
      isEditing.value = false;
      currentEditId.value = null;
      extractedData.value = {};
      
      eventForm.title = '';
      eventForm.date = startDate;
      eventForm.end_date = endDate;
      eventForm.start_time = '';
      eventForm.end_time = '';
      eventForm.description = '';
      eventForm.location = '';
      eventForm.organization = '';
      
      console.log(`Event creation initiated for date range: ${startDate} to ${endDate}`);
      
      // Clear the selection with smooth animation
      setTimeout(() => {
        if (info.view?.calendar) {
          info.view.calendar.unselect();
        }
      }, 100);
    }
    
    // Handle calendar date click (single day) with improved animations and error handling
    function handleDateClick(info) {
      if (!props.isAdmin) {
        console.warn('Date click attempted by non-admin user');
        return;
      }

      // Show first-time tooltip if user hasn't seen it
      if (!hasSeenTooltip.value) {
        // Store the pending click info for after tooltip dismissal
        pendingClickInfo.value = info;
        showFirstClickTooltip.value = true;
        return; // Don't proceed with event creation on first click
      }
      
      try {
        // Validate the date
        if (!info.date || !dayjs(info.date).isValid()) {
          throw new Error('Invalid date selected');
        }

        const clickedDate = dayjs(info.date).format('YYYY-MM-DD');
        
        // Prevent clicks on past dates (optional business rule)
        if (dayjs(clickedDate).isBefore(dayjs(), 'day')) {
          showPastDateWarning('You are creating an event for a past date. Continue?', () => {
            // Continue with event creation
            createEventForDate(clickedDate, info);
          });
          return;
        }
        
        // If not a past date, create event directly
        createEventForDate(clickedDate, info);
        
      } catch (error) {
        console.error('Error handling date click:', error);
        
        // User-friendly error message
        const errorMessage = error.message === 'Invalid date selected' 
          ? 'The selected date is invalid. Please try clicking on a different date.'
          : 'Unable to create event for the selected date. Please try again.';
          
        statusMessage.value = errorMessage;
        showStatusBanner.value = true;
        statusType.value = 'error';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          showStatusBanner.value = false;
        }, 5000);
        
        // Reset any visual changes on error
        if (info.dayEl) {
          info.dayEl.style.transform = '';
          info.dayEl.style.backgroundColor = '';
          info.dayEl.style.transition = '';
        }
      }
    }

    // Handle calendar date range selection (drag) with improved error handling
    function handleDateSelect(info) {
      if (!props.isAdmin) {
        console.warn('Date selection attempted by non-admin user');
        // Clear any selection
        if (info.view?.calendar) {
          info.view.calendar.unselect();
        }
        return;
      }
      
      // Show first-time tooltip if user hasn't seen it
      if (!hasSeenTooltip.value) {
        // Store the pending selection info for after tooltip dismissal
        pendingSelectInfo.value = info;
        showFirstClickTooltip.value = true;
        return; // Don't proceed with event creation on first drag
      }
      
      try {
        // Validate dates
        if (!info.start || !info.end || !dayjs(info.start).isValid() || !dayjs(info.end).isValid()) {
          throw new Error('Invalid date range selected');
        }

        const startDate = dayjs(info.start).format('YYYY-MM-DD');
        const endDate = dayjs(info.end).subtract(1, 'day').format('YYYY-MM-DD'); // FullCalendar end is exclusive
        
        // Validate date range
        if (dayjs(startDate).isAfter(dayjs(endDate))) {
          throw new Error('Invalid date range: start date is after end date');
        }
        
        // Check for reasonable date range (e.g., max 365 days)
        const daysDiff = dayjs(endDate).diff(dayjs(startDate), 'days');
        if (daysDiff > 365) {
          throw new Error('Date range too large. Please select a range of 365 days or less.');
        }
        
        // Warn for past dates
        if (dayjs(startDate).isBefore(dayjs(), 'day')) {
          showPastDateWarning(`You are creating an event starting in the past (${startDate}). Continue?`, () => {
            // Continue with event creation
            createEventForDateRange(startDate, endDate, info);
          });
          return;
        }
        
        // Set up new event with selected date range
        isEditing.value = false;
        currentEditId.value = null;
        extractedData.value = {};
        
        eventForm.title = '';
        eventForm.date = startDate;
        eventForm.end_date = endDate;
        eventForm.start_time = '';
        eventForm.end_time = '';
        eventForm.description = '';
        eventForm.location = '';
        eventForm.organization = '';
        
        console.log(`Event creation initiated for date range: ${startDate} to ${endDate}`);
        
        // Clear the selection with smooth animation
        setTimeout(() => {
          if (info.view?.calendar) {
            info.view.calendar.unselect();
          }
        }, 100);
        
      } catch (error) {
        console.error('Error handling date selection:', error);
        
        // User-friendly error messages
        let errorMessage;
        switch (error.message) {
          case 'Invalid date range selected':
            errorMessage = 'The selected date range is invalid. Please try selecting a different range.';
            break;
          case 'Invalid date range: start date is after end date':
            errorMessage = 'Invalid selection: the start date cannot be after the end date.';
            break;
          case 'Date range too large. Please select a range of 365 days or less.':
            errorMessage = error.message;
            break;
          default:
            errorMessage = 'Unable to create event for the selected date range. Please try again.';
        }
        
        statusMessage.value = errorMessage;
        showStatusBanner.value = true;
        statusType.value = 'error';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          showStatusBanner.value = false;
        }, 5000);
        
        // Clear selection on error
        if (info.view?.calendar) {
          info.view.calendar.unselect();
        }
      }
    }

    // Handle first-time tooltip interactions
    function dismissTooltip() {
      showFirstClickTooltip.value = false;
      hasSeenTooltip.value = true;
      
      // Set cooldown period in localStorage (1 day)
      const cooldownExpiry = new Date();
      cooldownExpiry.setDate(cooldownExpiry.getDate() + 1);
      localStorage.setItem('calendar-tooltip-cooldown', cooldownExpiry.getTime().toString());
      
      // Process any pending interactions after tooltip is dismissed
      processPendingInteraction();
    }

    function dismissTooltipPermanently() {
      showFirstClickTooltip.value = false;
      hasSeenTooltip.value = true;
      localStorage.setItem('calendar-tooltip-seen', 'true');
      
      // Process any pending interactions after tooltip is dismissed
      processPendingInteraction();
    }
    
    function processPendingInteraction() {
      // Process pending click info if exists
      if (pendingClickInfo.value) {
        const clickInfo = pendingClickInfo.value;
        pendingClickInfo.value = null;
        
        // Delay to ensure modal transition is complete
        setTimeout(() => {
          try {
            const clickedDate = dayjs(clickInfo.date).format('YYYY-MM-DD');
            
            if (dayjs(clickedDate).isBefore(dayjs(), 'day')) {
              showPastDateWarning('You are creating an event for a past date. Continue?', () => {
                createEventForDate(clickedDate, clickInfo);
              });
            } else {
              createEventForDate(clickedDate, clickInfo);
            }
          } catch (error) {
            console.error('Error processing pending click:', error);
          }
        }, 200);
      }
      
      // Process pending select info if exists
      if (pendingSelectInfo.value) {
        const selectInfo = pendingSelectInfo.value;
        pendingSelectInfo.value = null;
        
        // Delay to ensure modal transition is complete
        setTimeout(() => {
          try {
            const startDate = dayjs(selectInfo.start).format('YYYY-MM-DD');
            const endDate = dayjs(selectInfo.end).subtract(1, 'day').format('YYYY-MM-DD');
            
            if (dayjs(startDate).isBefore(dayjs(), 'day')) {
              showPastDateWarning(`You are creating an event starting in the past (${startDate}). Continue?`, () => {
                createEventForDateRange(startDate, endDate, selectInfo);
              });
            } else {
              createEventForDateRange(startDate, endDate, selectInfo);
            }
          } catch (error) {
            console.error('Error processing pending selection:', error);
            if (selectInfo.view?.calendar) {
              selectInfo.view.calendar.unselect();
            }
          }
        }, 200);
      }
    }

    function handleFileProcessed(data) {
  extractedData.value = data;
  
  // Populate the form with extracted data
  eventForm.title = data.title || '';
  eventForm.date = data.date || '';
  eventForm.end_date = data.end_date || data.date || '';
  eventForm.start_time = data.start_time || '';
  eventForm.end_time = data.end_time || '';
  eventForm.description = data.description || '';
  eventForm.location = data.location || '';
  eventForm.organization = data.organization || '';
}

function exportPastEventsCsv(pastEvents) {
  if (!pastEvents || !pastEvents.length) return;
  // Define the fields to export
  const fields = [
    'id', 'title', 'start_date', 'end_date', 'description'
  ];
  // Create CSV header
  const csvRows = [fields.join(',')];
  // Add event rows
  for (const event of pastEvents) {
    const row = fields.map(field => {
      let value = event[field] !== undefined ? event[field] : '';
      // Escape quotes and commas
      if (typeof value === 'string') {
        value = '"' + value.replace(/"/g, '""') + '"';
      }
      return value;
    });
    csvRows.push(row.join(','));
  }
  // Create CSV blob
  const csvContent = csvRows.join('\r\n');
  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  // Create a temporary link to trigger download
  const a = document.createElement('a');
  a.href = url;
  a.download = 'past_events.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

    return {
      showPastEventsModal,
      closePastEventsModal,
      handleEventDeleted,
      handleDeletePastEvent,
      events,
      displayedEvents,
      calendarOptions,
      extractedData,
      eventForm,
      formErrors,
      clearFormErrors,
      upcomingEvents,
      isEditing,
      isAdmin: props.isAdmin,
      showEventDetailsModal,
      selectedEvent,
      currentView,
      // Date picker navigation
      fullCalendar,
      selectedMonth,
      selectedYear,
      months,
      yearOptions,
      navigateToDate,
      goToToday,
      updateDatePickerFromCalendar,
      // Event functions
      saveEvent,
      editEvent,
      updateEvent,
      cancelEdit,
      deleteEvent,
      cancelEvent,
      formatDate,
      viewEventDetails,
      closeEventDetailsModal,
      createNewEvent,
      handleDateClick,
      handleDateSelect,
      handleFileProcessed,
      exportPastEventsCsv,
      // Confirmation modals
      showDeleteConfirmation,
      showCancelConfirmation,
      eventToDelete,
      eventToCancel,
      confirmDeleteEvent,
      cancelDeleteEvent,
      confirmCancelEvent,
      cancelCancelEvent,
      // First-time tooltip
      showFirstClickTooltip,
      dismissTooltip,
      dismissTooltipPermanently,
      pendingClickInfo,
      pendingSelectInfo,
      processPendingInteraction,
        // Status banner
        showStatusBanner,
        statusMessage,
        statusType,
        // Past date confirmation
        showPastDateConfirmation,
        pastDateMessage,
        showPastDateWarning,
        confirmPastDate,
        cancelPastDate
    };
  }
};


</script>

<style scoped>
/* Optimized 3D Flip Animation Styles */
.transform-style-preserve-3d {
  transform-style: preserve-3d;
}

.backface-hidden {
  backface-visibility: hidden;
}

.rotate-y-0 {
  transform: rotateY(0deg);
}

.rotate-y-180 {
  transform: rotateY(180deg);
}

/* Performance optimizations */
.duration-300 {
  transition-duration: 0.3s;
}

.ease-out {
  transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
}

/* Smooth transitions for view toggle */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.duration-700 {
  transition-duration: 700ms;
}

.ease-in-out {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom calendar styles */
.full-calendar-custom {
  font-family: inherit;
}

.full-calendar-custom .fc-toolbar {
  margin-bottom: 1rem;
}

.full-calendar-custom .fc-button {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: all 0.2s;
}

.full-calendar-custom .fc-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.full-calendar-custom .fc-button:disabled {
  opacity: 0.6;
  transform: none;
}

.full-calendar-custom .fc-event {
  border: none;
  border-radius: 0.375rem;
  padding: 0.25rem 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.full-calendar-custom .fc-event:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.full-calendar-custom .fc-daygrid-event {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-left: 4px solid #4f46e5;
}

.full-calendar-custom .fc-day-today {
  background-color: rgba(59, 130, 246, 0.1) !important;
}

.full-calendar-custom .fc-day-past {
  background-color: rgba(156, 163, 175, 0.05);
}

/* Loading animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Fade in animation for statistics */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn 0.3s ease-out;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .full-calendar-custom .fc-toolbar {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .full-calendar-custom .fc-toolbar-chunk {
    display: flex;
    justify-content: center;
  }
  
  .full-calendar-custom .fc-button {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
  }
}

/* Focus states for accessibility */
.focus\:ring-2:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.focus\:ring-blue-500:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Toggle button hover effects */
.hover\:bg-gray-100:hover {
  background-color: rgba(243, 244, 246, 1);
}

.hover\:text-gray-800:hover {
  color: rgba(31, 41, 55, 1);
}

/* Shadow effects */
.shadow-sm {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Ensure proper z-index for modals */
.z-50 {
  z-index: 50;
}

/* Modal transition styles */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.1s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  /* Ensure proper bottom spacing on mobile */
  .calendar-container {
    padding-bottom: 2rem;
    margin-bottom: 2rem;
  }
  
  /* Fix calendar overflow on mobile and optimize spacing */
  :deep(.fc) {
    font-size: 0.75rem;
  }
  
  /* Reduce calendar internal padding on mobile */
  :deep(.fc-header-toolbar) {
    padding: 0.5rem 0;
  }
  
  /* Optimize calendar table spacing */
  :deep(.fc-daygrid-day) {
    padding: 0.125rem;
  }
  
  :deep(.fc .fc-toolbar) {
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
  }
  
  :deep(.fc .fc-toolbar-chunk) {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  /* Make toolbar buttons smaller on mobile */
  :deep(.fc .fc-button) {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    min-width: auto;
  }
  
  /* Improve toolbar title on mobile */
  :deep(.fc .fc-toolbar-title) {
    font-size: 1rem;
    text-align: center;
    margin: 0.25rem 0;
  }
  
  /* Ensure events are properly visible */
  :deep(.fc-event) {
    font-size: 0.625rem;
    padding: 1px 2px;
    line-height: 1.2;
  }
  
  /* Better day number sizing */
  :deep(.fc-daygrid-day-number) {
    font-size: 0.75rem;
    padding: 0.125rem;
  }
  
  /* Improve column headers */
  :deep(.fc-col-header-cell-cushion) {
    font-size: 0.625rem;
    padding: 0.25rem 0.125rem;
  }
  
  /* Fix modal positioning on mobile */
  .modal-container {
    padding: 0.75rem;
    max-height: calc(100vh - 1.5rem);
    overflow-y: auto;
  }
  
  /* Fix statistics view container on mobile */
  .statistics-view-container {
    min-height: auto !important;
    height: auto !important;
    overflow: visible !important;
  }
  
  /* Improve calendar cell sizing */
  :deep(.fc-daygrid-day) {
    min-height: 2rem;
  }
  
  /* Better spacing for day content */
  :deep(.fc-daygrid-day-frame) {
    min-height: 2rem;
  }
}

/* Additional mobile fixes */
@media (max-width: 640px) {
  /* Extra small screens - remove side margins completely */
  .main-content {
    padding-bottom: 3rem;
  }
  
  /* Ensure flip container doesn't constrain height */
  .flip-container {
    height: auto !important;
    min-height: auto !important;
  }
  
  /* Remove any remaining side padding on very small screens */
  :deep(.fc-toolbar) {
    padding: 0.25rem;
  }
  
  /* Optimize day cell size for mobile */
  :deep(.fc-daygrid-day-frame) {
    padding: 0.125rem;
  }
  
  /* Further reduce calendar font sizes */
  :deep(.fc) {
    font-size: 0.625rem;
  }
  
  :deep(.fc .fc-button) {
    padding: 0.25rem 0.5rem;
    font-size: 0.625rem;
    border-radius: 0.375rem;
  }
  
  :deep(.fc .fc-toolbar-title) {
    font-size: 0.875rem;
  }
  
  /* Optimize day cells for small screens */
  :deep(.fc-daygrid-day) {
    min-height: 1.75rem;
  }
  
  :deep(.fc-daygrid-day-number) {
    font-size: 0.625rem;
    padding: 0.0625rem;
  }
  
  :deep(.fc-col-header-cell-cushion) {
    font-size: 0.5rem;
    padding: 0.125rem 0.0625rem;
  }
  
  /* Make events even smaller */
  :deep(.fc-event) {
    font-size: 0.5rem;
    padding: 0 1px;
    line-height: 1.1;
  }
  
  /* Better event title truncation on small screens */
  :deep(.fc-custom-event .event-title-truncated) {
    font-size: 0.5rem;
    max-width: 100%;
  }
  
  /* Adjust calendar wrapper padding and ensure full width usage */
  .calendar-wrapper {
    padding: 0.25rem;
    width: 100%;
  }
  
  /* Ensure calendar table uses full available width */
  :deep(.fc-scrollgrid) {
    width: 100% !important;
  }
  
  /* Optimize calendar cells for better space usage */
  :deep(.fc-daygrid-day-top) {
    padding: 0.125rem;
  }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
  :deep(.fc .fc-toolbar) {
    flex-direction: row;
    justify-content: space-between;
    gap: 0.5rem;
  }
  
  :deep(.fc .fc-toolbar-chunk) {
    flex-wrap: nowrap;
  }
  
  :deep(.fc .fc-button) {
    padding: 0.25rem 0.5rem;
    font-size: 0.625rem;
  }
}

/* Fix viewport height issues */
@media (max-height: 600px) {
  /* For very short screens */
  .modal-content {
    max-height: 80vh;
    overflow-y: auto;
  }
}

/* Touch-friendly improvements */
@media (pointer: coarse) {
  :deep(.fc .fc-button) {
    min-height: 2.5rem;
    min-width: 2.5rem;
    touch-action: manipulation;
  }
  
  :deep(.fc-daygrid-day) {
    min-height: 2.5rem;
    touch-action: manipulation;
  }
  
  :deep(.fc-event) {
    min-height: 1.25rem;
    touch-action: manipulation;
  }
}

/* High DPI screen optimizations */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  :deep(.fc) {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
}
</style>

<style>
/* Global dark mode overrides for FullCalendar - not scoped to ensure they override */
.dark .fc-daygrid-day-number {
  color: #f3f4f6 !important;
  opacity: 1 !important;
  font-weight: 500 !important;
}

.dark .fc-col-header-cell-cushion {
  color: #d1d5db !important;
  font-weight: 600 !important;
}

.dark .fc-toolbar-title {
  color: #f3f4f6 !important;
}

.dark .fc-theme-standard td {
  border-color: #4b5563 !important;
  background-color: transparent !important;
}

.dark .fc-theme-standard th {
  border-color: #4b5563 !important;
  background-color: #374151 !important;
}

.dark .fc-scrollgrid {
  border-color: #4b5563 !important;
  background-color: #1f2937 !important;
}

.dark .fc-daygrid-day {
  color: #f3f4f6 !important;
}

.dark .fc-daygrid-day a {
  color: #f3f4f6 !important;
  text-decoration: none !important;
}

.dark .fc-col-header-cell a {
  color: #d1d5db !important;
  text-decoration: none !important;
}

.dark .fc-button-primary {
  background-color: #3B82F6 !important;
  border-color: #3B82F6 !important;
  color: #ffffff !important;
}

/* Today highlighting for dark mode */
.dark .fc-daygrid-day.fc-day-today {
  background-color: rgba(59, 130, 246, 0.15) !important;
  border: 1px solid rgba(59, 130, 246, 0.3) !important;
}

.dark .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
  color: #93c5fd !important; /* blue-300 */
  font-weight: 600 !important;
}
</style>
<template>
  <div>
    <AuthenticatedLayout title="Student Organizations">
      
        
          <!-- Color Banner -->
          <div class="flex w-full mb-3 sm:mb-4 overflow-hidden rounded-lg shadow-lg">
            <div class="w-1/4 h-1 sm:h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
            <div class="w-1/4 h-1 sm:h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
            <div class="w-1/4 h-1 sm:h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
            <div class="w-1/4 h-1 sm:h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
          </div>

          <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:justify-between sm:items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
              Manage Student Organizations
            </h2>
            <button
              type="button"
              @click="openUserSelectionModalForNewOrg"
              class="flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm text-sm font-medium w-full sm:w-auto"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span class="hidden sm:inline">Add New Organization</span>
              <span class="sm:hidden">Add Organization</span>
            </button>
          </div>
        

        <div class="py-2 sm:py-4 md:py-6 lg:py-8">
          <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 xl:px-8">
            <!-- Alert Messages -->
            <div v-if="$page.props.flash && $page.props.flash.message" 
                 class="mb-3 sm:mb-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-3 sm:p-4 rounded shadow-sm" 
                 role="alert">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-4 w-4 sm:h-5 sm:w-5 text-green-500 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-2 sm:ml-3">
                  <p class="text-sm">{{ $page.props.flash.message }}</p>
                </div>
              </div>
            </div>

            <div v-if="$page.props.flash && $page.props.flash.error" 
                 class="mb-3 sm:mb-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-3 sm:p-4 rounded shadow-sm" 
                 role="alert">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-4 w-4 sm:h-5 sm:w-5 text-red-500 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-2 sm:ml-3">
                  <p class="text-sm">{{ $page.props.flash.error }}</p>
                </div>
              </div>
            </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-3 sm:p-4 lg:p-6 bg-white dark:bg-gray-800">
              <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                  <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Student Organizations by College
                  </h3>
                  <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Manage organizations across all colleges</p>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ getTotalUsersCount() }} Total Organizations
                </div>
              </div>

              <!-- Mobile Card View (hidden on large screens and above) -->
              <div class="block xl:hidden space-y-2 sm:space-y-3">
                <div v-for="college in colleges" :key="`mobile-${college.id}`" class="bg-gray-50 dark:bg-gray-700 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200 dark:border-gray-600 shadow-sm hover:shadow-md transition-all duration-200">
                  <div 
                    @click="toggleCollege(college.id, $event)"
                    class="flex justify-between items-center cursor-pointer"
                  >
                    <div class="flex items-center flex-1 min-w-0">
                      <div class="min-w-0 flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm sm:text-base lg:text-lg truncate">{{ college.acronym }}</h4>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 truncate">{{ college.name }}</p>
                      </div>
                    </div>
                    <div class="flex items-center ml-2 sm:ml-3 flex-shrink-0">
                      <span class="mr-1.5 sm:mr-2 text-xs font-medium px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full flex-shrink-0" 
                            :class="college.users.length > 0 ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-400'">
                        {{ college.users.length }}
                      </span>
                      <svg
                        :class="{'transform rotate-180': openColleges.includes(college.id)}"
                        class="w-4 h-4 sm:w-5 sm:h-5 transition-transform text-gray-500 dark:text-gray-400 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </div>
                  </div>

                  <!-- Mobile Organizations List -->
                  <div v-if="openColleges.includes(college.id)" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div v-if="college.users.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4 sm:py-6">
                      <svg class="mx-auto h-8 w-8 sm:h-10 sm:w-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <p class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium">No organizations</p>
                      <button
                        @click="openUserSelectionModalForCollege(college.id)"
                        class="mt-2 sm:mt-3 inline-flex items-center px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-lg shadow-sm transition-colors duration-200 w-full sm:w-auto"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Organization
                      </button>
                    </div>
                    <div v-else class="space-y-2 sm:space-y-3">
                      <div class="flex flex-col xs:flex-row xs:justify-between xs:items-center mb-2 sm:mb-3 space-y-1 xs:space-y-0">
                        <span class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">Organizations ({{ college.users.length }})</span>
                        <button
                          @click="openUserSelectionModalForCollege(college.id)"
                          class="inline-flex items-center px-2.5 sm:px-3 py-1 sm:py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg shadow-sm transition-colors duration-200 w-full xs:w-auto justify-center xs:justify-start"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                          </svg>
                          Add
                        </button>
                      </div>
                      <div v-for="user in college.users" :key="`mobile-org-${user.id}`" class="bg-white dark:bg-gray-600 rounded-lg p-2.5 sm:p-3 border border-gray-200 dark:border-gray-500 shadow-sm">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center space-x-2 flex-1 min-w-0">
                            <div v-if="user.profile_photo_url" class="flex-shrink-0 h-7 w-7 sm:h-8 sm:w-8">
                              <img class="h-7 w-7 sm:h-8 sm:w-8 rounded-full object-cover border border-gray-200 dark:border-gray-500" :src="user.profile_photo_url" alt="" />
                            </div>
                            <div v-else class="h-7 w-7 sm:h-8 sm:w-8 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner text-xs flex-shrink-0">
                              {{ user.name ? user.name.charAt(0).toUpperCase() : 'O' }}
                            </div>
                            <div class="min-w-0 flex-1">
                              <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ user.name || 'Unknown Organization' }}
                              </div>
                              <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.email || 'No email' }}</div>
                            </div>
                          </div>
                          <div class="flex items-center space-x-1.5 sm:space-x-2 ml-2 flex-shrink-0">
                            <!-- Status Toggle -->
                            <button
                              @click="toggleUserStatus(user)"
                              :class="[
                                'relative inline-flex items-center rounded-full transition-all duration-300 focus:outline-none',
                                user.status === 'active' ? 'bg-green-200 dark:bg-green-700' : 'bg-gray-200 dark:bg-gray-600',
                                'h-5 w-8 sm:h-6 sm:w-10'
                              ]"
                              :title="`Toggle status to ${user.status === 'active' ? 'inactive' : 'active'}`"
                            >
                              <span
                                :class="[
                                  'inline-block rounded-full shadow-sm transform transition-all duration-300',
                                  user.status === 'active' ? 'translate-x-3 sm:translate-x-4 bg-green-600' : 'translate-x-0 bg-gray-400',
                                  'h-3 w-3 sm:h-4 sm:w-4'
                                ]"
                              ></span>
                            </button>
                            <button
                              @click="removeUserFromCollege(user.id)"
                              class="inline-flex items-center justify-center text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150 p-1 sm:p-1.5 rounded-lg"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="colleges.length === 0" class="text-center py-12">
                  <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0h3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                  <p class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No colleges found</p>
                  <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Get started by adding colleges to the system.</p>
                </div>
              </div>

              <!-- Desktop Accordion View (hidden on mobile and tablet) -->
              <div class="hidden xl:block space-y-3">
                <div v-for="college in colleges" :key="college.id" class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden shadow-sm hover:shadow transition-shadow duration-200" data-college-accordion>
                  <div 
                    @click="toggleCollege(college.id, $event)"
                    class="flex justify-between items-center p-4 cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-150"
                  >
                    <div class="flex items-center">
                      <div>
                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ college.acronym }}</span>
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ college.name }}</span>
                      </div>
                    </div>
                    <div class="flex items-center">
                      <span class="mr-2 text-sm font-medium px-2 py-1 rounded-full" 
                            :class="college.users.length > 0 ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-400'">
                        {{ college.users.length }} Organizations
                      </span>
                      <svg
                        :class="{'transform rotate-180': openColleges.includes(college.id)}"
                        class="w-5 h-5 transition-transform text-gray-500 dark:text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7"
                        ></path>
                      </svg>
                    </div>
                  </div>

                  <!-- Users List -->
                  <div v-if="openColleges.includes(college.id)" 
                      class="p-4 divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 ease-in-out">
                    <div v-if="college.users.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                      <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <p class="mt-2">No organizations found for this college</p>
                      <button
                        @click="openUserSelectionModalForCollege(college.id)"
                        class="mt-3 inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
                      >
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                        Add Organization
                      </button>
                    </div>
                    <div v-else class="overflow-x-auto -mx-4 sm:-mx-0">
                      <div class="flex justify-between items-center mb-4 px-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Organizations in this college</h4>
                        <button
                          @click="openUserSelectionModalForCollege(college.id)"
                          class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
                        >
                          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                          </svg>
                          Add Organization
                        </button>
                      </div>
                      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                          <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                              Organization
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                              Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                              Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                              Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                              Actions
                            </th>
                          </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                          <tr v-for="user in college.users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="flex items-center">
                                <div v-if="user.profile_photo_url" class="flex-shrink-0 h-10 w-10">
                                  <img class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="user.profile_photo_url" alt="" />
                                </div>
                                <div v-else class="h-10 w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner">
                                  {{ user.name ? user.name.charAt(0).toUpperCase() : 'O' }}
                                </div>
                                <div class="ml-4">
                                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ user.name || 'Unknown Organization' }}
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="text-sm text-gray-900 dark:text-gray-100">{{ user.email || 'No email' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <span
                                :class="[
                                  'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                  user.role && user.role.slug === 'admin' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                ]"
                              >
                                {{ user.role ? user.role.name : 'No role' }}
                              </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <!-- Status Toggle Button -->
                              <button
                                @click="toggleUserStatus(user)"
                                :class="[
                                  'relative inline-flex items-center rounded-full transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                                  user.status === 'active' ? 'bg-green-200 hover:bg-green-300' : 'bg-gray-200 hover:bg-gray-300',
                                  'h-6 w-11'
                                ]"
                                :title="`Toggle status to ${user.status === 'active' ? 'inactive' : 'active'}`"
                              >
                                <!-- Toggle Knob -->
                                <span
                                  :class="[
                                    'inline-block rounded-full shadow-sm transform transition-all duration-300 ease-in-out',
                                    user.status === 'active' ? 'translate-x-5 bg-green-600' : 'translate-x-0 bg-gray-400',
                                    'h-4 w-4'
                                  ]"
                                >
                                  <!-- Icon inside the knob -->
                                  <span class="flex items-center justify-center h-full w-full text-white">
                                    <!-- Check Icon for Active -->
                                    <svg
                                      v-if="user.status === 'active'"
                                      class="h-2.5 w-2.5"
                                      fill="currentColor"
                                      viewBox="0 0 20 20"
                                    >
                                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    
                                    <!-- X Icon for Inactive -->
                                    <svg
                                      v-else
                                      class="h-2.5 w-2.5"
                                      fill="currentColor"
                                      viewBox="0 0 20 20"
                                    >
                                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                  </span>
                                </span>
                              </button>
                              <!-- Status Label -->
                              <span class="ml-2 text-xs font-medium" :class="user.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                                {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                              </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                              <button
                                @click="removeUserFromCollege(user.id)"
                                class="inline-flex items-center text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors duration-150"
                              >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <div v-if="colleges.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                  No colleges found.
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>

      <!-- User Selection Modal -->
      <Modal :show="showUserSelectionModal" @close="closeUserSelectionModal" maxWidth="lg">
        <div class="p-4 sm:p-6">
          <div class="flex items-center justify-between mb-4 sm:mb-5">
            <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 pr-4">
              Select Organization to Add to {{ selectedCollegeId ? selectedCollegeName : 'a College' }}
            </h2>
            <button @click="closeUserSelectionModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 flex-shrink-0">
              <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Search Input -->
          <div class="mb-3 sm:mb-4">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search organizations by name or email..."
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- College Selection (always show to allow changing selection) -->
          <div class="mb-3 sm:mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select College</label>
            <select
              v-model="selectedCollegeId"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option :value="null" disabled>Choose a college...</option>
              <option v-for="college in colleges" :key="college.id" :value="college.id">
                {{ college.acronym }} - {{ college.name }}
              </option>
            </select>
          </div>

          <!-- Users List -->
          <div class="max-h-64 sm:max-h-96 overflow-y-auto">
            <div v-if="filteredUsers.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-6 sm:py-8">
              <p class="text-sm">No organizations found.</p>
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="user in filteredUsers"
                :key="user.id"
                @click="toggleUserSelection(user)"
                :class="['flex items-center p-2.5 sm:p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors', selectedUsers.some(u => u.id === user.id) ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-400 dark:border-blue-500' : '']"
              >
                <input type="checkbox" :checked="selectedUsers.some(u => u.id === user.id)" @change.stop="toggleUserSelection(user)" class="mr-2 sm:mr-3" />
                <!-- User Avatar -->
                <div class="flex-shrink-0 mr-2 sm:mr-3">
                  <div v-if="user.profile_photo_url" class="h-8 w-8 sm:h-10 sm:w-10">
                    <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="user.profile_photo_url" alt="" />
                  </div>
                  <div v-else class="h-8 w-8 sm:h-10 sm:w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner text-xs sm:text-sm">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'O' }}
                  </div>
                </div>
                <!-- User Info -->
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ user.name || 'Unknown Organization' }}</div>
                  <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.email || 'No email' }}</div>
                  <!-- Mobile: Show role and status on separate lines -->
                  <div class="sm:hidden mt-1 space-y-1">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      Role: {{ user.role ? user.role.name : 'No role' }}
                    </div>
                    <div class="text-xs">
                      <span :class="user.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                        {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                  </div>
                </div>
                <!-- Desktop: Show role and status in separate columns -->
                <div class="hidden sm:block text-sm text-gray-500 dark:text-gray-400 ml-3">
                  {{ user.role ? user.role.name : 'No role' }}
                </div>
                <div class="hidden sm:block text-sm text-gray-500 dark:text-gray-400 ml-3">
                  <span :class="user.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                    {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row items-center justify-end mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2 sm:space-y-0 sm:space-x-3">
            <SecondaryButton @click="closeUserSelectionModal" class="w-full sm:w-auto order-2 sm:order-1" type="button">Cancel</SecondaryButton>
            <button
              class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm w-full sm:w-auto order-1 sm:order-2"
              :disabled="selectedUsers.length === 0"
              @click="openConfirmModal"
              type="button"
            >
              Add Selected Organizations
            </button>
          </div>
        </div>
      </Modal>

      <!-- Confirmation Modal -->
      <Modal :show="showConfirmModal" @close="closeConfirmModal" maxWidth="sm">
        <div class="p-4 sm:p-6">
          <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Confirm Add Organizations</h2>
          <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Are you sure you want to add the following organizations to <strong>{{ selectedCollegeName }}</strong>?</p>
          <div class="max-h-48 sm:max-h-64 overflow-y-auto space-y-2 sm:space-y-3">
            <div v-for="user in selectedUsers" :key="user.id" class="flex items-center p-2.5 sm:p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
              <!-- User Avatar -->
              <div class="flex-shrink-0 mr-2 sm:mr-3">
                <div v-if="user.profile_photo_url" class="h-8 w-8 sm:h-10 sm:w-10">
                  <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="user.profile_photo_url" alt="" />
                </div>
                <div v-else class="h-8 w-8 sm:h-10 sm:w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner text-xs sm:text-sm">
                  {{ user.name ? user.name.charAt(0).toUpperCase() : 'O' }}
                </div>
              </div>
              <!-- User Info -->
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ user.name || 'Unknown Organization' }}</div>
                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.email || 'No email' }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">{{ user.role ? user.role.name : 'No role' }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">
                  <span :class="user.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                    {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2 mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
            <SecondaryButton @click="closeConfirmModal" class="w-full sm:w-auto order-2 sm:order-1">Cancel</SecondaryButton>
            <button
              class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md transition-colors duration-200 text-sm w-full sm:w-auto order-1 sm:order-2"
              @click="confirmAssignUsers"
              type="button"
            >
              Confirm
            </button>
          </div>
        </div>
      </Modal>

      <!-- Remove Confirmation Modal -->
      <Modal :show="showRemoveConfirmModal" @close="closeRemoveConfirmModal" maxWidth="sm">
        <div class="p-4 sm:p-6">
          <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Remove Organization</h2>
          <p class="mb-3 sm:mb-4 text-sm text-gray-600 dark:text-gray-400">Are you sure you want to remove the following organization from <strong>{{ selectedCollegeName }}</strong>?</p>
          <div v-if="orgToRemove" class="flex items-center p-2.5 sm:p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 mb-3 sm:mb-4">
            <div class="flex-shrink-0 mr-2 sm:mr-3">
              <div v-if="orgToRemove.profile_photo_url" class="h-8 w-8 sm:h-10 sm:w-10">
                <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="orgToRemove.profile_photo_url" alt="" />
              </div>
              <div v-else class="h-8 w-8 sm:h-10 sm:w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner text-xs sm:text-sm">
                {{ orgToRemove.name ? orgToRemove.name.charAt(0).toUpperCase() : 'O' }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ orgToRemove.name || 'Unknown Organization' }}</div>
              <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">{{ orgToRemove.email || 'No email' }}</div>
              <div class="text-xs text-gray-400 dark:text-gray-500">{{ orgToRemove.role ? orgToRemove.role.name : 'No role' }}</div>
              <div class="text-xs text-gray-400 dark:text-gray-500">
                <span :class="orgToRemove.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                  {{ orgToRemove.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2 mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
            <SecondaryButton @click="closeRemoveConfirmModal" class="w-full sm:w-auto order-2 sm:order-1">Cancel</SecondaryButton>
            <button
              class="inline-flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-md transition-colors duration-200 text-sm w-full sm:w-auto order-1 sm:order-2"
              @click="confirmRemoveOrg"
              type="button"
            >
              Remove
            </button>
          </div>
        </div>
      </Modal>
    </AuthenticatedLayout>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {
  components: {
    AuthenticatedLayout,
    Modal,
    SecondaryButton
  },
  props: {
    colleges: Array,
    users: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      openColleges: [],
      showUserSelectionModal: false,
      showConfirmModal: false,
      showRemoveConfirmModal: false,
      orgToRemove: null,
      selectedCollegeId: null,
      selectedCollegeName: '',
      searchQuery: '',
      selectedUsers: [],
      clickOutsideHandler: null,
      assignForm: useForm({
        user_ids: [],
        college_id: null
      }),
      removeForm: useForm({
        user_id: null
      })
    };
  },
  computed: {
    filteredUsers() {
      if (!this.users || !Array.isArray(this.users)) {
        return [];
      }
      
      if (!this.searchQuery) {
        return this.users.filter(user => !user.college_id);
      }
      return this.users.filter(user => 
        !user.college_id && 
        ((user.name && user.name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
         (user.email && user.email.toLowerCase().includes(this.searchQuery.toLowerCase())))
      );
    }
  },
  mounted() {
    // Adding a global click handler to close dropdowns when clicking outside
    this.clickOutsideHandler = (event) => {
      // Only process if we have any open colleges
      if (this.openColleges.length > 0) {
        // Check if the click was outside of any college accordion
        const clickedOnAccordion = this.isClickInsideAccordion(event.target);
        if (!clickedOnAccordion) {
          this.closeAllColleges();
        }
      }
    };
    
    document.addEventListener('click', this.clickOutsideHandler);
  },
  
  beforeUnmount() {
    // Clean up the event listener when component is destroyed
    document.removeEventListener('click', this.clickOutsideHandler);
  },
  
  methods: {
    isClickInsideAccordion(element) {
      // Traverse up the DOM to check if the clicked element is inside any accordion
      let currentElement = element;
      while (currentElement) {
        // Check if this element has a data attribute or class that identifies it as part of an accordion
        if (currentElement.classList && 
            (currentElement.classList.contains('border-rounded-md') || 
             currentElement.hasAttribute && currentElement.hasAttribute('data-college-accordion'))) {
          return true;
        }
        currentElement = currentElement.parentElement;
      }
      return false;
    },
    
    closeAllColleges() {
      this.openColleges = [];
    },
    
    toggleCollege(collegeId, event) {
      // Stop propagation to prevent the global click handler from firing
      if (event) {
        event.stopPropagation();
      }
      
      if (this.openColleges.includes(collegeId)) {
        this.openColleges = this.openColleges.filter(id => id !== collegeId);
      } else {
        this.openColleges.push(collegeId);
      }
    },
    openUserSelectionModal(collegeId) {
      this.selectedCollegeId = collegeId;
      const college = this.colleges.find(c => c.id === collegeId);
      this.selectedCollegeName = college ? college.name : '';
      this.searchQuery = '';
      this.selectedUsers = [];
      this.showUserSelectionModal = true;
    },
    closeUserSelectionModal() {
      this.showUserSelectionModal = false;
      this.selectedCollegeId = null;
      this.selectedCollegeName = '';
      this.searchQuery = '';
      this.selectedUsers = [];
    },
    toggleUserSelection(user) {
      const idx = this.selectedUsers.findIndex(u => u.id === user.id);
      if (idx === -1) {
        this.selectedUsers.push(user);
      } else {
        this.selectedUsers.splice(idx, 1);
      }
    },
    openConfirmModal() {
      if (this.selectedUsers.length === 0) {
        alert('Please select at least one organization to add.');
        return;
      }
      
      // If no college is selected, show an alert
      if (!this.selectedCollegeId) {
        alert('Please select a college first.');
        return;
      }
      
      this.showConfirmModal = true;
    },
    closeConfirmModal() {
      this.showConfirmModal = false;
    },
    confirmAssignUsers() {
      if (!this.selectedCollegeId) {
        alert('Please select a college first.');
        return;
      }
      this.assignForm.user_ids = this.selectedUsers.map(u => u.id);
      this.assignForm.college_id = this.selectedCollegeId;
      this.assignForm.post(route('admin.student-orgs.assign-user'), {
        preserveScroll: true,
        onSuccess: () => {
          this.closeConfirmModal();
          this.closeUserSelectionModal();
        }
      });
    },
    removeUserFromCollege(userId) {
      const college = this.colleges.find(col => col.users.some(u => u.id === userId));
      const org = college ? college.users.find(u => u.id === userId) : null;
      this.orgToRemove = org;
      this.selectedCollegeName = college ? college.name : '';
      this.showRemoveConfirmModal = true;
    },
    closeRemoveConfirmModal() {
      this.showRemoveConfirmModal = false;
      this.orgToRemove = null;
    },
    confirmRemoveOrg() {
      this.removeForm.user_id = this.orgToRemove.id;
      this.removeForm.post(route('admin.student-orgs.remove-user'), {
        preserveScroll: true,
        onSuccess: () => {
          this.closeRemoveConfirmModal();
        }
      });
    },
    getTotalUsersCount() {
      return this.colleges.reduce((total, college) => total + college.users.length,0);
    },
    openUserSelectionModalForNewOrg() {
      this.selectedCollegeId = null;
      this.selectedCollegeName = 'any college';
      this.searchQuery = '';
      this.selectedUsers = [];
      this.showUserSelectionModal = true;
    },
    openUserSelectionModalForCollege(collegeId) {
      this.selectedCollegeId = collegeId;
      const college = this.colleges.find(c => c.id === collegeId);
      this.selectedCollegeName = college ? college.name : '';
      this.searchQuery = '';
      this.selectedUsers = [];
      this.showUserSelectionModal = true;
    },
    toggleUserStatus(user) {
      // Create a form to submit the status toggle
      const form = useForm({
        user_id: user.id
      });
      
      form.post(route('admin.student-orgs.toggle-status'), {
        preserveScroll: true,
        onSuccess: () => {
          // Update the user's status locally for immediate feedback
          user.status = user.status === 'active' ? 'inactive' : 'active';
        },
        onError: (errors) => {
          console.error('Error toggling status:', errors);
        }
      });
    }
  }
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
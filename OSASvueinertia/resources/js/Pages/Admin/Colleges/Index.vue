<template>
  <AuthenticatedLayout>
    <Head title="Manage Colleges" />
    
    <div class="py-4 sm:py-6 lg:py-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <!-- Animated colored banner -->
        <div class="flex w-full mb-4 sm:mb-6 overflow-hidden rounded-lg shadow-md">
          <div class="w-1/4 h-1 sm:h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
          <div class="w-1/4 h-1 sm:h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
          <div class="w-1/4 h-1 sm:h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
          <div class="w-1/4 h-1 sm:h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
        </div>

        <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:justify-between sm:items-center mb-6">
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-200 flex flex-col sm:flex-row sm:items-center">
            <span class="mr-0 sm:mr-2">Manage Colleges</span>
            <span class="text-xs sm:text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 py-1 px-2 rounded-full mt-1 sm:mt-0 self-start sm:self-auto">
              {{ colleges.length }} total
            </span>
          </h1>
          
          <button 
            @click="openCreateModal" 
            class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center shadow-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden sm:inline">Add New College</span>
            <span class="sm:hidden">Add College</span>
          </button>
        </div>

        <!-- Alert Messages -->
        <div v-if="$page.props.flash && $page.props.flash.message" 
            class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 rounded-lg flex items-center shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-green-500 dark:text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span class="text-sm sm:text-base">{{ $page.props.flash.message }}</span>
        </div>
        
        <div v-if="$page.props.flash && $page.props.flash.error" 
            class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 rounded-lg flex items-center shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-red-500 dark:text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span class="text-sm sm:text-base">{{ $page.props.flash.error }}</span>
        </div>

        <!-- No colleges placeholder -->
        <div v-if="colleges.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 sm:p-8 text-center">
          <div class="flex flex-col items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 sm:h-16 sm:w-16 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-base sm:text-lg font-medium">No colleges found</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Click "Add College" to create one</p>
          </div>
        </div>

        <!-- Colleges Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
          <div
            v-for="college in colleges"
            :key="college.id"
            class="relative bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg dark:hover:shadow-gray-900/50 transition-all duration-300 flex flex-col"
          >
            <!-- Colored top border, random color per college -->
            <div class="h-1 w-full rounded-t-xl" :class="{
                'bg-blue-500': college.id % 4 === 0,
                'bg-green-500': college.id % 4 === 1,
                'bg-yellow-500': college.id % 4 === 2,
                'bg-red-500': college.id % 4 === 3,
            }"></div>
            
            <div class="p-4 sm:p-6 flex-1 flex flex-col">
              <div class="flex items-start justify-between">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100 leading-tight flex-1 min-w-0 mr-2">
                  <span class="break-words">{{ college.name }}</span>
                </h2>
                <span v-if="college.acronym" 
                      class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs sm:text-sm px-2 py-1 rounded-md">
                  {{ college.acronym }}
                </span>
              </div>
              
              <p v-if="college.description" class="text-gray-600 dark:text-gray-400 mt-2 sm:mt-3 text-xs sm:text-sm line-clamp-3">
                {{ college.description }}
              </p>
              <div v-else class="text-gray-400 dark:text-gray-500 italic text-xs sm:text-sm mt-2 sm:mt-3">No description available</div>
              
              <div class="mt-auto pt-3 sm:pt-4 flex items-center text-xs sm:text-sm">
                <span class="flex items-center text-gray-500 dark:text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  {{ college.users_count }} {{ college.users_count === 1 ? 'Organization' : 'Organizations' }}
                </span>
              </div>
            </div>
            
            <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-100 dark:border-gray-600">
              <!-- Responsive: Stack buttons vertically on mobile, horizontally on larger screens -->
              <!-- Added pr-16 sm:pr-20 to prevent overlap with college logo -->
              <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2 space-y-2 sm:space-y-0 pr-16 sm:pr-20">
                <Link
                  :href="route('admin.colleges.show', [college.id])"
                  class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-lg shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                  style="min-width: 120px;"
                >
                  <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                  <span class="hidden sm:inline">View Details</span>
                  <span class="sm:hidden">View</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
                <button 
                  @click="openEditModal(college)" 
                  class="w-full sm:w-auto p-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-colors duration-150 flex items-center justify-center"
                  title="Edit"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 dark:text-blue-400 mr-1 sm:mr-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span class="sm:hidden">Edit</span>
                </button>
                <button 
                  @click="openDeleteModal(college)" 
                  class="w-full sm:w-auto p-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-colors duration-150 flex items-center justify-center"
                  title="Delete"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 dark:text-red-400 mr-1 sm:mr-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span class="sm:hidden">Delete</span>
                </button>
              </div>
            </div>
            
            <!-- College Logo/Avatar - Positioned to overflow -->
            <div class="absolute -bottom-3 -right-3 sm:-bottom-4 sm:-right-4 z-10">
              <img 
                :src="getCollegeLogo(college.acronym, college.logo_path)" 
                :alt="`${college.name} logo`"
                class="college-logo"
                @error="handleImageError"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <Modal :show="isCreateModalOpen" @close="isCreateModalOpen = false" :closeable="true" max-width="lg">
      <div class="p-3 sm:p-4 lg:p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2 sm:pb-3 mb-3 sm:mb-4 lg:mb-6">
          <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New College
          </h2>
          <button @click="isCreateModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="handleCreateSubmit" enctype="multipart/form-data" class="space-y-3 sm:space-y-4 lg:space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Name</label>
              <input 
                type="text" 
                id="name" 
                v-model="form.name" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm transition-colors duration-200"
                placeholder="Enter college name"
                required
              />
              <div v-if="errors && errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
            </div>
            
            <div>
              <label for="acronym" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acronym</label>
              <input 
                type="text" 
                id="acronym" 
                v-model="form.acronym" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm transition-colors duration-200"
                placeholder="e.g. CAS, COE"
                required
              />
              <div v-if="errors && errors.acronym" class="text-red-500 text-sm mt-1">{{ errors.acronym }}</div>
            </div>
          </div>
          
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea 
              id="description" 
              v-model="form.description" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm resize-none transition-colors duration-200"
              rows="3"
              placeholder="Enter a brief description of the college"
            ></textarea>
            <div v-if="errors && errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Logo</label>
            <div class="mt-2 p-3 sm:p-4 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700">
              <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                <!-- Logo Preview -->
                <div class="flex-shrink-0">
                  <div class="relative group">
                    <img 
                      :src="logoPreview || '/images/lspu_logo_better.png'" 
                      class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-4 border-blue-200 shadow-md transition-all duration-200 group-hover:border-blue-300" 
                    />
                    <div 
                      v-if="logoPreview"
                      class="absolute inset-0 bg-black bg-opacity-40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center"
                    >
                      <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                  </div>
                </div>
                
                <!-- Logo Controls -->
                <div class="flex-1 space-y-3">
                  <!-- Choose Logo Button -->
                  <div class="relative">
                    <input 
                      type="file" 
                      accept="image/*" 
                      @change="handleLogoChange" 
                      class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                      id="college-logo-input"
                    />
                    <label 
                      for="college-logo-input"
                      class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group cursor-pointer"
                    >
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      Choose Logo
                    </label>
                  </div>
                  
                  <!-- Logo Guidelines -->
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center sm:text-left">
                    Recommended: Square image, at least 200x200 pixels. Maximum file size: 5MB. Leave empty to use default logo.
                  </p>
                </div>
              </div>
            </div>
            <div v-if="errors && errors.logo" class="text-red-500 text-sm mt-1">{{ errors.logo }}</div>
          </div>
          
          <div class="flex flex-col sm:flex-row pt-4 border-t border-gray-100 dark:border-gray-700 space-y-3 sm:space-y-0 sm:space-x-3">
            <button 
              type="button" 
              @click="isCreateModalOpen = false" 
              class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="formProcessing" 
              class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-lg shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 disabled:opacity-50"
            >
              <svg v-if="formProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ formProcessing ? 'Saving...' : 'Save College' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Edit Modal -->
    <Modal :show="isEditModalOpen" @close="isEditModalOpen = false" :closeable="true" max-width="lg">
      <div class="p-3 sm:p-4 lg:p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 sm:mb-6">
          <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit College
          </h2>
          <button @click="isEditModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="handleEditSubmit" class="space-y-3 sm:space-y-4 lg:space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
            <div>
              <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Name</label>
              <input 
                type="text" 
                id="edit-name" 
                v-model="editForm.name" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm transition-colors duration-200"
                required
              />
              <div v-if="editErrors && editErrors.name" class="text-red-500 text-sm mt-1">{{ editErrors.name }}</div>
            </div>
            
            <div>
              <label for="edit-acronym" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acronym</label>
              <input 
                type="text" 
                id="edit-acronym" 
                v-model="editForm.acronym" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm transition-colors duration-200"
                required
              />
              <div v-if="editErrors && editErrors.acronym" class="text-red-500 text-sm mt-1">{{ editErrors.acronym }}</div>
            </div>
          </div>
          
          <div>
            <label for="edit-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea 
              id="edit-description" 
              v-model="editForm.description" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm resize-none transition-colors duration-200"
              rows="3"
            ></textarea>
            <div v-if="editErrors && editErrors.description" class="text-red-500 text-sm mt-1">{{ editErrors.description }}</div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Logo</label>
            <div class="mt-2 p-3 sm:p-4 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700">
              <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                <!-- Logo Preview -->
                <div class="flex-shrink-0">
                  <div class="relative group">
                    <img 
                      :src="editLogoPreview || getCollegeLogo(collegeToEdit?.acronym, collegeToEdit?.logo_path)" 
                      class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-4 border-blue-200 shadow-md transition-all duration-200 group-hover:border-blue-300" 
                    />
                    <div 
                      v-if="editLogoPreview || collegeToEdit?.logo_path"
                      class="absolute inset-0 bg-black bg-opacity-40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center"
                    >
                      <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                  </div>
                </div>
                
                <!-- Logo Controls -->
                <div class="flex-1 space-y-3">
                  <!-- Choose Logo Button -->
                  <div class="relative">
                    <input 
                      type="file" 
                      accept="image/*" 
                      @change="handleEditLogoChange" 
                      class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                      id="edit-college-logo-input"
                    />
                    <label 
                      for="edit-college-logo-input"
                      class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group cursor-pointer"
                    >
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      Choose Logo
                    </label>
                  </div>
                  
                  <!-- Logo Guidelines -->
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center sm:text-left">
                    Recommended: Square image, at least 200x200 pixels. Maximum file size: 5MB. Leave empty to keep current logo.
                  </p>
                </div>
              </div>
            </div>
            <div v-if="editErrors && editErrors.logo" class="text-red-500 text-sm mt-1">{{ editErrors.logo }}</div>
          </div>
          
          <div class="flex flex-col sm:flex-row pt-4 border-t border-gray-100 dark:border-gray-700 space-y-3 sm:space-y-0 sm:space-x-3">
            <button 
              type="button" 
              @click="isEditModalOpen = false" 
              class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="editFormProcessing" 
              class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-lg shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 disabled:opacity-50"
            >
              <svg v-if="editFormProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ editFormProcessing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" :closeable="true" max-width="md">
      <div class="p-3 sm:p-4 lg:p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 sm:mb-6">
          <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete College
          </h2>
          <button @click="isDeleteModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="flex flex-col items-center justify-center min-h-[120px] sm:min-h-[180px]">
          <!-- Warning Icon -->
          <div class="mb-4 sm:mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-red-100 dark:bg-red-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-8 sm:w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
          
          <!-- Warning Message -->
          <div class="text-center mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
              Delete College
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 sm:mb-4 px-2">
              Are you sure you want to delete <span class="font-bold text-red-600 dark:text-red-400">{{ collegeToDelete ? collegeToDelete.name : '' }}</span>?
            </p>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 sm:p-4">
              <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 dark:text-red-400 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm text-red-700 dark:text-red-300">
                  <p class="font-medium">This action cannot be undone.</p>
                  <p class="mt-1">All data associated with this college will be permanently removed.</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row justify-center gap-3 w-full">
            <button 
              type="button" 
              @click="isDeleteModalOpen = false" 
              class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300"
            >
              Cancel
            </button>
            <button 
              type="button" 
              @click="handleDelete" 
              :disabled="deleteProcessing" 
              class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-sm font-medium text-white rounded-lg shadow-md hover:shadow-red-300/30 hover:from-red-400 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-red-600 active:to-red-700 transition-all duration-300 disabled:opacity-50"
            >
              <svg v-if="deleteProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ deleteProcessing ? 'Deleting...' : 'Delete College' }}
            </button>
          </div>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

export default {
  components: {
    AuthenticatedLayout,
    Modal,
    Head,
    Link
  },
  
  props: {
    colleges: Array,
    errors: Object
  },
  
  data() {
    return {
      isCreateModalOpen: false,
      isEditModalOpen: false,
      isDeleteModalOpen: false,
      collegeToEdit: null,
      collegeToDelete: null,
      deleteProcessing: false,
      editErrors: {},
      logoPreview: null,
      editLogoPreview: null,
      // Form data for create
      form: {
        name: '',
        acronym: '',
        description: '',
        logo: null
      },
      // Form data for edit
      editForm: {
        name: '',
        acronym: '',
        description: '',
        logo: null
      },
      // Processing states
      formProcessing: false,
      editFormProcessing: false
    };
  },
  
  setup() {
    // We'll handle form data manually for file uploads
    return {};
  },
  

  
  methods: {
    openCreateModal() {
      this.form = {
        name: '',
        acronym: '',
        description: '',
        logo: null
      };
      this.logoPreview = null;
      this.isCreateModalOpen = true;
    },
    
    handleCreateSubmit() {
      this.formProcessing = true;
      
      // Create FormData for file upload
      const formData = new FormData();
      formData.append('name', this.form.name);
      formData.append('acronym', this.form.acronym);
      formData.append('description', this.form.description);
      
      if (this.form.logo) {
        formData.append('logo', this.form.logo);
      }
      
      // Use $inertia.post with FormData
      this.$inertia.post(route('admin.colleges.store'), formData, {
        onSuccess: () => {
          this.isCreateModalOpen = false;
          this.form = {
            name: '',
            acronym: '',
            description: '',
            logo: null
          };
          this.logoPreview = null;
        },
        onError: (errors) => {
          // Handle errors if needed
        },
        onFinish: () => {
          this.formProcessing = false;
        }
      });
    },
    
    openEditModal(college) {
      this.collegeToEdit = college;
      this.editForm = {
        name: college.name,
        acronym: college.acronym,
        description: college.description || '',
        logo: null
      };
      this.editLogoPreview = null;
      this.isEditModalOpen = true;
      this.editErrors = {};
    },
    
    handleEditSubmit() {
      this.editFormProcessing = true;
      
      // Create FormData for file upload
      const formData = new FormData();
      formData.append('name', this.editForm.name);
      formData.append('acronym', this.editForm.acronym);
      formData.append('description', this.editForm.description);
      formData.append('_method', 'PUT'); // Required for PUT requests with FormData
      
      if (this.editForm.logo) {
        formData.append('logo', this.editForm.logo);
      }
      
      // Use $inertia.post with FormData (PUT method)
      this.$inertia.post(route('admin.colleges.update', this.collegeToEdit.id), formData, {
        onSuccess: () => {
          this.isEditModalOpen = false;
          this.editForm = {
            name: '',
            acronym: '',
            description: '',
            logo: null
          };
          this.collegeToEdit = null;
          this.editErrors = {};
          this.editLogoPreview = null;
        },
        onError: (errors) => {
          this.editErrors = errors;
        },
        onFinish: () => {
          this.editFormProcessing = false;
        }
      });
    },
    
    openDeleteModal(college) {
      this.collegeToDelete = college;
      this.isDeleteModalOpen = true;
    },
    
    handleDelete() {
      this.deleteProcessing = true;
      this.$inertia.delete(route('admin.colleges.destroy', this.collegeToDelete.id), {
        onSuccess: () => {
          this.isDeleteModalOpen = false;
          this.collegeToDelete = null;
        },
        onFinish: () => {
          this.deleteProcessing = false;
        }
      });
    },
    
    getCollegeLogo(acronym, customLogoPath = null) {
      // If college has a custom uploaded logo, use it
      if (customLogoPath) {
        // Add /storage/ prefix to access files through the storage link
        return `/storage/${customLogoPath}`;
      }
      
      if (!acronym) {
        return '/images/lspu_logo_better.png';
      }
      
      const logoMap = {
        'CAS': '/images/cas-logo.jpg',
        'CCS': '/images/ccs-logo.jpg',
        'CCJE': '/images/ccje-logo.jpg',
        'COE': '/images/coe-logo.jpg',
        'CIT': '/images/cit-logo.jpg',
        'CTE': '/images/cte-logo.jpg',
        'CIHTM': '/images/chmt-logo.jpg',
        'CBAA': '/images/cbaa-logo.jpg'
      };
      
      return logoMap[acronym.toUpperCase()] || '/images/lspu_logo_better.png'; // Default fallback
    },
    
    handleImageError(event) {
      // Fallback to default logo if image fails to load
      if (event && event.target) {
        event.target.src = '/images/lspu_logo_better.png';
      }
    },
    
    handleLogoChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.form.logo = file;
        this.logoPreview = URL.createObjectURL(file);
      } else {
        this.form.logo = null;
        this.logoPreview = null;
      }
    },
    
    handleEditLogoChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.editForm.logo = file;
        this.editLogoPreview = URL.createObjectURL(file);
      } else {
        this.editForm.logo = null;
        this.editLogoPreview = null;
      }
    }
  }
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Ensure consistent logo sizing and positioning */
.college-logo {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  object-fit: cover;
  object-position: center;
  border: 3px solid white;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  background-color: white;
  flex-shrink: 0;
}

@media (min-width: 640px) {
  .college-logo {
    width: 5rem;
    height: 5rem;
    border-width: 4px;
  }
}

:global(.dark) .college-logo {
  border-color: #374151; /* gray-700 */
  background-color: #374151; /* gray-700 */
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}
</style>
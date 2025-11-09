<template>
    <AuthenticatedLayout>
        <Head :title="college.name" />

        <div class="py-4 sm:py-8 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Animated colored banner -->
                <div class="flex w-full mb-4 sm:mb-6 overflow-hidden rounded-lg shadow-md">
                    <div class="w-1/4 h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
                </div>

                <!-- Back button -->
                <div class="mb-4 sm:mb-6">
                    <Link :href="route('admin.colleges.index')" class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Manage Colleges
                    </Link>
                </div>

                <!-- College details card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden mb-6 sm:mb-8">
                    <!-- College header with custom accent color -->
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 relative">
                        <div class="absolute top-0 left-0 w-full h-1" :class="{
                            'bg-blue-500': college.id % 4 === 0,
                            'bg-green-500': college.id % 4 === 1,  
                            'bg-yellow-500': college.id % 4 === 2,
                            'bg-red-500': college.id % 4 === 3,
                        }"></div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-2">
                            <div class="mb-3 sm:mb-0">
                                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100">{{ college.name }}</h1>
                                <div v-if="college.acronym" class="text-gray-600 dark:text-gray-400 text-sm sm:text-base mt-1">
                                    {{ college.acronym }}
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-0 flex items-center gap-2 sm:gap-3 flex-wrap">
                                <span class="inline-flex items-center px-2.5 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    {{ college.users.length }} {{ college.users.length === 1 ? 'Organization' : 'Organizations' }}
                                </span>
                                <!-- Admin Actions -->
                                <div class="flex gap-2">
                                    <button 
                                        @click="openEditModal(college)" 
                                        class="p-1.5 sm:p-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 dark:focus:ring-offset-gray-800 transition-colors duration-150 ease-in-out"
                                        title="Edit College"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button 
                                        @click="openDeleteModal(college)" 
                                        class="p-1.5 sm:p-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 dark:focus:ring-offset-gray-800 transition-colors duration-150 ease-in-out"
                                        title="Delete College"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- College description -->
                    <div class="px-4 sm:px-6 py-3 sm:py-4" v-if="college.description">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">About</h2>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">{{ college.description }}</p>
                    </div>
                    <div class="px-4 sm:px-6 py-3 sm:py-4 italic text-sm sm:text-base text-gray-500 dark:text-gray-400" v-else>
                        No description available for this college.
                    </div>
                </div>

                <!-- Student Organizations section -->
                <div class="mb-6 sm:mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Student Organizations</h2>
                        <Link 
                            :href="route('admin.student-orgs.index')" 
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Manage Organizations
                        </Link>
                    </div>

                    <div v-if="college.users.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 sm:p-8 text-center">
                        <div class="text-sm sm:text-base text-gray-500 dark:text-gray-400">No student organizations found for this college.</div>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div
                            v-for="org in college.users"
                            :key="org.id"
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg dark:hover:shadow-gray-900/50 transition-all duration-300 overflow-hidden flex flex-col"
                        >
                            <!-- Status indicator at top -->
                            <div class="w-full h-1.5" :class="org.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></div>
                            
                            <div class="p-4 sm:p-5 flex-1 flex flex-col">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-semibold text-sm sm:text-base text-gray-800 dark:text-gray-100">
                                        <Link 
                                            :href="route('admin.student-orgs.show', org.id)" 
                                            class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                        >
                                            {{ org.name }}
                                        </Link>
                                    </h3>
                                    <span v-if="org.college && org.college.acronym" 
                                          class="ml-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md flex-shrink-0">
                                        {{ org.college.acronym }}
                                    </span>
                                </div>
                                
                                <!-- Logo centered -->
                                <div v-if="org.profile_photo_url" class="flex justify-center my-3 sm:my-4">
                                    <img
                                        :src="org.profile_photo_url"
                                        :alt="`${org.name} logo`"
                                        class="h-16 w-16 sm:h-20 sm:w-20 object-cover rounded-full border border-gray-200 dark:border-gray-600 shadow-inner bg-gray-50 dark:bg-gray-700"
                                    />
                                </div>
                                <div v-else class="flex justify-center my-3 sm:my-4">
                                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white text-xl sm:text-2xl font-medium shadow-inner select-none">
                                        {{ org.name ? org.name.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                </div>
                                
                                <p v-if="org.description" class="text-gray-600 dark:text-gray-400 text-sm mt-3 line-clamp-3">
                                    {{ org.description }}
                                </p>
                                <div v-else class="text-gray-400 dark:text-gray-500 italic text-sm mt-3">No description available</div>
                                
                                <div class="mt-auto pt-4 flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center text-xs px-2 py-1 rounded-full font-medium"
                                        :class="org.status === 'active' 
                                            ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                                            : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'"
                                    >
                                        <span class="w-2 h-2 rounded-full mr-1.5" :class="org.status === 'active' ? 'bg-green-500 dark:bg-green-400' : 'bg-red-500 dark:bg-red-400'"></span>
                                        {{ org.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                    
                                    <Link
                                        :href="route('admin.student-orgs.show', org.id)"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium flex items-center"
                                    >
                                        Details
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <Modal :show="isEditModalOpen" @close="isEditModalOpen = false" :closeable="true" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit College
                    </h2>
                    <button @click="isEditModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="handleEditSubmit" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College Name</label>
                            <input 
                                type="text" 
                                id="edit-name" 
                                v-model="editForm.name" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm transition-colors duration-200"
                                placeholder="Enter college name"
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
                                placeholder="e.g. CAS, COE"
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
                            placeholder="Enter a brief description of the college"
                        ></textarea>
                        <div v-if="editErrors && editErrors.description" class="text-red-500 text-sm mt-1">{{ editErrors.description }}</div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="isEditModalOpen = false" 
                            class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-600 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="editFormProcessing" 
                            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group ml-2 disabled:opacity-50"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
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
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete College
                    </h2>
                    <button @click="isDeleteModalOpen = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex flex-col items-center justify-center min-h-[180px]">
                    <!-- Warning Icon -->
                    <div class="mb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Warning Message -->
                    <div class="text-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Delete College
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Are you sure you want to delete <span class="font-bold text-red-600 dark:text-red-400">{{ collegeToDelete ? collegeToDelete.name : '' }}</span>?
                        </p>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
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
                    <div class="flex justify-center gap-3 w-full">
                        <button 
                            type="button" 
                            @click="isDeleteModalOpen = false" 
                            class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-600 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Cancel
                        </button>
                        <button 
                            type="button" 
                            @click="handleDelete" 
                            :disabled="deleteProcessing" 
                            class="inline-flex items-center justify-center px-6 py-2 bg-gradient-to-r from-red-500 to-red-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-red-300/30 hover:from-red-400 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-red-600 active:to-red-700 transition-all duration-300 relative overflow-hidden group disabled:opacity-50"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
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
    college: Object,
    errors: Object
  },
  
  data() {
    return {
      isEditModalOpen: false,
      isDeleteModalOpen: false,
      collegeToEdit: null,
      collegeToDelete: null,
      deleteProcessing: false,
      editErrors: {},
      editFormProcessing: false,
      // Form data for edit
      editForm: {
        name: '',
        acronym: '',
        description: '',
        logo: null
      }
    };
  },
  
  methods: {
    openEditModal(college) {
      this.collegeToEdit = college;
      this.editForm = {
        name: college.name,
        acronym: college.acronym,
        description: college.description || '',
        logo: null
      };
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
      formData.append('_method', 'PUT');
      
      // Use $inertia.post with FormData
      this.$inertia.post(route('admin.colleges.update', this.collegeToEdit.id), formData, {
        onSuccess: () => {
          this.isEditModalOpen = false;
          this.collegeToEdit = null;
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
    }
  }
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

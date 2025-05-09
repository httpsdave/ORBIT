<template>
  <AuthenticatedLayout>
    <Head title="Manage Colleges" />
    
    <div class="py-12 bg-gray-50">
     
        <!-- Colored banner -->
        <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
          <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
          <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
          <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
          <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
              <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                <div class="bg-blue-500 w-1 h-8 rounded-full"></div>
                <h2 class="text-xl font-bold text-gray-800">Manage Colleges</h2>
              </div>
              
              <button 
                @click="openCreateModal" 
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition-colors duration-200 flex items-center"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New College
              </button>
            </div>

            <!-- Alert Messages -->
            <div v-if="$page.props.flash && $page.props.flash.message" 
                class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-md flex items-center shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ $page.props.flash.message }}
            </div>
            
            <div v-if="$page.props.flash && $page.props.flash.error" 
                class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-md flex items-center shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              {{ $page.props.flash.error }}
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
              <div class="overflow-x-auto">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                      <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acronym</th>
                      <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Description</th>
                      <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="college in colleges" :key="college.id" class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                      <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ college.name }}</td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-xs font-medium">
                          {{ college.acronym }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate hidden md:table-cell">{{ college.description || 'N/A' }}</td>
                      <td class="px-4 py-3 text-right whitespace-nowrap flex justify-end space-x-1">
                          <button 
                          @click="openEditModal(college)" 
                          class="p-1.5 bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition-colors duration-150 ease-in-out"
                          title="Edit"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button 
                          @click="openDeleteModal(college)" 
                          class="p-1.5 bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition-colors duration-150 ease-in-out"
                          title="Delete"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="colleges.length === 0">
                      <td colspan="4" class="px-4 py-8 text-center">
                        <div class="flex flex-col items-center justify-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                          </svg>
                          <p class="text-gray-500 text-lg">No colleges found</p>
                          <p class="text-gray-400 text-sm mt-1">Click "Add New College" to create one</p>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
   
    </div>

    <!-- Create Modal -->
    <Modal :show="isCreateModalOpen" @close="isCreateModalOpen = false" :closeable="false" max-width="md">
      <div class="p-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-6">
          <h2 class="text-lg font-bold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New College
          </h2>
          <button @click="isCreateModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="handleCreateSubmit">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">College Name</label>
            <input 
              type="text" 
              id="name" 
              v-model="form.name" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              placeholder="Enter college name"
              required
            />
            <div v-if="errors && errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
          </div>
          <div class="mb-4">
            <label for="acronym" class="block text-sm font-medium text-gray-700 mb-1">Acronym</label>
            <input 
              type="text" 
              id="acronym" 
              v-model="form.acronym" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              placeholder="e.g. CAS, COE"
              required
            />
            <div v-if="errors && errors.acronym" class="text-red-500 text-sm mt-1">{{ errors.acronym }}</div>
          </div>
          <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              id="description" 
              v-model="form.description" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              rows="3"
              placeholder="Enter a brief description of the college"
            ></textarea>
            <div v-if="errors && errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</div>
          </div>
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="isCreateModalOpen = false" 
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 flex items-center"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Saving...' : 'Save College' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Edit Modal -->
    <Modal :show="isEditModalOpen" @close="isEditModalOpen = false" :closeable="false" max-width="md">
      <div class="p-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-6">
          <h2 class="text-lg font-bold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit College
          </h2>
          <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="handleEditSubmit">
          <div class="mb-4">
            <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">College Name</label>
                          <input 
              type="text" 
              id="edit-name" 
              v-model="editForm.name" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              required
            />
            <div v-if="editErrors && editErrors.name" class="text-red-500 text-sm mt-1">{{ editErrors.name }}</div>
          </div>
          <div class="mb-4">
            <label for="edit-acronym" class="block text-sm font-medium text-gray-700 mb-1">Acronym</label>
            <input 
              type="text" 
              id="edit-acronym" 
              v-model="editForm.acronym" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              required
            />
            <div v-if="editErrors && editErrors.acronym" class="text-red-500 text-sm mt-1">{{ editErrors.acronym }}</div>
          </div>
          <div class="mb-6">
            <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              id="edit-description" 
              v-model="editForm.description" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              rows="3"
            ></textarea>
            <div v-if="editErrors && editErrors.description" class="text-red-500 text-sm mt-1">{{ editErrors.description }}</div>
          </div>
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="isEditModalOpen = false" 
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="editForm.processing" 
              class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 flex items-center"
            >
              <svg v-if="editForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" :closeable="false" max-width="md">
      <div class="p-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-6">
          <h2 class="text-lg font-bold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete College
          </h2>
          <button @click="isDeleteModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">
                Are you sure you want to delete <span class="font-bold">{{ collegeToDelete ? collegeToDelete.name : '' }}</span>?
              </p>
              <p class="text-sm text-red-600 mt-1">
                This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
        <div class="flex justify-end space-x-3">
          <button 
            type="button" 
            @click="isDeleteModalOpen = false" 
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors duration-200"
          >
            Cancel
          </button>
          <button 
            type="button" 
            @click="handleDelete" 
            :disabled="deleteProcessing" 
            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 flex items-center"
          >
            <svg v-if="deleteProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ deleteProcessing ? 'Deleting...' : 'Delete College' }}
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';

export default {
  components: {
    AuthenticatedLayout,
    Modal,
    Head
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
      editErrors: {}
    };
  },
  
  setup() {
    const form = useForm({
      name: '',
      acronym: '',
      description: ''
    });
    
    const editForm = useForm({
      name: '',
      acronym: '',
      description: ''
    });
    
    return { form, editForm };
  },
  
  methods: {
    openCreateModal() {
      this.form.reset();
      this.isCreateModalOpen = true;
    },
    
    handleCreateSubmit() {
      this.form.post(route('admin.colleges.store'), {
        onSuccess: () => {
          this.isCreateModalOpen = false;
          this.form.reset();
        }
      });
    },
    
    openEditModal(college) {
      this.collegeToEdit = college;
      this.editForm.name = college.name;
      this.editForm.acronym = college.acronym;
      this.editForm.description = college.description || '';
      this.isEditModalOpen = true;
      this.editErrors = {};
    },
    
    handleEditSubmit() {
      this.editForm.put(route('admin.colleges.update', this.collegeToEdit.id), {
        onSuccess: () => {
          this.isEditModalOpen = false;
          this.editForm.reset();
          this.collegeToEdit = null;
          this.editErrors = {};
        },
        onError: (errors) => {
          this.editErrors = errors;
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
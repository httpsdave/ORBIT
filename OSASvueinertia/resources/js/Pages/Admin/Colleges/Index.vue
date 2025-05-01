<template>
    <AuthenticatedLayout>
      <Head title="Manage Colleges" />
      
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Colleges</h2>
                <button 
                  @click="openCreateModal" 
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  Add New College
                </button>
              </div>
  
              <div v-if="$page.props.flash && $page.props.flash.message" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ $page.props.flash.message }}
              </div>
              
              <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ $page.props.flash.error }}
              </div>
  
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acronym</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="college in colleges" :key="college.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ college.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ college.acronym }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ college.description || 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-2">
                        <button 
                          @click="openEditModal(college)" 
                          class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                          Edit
                        </button>
                        <button 
                          @click="openDeleteModal(college)" 
                          class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="colleges.length === 0">
                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No colleges found</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Create Modal -->
      <Modal :show="isCreateModalOpen" @close="isCreateModalOpen = false">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Add New College</h2>
          <form @submit.prevent="handleCreateSubmit">
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input 
                type="text" 
                id="name" 
                v-model="form.name" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <div v-if="errors && errors.acronym" class="text-red-500 text-sm mt-1">{{ errors.acronym }}</div>
            </div>
            <div class="mb-4">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea 
                id="description" 
                v-model="form.description" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows="3"
              ></textarea>
              <div v-if="errors && errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</div>
            </div>
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="isCreateModalOpen = false" 
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="form.processing" 
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ form.processing ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>
  
      <!-- Edit Modal -->
      <Modal :show="isEditModalOpen" @close="isEditModalOpen = false">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Edit College</h2>
          <form @submit.prevent="handleEditSubmit">
            <div class="mb-4">
              <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input 
                type="text" 
                id="edit-name" 
                v-model="editForm.name" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <div v-if="editErrors && editErrors.acronym" class="text-red-500 text-sm mt-1">{{ editErrors.acronym }}</div>
            </div>
            <div class="mb-4">
              <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea 
                id="edit-description" 
                v-model="editForm.description" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows="3"
              ></textarea>
              <div v-if="editErrors && editErrors.description" class="text-red-500 text-sm mt-1">{{ editErrors.description }}</div>
            </div>
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="isEditModalOpen = false" 
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="editForm.processing" 
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>
  
      <!-- Delete Confirmation Modal -->
      <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Delete College</h2>
          <p class="mb-4 text-gray-700">
            Are you sure you want to delete <span class="font-medium">{{ collegeToDelete ? collegeToDelete.name : '' }}</span>? 
            This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="isDeleteModalOpen = false" 
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Cancel
            </button>
            <button 
              type="button" 
              @click="handleDelete" 
              :disabled="deleteProcessing" 
              class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
            >
              {{ deleteProcessing ? 'Deleting...' : 'Delete' }}
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
<template>
  <div>
    <AuthenticatedLayout title="Student Organizations">
      
        <template #header>
          <!-- Color Banner -->
          <div class="flex w-full mb-4 overflow-hidden rounded-lg shadow-lg">
            <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
            <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
            <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
            <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
          </div>

          <div class="flex justify-between items-center">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Student Organizations
          </h2>
          <button
            type="button"
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 transition ease-in-out duration-150 shadow-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Organization
          </button>
        </div>
        </template>
          <!-- Alert Messages -->
          <div v-if="$page.props.flash && $page.props.flash.message" 
               class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" 
               role="alert">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm">{{ $page.props.flash.message }}</p>
              </div>
            </div>
          </div>

          <div v-if="$page.props.flash && $page.props.flash.error" 
               class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" 
               role="alert">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm">{{ $page.props.flash.error }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900">
                  Student Organizations by College
                </h3>
                <div class="text-sm text-gray-500">
                  {{ getTotalOrganizationsCount() }} Total Organizations
                </div>
              </div>

              <!-- Colleges Accordion -->
              <div class="space-y-3">
                <div v-for="college in colleges" :key="college.id" class="border rounded-md overflow-hidden shadow-sm hover:shadow transition-shadow duration-200" data-college-accordion>
                  <div 
                    @click="toggleCollege(college.id, $event)"
                    class="flex justify-between items-center p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-150"
                  >
                    <div class="flex items-center">
                      
                      <div>
                        <span class="text-lg font-medium text-gray-900">{{ college.acronym }}</span>
                        <span class="ml-2 text-sm text-gray-600">{{ college.name }}</span>
                      </div>
                    </div>
                    <div class="flex items-center">
                      <span class="mr-2 text-sm font-medium px-2 py-1 rounded-full" 
                            :class="college.student_orgs.length > 0 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'">
                        {{ college.student_orgs.length }} Organizations
                      </span>
                      <svg
                        :class="{'transform rotate-180': openColleges.includes(college.id)}"
                        class="w-5 h-5 transition-transform text-gray-500"
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

                  <!-- Student Organizations List -->
                  <div v-if="openColleges.includes(college.id)" 
                      class="p-4 divide-y divide-gray-100 bg-white transition-all duration-300 ease-in-out">
                    <div v-if="college.student_orgs.length === 0" class="text-center text-gray-500 py-8">
                      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <p class="mt-2">No organizations found for this college</p>
                      <button
                        @click="openCreateModalForCollege(college.id)"
                        class="mt-3 inline-flex items-center px-3 py-1.5 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
                      >
                        Add Organization
                      </button>
                    </div>
                    <div v-else class="overflow-x-auto -mx-4 sm:-mx-0">
                      <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                          <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Organization
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Acronym
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Actions
                            </th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-for="org in college.student_orgs" :key="org.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="flex items-center">
                                <div v-if="org.logo_path" class="flex-shrink-0 h-10 w-10">
                                  <img class="h-10 w-10 rounded-full object-cover border border-gray-200" :src="'/storage/' + org.logo_path" alt="" />
                                </div>
                                <div v-else class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-500">
                                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                  </svg>
                                </div>
                                <div class="ml-4">
                                  <div class="text-sm font-medium text-gray-900">
                                    {{ org.name }}
                                  </div>
                                  <div v-if="org.description" class="text-xs text-gray-500 max-w-md truncate">
                                    {{ org.description }}
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="text-sm text-gray-900">{{ org.acronym || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <span
                                :class="[
                                  'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                  org.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]"
                              >
                                {{ org.status }}
                              </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                              <button
                                @click="openEditModal(org)"
                                class="inline-flex items-center text-blue-500 hover:text-blue-700 mr-3"
                              >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                              </button>
                              <button
                                @click="confirmDelete(org)"
                                class="inline-flex items-center text-red-500 hover:text-red-700"
                              >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                              </button>
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
        
      

      <!-- Create/Edit Modal -->
      <Modal :show="showModal" @close="closeModal" maxWidth="md">
        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-medium text-gray-900">
              {{ editMode ? 'Edit Student Organization' : 'Add New Student Organization' }}
            </h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="editMode ? updateOrg() : createOrg()" class="mt-6 space-y-6" enctype="multipart/form-data">
            <!-- College Select -->
            <div>
              <InputLabel for="college_id" value="College" />
              <select
                id="college_id"
                v-model="form.college_id"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option :value="null" disabled>Select a College</option>
                <option v-for="college in colleges" :key="college.id" :value="college.id">
                  {{ college.acronym }} - {{ college.name }}
                </option>
              </select>
              <InputError :message="form.errors.college_id" class="mt-2" />
            </div>

            <!-- Organization Name -->
            <div>
              <InputLabel for="name" value="Organization Name" />
              <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Organization Acronym -->
              <div>
                <InputLabel for="acronym" value="Acronym (Optional)" />
                <TextInput
                  id="acronym"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.acronym"
                />
                <InputError :message="form.errors.acronym" class="mt-2" />
              </div>

              <!-- Organization Status -->
              <div>
                <InputLabel for="status" value="Status" />
                <div class="mt-1 flex items-center space-x-4">
                  <label class="inline-flex items-center">
                    <input type="radio" v-model="form.status" value="active" class="form-radio text-blue-500 focus:ring-blue-500" />
                    <span class="ml-2 text-gray-700">Active</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input type="radio" v-model="form.status" value="inactive" class="form-radio text-red-500 focus:ring-red-500" />
                    <span class="ml-2 text-gray-700">Inactive</span>
                  </label>
                </div>
                <InputError :message="form.errors.status" class="mt-2" />
              </div>
            </div>

            <!-- Organization Description -->
            <div>
              <InputLabel for="description" value="Description (Optional)" />
              <textarea
                id="description"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                v-model="form.description"
                rows="3"
              ></textarea>
              <InputError :message="form.errors.description" class="mt-2" />
            </div>

            <!-- Organization Logo -->
            <div>
              <InputLabel for="logo" value="Logo (Optional)" />
              <div class="mt-1 flex items-center">
                <div v-if="editMode && currentOrg.logo_path" class="mr-4">
                  <img class="h-16 w-16 object-cover rounded-full border border-gray-200" :src="'/storage/' + currentOrg.logo_path" alt="Current logo" />
                </div>
                <div class="flex-1">
                  <input
                    type="file"
                    id="logo"
                    @input="form.logo = $event.target.files[0]"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    accept="image/*"
                  />
                  <InputError :message="form.errors.logo" class="mt-2" />
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200">
              <SecondaryButton @click="closeModal" class="mr-3" type="button">Cancel</SecondaryButton>
              <PrimaryButton 
                :class="{ 'opacity-25': form.processing }" 
                :disabled="form.processing"
                class="bg-blue-500 hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700"
              >
                {{ editMode ? 'Update Organization' : 'Create Organization' }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Delete Confirmation Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal" maxWidth="sm">
        <div class="p-6">
          <div class="mb-5 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-500">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">
              Delete Student Organization
            </h3>
            <p class="mt-2 text-sm text-gray-600">
              Are you sure you want to delete "{{ currentOrg.name }}"? This action cannot be undone.
            </p>
          </div>

          <div class="mt-6 flex justify-center space-x-4">
            <SecondaryButton @click="closeDeleteModal" type="button">
              Cancel
            </SecondaryButton>
            <DangerButton 
              @click="deleteOrg" 
              :class="{ 'opacity-25': deleting }" 
              :disabled="deleting"
              class="bg-red-500 hover:bg-red-600 focus:bg-red-600"
            >
              Delete Organization
            </DangerButton>
          </div>
        </div>
      </Modal>
    </AuthenticatedLayout>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { useForm } from '@inertiajs/vue3';

export default {
  components: {
    AuthenticatedLayout,
    Modal,
    InputLabel,
    TextInput,
    InputError,
    PrimaryButton,
    SecondaryButton,
    DangerButton
  },
  props: {
    colleges: Array
  },
  data() {
    return {
      openColleges: [],
      showModal: false,
      showDeleteModal: false,
      editMode: false,
      currentOrg: {},
      deleting: false,
      clickOutsideHandler: null,
      form: useForm({
        college_id: null,
        name: '',
        acronym: '',
        description: '',
        logo: null,
        status: 'active'
      })
    };
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
    openCreateModal() {
      this.editMode = false;
      this.form.reset();
      this.form.clearErrors();
      this.showModal = true;
    },
    openCreateModalForCollege(collegeId) {
      this.editMode = false;
      this.form.reset();
      this.form.clearErrors();
      this.form.college_id = collegeId;
      this.showModal = true;
    },
    openEditModal(org) {
      this.editMode = true;
      this.currentOrg = org;
      this.form.clearErrors();
      
      this.form.college_id = org.college_id;
      this.form.name = org.name;
      this.form.acronym = org.acronym || '';
      this.form.description = org.description || '';
      this.form.logo = null; // Clear previous file
      this.form.status = org.status;
      
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      setTimeout(() => {
        this.editMode = false;
        this.currentOrg = {};
        this.form.reset();
        this.form.clearErrors();
      }, 300);
    },
    createOrg() {
      this.form.post(route('admin.student-orgs.store'), {
        preserveScroll: true,
        onSuccess: () => this.closeModal()
      });
    },
    updateOrg() {
  this.form.post(route('admin.student-orgs.update', this.currentOrg.id), {
    preserveScroll: true,
    onSuccess: () => this.closeModal()
  });
},
    confirmDelete(org) {
      this.currentOrg = org;
      this.showDeleteModal = true;
    },
    closeDeleteModal() {
      this.showDeleteModal = false;
      setTimeout(() => {
        this.currentOrg = {};
      }, 300);
    },
    deleteOrg() {
      this.deleting = true;
      this.$inertia.delete(route('admin.student-orgs.destroy', this.currentOrg.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.closeDeleteModal();
          this.deleting = false;
        },
        onError: () => {
          this.deleting = false;
        }
      });
    },
    getTotalOrganizationsCount() {
      return this.colleges.reduce((total, college) => total + college.student_orgs.length, 0);
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
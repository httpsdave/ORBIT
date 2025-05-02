<template>
  <div>
      <AuthenticatedLayout title="Student Organizations">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Manage Student Organizations
        </h2>
      </template>

      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900">
                  Student Organizations by College
                </h3>
                <button
                  type="button"
                  @click="openCreateModal"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  Add New Organization
                </button>
              </div>

              <!-- Success Message -->
              <div v-if="$page.props.flash && $page.props.flash.message" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ $page.props.flash.message }}
              </div>

              <!-- Error Message -->
              <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ $page.props.flash.error }}
              </div>

              <!-- Colleges Accordion -->
              <div class="space-y-4">
                <div v-for="college in colleges" :key="college.id" class="border rounded-md">
                  <div 
                    @click="toggleCollege(college.id)"
                    class="flex justify-between items-center p-4 cursor-pointer bg-gray-50 hover:bg-gray-100"
                  >
                    <div class="flex items-center">
                      <span class="text-lg font-medium">{{ college.acronym }}</span>
                      <span class="ml-2 text-sm text-gray-600">{{ college.name }}</span>
                    </div>
                    <div class="flex items-center">
                      <span class="mr-2 text-sm text-gray-600">
                        {{ college.student_orgs.length }} Organizations
                      </span>
                      <svg
                        :class="{'transform rotate-180': openColleges.includes(college.id)}"
                        class="w-5 h-5 transition-transform"
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
                  <div v-if="openColleges.includes(college.id)" class="p-4">
                    <div v-if="college.student_orgs.length === 0" class="text-center text-gray-500 py-4">
                      No organizations found for this college
                    </div>
                    <div v-else class="overflow-x-auto">
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
                          <tr v-for="org in college.student_orgs" :key="org.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="flex items-center">
                                <div v-if="org.logo_path" class="flex-shrink-0 h-10 w-10">
                                  <img class="h-10 w-10 rounded-full object-cover" :src="'/storage/' + org.logo_path" alt="" />
                                </div>
                                <div class="ml-4">
                                  <div class="text-sm font-medium text-gray-900">
                                    {{ org.name }}
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
                                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                  org.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]"
                              >
                                {{ org.status }}
                              </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                              <button
                                @click="openEditModal(org)"
                                class="text-indigo-600 hover:text-indigo-900 mr-2"
                              >
                                Edit
                              </button>
                              <button
                                @click="confirmDelete(org)"
                                class="text-red-600 hover:text-red-900"
                              >
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
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="showModal" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900">
            {{ editMode ? 'Edit Student Organization' : 'Add New Student Organization' }}
          </h2>

          <form @submit.prevent="editMode ? updateOrg() : createOrg()" class="mt-6 space-y-6" enctype="multipart/form-data">
            <!-- College Select -->
            <div>
              <InputLabel for="college_id" value="College" />
              <select
                id="college_id"
                v-model="form.college_id"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
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

            <!-- Organization Description -->
            <div>
              <InputLabel for="description" value="Description (Optional)" />
              <textarea
                id="description"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                v-model="form.description"
                rows="3"
              ></textarea>
              <InputError :message="form.errors.description" class="mt-2" />
            </div>

            <!-- Organization Logo -->
            <div>
              <InputLabel for="logo" value="Logo (Optional)" />
              <input
                type="file"
                id="logo"
                @input="form.logo = $event.target.files[0]"
                class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50"
                accept="image/*"
              />
              <InputError :message="form.errors.logo" class="mt-2" />
              <div v-if="editMode && currentOrg.logo_path" class="mt-2">
                <p class="text-sm text-gray-600">Current logo:</p>
                <img class="h-16 w-16 object-cover rounded" :src="'/storage/' + currentOrg.logo_path" alt="Current logo" />
              </div>
            </div>

            <!-- Organization Status -->
            <div>
              <InputLabel for="status" value="Status" />
              <select
                id="status"
                v-model="form.status"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <InputError :message="form.errors.status" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
              <SecondaryButton @click="closeModal" class="mr-2">Cancel</SecondaryButton>
              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ editMode ? 'Update' : 'Create' }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Delete Confirmation Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">
            Delete Student Organization
          </h2>
          <p class="mb-6 text-gray-600">
            Are you sure you want to delete "{{ currentOrg.name }}"? This action cannot be undone.
          </p>

          <div class="flex justify-end">
            <SecondaryButton @click="closeDeleteModal" class="mr-2">Cancel</SecondaryButton>
            <DangerButton @click="deleteOrg" :class="{ 'opacity-25': deleting }" :disabled="deleting">
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
  methods: {
    toggleCollege(collegeId) {
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
      // For file uploads with PUT, we need to use FormData directly
      this.form.transform((data) => {
        const formData = new FormData();
        
        // Add all form fields to the FormData
        for (const key in data) {
          if (key !== '_method') { // Skip the _method field as we'll handle it separately
            if (data[key] !== null && data[key] !== undefined) {
              formData.append(key, data[key]);
            }
          }
        }
        
        // Add the method spoofing field - this is critical for Laravel to recognize it as PUT
        formData.append('_method', 'PUT');
        
        return formData;
      }).post(route('admin.student-orgs.update', this.currentOrg.id), {
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
    }
  }
};
</script>
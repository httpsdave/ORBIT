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
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Manage Student Organizations
          </h2>
          <button
            type="button"
            @click="openUserSelectionModalForNewOrg"
            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Organization
          </button>
        </div>
        </template>
          <!-- Alert Messages -->
          <div v-if="$page.props.flash && $page.props.flash.message" 
               class="mb-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 rounded shadow-sm" 
               role="alert">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm">{{ $page.props.flash.message }}</p>
              </div>
            </div>
          </div>

          <div v-if="$page.props.flash && $page.props.flash.error" 
               class="mb-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4 rounded shadow-sm" 
               role="alert">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm">{{ $page.props.flash.error }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
              <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                  Student Organizations by College
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ getTotalUsersCount() }} Total Organizations
                </div>
              </div>

              <!-- Colleges Accordion -->
              <div class="space-y-3">
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
              </div>
            </div>
          </div>
        
      

      <!-- User Selection Modal -->
      <Modal :show="showUserSelectionModal" @close="closeUserSelectionModal" maxWidth="lg">
        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
              Select Organization to Add to {{ selectedCollegeId ? selectedCollegeName : 'a College' }}
            </h2>
            <button @click="closeUserSelectionModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Search Input -->
          <div class="mb-4">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search organizations by name or email..."
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- College Selection (always show to allow changing selection) -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select College</label>
            <select
              v-model="selectedCollegeId"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option :value="null" disabled>Choose a college...</option>
              <option v-for="college in colleges" :key="college.id" :value="college.id">
                {{ college.acronym }} - {{ college.name }}
              </option>
            </select>
          </div>

          <!-- Users List -->
          <div class="max-h-96 overflow-y-auto">
            <div v-if="filteredUsers.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
              <p>No organizations found.</p>
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="user in filteredUsers"
                :key="user.id"
                @click="toggleUserSelection(user)"
                :class="['flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors', selectedUsers.some(u => u.id === user.id) ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-400 dark:border-blue-500' : '']"
              >
                <input type="checkbox" :checked="selectedUsers.some(u => u.id === user.id)" @change.stop="toggleUserSelection(user)" class="mr-3" />
                <!-- User Avatar -->
                <div class="flex-shrink-0 mr-3">
                  <div v-if="user.profile_photo_url" class="h-10 w-10">
                    <img class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="user.profile_photo_url" alt="" />
                  </div>
                  <div v-else class="h-10 w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'O' }}
                  </div>
                </div>
                <!-- User Info -->
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ user.name || 'Unknown Organization' }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.email || 'No email' }}</div>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 ml-3">
                  {{ user.role ? user.role.name : 'No role' }}
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <SecondaryButton @click="closeUserSelectionModal" class="mr-3" type="button">Cancel</SecondaryButton>
            <button
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-md hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="selectedUsers.length === 0"
              @click="openConfirmModal"
              type="button"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              Add Selected Organizations
            </button>
          </div>
        </div>
      </Modal>

      <!-- Confirmation Modal -->
      <Modal :show="showConfirmModal" @close="closeConfirmModal" maxWidth="sm">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Add Organizations</h2>
          <p class="mb-2 text-gray-600 dark:text-gray-400">Are you sure you want to add the following organizations to <strong>{{ selectedCollegeName }}</strong>?</p>
          <div class="max-h-64 overflow-y-auto space-y-3">
            <div v-for="user in selectedUsers" :key="user.id" class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
              <!-- User Avatar -->
              <div class="flex-shrink-0 mr-3">
                <div v-if="user.profile_photo_url" class="h-10 w-10">
                  <img class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="user.profile_photo_url" alt="" />
                </div>
                <div v-else class="h-10 w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner">
                  {{ user.name ? user.name.charAt(0).toUpperCase() : 'O' }}
                </div>
              </div>
              <!-- User Info -->
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ user.name || 'Unknown Organization' }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.email || 'No email' }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">{{ user.role ? user.role.name : 'No role' }}</div>
              </div>
            </div>
          </div>
          <div class="flex justify-end space-x-2 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <SecondaryButton @click="closeConfirmModal">Cancel</SecondaryButton>
            <button
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-md hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group"
              @click="confirmAssignUsers"
              type="button"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              Confirm
            </button>
          </div>
        </div>
      </Modal>

      <!-- Remove Confirmation Modal -->
      <Modal :show="showRemoveConfirmModal" @close="closeRemoveConfirmModal" maxWidth="sm">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Remove Organization</h2>
          <p class="mb-4 text-gray-600 dark:text-gray-400">Are you sure you want to remove the following organization from <strong>{{ selectedCollegeName }}</strong>?</p>
          <div v-if="orgToRemove" class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 mb-4">
            <div class="flex-shrink-0 mr-3">
              <div v-if="orgToRemove.profile_photo_url" class="h-10 w-10">
                <img class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" :src="orgToRemove.profile_photo_url" alt="" />
              </div>
              <div v-else class="h-10 w-10 bg-gradient-to-br from-blue-500 to-green-400 rounded-full flex items-center justify-center text-white font-medium shadow-inner">
                {{ orgToRemove.name ? orgToRemove.name.charAt(0).toUpperCase() : 'O' }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ orgToRemove.name || 'Unknown Organization' }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ orgToRemove.email || 'No email' }}</div>
              <div class="text-xs text-gray-400 dark:text-gray-500">{{ orgToRemove.role ? orgToRemove.role.name : 'No role' }}</div>
            </div>
          </div>
          <div class="flex justify-end space-x-2 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <SecondaryButton @click="closeRemoveConfirmModal">Cancel</SecondaryButton>
            <button
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl shadow-md hover:from-red-400 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group"
              @click="confirmRemoveOrg"
              type="button"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
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
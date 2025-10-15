<template>
  <AuthenticatedLayout :user="$page.props.auth.user">
    <Head title="Frequently Asked Questions" />

    <div class="py-8 min-h-screen" :class="isDarkMode ? 'bg-gray-900' : 'bg-gray-50'">
      <!-- Animated colored banner -->
      <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
        <div class="w-1/4 h-1.5 bg-blue-500"></div>
        <div class="w-1/4 h-1.5 bg-green-500"></div>
        <div class="w-1/4 h-1.5 bg-yellow-500"></div>
        <div class="w-1/4 h-1.5 bg-red-500"></div>
      </div>

      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
          <div class="inline-flex items-center justify-center p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" viewBox="0 -960 960 960" fill="currentColor">
              <path d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
            </svg>
          </div>
          <h1 class="text-3xl sm:text-4xl font-bold mb-2" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
            Frequently Asked Questions
          </h1>
          <p class="text-base sm:text-lg" :class="isDarkMode ? 'text-gray-400' : 'text-gray-600'">
            Find answers to common questions about using the ORBIT system
          </p>
        </div>

        <!-- Search Bar -->
        <div class="mb-8">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search FAQs..."
              class="w-full px-4 py-3 pl-12 border rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors duration-300"
              :class="isDarkMode 
                ? 'bg-gray-800 border-gray-600 text-white placeholder-gray-400' 
                : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'"
            />
            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Category Tabs -->
        <div class="mb-8">
          <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
            <button
              v-for="category in categories"
              :key="category"
              @click="selectedCategory = category"
              :class="[
                'px-3 py-1.5 sm:px-4 sm:py-2 rounded-full font-medium transition-all duration-300 text-xs sm:text-sm md:text-base whitespace-nowrap',
                selectedCategory === category
                  ? 'bg-blue-600 text-white shadow-md'
                  : isDarkMode
                    ? 'bg-gray-800 text-gray-300 hover:bg-gray-700'
                    : 'bg-white text-gray-700 hover:bg-gray-100'
              ]"
            >
              {{ category }}
            </button>
          </div>
        </div>

        <!-- FAQ Items -->
        <div class="space-y-4">
          <div
            v-for="(faq, index) in filteredFaqs"
            :key="index"
            class="rounded-lg shadow-sm overflow-hidden transition-all duration-300"
            :class="isDarkMode ? 'bg-gray-800 border border-gray-700' : 'bg-white border border-gray-200'"
          >
            <button
              @click="toggleFaq(index)"
              class="w-full px-4 sm:px-6 py-3 sm:py-4 text-left flex justify-between items-center hover:bg-opacity-50 transition-colors duration-300 focus:outline-none"
              :class="isDarkMode ? 'hover:bg-gray-700' : 'hover:bg-gray-50'"
            >
              <div class="flex items-start flex-1 min-w-0">
                <span class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full mr-3 sm:mr-4 flex-shrink-0 mt-0.5" :class="faq.category === 'General' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : faq.category === 'Applications' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : faq.category === 'File Management' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' : faq.category === 'Organizations' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </span>
                <span class="font-semibold text-base sm:text-lg pr-2" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                  {{ faq.question }}
                </span>
              </div>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 transition-transform duration-300 flex-shrink-0 ml-2"
                :class="[
                  openFaqs.includes(faqs.indexOf(faq)) ? 'transform rotate-180' : '',
                  isDarkMode ? 'text-gray-400' : 'text-gray-500'
                ]"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <transition
              enter-active-class="transition-all duration-400 ease-out"
              enter-from-class="opacity-0 transform -translate-y-2"
              enter-to-class="opacity-100 transform translate-y-0"
              leave-active-class="transition-all duration-300 ease-in"
              leave-from-class="opacity-100 transform translate-y-0"
              leave-to-class="opacity-0 transform -translate-y-2"
            >
              <div v-show="openFaqs.includes(faqs.indexOf(faq))" class="px-4 sm:px-6 pb-4 sm:pb-6 overflow-hidden">
                <div class="pt-4 border-t" :class="isDarkMode ? 'border-gray-700' : 'border-gray-200'">
                  <div class="prose prose-sm sm:prose-base max-w-none leading-relaxed" :class="isDarkMode ? 'prose-invert' : ''" v-html="faq.answer"></div>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <!-- No Results -->
        <div v-if="filteredFaqs.length === 0" class="text-center py-12">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" :class="isDarkMode ? 'text-gray-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-lg font-medium mb-2" :class="isDarkMode ? 'text-gray-400' : 'text-gray-600'">
            No FAQs found
          </p>
          <p class="text-sm" :class="isDarkMode ? 'text-gray-500' : 'text-gray-500'">
            Try adjusting your search or category filter
          </p>
        </div>

        <!-- Contact Support -->
        <div class="mt-12 p-6 rounded-lg" :class="isDarkMode ? 'bg-gray-800 border border-gray-700' : 'bg-blue-50 border border-blue-100'">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold mb-2" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                Still have questions?
              </h3>
              <p class="mb-3" :class="isDarkMode ? 'text-gray-400' : 'text-gray-700'">
                If you couldn't find the answer you're looking for, please contact the Office of Student Affairs and Services (OSAS) for assistance.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { useTheme } from '@/Composables/useTheme';
import { usePage } from '@inertiajs/vue3';

const { isDark: isDarkMode } = useTheme();
const page = usePage();

// Check if user is admin
const isAdmin = computed(() => {
  const user = page.props.auth.user;
  return user && user.role && user.role.slug === 'admin';
});

const searchQuery = ref('');
const selectedCategory = ref('All');
const openFaqs = ref([]);

// Filter categories based on user role
const categories = computed(() => {
  const baseCategories = ['All', 'General', 'Applications', 'File Management', 'Organizations'];
  if (isAdmin.value) {
    baseCategories.push('Admin');
  }
  return baseCategories;
});

const faqs = [
  // General Questions
  {
    category: 'General',
    question: 'What is ORBIT and what does it do?',
    answer: '<p><strong>ORBIT (Organization Registration and Business Intelligence Tool)</strong> is a comprehensive system designed to streamline the management of student organizations and their applications. It provides tools for:</p><ul><li>Submitting and managing organization applications</li><li>Tracking application statuses</li><li>Managing members and officers</li><li>Creating and monitoring Plans of Activities</li><li>Organizing events through a shared calendar</li><li>Archiving records at the end of each academic year</li></ul>'
  },
  {
    category: 'General',
    question: 'How do I access different features based on my role?',
    answer: '<p>ORBIT has two main user roles:</p><ul><li><strong>Regular Users (Student Organizations):</strong> Can create applications, manage their own organization data, view colleges and other organizations, submit activity reports, and access their dashboard.</li><li><strong>Administrators (OSAS Staff):</strong> Have access to all features including user management, reviewing and approving applications, managing colleges and organizations, viewing all activity reports, and archiving records.</li></ul><p>Your role determines which menu items and features you can access from the sidebar navigation.</p>'
  },
  {
    category: 'General',
    question: 'What are the different themes available and how do I change them?',
    answer: '<p>ORBIT offers three theme options:</p><ul><li><strong>Light Mode:</strong> Bright background with dark text</li><li><strong>Dark Mode:</strong> Dark background with light text (easier on eyes in low-light conditions)</li><li><strong>System/Auto:</strong> Automatically matches your device\'s system preferences</li></ul><p>To change themes, click on your profile photo/avatar in the top-right corner, then use the theme toggle switch in the dropdown menu. Your preference is saved automatically.</p>'
  },

  // Applications
  {
    category: 'Applications',
    question: 'What are the different application statuses and what do they mean?',
    answer: '<p>Applications can have the following statuses:</p><ul><li><strong>Pending:</strong> Application has been submitted and is waiting for admin review. You can still edit it.</li><li><strong>Approved:</strong> Your application has been approved by OSAS. You cannot edit approved applications.</li><li><strong>Rejected:</strong> Application was not approved. Check feedback for reasons. You can edit and resubmit rejected applications.</li><li><strong>Archived:</strong> Application has been archived (typically at end of academic year).</li></ul>'
  },
  {
    category: 'Applications',
    question: 'Can I edit my application after submitting it?',
    answer: '<p>This depends on the application status:</p><ul><li><strong>Pending:</strong> Yes, you can still edit your application while it is pending review.</li><li><strong>Approved:</strong> No, you cannot edit approved applications. Contact OSAS if changes are needed.</li><li><strong>Rejected:</strong> Yes, you can edit and resubmit rejected applications based on admin feedback.</li></ul>'
  },
  {
    category: 'Applications',
    question: 'When should I submit a Recognition Form vs a Renewal Form?',
    answer: '<p>The type of form you submit depends on your organization\'s status:</p><p><strong>Recognition Form (LSPU-OSAS-SF-001):</strong></p><ul><li>For <strong>newly established organizations</strong> that have never been registered before</li><li>First-time registration with OSAS</li><li>Organizations applying for official recognition for the first time</li></ul><p><strong>Renewal Form (LSPU-OSAS-SF-002):</strong></p><ul><li>For organizations that have been <strong>previously recognized or accredited</strong></li><li>Annual renewal process for existing organizations</li><li>Maintains active status for organizations already in the system</li></ul><p><em>Important:</em> If your organization was recognized in a previous academic year, you only need to submit a renewal form to continue operations. Recognition is a one-time process, while renewal is required periodically (typically annually) to maintain active status.</p>'
  },

  {
    category: 'Applications',
    question: 'How do I view feedback on my application?',
    answer: '<p>To view admin feedback on your application:</p><ol><li>Go to the <strong>Applications</strong> page from your dashboard</li><li>Click on the application you want to review</li><li>Look for the <strong>"Feedback"</strong> button or tab</li><li>Admin comments and suggestions will be displayed there</li></ol><p>Feedback is especially important for rejected applications, as it explains what needs to be corrected before resubmission.</p>'
  },
  {
    category: 'Applications',
    question: 'What types of forms can I create in ORBIT?',
    answer: '<p>ORBIT supports the following official LSPU-OSAS forms:</p><ul><li><strong>LSPU-OSAS-SF-001:</strong> Recognition Form - For new organization recognition</li><li><strong>LSPU-OSAS-SF-002:</strong> Renewal Form - For annual organization renewal</li><li><strong>LSPU-OSAS-SF-003:</strong> Commitment Form</li><li><strong>LSPU-OSAS-SF-004:</strong> Plan of Activities - Semester/annual activity planning</li><li><strong>LSPU-OSAS-SF-005:</strong> List of Members - Organization membership roster</li><li><strong>List of Officers</strong> - Organization officers roster</li><li><strong>LSPU-OSAS-SF-006:</strong> Certification Form</li><li><strong>LSPU-OSAS-SF-009:</strong> Student Activity Attendance Form - Event attendance tracking</li><li><strong>Evaluation Form</strong> - Post-event evaluation</li></ul><p>Each form type has specific fields and requirements tailored to its purpose.</p>'
  },

  // File Management
  {
    category: 'File Management',
    question: 'What are the file size limits for uploads?',
    answer: '<p>ORBIT has the following file upload limits:</p><ul><li><strong>Document uploads (PDFs, DOCX, etc.):</strong> Maximum 10 MB per file</li><li><strong>Profile photos:</strong> Maximum 2 MB</li><li><strong>Activity report attachments:</strong> Maximum 10 MB per file</li><li><strong>Signed documents:</strong> Maximum 15 MB (typically for scanned PDFs)</li></ul><p><em>Tip:</em> If your file exceeds the limit, try compressing it or splitting it into multiple files. For PDFs, use online compression tools to reduce file size without losing quality.</p>'
  },
  {
    category: 'File Management',
    question: 'What file formats are accepted?',
    answer: '<p>Accepted file formats vary by upload type:</p><ul><li><strong>Documents:</strong> PDF (.pdf), Microsoft Word (.doc, .docx)</li><li><strong>Images:</strong> JPEG (.jpg, .jpeg), PNG (.png), GIF (.gif)</li><li><strong>Signed Documents:</strong> Primarily PDF (.pdf) for best compatibility</li><li><strong>Activity Reports:</strong> PDF (.pdf) preferred, DOCX accepted</li></ul><p><strong>Best Practice:</strong> For official documents and submissions, always use PDF format to ensure formatting is preserved and documents cannot be easily modified.</p>'
  },
  {
    category: 'File Management',
    question: 'How do I upload a signed document?',
    answer: '<p>To upload a signed document:</p><ol><li>Navigate to the application that requires a signed document</li><li>Click the <strong>"Document"</strong> button or tab</li><li>Click <strong>"Upload Document"</strong> and select your signed PDF file</li><li>Confirm the upload</li></ol><p><em>Important:</em> Make sure your document is properly signed with all required signatures before uploading.</p>'
  },
  {
    category: 'File Management',
    question: 'Can I delete or replace an uploaded document?',
    answer: '<p><strong>Before Approval:</strong></p><ul><li>Yes, you can delete and re-upload documents</li><li>Use the "Delete Document" button in the document view</li><li>Upload the new version immediately after deletion</li></ul><p><strong>After Approval:</strong></p><ul><li>Contact OSAS administrators for document changes</li><li>Approved documents typically cannot be modified to maintain record integrity</li></ul><p><em>Tip:</em> Always double-check your documents before approval to avoid needing replacements.</p>'
  },

  // Organizations
  {
    category: 'Organizations',
    question: 'What is the difference between College-Affiliated and Non-College Affiliated organizations?',
    answer: '<p><strong>College-Affiliated Organizations:</strong></p><ul><li>Associated with a specific college (e.g., College of Engineering, College of Arts & Sciences)</li><li>Typically department-based or program-specific</li><li>Members are usually from the same college</li></ul><p><strong>Non-College Affiliated Organizations:</strong></p><ul><li>University-wide organizations open to students from all colleges</li><li>Interest-based or cause-based groups</li><li>Examples: student government, special interest clubs, honor societies</li></ul><p>The classification affects how organizations are listed and managed in the system.</p>'
  },
  {
    category: 'Organizations',
    question: 'What are Parent and Sub-Organizations?',
    answer: '<p><strong>Parent Organizations</strong> are main organizations that oversee one or more sub-organizations. <strong>Sub-Organizations</strong> are affiliated chapters or branches under a parent organization.</p><p><strong>Example:</strong> A university-wide student council (parent) may have college-specific chapters (sub-organizations).</p><p><strong>Important Rules:</strong></p><ul><li>Organizations cannot be both parent and sub at the same time</li><li>Parent and sub-organizations must have compatible college affiliations (both same college OR both non-college)</li><li>Organizations with parent/sub relationships <strong>cannot change their college affiliation</strong> to maintain organizational structure integrity</li></ul>'
  },
  {
    category: 'Organizations',
    question: 'How do I manage my organization\'s members and officers?',
    answer: '<p>To manage members and officers:</p><ol><li>Go to your dashboard or application</li><li>Look for <strong>"Members & Officers"</strong> section</li><li>Add new members by clicking "Add Member" button</li><li>Designate officers by setting their positions (President, Vice President, etc.)</li><li>Update information as needed throughout the year</li><li>Remove members who are no longer active</li></ol><p><strong>Admin users</strong> can access a comprehensive Members & Officers page from the sidebar to view all organization rosters across the university.</p>'
  },
  {
    category: 'Organizations',
    question: 'How does the organization status toggle work?',
    answer: '<p>Organization status indicates whether an organization is currently active:</p><ul><li><strong>Active:</strong> Organization is operating and can submit applications and reports (shown with green indicator)</li><li><strong>Inactive:</strong> Organization is dormant or suspended (shown with gray indicator)</li></ul><p><strong>For Administrators:</strong></p><ul><li>Use the status toggle switch in the Organizations management page</li><li>Click the toggle to instantly change status</li><li>Changes are reflected immediately across the system</li></ul><p><em>Note:</em> Only administrators can change organization status. Inactive organizations can still be viewed but have limited functionality.</p>'
  },

  // Admin Features
  {
    category: 'Admin',
    question: 'How do I review and approve applications as an admin?',
    answer: '<p>As an administrator, follow these steps:</p><ol><li>Go to <strong>"All Applications"</strong> from the sidebar</li><li>Click on the application you want to review</li><li>Review all sections carefully (organization details, members, plans, etc.)</li><li>Check for completeness and accuracy</li><li>Click <strong>"Update Status"</strong> button</li><li>Select the appropriate status:<ul><li><strong>Approved</strong> - if everything is in order</li><li><strong>Rejected</strong> - if application doesn\'t meet requirements (organization can edit and resubmit)</li></ul></li><li>Add feedback comments explaining your decision</li><li>Click "Save" to update</li></ol><p>The organization will be notified of the status change through their dashboard.</p>'
  },
  {
    category: 'Admin',
    question: 'How does the Archive Management system work?',
    answer: '<p>The Archive system helps manage academic year transitions:</p><p><strong>End of Year Process:</strong></p><ol><li>Go to <strong>"Archive Management"</strong> in the admin sidebar</li><li>Review current year applications and data</li><li>Click <strong>"End Academic Year"</strong> button</li><li>Confirm the action (this archives all current applications)</li><li>All applications are moved to archived status</li><li>Organizations can start fresh for the new academic year</li></ol><p><strong>Restoring Archives:</strong></p><ul><li>View archived applications in the Archive Management page</li><li>Use the "Restore" button to bring an application back to active status</li><li>Useful if an archive was done by mistake or data needs to be referenced</li></ul>'
  },
  {
    category: 'Admin',
    question: 'How do I add or remove organizations from colleges?',
    answer: '<p>To manage college-organization assignments:</p><ol><li>Navigate to <strong>"Organizations"</strong> in the admin sidebar</li><li>Find the organization you want to assign/remove</li><li><strong>To Assign:</strong><ul><li>Click the "Assign to College" button (plus icon)</li><li>Select the target college from the dropdown</li><li>Confirm the assignment</li></ul></li><li><strong>To Remove:</strong><ul><li>Click the "Remove from College" button (trash icon)</li><li>Confirm the removal</li></ul></li></ol><p><strong>Important:</strong> Organizations that are parent or sub-organizations cannot change their college affiliation. This restriction maintains organizational hierarchy integrity.</p>'
  },
  {
    category: 'Admin',
    question: 'What is the difference between Users and Organizations management?',
    answer: '<p><strong>Users Management:</strong></p><ul><li>Manage system accounts (login credentials)</li><li>Assign roles (Admin or Regular User)</li><li>Control access permissions</li><li>Reset passwords and manage user profiles</li></ul><p><strong>Organizations Management:</strong></p><ul><li>Manage student organizations as entities</li><li>Assign organizations to colleges</li><li>Set parent/sub-organization relationships</li><li>Toggle organization active/inactive status</li><li>View organization applications and activities</li></ul><p><em>Note:</em> Each organization has an associated user account for system access. The user account is for login, while the organization profile is for institutional management.</p>'
  },
  {
    category: 'Admin',
    question: 'How do I create and manage notifications?',
    answer: '<p>Administrators can create system-wide notifications:</p><ol><li>Go to <strong>"Admin Dashboard"</strong></li><li>Look for the <strong>"Notifications"</strong> section</li><li>Click <strong>"Create Notification"</strong></li><li>Fill in the details:<ul><li>Title (clear and concise)</li><li>Message content (detailed information)</li><li>Priority level (Low, Medium, High)</li><li>Target audience (All users, specific colleges, specific organizations)</li></ul></li><li>Set active status (turn on/off)</li><li>Save the notification</li></ol><p>Users will see new notifications in the notification bell icon dropdown. You can edit, toggle active status, or delete notifications at any time.</p>'
  },

  // Activity Reports & Calendar
  {
    category: 'Applications',
    question: 'How do I submit activity reports for my Plan of Activities?',
    answer: '<p>To submit an activity report:</p><ol><li>Navigate to your <strong>Plan of Activities</strong> application</li><li>Click on the <strong>"Reports"</strong> tab or button</li><li>Click <strong>"Upload Report"</strong></li><li>Fill in the report details:<ul><li>Activity name (must match one from your approved plan)</li><li>Report date</li><li>Description of how the activity went</li><li>Number of attendees</li></ul></li><li>Upload supporting documents (photos, attendance sheets, etc.)</li><li>Submit for admin review</li></ol><p>Admins will review your report and may provide feedback or request changes. Track report status in the Reports section.</p>'
  },
  {
    category: 'General',
    question: 'How does the shared Calendar work?',
    answer: '<p>The Calendar feature provides a centralized view of all university events:</p><p><strong>For Regular Users:</strong></p><ul><li>View all approved events across the university</li><li>See event details by clicking on events</li><li>Filter by college or organization</li><li>Export to personal calendar (if available)</li></ul><p><strong>For Administrators:</strong></p><ul><li>All viewing capabilities plus:</li><li>Create new events</li><li>Edit existing events</li><li>Cancel events if needed</li><li>Manage event conflicts and schedule coordination</li></ul><p><strong>Event badges:</strong> Blue badge indicates upcoming events, red badge indicates events happening today. Click the calendar icon in the header to access.</p>'
  },

  // Common Issues
  {
    category: 'General',
    question: 'Why am I getting a "419 Page Expired" error?',
    answer: '<p>This error typically occurs due to expired session tokens. To fix it:</p><ol><li>Try refreshing the page (F5 or Ctrl+R)</li><li>If that doesn\'t work, click the <strong>"Clear Cookies"</strong> link (if available) or visit <code>/clear-cookies</code> route</li><li>Log out and log back in</li><li>Clear your browser cache and cookies</li><li>Make sure cookies are enabled in your browser</li></ol><p>This error often happens when:</p><ul><li>Your session has expired due to inactivity</li><li>You have the same site open in multiple tabs</li><li>Your browser has corrupted session data</li></ul>'
  },
  {
    category: 'General',
    question: 'What should I do if my uploaded file is not showing?',
    answer: '<p>If your file upload doesn\'t appear:</p><ol><li><strong>Check file size:</strong> Ensure your file is within the size limit (typically 10 MB for documents, 2 MB for images)</li><li><strong>Check file format:</strong> Verify you\'re using an accepted format (PDF, DOCX, JPG, PNG)</li><li><strong>Refresh the page:</strong> Sometimes uploads succeed but the page needs refreshing</li><li><strong>Check internet connection:</strong> Unstable connection may interrupt uploads</li><li><strong>Try again:</strong> Re-upload the file</li><li><strong>Use a different browser:</strong> Some browsers handle uploads better than others</li></ol><p>If the problem persists, contact OSAS with details about the file type, size, and where you\'re trying to upload it.</p>'
  },
  {
    category: 'Applications',
    question: 'How do I export my application or report as PDF?',
    answer: '<p>ORBIT provides PDF export functionality for various documents:</p><ol><li>Open the application or report you want to export</li><li>Look for the <strong>"Export PDF"</strong> or download icon button</li><li>Select the specific form type you want to export:<ul><li>Full Application</li><li>Plan of Activities</li><li>Member/Officer List</li><li>Attendance Sheet</li><li>Evaluation Form</li><li>Certification</li></ul></li><li>The PDF will be generated and downloaded automatically</li><li>Check your browser\'s download folder</li></ol><p>PDFs are formatted according to official OSAS templates and are suitable for printing and official records.</p>'
  }
];

const filteredFaqs = computed(() => {
  let filtered = faqs;

  // Filter out Admin category for non-admin users
  if (!isAdmin.value) {
    filtered = filtered.filter(faq => faq.category !== 'Admin');
  }

  // Filter by category
  if (selectedCategory.value !== 'All') {
    filtered = filtered.filter(faq => faq.category === selectedCategory.value);
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(faq => 
      faq.question.toLowerCase().includes(query) ||
      faq.answer.toLowerCase().includes(query) ||
      faq.category.toLowerCase().includes(query)
    );
  }

  return filtered;
});

const toggleFaq = (index) => {
  const actualIndex = faqs.indexOf(filteredFaqs.value[index]);
  if (actualIndex === -1) return; // Safety check
  
  const idx = openFaqs.value.indexOf(actualIndex);
  if (idx === -1) {
    openFaqs.value.push(actualIndex);
  } else {
    openFaqs.value.splice(idx, 1);
  }
};
</script>

<style scoped>
/* Prose styling for better readability */
.prose {
  color: #374151;
  line-height: 1.75;
}

.prose p {
  margin-bottom: 1rem;
  font-size: 0.95rem;
  line-height: 1.7;
}

.prose strong {
  color: #111827;
  font-weight: 600;
}

.prose ul, .prose ol {
  margin-top: 0.75rem;
  margin-bottom: 0.75rem;
  padding-left: 1.5rem;
}

.prose li {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  line-height: 1.7;
}

.prose code {
  color: #1e40af;
  background-color: #eff6ff;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.875em;
}

/* Dark mode prose styling */
.prose-invert {
  color: #e5e7eb;
}

.prose-invert p {
  color: #e5e7eb;
  line-height: 1.7;
}

.prose-invert strong {
  color: #f9fafb;
  font-weight: 600;
}

.prose-invert ul, .prose-invert ol {
  color: #e5e7eb;
}

.prose-invert li {
  line-height: 1.7;
}

.prose-invert code {
  color: #93c5fd;
  background-color: #1f2937;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
}

/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.dark ::-webkit-scrollbar-thumb {
  background: #4b5563;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>

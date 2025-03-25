<template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h1 class="text-2xl font-bold mb-6">Generate PDF Form</h1>
            
            <form @submit.prevent="generatePdfForm">
              <div class="mb-6">
                <label for="template" class="block text-sm font-medium text-gray-700">Template</label>
                <select 
                  id="template" 
                  v-model="form.template" 
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  @change="loadTemplateFields"
                >
                  <option value="">Select a template</option>
                  <option v-for="(name, id) in templates" :key="id" :value="id">{{ name }}</option>
                </select>
              </div>
              
              <!-- Static fields for all templates -->
              <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label for="document_number" class="block text-sm font-medium text-gray-700">Document Number</label>
                  <input 
                    id="document_number" 
                    v-model="form.data.document_number" 
                    type="text" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                </div>
                
                <div>
                  <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                  <input 
                    id="date" 
                    v-model="form.data.date" 
                    type="date" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                </div>
              </div>
              
              <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label for="recipient_name" class="block text-sm font-medium text-gray-700">Recipient Name</label>
                  <input 
                    id="recipient_name" 
                    v-model="form.data.recipient_name" 
                    type="text" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                </div>
                
                <div>
                  <label for="recipient_address" class="block text-sm font-medium text-gray-700">Recipient Address</label>
                  <textarea 
                    id="recipient_address" 
                    v-model="form.data.recipient_address" 
                    rows="3" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  ></textarea>
                </div>
              </div>
              
              <!-- Dynamic fields based on selected template -->
              <div v-if="form.template && dynamicFields.length > 0" class="space-y-6 border-t pt-6 mt-6">
                <h2 class="text-lg font-medium">Template-Specific Fields</h2>
                <p class="text-sm text-gray-500">These fields will be editable in the generated PDF form.</p>
                
                <div v-for="field in dynamicFields" :key="field.name" class="mb-4">
                  <label :for="field.name" class="block text-sm font-medium text-gray-700">{{ field.label }}</label>
                  
                  <div class="mt-1">
                    <!-- Text input -->
                    <input 
                      v-if="field.type === 'text'" 
                      :id="field.name" 
                      v-model="form.data[field.name]" 
                      type="text" 
                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :placeholder="`Default value for ${field.label}`"
                    >
                    
                    <!-- Textarea -->
                    <textarea 
                      v-else-if="field.type === 'textarea'" 
                      :id="field.name" 
                      v-model="form.data[field.name]" 
                      rows="3" 
                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :placeholder="`Default value for ${field.label}`"
                    ></textarea>
                    
                    <!-- Checkbox -->
                    <div v-else-if="field.type === 'checkbox'" class="flex items-center">
                      <input 
                        :id="field.name" 
                        v-model="form.data[field.name]" 
                        type="checkbox" 
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                      >
                      <label :for="field.name" class="ml-2 block text-sm text-gray-900">
                        {{ field.checkboxLabel || 'Yes' }}
                      </label>
                    </div>
                    
                    <!-- Dropdown -->
                    <select 
                      v-else-if="field.type === 'dropdown'" 
                      :id="field.name" 
                      v-model="form.data[field.name]" 
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                      <option value="">Select an option</option>
                      <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="mt-6">
                <button 
                  type="submit" 
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  :disabled="processing || !form.template"
                >
                  {{ processing ? 'Generating PDF...' : 'Generate PDF Form' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent } from 'vue'
  import { router } from '@inertiajs/vue3'
  import {  useForm } from '@inertiajs/vue3'
  

  
  
  export default defineComponent({
    props: {
      templates: {
        type: Object,
        required: true
      }
    },
    
    data() {
      return {
        form: {
          template: '',
          data: {
            document_number: 'DOC-' + new Date().toISOString().slice(0, 10).replace(/-/g, ''),
            date: new Date().toISOString().slice(0, 10),
            recipient_name: '',
            recipient_address: ''
          }
        },
        dynamicFields: [],
        processing: false
      }
    },
    
    methods: {
      loadTemplateFields() {
        // Reset template-specific fields
        this.dynamicFields = []
        
        // Based on selected template, load the appropriate fields
        // In a real app, you might fetch this from the server
        const templateFieldsMap = {
          'invoice': [
            {
              name: 'client_name',
              label: 'Client Name',
              type: 'text'
            },
            {
              name: 'client_email',
              label: 'Client Email',
              type: 'text'
            },
            {
              name: 'description',
              label: 'Service Description',
              type: 'textarea'
            },
            {
              name: 'agreed_payment',
              label: 'Agreed to Payment Terms',
              type: 'checkbox',
              checkboxLabel: 'Yes, I agree to the payment terms'
            },
            
          ],
          'contract': [
            {
              name: 'client_name',
              label: 'Client Name',
              type: 'text'
            },
            {
              name: 'special_terms',
              label: 'Special Terms',
              type: 'textarea'
            },
            {
              name: 'service_level',
              label: 'Service Level',
              type: 'dropdown',
              options: ['Basic', 'Standard', 'Premium']
            }
          ],
          'report': [
            {
              name: 'assessment',
              label: 'Assessment Details',
              type: 'textarea'
            },
            {
              name: 'reviewer_name',
              label: 'Reviewer Name',
              type: 'text'
            },
            {
              name: 'reviewed_date',
              label: 'Reviewed Date',
              type: 'text'
            }
          ],
          
        }
        
        this.dynamicFields = templateFieldsMap[this.form.template] || []
        
        // Initialize data objects for each field
        this.dynamicFields.forEach(field => {
          // Set default values based on field type
          if (field.type === 'checkbox') {
            this.form.data[field.name] = false
          } else if (field.type === 'dropdown' && field.options && field.options.length > 0) {
            this.form.data[field.name] = field.options[0]
          } else {
            this.form.data[field.name] = ''
          }
        })
      },
      
      generatePdfForm() {
        this.processing = true
        
        router.post(route('pdf-forms.generate'), this.form, {
          onSuccess: () => {
            this.processing = false
          },
          onError: () => {
            this.processing = false
          }
        })
      }
    }
  })
  </script>
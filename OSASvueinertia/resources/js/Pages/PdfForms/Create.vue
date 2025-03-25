<template>
   <Frontend>
    <div class="p-6 bg-white shadow-md rounded">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        PDF Form Generator
      </h2>
  
      <pdf-form-component :templates="templates" />
      
    </div>
   </Frontend>
    
  </template>
  
  <script>
  import { defineComponent } from 'vue'
  import PdfFormComponent from '@/Components/PdfFormComponent.vue'
  import Frontend from '@/Layouts/FrontendLayout.vue';
  export default defineComponent({
    components: {
      PdfFormComponent,
      Frontend,
    },
  
    props: {
      templates: Object
    }
  })
  </script>
  
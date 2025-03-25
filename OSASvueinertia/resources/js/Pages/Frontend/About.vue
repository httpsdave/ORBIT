<template>
    <Frontend>
        <Head title="About Us"/>
        <h1>This is an {{ title }}</h1>
    </Frontend>
    </template>
    
    <script setup>
    import Frontend from '@/Layouts/FrontendLayout.vue';
    import {Head} from '@inertiajs/vue3'
    defineProps({
        title:String
    });
    </script>
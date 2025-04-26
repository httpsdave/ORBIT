<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login | LSPU ORBIT" />
    
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-12">
        <div class="w-full max-w-5xl flex flex-col md:flex-row gap-3 relative">
            <!-- Left side panel with gradient and logo (hidden on small screens) -->
            <div class="hidden md:flex md:w-2/5 lg:w-1/2 bg-gradient-to-br from-green-600 via-teal-500 to-blue-600 p-8 flex-col items-center justify-center text-white relative rounded-2xl shadow-xl overflow-hidden">
                <!-- Animated background pattern -->
                <div class="absolute inset-0 overflow-hidden opacity-10">
                    <div class="absolute top-1/4 left-1/4 w-20 h-20 rounded-full bg-white transform rotate-45"></div>
                    <div class="absolute bottom-1/3 right-1/5 w-24 h-24 rounded-full bg-white"></div>
                    <div class="absolute top-3/4 left-1/2 w-16 h-16 rounded-full bg-white"></div>
                </div>
                
                <div class="relative z-10 flex flex-col items-center">
                    <div class="p-3 bg-white bg-opacity-10 rounded-full mb-6">
                        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="w-28 h-28 filter drop-shadow-lg">
                    </div>
                    
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold mb-1">WELCOME TO</h2>
                        <h1 class="text-4xl font-bold mb-3">LSPU ORBIT</h1>
                        <div class="h-1 w-24 bg-white opacity-70 mx-auto mb-5 rounded-full"></div>
                        <p class="text-white text-opacity-90 text-sm max-w-xs mx-auto">Your comprehensive portal to university resources, services, and online learning tools</p>
                    </div>
                </div>

                <!-- Decorative elements -->
                <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black opacity-20"></div>
                
                <!-- Social links with hover effect -->
                <div class="absolute bottom-6 left-0 right-0 flex justify-center space-x-3">
                    <a href="#" class="bg-white bg-opacity-10 hover:bg-opacity-30 p-2.5 rounded-full transition-all duration-300 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="bg-white bg-opacity-10 hover:bg-opacity-30 p-2.5 rounded-full transition-all duration-300 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" class="bg-white bg-opacity-10 hover:bg-opacity-30 p-2.5 rounded-full transition-all duration-300 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Right side panel with login form -->
            <div class="w-full md:w-3/5 lg:w-1/2 bg-gray-900 p-6 md:p-8 rounded-2xl shadow-xl">
                <!-- Top banner for small screens -->
                <div class="md:hidden w-full bg-gradient-to-r from-green-600 to-blue-600 p-4 flex justify-center items-center rounded-xl mb-6">
                    <img src="/images/lspu-logo.png" alt="LSPU Logo" class="w-12 h-12 mr-3">
                    <div class="text-white">
                        <h2 class="text-lg font-bold">LSPU ORBIT</h2>
                        <div class="h-0.5 w-16 bg-white opacity-70"></div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <!-- Logo and banner (shown only on larger screens) -->
                    <div class="hidden md:flex justify-center items-center mb-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-blue-500 opacity-20 blur-sm rounded-lg transform rotate-45"></div>
                            <svg width="40" height="40" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="relative">
                                <path d="M24 12L36 24L24 36L12 24L24 12Z" fill="#3B82F6"/>
                                <path d="M24 6L42 24L24 42L6 24L24 6Z" stroke="#3B82F6" stroke-width="2.5"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-white ml-3">LSPU<span class="text-blue-400">ORBIT</span></h1>
                    </div>

                    <!-- Colored banner -->
                    <div class="flex w-full mb-4 overflow-hidden rounded-lg">
                        <div class="w-1/4 h-2 bg-blue-500"></div>
                        <div class="w-1/4 h-2 bg-green-500"></div>
                        <div class="w-1/4 h-2 bg-yellow-500"></div>
                        <div class="w-1/4 h-2 bg-red-500"></div>
                    </div>
                    
                    <p class="text-gray-400 text-xs text-center tracking-wider">INTEGRITY • PROFESSIONALISM • INNOVATION</p>
                </div>
                
                <div v-if="status" class="mb-4 py-2 px-3 bg-green-500 bg-opacity-10 border border-green-500 border-opacity-20 text-green-400 text-xs font-medium rounded-lg text-center">
                    {{ status }}
                </div>

                <h2 class="text-white text-xl font-medium mb-6 text-center">Sign in to your account</h2>

                <form @submit.prevent="submit">
                    <div class="mb-5">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 group-focus-within:text-blue-400 transition-colors duration-200">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-10 py-3 bg-gray-800 bg-opacity-50 border-gray-700 text-white rounded-lg w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-40 transition duration-300"
                                v-model="form.email"
                                placeholder="Username or Email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <InputError class="mt-1 text-red-400 text-xs" :message="form.errors.email" />
                    </div>

                    <div class="mb-5">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500 group-focus-within:text-blue-400 transition-colors duration-200">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="pl-10 py-3 bg-gray-800 bg-opacity-50 border-gray-700 text-white rounded-lg w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-40 transition duration-300"
                                v-model="form.password"
                                placeholder="Password"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        <InputError class="mt-1 text-red-400 text-xs" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" class="text-blue-500 border-gray-700 rounded focus:ring-offset-gray-900 focus:ring-blue-500" />
                            <span class="ml-2 text-sm text-gray-400">Remember me</span>
                        </label>
                        
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-gray-400 hover:text-blue-400 focus:outline-none transition duration-300"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-3 px-4 rounded-lg transition duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg"
                        :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-32 group-hover:h-32 opacity-10"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        Sign In
                    </button>

                    <div class="mt-5 text-center">
                        <Link
                            :href="route('register')"
                            class="text-sm text-gray-400 hover:text-blue-400 focus:outline-none transition duration-300"
                        >
                            Don't have an account? <span class="font-medium">Register</span>
                        </Link>
                    </div>
                </form>
                
                <div class="mt-8 text-center text-xs text-gray-600">
                    © 2025 Laguna State Polytechnic University
                </div>
            </div>
        </div>
    </div>
</template>
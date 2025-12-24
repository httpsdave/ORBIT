<script setup>
import { useTheme } from '@/Composables/useTheme';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'dropdown'].includes(value)
  },
  showLabel: {
    type: Boolean,
    default: false
  }
});

const { isDark, currentTheme, themeIcon, themeLabel, toggleTheme, cycleTheme, setTheme, THEMES } = useTheme();

// Size classes
const sizeClasses = {
  sm: {
    button: 'p-1.5',
    icon: 'h-4 w-4',
    text: 'text-xs'
  },
  md: {
    button: 'p-2',
    icon: 'h-5 w-5',
    text: 'text-sm'
  },
  lg: {
    button: 'p-3',
    icon: 'h-6 w-6',
    text: 'text-base'
  }
};

const currentSize = sizeClasses[props.size];

// Handle theme toggle
const handleToggle = () => {
  toggleTheme();
};

// Theme options for dropdown variant
const themeOptions = [
  { key: THEMES.LIGHT, label: 'Light', icon: 'sun' },
  { key: THEMES.DARK, label: 'Dark', icon: 'moon' }
];
</script>

<template>
  <div class="relative">
    <!-- Simple Button Toggle -->
    <button
      v-if="variant === 'button'"
      @click="handleToggle"
      :class="[
        currentSize.button,
        'rounded-lg transition-colors duration-200 ease-in-out',
        'text-gray-600 hover:text-gray-800 hover:bg-gray-100',
        'dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800',
        'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
        'dark:focus:ring-offset-gray-900'
      ]"
      :title="`Switch to ${isDark ? 'light' : 'dark'} mode`"
    >
      <div class="flex items-center space-x-2">
        <!-- Sun Icon (Light Mode) -->
        <svg
          v-if="!isDark"
          :class="currentSize.icon"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
          />
        </svg>
        
        <!-- Moon Icon (Dark Mode) -->
        <svg
          v-else
          :class="currentSize.icon"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"
          />
        </svg>
        
        <span v-if="showLabel" :class="currentSize.text">
          {{ isDark ? 'Dark' : 'Light' }}
        </span>
      </div>
    </button>

    <!-- Dropdown Toggle (cycles through all themes) -->
    <div
      v-else
      class="relative inline-flex"
    >
      <!-- Modern Toggle Switch Button -->
      <button
        @click="handleToggle"
        :class="[
          'relative inline-flex items-center rounded-full transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
          currentTheme === THEMES.LIGHT ? 'bg-amber-200 hover:bg-amber-300' : 'bg-slate-600 hover:bg-slate-700',
          currentSize.button === 'p-1.5' ? 'h-6 w-11' : 
          currentSize.button === 'p-2' ? 'h-7 w-12' : 'h-8 w-14'
        ]"
        :title="`Current: ${themeLabel} theme - Click to cycle`"
      >
        <!-- Toggle Knob -->
        <span
          :class="[
            'inline-block rounded-full shadow-sm transform transition-all duration-300 ease-in-out',
            currentTheme === THEMES.LIGHT ? 'translate-x-0 bg-amber-500' : 'translate-x-5 bg-slate-900',
            currentSize.button === 'p-1.5' ? 'h-4 w-4' : 
            currentSize.button === 'p-2' ? 'h-5 w-5' : 'h-6 w-6'
          ]"
        >
          <!-- Icon inside the knob -->
          <span class="flex items-center justify-center h-full w-full text-white">
            <!-- Sun Icon for Light -->
            <svg
              v-if="currentTheme === THEMES.LIGHT"
              :class="currentSize.button === 'p-1.5' ? 'h-2.5 w-2.5' : currentSize.button === 'p-2' ? 'h-3 w-3' : 'h-3.5 w-3.5'"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
            </svg>
            
            <!-- Moon Icon for Dark -->
            <svg
              v-else
              :class="currentSize.button === 'p-1.5' ? 'h-2.5 w-2.5' : currentSize.button === 'p-2' ? 'h-3 w-3' : 'h-3.5 w-3.5'"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
          </span>
        </span>
      </button>
      
      <!-- Theme Labels (Optional) -->
      <div v-if="showLabel" class="ml-2 flex flex-col text-xs">
        <span :class="currentSize.text">{{ themeLabel }}</span>
      </div>
    </div>
  </div>
</template>
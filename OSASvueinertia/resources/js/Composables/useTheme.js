import { ref, computed, watch } from 'vue';

// Global theme state
const isDark = ref(false);
const isInitialized = ref(false);

// Theme modes
const THEMES = {
  LIGHT: 'light',
  DARK: 'dark',
  SYSTEM: 'system'
};

// Current theme preference (light, dark, or system)
const currentTheme = ref(THEMES.LIGHT);

export function useTheme() {
  // Initialize theme from localStorage and system preference
  const initializeTheme = () => {
    if (isInitialized.value) return;

    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme && Object.values(THEMES).includes(savedTheme)) {
      currentTheme.value = savedTheme;
    } else {
      currentTheme.value = THEMES.LIGHT;
    }

    updateTheme();
    isInitialized.value = true;
  };

  // Get system preference
  const getSystemPreference = () => {
    if (typeof window !== 'undefined' && window.matchMedia) {
      return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    return false;
  };

  // Update the actual theme applied to the document
  const updateTheme = () => {
    let shouldBeDark;

    switch (currentTheme.value) {
      case THEMES.DARK:
        shouldBeDark = true;
        break;
      case THEMES.LIGHT:
        shouldBeDark = false;
        break;
      case THEMES.SYSTEM:
      default:
        shouldBeDark = getSystemPreference();
        break;
    }

    isDark.value = shouldBeDark;

    // Apply or remove dark class to html element
    if (typeof document !== 'undefined') {
      if (shouldBeDark) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    }
  };

  // Set theme and persist to localStorage
  const setTheme = (theme) => {
    if (!Object.values(THEMES).includes(theme)) {
      console.warn(`Invalid theme: ${theme}`);
      return;
    }

    currentTheme.value = theme;
    localStorage.setItem('theme', theme);
    updateTheme();
  };

  // Toggle between light and dark (skips system)
  const toggleTheme = () => {
    if (currentTheme.value === THEMES.LIGHT) {
      setTheme(THEMES.DARK);
    } else {
      setTheme(THEMES.LIGHT);
    }
  };

  // Cycle through all themes: light -> dark -> system
  const cycleTheme = () => {
    switch (currentTheme.value) {
      case THEMES.LIGHT:
        setTheme(THEMES.DARK);
        break;
      case THEMES.DARK:
        setTheme(THEMES.SYSTEM);
        break;
      case THEMES.SYSTEM:
      default:
        setTheme(THEMES.LIGHT);
        break;
    }
  };

  // Listen for system theme changes
  const setupSystemThemeListener = () => {
    if (typeof window !== 'undefined' && window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      
      const handleChange = () => {
        if (currentTheme.value === THEMES.SYSTEM) {
          updateTheme();
        }
      };

      // Modern browsers
      if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', handleChange);
      } 
      // Fallback for older browsers
      else if (mediaQuery.addListener) {
        mediaQuery.addListener(handleChange);
      }

      return () => {
        if (mediaQuery.removeEventListener) {
          mediaQuery.removeEventListener('change', handleChange);
        } else if (mediaQuery.removeListener) {
          mediaQuery.removeListener(handleChange);
        }
      };
    }
  };

  // Computed properties
  const themeIcon = computed(() => {
    switch (currentTheme.value) {
      case THEMES.LIGHT:
        return 'sun';
      case THEMES.DARK:
        return 'moon';
      case THEMES.SYSTEM:
      default:
        return 'desktop';
    }
  });

  const themeLabel = computed(() => {
    switch (currentTheme.value) {
      case THEMES.LIGHT:
        return 'Light';
      case THEMES.DARK:
        return 'Dark';
      case THEMES.SYSTEM:
      default:
        return 'System';
    }
  });

  // Auto-initialize when composable is used
  if (typeof window !== 'undefined') {
    initializeTheme();
    setupSystemThemeListener();
  }

  return {
    // State
    isDark: computed(() => isDark.value),
    currentTheme: computed(() => currentTheme.value),
    isInitialized: computed(() => isInitialized.value),
    
    // Constants
    THEMES,
    
    // Methods
    setTheme,
    toggleTheme,
    cycleTheme,
    initializeTheme,
    
    // Computed
    themeIcon,
    themeLabel,
  };
}
<template>
  <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <!-- Definitions for gradients -->
  <defs>
    <!-- Colorful ring gradient -->
    <linearGradient id="ringGradient" x1="0%" y1="50%" x2="100%" y2="50%">
      <stop offset="0%" stop-color="#3498db" />
      <stop offset="25%" stop-color="#2ecc71" />
      <stop offset="50%" stop-color="#f1c40f" />
      <stop offset="75%" stop-color="#e74c3c" />
      <stop offset="100%" stop-color="#3498db" />
    </linearGradient>
    
    <!-- Planet gradient -->
    <linearGradient id="planetGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#34495e" />
      <stop offset="100%" stop-color="#2c3e50" />
    </linearGradient>
    
    <!-- Clip path for better ring transition -->
    <clipPath id="frontRingClip">
      <rect x="0" y="200" width="400" height="200" />
    </clipPath>
    
    <clipPath id="backRingClip">
      <rect x="0" y="0" width="400" height="200" />
    </clipPath>
  </defs>
  
  <!-- Middle transition ring section to create seamless transition -->
  <g transform="rotate(-20, 200, 200)">
    <path d="M 40 200 A 160 40 0 0 1 360 200" fill="none" stroke="url(#ringGradient)" stroke-width="15" stroke-linecap="round" />
  </g>
  
  <!-- Planet body (drawn over the back of the ring) -->
  <circle cx="200" cy="200" r="80" fill="url(#planetGradient)" />
  
  <!-- Craters on the planet (back to original style) -->
  <circle cx="170" cy="170" r="15" fill="#2c3e50" opacity="0.6" />
  <circle cx="230" cy="160" r="10" fill="#2c3e50" opacity="0.5" />
  <circle cx="180" cy="220" r="18" fill="#2c3e50" opacity="0.7" />
  <circle cx="210" cy="250" r="12" fill="#2c3e50" opacity="0.5" />
  <circle cx="240" cy="210" r="8" fill="#2c3e50" opacity="0.6" />
  <circle cx="150" cy="200" r="12" fill="#2c3e50" opacity="0.5" />
  <circle cx="190" cy="160" r="7" fill="#2c3e50" opacity="0.4" />
  
  <!-- Front part of the ring (showing through) -->
  <g transform="rotate(-20, 200, 200)">
    <path d="M 40 200 A 160 40 0 0 0 360 200" fill="none" stroke="url(#ringGradient)" stroke-width="40" stroke-linecap="round" clip-path="url(#frontRingClip)" />
  </g>
</svg>
  </template>
  
  <script>
  export default {
    name: 'ApplicationLogo'
  }
  </script>
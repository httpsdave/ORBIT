<template>
 <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <!-- Definitions for gradients and filters -->
  <defs>
    <!-- Colorful ring gradient -->
    <linearGradient id="ringGradient" x1="0%" y1="50%" x2="100%" y2="50%">
      <stop offset="0%" stop-color="#3498db" />
      <stop offset="25%" stop-color="#2ecc71" />
      <stop offset="50%" stop-color="#f1c40f" />
      <stop offset="75%" stop-color="#e74c3c" />
      <stop offset="100%" stop-color="#3498db" />
    </linearGradient>
    
    <!-- Planet gradient with more depth -->
    <radialGradient id="planetGradient" cx="40%" cy="40%" r="80%" fx="30%" fy="30%">
      <stop offset="0%" stop-color="#455a73" />
      <stop offset="80%" stop-color="#2c3e50" />
      <stop offset="100%" stop-color="#1a2530" />
    </radialGradient>
    
    <!-- Subtle shadow for the planet -->
    <filter id="planetShadow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur in="SourceAlpha" stdDeviation="8" />
      <feOffset dx="4" dy="4" result="offsetblur" />
      <feComponentTransfer>
        <feFuncA type="linear" slope="0.3" />
      </feComponentTransfer>
      <feMerge>
        <feMergeNode />
        <feMergeNode in="SourceGraphic" />
      </feMerge>
    </filter>
  </defs>
  
  <!-- Back part of the ring (thinner) -->
  <ellipse cx="200" cy="200" rx="160" ry="40" transform="rotate(-20, 200, 200)" fill="none" stroke="url(#ringGradient)" stroke-width="8" stroke-opacity="0.8" />
  
  <!-- Planet body with enhanced shading -->
  <circle cx="200" cy="200" r="80" fill="url(#planetGradient)" filter="url(#planetShadow)" />
  
  <!-- Planet highlights for more depth -->
  <circle cx="180" cy="180" r="65" fill="white" opacity="0.05" />
  <circle cx="170" cy="170" r="45" fill="white" opacity="0.05" />
  
  <!-- Craters on the planet -->
  <circle cx="170" cy="170" r="15" fill="#2c3e50" opacity="0.7" />
  <circle cx="230" cy="160" r="10" fill="#2c3e50" opacity="0.6" />
  <circle cx="180" cy="220" r="18" fill="#2c3e50" opacity="0.7" />
  <circle cx="210" cy="250" r="12" fill="#2c3e50" opacity="0.6" />
  <circle cx="240" cy="210" r="8" fill="#2c3e50" opacity="0.7" />
  <circle cx="150" cy="200" r="12" fill="#2c3e50" opacity="0.6" />
  <circle cx="190" cy="160" r="7" fill="#2c3e50" opacity="0.5" />
  
  <!-- Enhanced crater details -->
  <circle cx="170" cy="170" r="10" fill="#1a2530" opacity="0.5" />
  <circle cx="180" cy="220" r="14" fill="#1a2530" opacity="0.5" />
  
  <!-- Front part of the ring (thicker) -->
  <path d="M 40 216 A 160 40 0 0 0 360 184" transform="rotate(-20, 200, 200)" fill="none" stroke="url(#ringGradient)" stroke-width="18" stroke-linecap="round" />
  
  <!-- Ring shadow on planet (subtle effect) -->
  <path d="M 120 200 L 280 200" stroke="rgba(0,0,0,0.2)" stroke-width="4" transform="rotate(-18, 200, 200)" />
</svg>
  </template>
  
  <script>
  export default {
    name: 'ApplicationLogo'
  }
  </script>
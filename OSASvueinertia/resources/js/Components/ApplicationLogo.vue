<template>
   <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <!-- Definitions for gradients -->
  <defs>
    <!-- Blue gradient (water/maritime) -->
    <linearGradient id="blueGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0088cc" />
      <stop offset="100%" stop-color="#005f8f" />
    </linearGradient>
    
    <!-- Green gradient (agriculture/farming) -->
    <linearGradient id="greenGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#34d058" />
      <stop offset="100%" stop-color="#28a745" />
    </linearGradient>
    
    <!-- Yellow gradient (technology/circuits) -->
    <linearGradient id="yellowGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#ffdd57" />
      <stop offset="100%" stop-color="#ffaa00" />
    </linearGradient>
    
    <!-- Red gradient (industry/manufacturing) -->
    <linearGradient id="redGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#ff3860" />
      <stop offset="100%" stop-color="#cc0022" />
    </linearGradient>
  </defs>
  
  <!-- Saturn planet (hollow with blue stroke) - enlarged -->
  <circle cx="200" cy="200" r="110" fill="none" stroke="url(#blueGradient)" stroke-width="5" />
  
  <!-- Saturn rings with different gradient colors - enlarged -->
  <g transform="rotate(-15, 200, 200)">
    <!-- Outer ring (red) -->
    <ellipse cx="200" cy="200" rx="190" ry="35" fill="none" stroke="url(#redGradient)" stroke-width="4" />
    
    <!-- Middle ring (yellow) -->
    <ellipse cx="200" cy="200" rx="170" ry="30" fill="none" stroke="url(#yellowGradient)" stroke-width="4" />
    
    <!-- Inner ring (green) -->
    <ellipse cx="200" cy="200" rx="150" ry="25" fill="none" stroke="url(#greenGradient)" stroke-width="4" />
  </g>
</svg>
  </template>
  
  <script>
  export default {
    name: 'ApplicationLogo'
  }
  </script>
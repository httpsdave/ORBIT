<template>
 <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <!-- Enhanced definitions for gradients and filters -->
  <defs>
    <!-- Improved colorful ring gradient with smoother transitions -->
    <linearGradient id="ringGradient" x1="0%" y1="50%" x2="100%" y2="50%" gradientUnits="userSpaceOnUse" spreadMethod="reflect">
      <stop offset="0%" stop-color="#3498db" />
      <stop offset="20%" stop-color="#2ecc71" />
      <stop offset="40%" stop-color="#f1c40f" />
      <stop offset="60%" stop-color="#e74c3c" />
      <stop offset="80%" stop-color="#9b59b6" />
      <stop offset="100%" stop-color="#3498db" />
    </linearGradient>
    
    <!-- Enhanced planet gradient with more realistic depth -->
    <radialGradient id="planetGradient" cx="40%" cy="40%" r="80%" fx="30%" fy="30%">
      <stop offset="0%" stop-color="#5d7795" />
      <stop offset="50%" stop-color="#34495e" />
      <stop offset="90%" stop-color="#2c3e50" />
      <stop offset="100%" stop-color="#1a2530" />
    </radialGradient>
    
    <!-- Improved shadow for the planet -->
    <filter id="planetShadow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur in="SourceAlpha" stdDeviation="10" />
      <feOffset dx="4" dy="6" result="offsetblur" />
      <feComponentTransfer>
        <feFuncA type="linear" slope="0.4" />
      </feComponentTransfer>
      <feMerge>
        <feMergeNode />
        <feMergeNode in="SourceGraphic" />
      </feMerge>
    </filter>
    
    <!-- Glow effect for the ring -->
    <filter id="ringGlow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="6" result="blur" />
      <feComposite in="SourceGraphic" in2="blur" operator="over" />
    </filter>
    
    <!-- Subtle texture for the planet -->
    <pattern id="planetTexture" width="40" height="40" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
      <rect width="40" height="40" fill="#34495e" />
      <rect width="20" height="20" fill="#2c3e50" opacity="0.3" />
    </pattern>
    
    <!-- Stars background pattern -->
    <pattern id="starsPattern" width="100" height="100" patternUnits="userSpaceOnUse">
      <circle cx="10" cy="10" r="0.8" fill="white" opacity="0.6" />
      <circle cx="30" cy="40" r="0.6" fill="white" opacity="0.5" />
      <circle cx="70" cy="25" r="1" fill="white" opacity="0.7" />
      <circle cx="90" cy="60" r="0.7" fill="white" opacity="0.6" />
      <circle cx="50" cy="85" r="0.9" fill="white" opacity="0.8" />
      <circle cx="20" cy="70" r="0.5" fill="white" opacity="0.4" />
      <circle cx="80" cy="10" r="0.6" fill="white" opacity="0.5" />
    </pattern>
  </defs>
  
  <!-- Subtle background with stars -->
  <rect x="0" y="0" width="400" height="400" fill="url(#starsPattern)" opacity="0.2" />
  
  <!-- Subtle outer glow -->
  <circle cx="200" cy="200" r="150" fill="none" stroke="#3498db" stroke-width="1" opacity="0.1" />
  
  <!-- Back part of the ring (enhanced) -->
  <ellipse cx="200" cy="200" rx="160" ry="40" transform="rotate(-20, 200, 200)" fill="none" stroke="url(#ringGradient)" stroke-width="8" stroke-opacity="0.7" />
  
  <!-- Ring shadow on planet -->
  <path d="M 120 200 L 280 200" stroke="rgba(0,0,0,0.2)" stroke-width="6" transform="rotate(-18, 200, 200)" />
  
  <!-- Planet body with enhanced shading and texture -->
  <circle cx="200" cy="200" r="85" fill="url(#planetGradient)" filter="url(#planetShadow)" />
  <circle cx="200" cy="200" r="85" fill="url(#planetTexture)" opacity="0.1" />
  
  <!-- Planet atmosphere glow -->
  <circle cx="200" cy="200" r="88" fill="none" stroke="#5d7795" stroke-width="2" opacity="0.3" />
  
  <!-- Planet highlights for more depth -->
  <circle cx="175" cy="175" r="70" fill="white" opacity="0.05" />
  <circle cx="165" cy="165" r="50" fill="white" opacity="0.04" />
  
  <!-- Enhanced craters on the planet -->
  <circle cx="170" cy="170" r="15" fill="#1a2530" opacity="0.7" />
  <circle cx="230" cy="160" r="10" fill="#1a2530" opacity="0.6" />
  <circle cx="180" cy="220" r="18" fill="#1a2530" opacity="0.7" />
  <circle cx="210" cy="250" r="12" fill="#1a2530" opacity="0.6" />
  <circle cx="240" cy="210" r="8" fill="#1a2530" opacity="0.7" />
  <circle cx="150" cy="200" r="12" fill="#1a2530" opacity="0.6" />
  <circle cx="190" cy="160" r="7" fill="#1a2530" opacity="0.5" />
  
  <!-- Enhanced crater details with rim highlights -->
  <circle cx="170" cy="170" r="13" fill="#1a2530" opacity="0.6" />
  <circle cx="170" cy="170" r="15" fill="none" stroke="#5d7795" stroke-width="1" opacity="0.3" />
  
  <circle cx="180" cy="220" r="16" fill="#1a2530" opacity="0.6" />
  <circle cx="180" cy="220" r="18" fill="none" stroke="#5d7795" stroke-width="1" opacity="0.3" />
  
  <!-- Front part of the ring (thicker with glow effect) -->
  <path d="M 40 216 A 160 40 0 0 0 360 184" transform="rotate(-20, 200, 200)" fill="none" stroke="url(#ringGradient)" stroke-width="18" stroke-linecap="round" filter="url(#ringGlow)" />
  
  <!-- Ring particles/details -->
  <g transform="rotate(-20, 200, 200)">
    <!-- Small particles in the ring -->
    <circle cx="100" cy="200" r="2" fill="white" opacity="0.7" />
    <circle cx="120" cy="200" r="1.5" fill="white" opacity="0.6" />
    <circle cx="150" cy="200" r="2.2" fill="white" opacity="0.8" />
    <circle cx="180" cy="200" r="1.8" fill="white" opacity="0.7" />
    <circle cx="220" cy="200" r="1.6" fill="white" opacity="0.6" />
    <circle cx="250" cy="200" r="2.3" fill="white" opacity="0.8" />
    <circle cx="280" cy="200" r="1.5" fill="white" opacity="0.7" />
    <circle cx="310" cy="200" r="1.7" fill="white" opacity="0.6" />
  </g>
</svg>
  </template>
  
  <script>
  export default {
    name: 'ApplicationLogo'
  }
  </script>
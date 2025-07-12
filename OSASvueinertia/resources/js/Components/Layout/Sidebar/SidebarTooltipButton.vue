<template>
  <div
    ref="btnRef"
    @mouseenter="showTooltip"
    @mouseleave="hideTooltip"
    @focus="showTooltip"
    @blur="hideTooltip"
    class="relative"
    tabindex="-1"
  >
    <slot />
    <teleport to="body">
      <span
        v-if="isHovered && !sidebarExpanded && !showingSidebar"
        :style="{
          position: 'fixed',
          top: tooltipPos.top + 'px',
          left: tooltipPos.left + 'px',
          transform: 'translateY(-50%)',
          zIndex: 9999
        }"
        class="bg-gray-800 text-white text-xs rounded py-1 px-2 shadow-lg opacity-100 transition-opacity duration-300 whitespace-nowrap pointer-events-none"
      >
        {{ tooltip }}
      </span>
    </teleport>
  </div>
</template>

<script setup>
import { ref, nextTick, defineProps, watch } from 'vue';

const props = defineProps({
  tooltip: {
    type: String,
    required: true
  },
  sidebarExpanded: {
    type: Boolean,
    required: true
  },
  showingSidebar: {
    type: Boolean,
    required: true
  }
});

const isHovered = ref(false);
const tooltipPos = ref({ top: 0, left: 0 });
const btnRef = ref(null);

const showTooltip = async () => {
  if (props.sidebarExpanded || props.showingSidebar) return;
  isHovered.value = true;
  await nextTick();
  if (btnRef.value && btnRef.value.getBoundingClientRect) {
    const rect = btnRef.value.getBoundingClientRect();
    tooltipPos.value = {
      top: rect.top + rect.height / 2,
      left: rect.right + 8 // 8px gap
    };
  }
};
const hideTooltip = () => {
  isHovered.value = false;
};
</script> 
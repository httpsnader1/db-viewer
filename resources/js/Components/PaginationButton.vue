<template>
  <button
    v-bind="$attrs"
    :disabled="disabled"
    :class="[
      'w-8 h-8 rounded-lg text-sm font-medium transition-all duration-100 flex items-center justify-center',
      active
        ? 'bg-violet-600 text-white shadow shadow-violet-500/30'
        : disabled
          ? 'text-gray-700 cursor-not-allowed'
          : 'text-gray-400 hover:text-white hover:bg-gray-800',
    ]"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  active:   { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
</script>

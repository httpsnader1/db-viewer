<template>
  <span v-if="value === null || value === undefined" class="text-gray-700 italic text-xs">NULL</span>
  <span v-else-if="typeof value === 'boolean'" 
    :class="['inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded-full font-medium', 
             value ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400']">
    {{ value ? $t('true') : $t('false') }}
  </span>
  <span v-else class="truncate max-w-xs block font-mono text-[13px]" :title="String(value)">
    {{ value }}
  </span>
</template>

<script setup>
defineProps({
  value: { default: null },
  type:  { type: String, default: 'string' },
})
</script>

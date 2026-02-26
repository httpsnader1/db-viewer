<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div
      v-for="card in cards"
      :key="card.label"
      class="relative overflow-hidden rounded-xl bg-gray-900 border border-gray-800 p-5 group hover:border-violet-500/40 transition-all duration-300"
    >
      <!-- Glow blob -->
      <div :class="['absolute -top-4 -right-4 w-20 h-20 rounded-full blur-2xl opacity-20 group-hover:opacity-40 transition-opacity', card.blob]"></div>

      <div class="relative flex flex-col gap-3">
        <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', card.iconBg]">
          <component :is="card.icon" class="w-5 h-5" :class="card.iconColor" />
        </div>
        <div>
          <p class="text-2xl font-bold text-white tabular-nums">{{ card.value }}</p>
          <p class="text-sm text-gray-400 mt-0.5">{{ card.label }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CircleStackIcon,
  TableCellsIcon,
  ServerIcon,
  CpuChipIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: { type: Object, required: true },
})

const totalRows = computed(() =>
  (props.stats.table_stats ?? []).reduce((s, t) => s + (t.row_count ?? 0), 0)
)

const cards = computed(() => [
  {
    label:     'Database',
    value:     props.stats.database ?? '—',
    icon:      CircleStackIcon,
    iconBg:    'bg-violet-500/15',
    iconColor: 'text-violet-400',
    blob:      'bg-violet-500',
  },
  {
    label:     'Total Tables',
    value:     props.stats.total_tables ?? 0,
    icon:      TableCellsIcon,
    iconBg:    'bg-indigo-500/15',
    iconColor: 'text-indigo-400',
    blob:      'bg-indigo-500',
  },
  {
    label:     'Total Rows (top tables)',
    value:     totalRows.value.toLocaleString(),
    icon:      ServerIcon,
    iconBg:    'bg-emerald-500/15',
    iconColor: 'text-emerald-400',
    blob:      'bg-emerald-500',
  },
  {
    label:     'Driver',
    value:     props.stats.driver ?? '—',
    icon:      CpuChipIcon,
    iconBg:    'bg-amber-500/15',
    iconColor: 'text-amber-400',
    blob:      'bg-amber-500',
  },
])
</script>

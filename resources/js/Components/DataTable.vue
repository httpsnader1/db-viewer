<template>
  <div class="flex flex-col gap-4">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Quick Global Search -->
      <div class="relative flex-1 min-w-48">
        <MagnifyingGlassIcon :class="['absolute top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none', $page.props.db_viewer.locale === 'ar' ? 'right-3' : 'left-3']" />
        <input
          v-model="localSearch"
          type="text"
          :placeholder="$t('quick_search_placeholder')"
          :class="['w-full py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 transition-colors', $page.props.db_viewer.locale === 'ar' ? 'pr-9 pl-3' : 'pl-9 pr-3']"
        />
        <button
          v-if="localSearch"
          @click="localSearch = ''"
          :class="['absolute top-1/2 -translate-y-1/2 text-gray-500 hover:text-white', $page.props.db_viewer.locale === 'ar' ? 'left-3' : 'right-3']"
        >
          <XMarkIcon class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Per Page -->
      <select
        v-model="localPerPage"
        class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-violet-500 shadow-sm"
      >
        <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }} / {{ $t('per_page') }}</option>
      </select>

      <!-- Slot for extra actions (Column Picker, Advanced Filter etc) -->
      <slot name="actions" />
    </div>

    <!-- Table Wrapper -->
    <div class="rounded-xl border border-gray-800 bg-gray-900/40 overflow-hidden shadow-2xl">
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="border-b border-gray-800 bg-gray-900/90 backdrop-blur-md sticky top-0 z-10">
              <th
                v-for="col in visibleColumns"
                :key="col.name"
                :class="['px-4 py-3.5 text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap select-none', $page.props.db_viewer.locale === 'ar' ? 'text-right' : 'text-left']"
              >
                <button
                  class="flex items-center gap-2 group hover:text-white transition-colors"
                  @click="toggleSort(col.name)"
                >
                  {{ col.name }}
                  <span class="text-gray-700 group-hover:text-gray-400">
                    <ChevronUpDownIcon v-if="localSort !== col.name" class="w-3.5 h-3.5" />
                    <ChevronUpIcon    v-else-if="localDirection === 'asc'"  class="w-3.5 h-3.5 text-violet-400" />
                    <ChevronDownIcon  v-else                                class="w-3.5 h-3.5 text-violet-400" />
                  </span>
                </button>
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-800/60">
            <tr v-if="loading">
              <td :colspan="visibleColumns.length" class="py-24 text-center">
                <div class="inline-flex flex-col items-center gap-4 text-gray-500">
                  <div class="w-8 h-8 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
                  <span class="text-sm font-medium">{{ $t('fetching_records') }}</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="!rows.length">
              <td :colspan="visibleColumns.length" class="py-24 text-center">
                <div class="inline-flex flex-col items-center gap-3 text-gray-600">
                  <TableCellsIcon class="w-12 h-12 opacity-20" />
                  <p class="text-base font-medium">{{ $t('no_results_found') }}</p>
                  <p class="text-xs">{{ $t('adjust_search_filters') }}</p>
                </div>
              </td>
            </tr>

            <tr
              v-for="(row, i) in rows"
              :key="i"
              class="hover:bg-violet-500/5 cursor-pointer transition-colors group"
              @click="$emit('row-click', row)"
            >
              <td
                v-for="col in visibleColumns"
                :key="col.name"
                :class="['px-4 py-3.5 text-gray-300 border-gray-800/30', $page.props.db_viewer.locale === 'ar' ? 'border-l last:border-l-0' : 'border-r last:border-r-0']"
              >
                <CellValue :value="row[col.name]" :type="col.type" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between px-2 py-1">
      <div class="text-xs text-gray-500">
        {{ $t('showing') }}
        <span class="text-gray-300">{{ pagination.from ?? 0 }}</span>
        {{ $t('to') }}
        <span class="text-gray-300">{{ pagination.to ?? 0 }}</span>
        {{ $t('of') }}
        <span class="text-gray-300">{{ pagination.total.toLocaleString() }}</span>
        {{ $t('records') }}
      </div>

      <div :class="['flex items-center gap-1.5', $page.props.db_viewer.locale === 'ar' ? 'flex-row-reverse' : '']">
        <PaginationButton :disabled="pagination.current_page <= 1" @click="goPage(1)">«</PaginationButton>
        <PaginationButton :disabled="pagination.current_page <= 1" @click="goPage(pagination.current_page - 1)">‹</PaginationButton>

        <template v-for="p in pageRange" :key="p">
          <span v-if="p === '…'" class="px-2 text-gray-700">…</span>
          <PaginationButton v-else :active="p === pagination.current_page" @click="goPage(p)">{{ p }}</PaginationButton>
        </template>

        <PaginationButton :disabled="pagination.current_page >= pagination.last_page" @click="goPage(pagination.current_page + 1)">›</PaginationButton>
        <PaginationButton :disabled="pagination.current_page >= pagination.last_page" @click="goPage(pagination.last_page)">»</PaginationButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  TableCellsIcon,
  ChevronUpDownIcon,
  ChevronUpIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'
import CellValue       from './CellValue.vue'
import PaginationButton from './PaginationButton.vue'

const props = defineProps({
  columns:        { type: Array,   required: true },
  rows:           { type: Array,   required: true },
  pagination:     { type: Object,  required: true },
  filters:        { type: Object,  required: true },
  columnVisible:  { type: Object,  default: () => ({}) },
  perPageOptions: { type: Array,   default: () => [10, 25, 50, 100] },
  loading:        { type: Boolean, default: false },
})

const emit = defineEmits(['change', 'row-click'])

// ─── Local State ──────────────────────────────────────────────────────────────
const localSearch    = ref(props.filters.search    ?? '')
const localPerPage   = ref(props.filters.perPage   ?? 25)
const localSort      = ref(props.filters.sort      ?? null)
const localDirection = ref(props.filters.direction ?? 'asc')

// ─── Computed ─────────────────────────────────────────────────────────────────
const visibleColumns = computed(() =>
  props.columns.filter((c) => props.columnVisible[c.name] !== false)
)

const pageRange = computed(() => {
  const { current_page: curr, last_page: last } = props.pagination
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  if (curr <= 4) return [1, 2, 3, 4, 5, '…', last]
  if (curr >= last - 3) return [1, '…', last - 4, last - 3, last - 2, last - 1, last]
  return [1, '…', curr - 1, curr, curr + 1, '…', last]
})

// ─── Watchers ─────────────────────────────────────────────────────────────────
let searchTimer = null
watch(localSearch, (v) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => emitChange({ search: v, page: 1 }), 300)
})

watch(localPerPage, (v) => emitChange({ perPage: v, page: 1 }))

// ─── Methods ──────────────────────────────────────────────────────────────────
const toggleSort = (col) => {
  if (localSort.value === col) {
    localDirection.value = localDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    localSort.value      = col
    localDirection.value = 'asc'
  }
  emitChange({ sort: localSort.value, direction: localDirection.value, page: 1 })
}

const goPage = (page) => emitChange({ page })

const emitChange = (partial) => {
  emit('change', {
    search:    localSearch.value,
    perPage:   localPerPage.value,
    sort:      localSort.value,
    direction: localDirection.value,
    page:      props.pagination.current_page,
    ...partial,
  })
}
</script>

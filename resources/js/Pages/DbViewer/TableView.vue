<template>
  <DbViewerLayout :breadcrumbs="['DB Viewer', 'Tables', table]">
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link
            :href="route('db-viewer.dashboard')"
            class="w-10 h-10 rounded-xl bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-500 hover:text-white transition-all shadow-lg"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold text-white tracking-tight">{{ table }}</h1>
              <span class="text-[10px] bg-violet-500/10 text-violet-400 border border-violet-500/20 px-2 py-0.5 rounded-md font-mono uppercase tracking-widest">
                {{ pagination.total.toLocaleString() }} records
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
           <button
            @click="showFilterModal = true"
            :class="[
              'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold transition-all shadow-sm',
              hasAdvancedFilters 
                ? 'bg-violet-600/10 border-violet-500/50 text-violet-400 shadow-violet-500/10' 
                : 'bg-gray-950 border-gray-800 text-gray-400 hover:border-gray-700'
            ]"
          >
            <FunnelIcon class="w-4 h-4" />
            Filters
            <span v-if="localAdvancedFilters.length" class="ml-1 px-1.5 py-0.5 bg-violet-600 text-white text-[10px] rounded-md font-mono">
              {{ localAdvancedFilters.length }}
            </span>
          </button>

          <ColumnPicker
            :columns="columns"
            :table="table"
            v-model="columnVisible"
          />
        </div>
      </div>

      <!-- DataTable -->
      <DataTable
        :columns="columns"
        :rows="rows"
        :pagination="pagination"
        :filters="filters"
        :column-visible="columnVisible"
        :per-page-options="perPageOptions"
        :loading="loading"
        @change="handleChange"
        @row-click="openModal"
      />
    </div>

    <!-- Row Modal -->
    <RowDetailsModal
      :show="modalOpen"
      :row="selectedRow"
      @close="modalOpen = false"
    />

    <!-- Advanced Filter Modal -->
    <FilterModal
      :show="showFilterModal"
      :columns="columns"
      :filters="localAdvancedFilters"
      @close="showFilterModal = false"
      @apply="applyAdvancedFilters"
    />
  </DbViewerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ArrowLeftIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import DbViewerLayout from '../../Components/DbViewerLayout.vue'
import DataTable        from '../../Components/DataTable.vue'
import ColumnPicker     from '../../Components/ColumnPicker.vue'
import RowDetailsModal  from '../../Components/RowDetailsModal.vue'
import FilterModal      from '../../Components/FilterModal.vue'

const props = defineProps({
  table:          { type: String, required: true },
  columns:        { type: Array,  required: true },
  primaryKey:     { type: String, default: null },
  rows:           { type: Array,  required: true },
  pagination:     { type: Object, required: true },
  filters:        { type: Object, required: true },
  perPageOptions: { type: Array,  default: () => [10, 25, 50, 100] },
})

// ─── Column Visibility ────────────────────────────────────────────────────────
const columnVisible = ref({})

// ─── Filters & Modal ──────────────────────────────────────────────────────────
const showFilterModal = ref(false)
const localAdvancedFilters = ref((() => {
  if (typeof props.filters.advanced === 'string') {
    try { return JSON.parse(props.filters.advanced) } catch(e) { return [] }
  }
  return props.filters.advanced || []
})())

import { watch } from 'vue'
watch(() => props.filters.advanced, (newVal) => {
  if (typeof newVal === 'string') {
    try { localAdvancedFilters.value = JSON.parse(newVal) } catch(e) { localAdvancedFilters.value = [] }
  } else {
    localAdvancedFilters.value = newVal || []
  }
}, { deep: true })



const hasAdvancedFilters = computed(() => localAdvancedFilters.value.length > 0)

const applyAdvancedFilters = (newFilters) => {
  localAdvancedFilters.value = newFilters
  handleChange({ page: 1 }) // Trigger reload
}

// ─── Loading ──────────────────────────────────────────────────────────────────
const loading = ref(false)

// ─── Modal ────────────────────────────────────────────────────────────────────
const modalOpen   = ref(false)
const selectedRow = ref(null)

const openModal = (row) => {
  selectedRow.value = row
  modalOpen.value   = true
}

// ─── Handle DataTable changes (search / filter / sort / page) ─────────────────
const handleChange = (params) => {
  loading.value = true

  const query = {
    search:    params?.search    ?? props.filters.search,
    sort:      params?.sort      ?? props.filters.sort,
    direction: params?.direction ?? props.filters.direction,
    page:      params?.page      ?? props.pagination.current_page,
    perPage:   params?.perPage   ?? props.filters.perPage,
    // نرسل الفلاتر المتقدمة كنص JSON مشفر
    advanced:  localAdvancedFilters.value.length > 0 ? JSON.stringify(localAdvancedFilters.value) : undefined
  }

  // تنظيف القيم الفارغة
  Object.keys(query).forEach(k => {
    if (query[k] === null || query[k] === undefined || query[k] === '') {
      delete query[k]
    }
  })

  router.get(
    route('db-viewer.tables.show', props.table),
    query,
    {
      preserveState:  true,
      preserveScroll: true,
      replace:        true,
      onFinish:       () => { loading.value = false },
    }
  )
}

</script>

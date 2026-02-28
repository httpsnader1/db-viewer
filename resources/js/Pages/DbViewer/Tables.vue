<template>
  <DbViewerLayout :breadcrumbs="[$t('db_viewer_title'), $t('tables')]">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-white">{{ $t('tables') }}</h1>
          <p class="text-sm text-gray-400 mt-1">
            {{ tables.length }} {{ $t('total_tables') }}
          </p>
        </div>
        <!-- Search -->
        <div class="relative">
          <MagnifyingGlassIcon :class="['absolute top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500', $page.props.db_viewer.locale === 'ar' ? 'right-3' : 'left-3']" />
          <input
            v-model="search"
            type="text"
            :placeholder="$t('filter_by_name')"
            :class="['py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 w-64', $page.props.db_viewer.locale === 'ar' ? 'pr-9 pl-4' : 'pl-9 pr-4']"
          />
        </div>
      </div>

      <!-- Tables Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        <Link
          v-for="table in filteredTables"
          :key="table.name"
          :href="route('db-viewer.tables.show', table.name)"
          class="group relative overflow-hidden bg-gray-900 border border-gray-800 rounded-xl p-4 hover:border-violet-500/50 hover:bg-gray-800/80 transition-all duration-200"
        >
          <!-- Glow -->
          <div class="absolute top-0 right-0 w-16 h-16 bg-violet-500/5 rounded-full blur-xl group-hover:bg-violet-500/10 transition-all" />

          <div class="relative flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-500/20 transition-colors">
              <TableCellsIcon class="w-4 h-4 text-violet-400" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white font-mono truncate">{{ table.name }}</p>
              <p class="text-xs text-gray-500 mt-0.5">
                {{ table.row_count.toLocaleString() }} {{ $t('rows') }}
              </p>
            </div>
            <ArrowRightIcon class="w-4 h-4 text-gray-700 group-hover:text-violet-400 group-hover:translate-x-0.5 transition-all flex-shrink-0 mt-0.5" />
          </div>
        </Link>

        <!-- Empty -->
        <div v-if="!filteredTables.length" class="col-span-full py-16 text-center">
          <CircleStackIcon class="w-10 h-10 text-gray-700 mx-auto mb-3" />
          <p class="text-gray-500 text-sm">{{ $t('no_tables_found') }}</p>
        </div>
      </div>
    </div>
  </DbViewerLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  MagnifyingGlassIcon,
  TableCellsIcon,
  ArrowRightIcon,
  CircleStackIcon,
} from '@heroicons/vue/24/outline'
import DbViewerLayout from '../../Components/DbViewerLayout.vue'

const props = defineProps({
  tables: { type: Array, required: true },
})

const search = ref('')

const filteredTables = computed(() =>
  props.tables.filter((t) =>
    t.name.toLowerCase().includes(search.value.toLowerCase())
  )
)
</script>

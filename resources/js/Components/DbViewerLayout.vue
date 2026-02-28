<template>
  <div :dir="page.props.db_viewer.locale === 'ar' ? 'rtl' : 'ltr'" class="min-h-screen bg-gray-950">
  <!-- Sidebar Navigation -->
  <aside 
    :class="[
      'fixed inset-y-0 w-64 bg-gray-900 border-gray-800 flex flex-col z-40',
      page.props.db_viewer.locale === 'ar' ? 'right-0 border-l' : 'left-0 border-r'
    ]"
  >
    <!-- Logo -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/25">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4" />
        </svg>
      </div>
      <div>
        <p class="text-sm font-semibold text-white">{{ $t('db_viewer_title') }}</p>
        <p class="text-xs text-gray-500">{{ database }}</p>
      </div>
    </div>

    <!-- Nav Links -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <Link
        :href="route('db-viewer.dashboard')"
        :class="[
          'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150 mb-4',
          isActive(route('db-viewer.dashboard'))
            ? 'bg-violet-500/15 text-violet-400 border border-violet-500/30'
            : 'text-gray-400 hover:text-white hover:bg-gray-800',
        ]"
      >
        <HomeIcon class="w-4 h-4 flex-shrink-0" />
        {{ $t('dashboard') }}
      </Link>

      <div class="px-3 mb-2 flex items-center justify-between">
         <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ $t('tables') }}</span>
         <span class="text-[10px] bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded">{{ allTables.length }}</span>
      </div>

      <!-- Global Search Tables -->
      <div class="px-3 mb-4">
        <div class="relative group">
          < MagnifyingGlassIcon :class="['absolute top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-600 transition-colors group-focus-within:text-violet-500', page.props.db_viewer.locale === 'ar' ? 'right-3' : 'left-3']" />
          <input 
            v-model="tableSearch"
            type="text" 
            :placeholder="$t('search_tables_placeholder')" 
            :class="[
              'w-full bg-gray-950/50 border border-gray-800 rounded-lg py-1.5 text-[12px] text-white placeholder-gray-600 focus:outline-none focus:border-violet-500 transition-all',
              page.props.db_viewer.locale === 'ar' ? 'pr-9 pl-3' : 'pl-9 pr-3'
            ]"
          />
        </div>
      </div>

      <div class="space-y-0.5">
        <Link
          v-for="table in filteredTables"
          :key="table.name"
          :href="route('db-viewer.tables.show', table.name)"
          :class="[
            'flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150 group',
            isActive(route('db-viewer.tables.show', table.name))
              ? 'bg-violet-500/10 text-violet-400'
              : 'text-gray-400 hover:text-white hover:bg-gray-800/50',
          ]"
        >
          <div class="flex items-center gap-2.5 truncate">
            <TableCellsIcon class="w-3.5 h-3.5 flex-shrink-0 opacity-50 group-hover:opacity-100" />
            <span class="truncate font-mono text-[13px]">{{ table.name }}</span>
          </div>
          <span class="text-[10px] text-gray-600 font-mono">{{ formatCount(table.row_count) }}</span>
        </Link>
      </div>

    </nav>

    <!-- Sidebar Footer (Author) -->
    <div class="p-4 border-t border-gray-800 bg-gray-950/50">
      <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mb-3">{{ $t('made_with') }} <span v-text="`❤️`" class="animate-pulse"/> {{ $t('by') }}</p>
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-gray-300">Mohamed Nader</span>
        <div class="flex items-center gap-2">
          <!-- GitHub -->
          <a href="https://github.com/httpsnader1" target="_blank" class="text-gray-500 hover:text-white transition-colors" title="GitHub">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.041-1.416-4.041-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
          </a>
          <!-- LinkedIn -->
          <a href="https://www.linkedin.com/in/a-mohamed-nader/" target="_blank" class="text-gray-500 hover:text-[#0a66c2] transition-colors" title="LinkedIn">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.238 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
          </a>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <div 
    :class="[
      'min-h-screen flex flex-col',
      page.props.db_viewer.locale === 'ar' ? 'pr-64' : 'pl-64'
    ]"
  >
    <!-- Top Bar -->
    <header class="sticky top-0 z-30 bg-gray-950/80 backdrop-blur-sm border-b border-gray-800 px-6 py-3 flex items-center gap-6">
      <!-- Breadcrumbs -->
      <div class="flex items-center gap-2 text-sm text-gray-400 flex-1">
        <span v-for="(crumb, i) in breadcrumbs" :key="i">
          <span v-if="i > 0" class="mx-2 text-gray-700">/</span>
          <span :class="i === breadcrumbs.length - 1 ? 'text-white font-medium' : 'hover:text-white cursor-pointer'">
            {{ crumb }}
          </span>
        </span>
      </div>

      <div class="flex-1"></div>

      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          {{ database }}
        </span>

        <button
          @click="logout"
          class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-500/70 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 transition-all duration-150"
          :title="$t('logout')"
        >
          <ArrowLeftOnRectangleIcon :class="['w-3.5 h-3.5', page.props.db_viewer.locale === 'ar' ? 'rotate-180' : '']" />
          <span>{{ $t('logout') }}</span>
        </button>
      </div>
    </header>


    <!-- Page Slot -->
    <main class="flex-1 p-6">
      <slot />
    </main>
  </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import {
  HomeIcon,
  TableCellsIcon,
  ArrowLeftOnRectangleIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()

const tableSearch = ref('')

const logout = () => {
  router.post(route('db-viewer.logout'))
}

const database  = computed(() => page.props?.db_viewer?.dbName ?? '—')
const allTables = computed(() => page.props?.db_viewer?.tables ?? [])

const filteredTables = computed(() => {
  if (!tableSearch.value) return allTables.value
  return allTables.value.filter(t => 
    t.name.toLowerCase().includes(tableSearch.value.toLowerCase())
  )
})

const formatCount = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num
}


const isActive = (href) => {
  const currentPath = page.url?.split('?')[0] // تجاهل الـ query params
  const targetPath  = href.replace(window.location.origin, '')
  
  // إذا كان الرابط هو الـ Dashboard (المسار الرئيسي للباكدج)
  if (targetPath === route('db-viewer.dashboard', [], false)) {
    return currentPath === targetPath
  }

  // لباقي الروابط (مثل الجداول)، نستخدم startsWith لكي يظل الـ highlight فعالاً عند الدخول لجدول معين
  return currentPath.startsWith(targetPath)
}

const props = defineProps({
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
})
</script>

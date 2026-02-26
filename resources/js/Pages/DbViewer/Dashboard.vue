<template>
  <DbViewerLayout :breadcrumbs="['DB Viewer', 'Dashboard']">
    <div class="space-y-8">
      <!-- Page Title & Date Filter -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white tracking-tight">Database Overview</h1>
          <p class="text-sm text-gray-400 mt-1">
            Connected to <span class="text-violet-400 font-medium">{{ stats.database }}</span>
            via <span class="text-gray-300 font-medium">{{ stats.driver }}</span>
          </p>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 bg-gray-900 border border-gray-800 p-1 rounded-2xl shadow-inner">
            <VueDatePicker
              v-model="dateRange"
              range
              dark
              :enable-time-picker="false"
              auto-apply
              :format="formatDateRange"
              placeholder="Select Date Range"
              class="db-viewer-datepicker"
              @update:model-value="fetchStats"
            >
              <template #trigger>
                <button class="flex items-center gap-2 bg-gray-950 border border-gray-800 rounded-xl px-4 py-2 text-xs text-white hover:border-violet-500/50 transition-all min-w-[240px]">
                  <CalendarIcon class="w-4 h-4 text-violet-400" />
                  <span>{{ dateRangeText }}</span>
                </button>
              </template>
            </VueDatePicker>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center">
              <CircleStackIcon class="w-5 h-5 text-orange-400" />
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold text-white">{{ stats.total_records.toLocaleString() }}</div>
              <div class="text-[10px] text-gray-500 uppercase font-bold">Total Records</div>
            </div>
          </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-0">
            <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center">
              <TableCellsIcon class="w-5 h-5 text-violet-400" />
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold text-white">{{ stats.total_tables }}</div>
              <div class="text-[10px] text-gray-500 uppercase font-bold">Total Tables</div>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
              <CircleStackIcon class="w-5 h-5 text-emerald-400" />
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold text-white">{{ stats.driver }}</div>
              <div class="text-[10px] text-gray-500 uppercase font-bold">DB Driver</div>
            </div>
          </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex items-center justify-between">
            <div class="text-xs text-gray-500">
              Last updated: {{ new Date().toLocaleTimeString() }}
            </div>
            <button @click="fetchStats" class="text-violet-400 hover:text-white transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </button>
        </div>
      </div>

      <!-- Charts Section -->
      <div v-if="stats.charts && stats.charts.length" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div 
          v-for="(chart, index) in stats.charts" 
          :key="index"
          class="bg-gray-900 border border-gray-800 rounded-2xl p-6"
        >
          <div class="flex items-center justify-between mb-2">
            <div>
              <h3 class="text-sm font-bold text-gray-300">{{ chart.label }}</h3>
              <p class="text-2xl font-black text-white mt-1">{{ chart.total.toLocaleString() }} <span class="text-xs font-normal text-gray-500">records</span></p>
            </div>
          </div>
          
          <div class="h-[250px] w-full overflow-hidden">
            <apexchart 
              type="area" 
              height="100%" 
              :options="getChartOptions(chart)" 
              :series="[{ name: chart.label, data: chart.data }]"
            />
          </div>
        </div>
      </div>

      <!-- Top Tables Chart -->
      <div class="rounded-2xl bg-gray-900 border border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
          <h2 class="text-sm font-bold text-gray-300">Popular Tables (by Record Count)</h2>
          <Link
            :href="route('db-viewer.tables')"
            class="text-xs text-violet-400 hover:text-violet-300 font-bold transition-colors"
          >
            View all Tables
          </Link>
        </div>

        <div class="p-6 space-y-4">
          <div
            v-for="table in stats.table_stats"
            :key="table.name"
            class="flex items-center gap-4 group"
          >
            <!-- Table name -->
            <Link
              :href="route('db-viewer.tables.show', table.name)"
              class="w-48 text-sm text-gray-400 group-hover:text-violet-400 font-mono truncate transition-colors"
            >
              {{ table.name }}
            </Link>

            <!-- Bar -->
            <div class="flex-1 h-2 bg-gray-800 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 transition-all duration-700"
                :style="{ width: barWidth(table.row_count) + '%' }"
              />
            </div>

            <!-- Count -->
            <span class="w-24 text-right text-xs text-gray-500 tabular-nums font-mono">
              {{ table.row_count.toLocaleString() }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </DbViewerLayout>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DbViewerLayout from '../../Components/DbViewerLayout.vue'
import { 
  TableCellsIcon, 
  CircleStackIcon, 
  CalendarIcon 
} from '@heroicons/vue/24/outline'
import VueApexCharts from "vue3-apexcharts";
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
  stats: { type: Object, required: true },
})

const apexchart = VueApexCharts;

const dateRange = ref([])

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const start = urlParams.get('startDate');
  const end = urlParams.get('endDate');

  if (start && end) {
    dateRange.value = [new Date(start), new Date(end)];
  } else {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    dateRange.value = [firstDay, lastDay];
  }
})

const dateRangeText = computed(() => {
  if (!dateRange.value || !dateRange.value[0] || !dateRange.value[1]) return 'Select Date Range';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return `${dateRange.value[0].toLocaleDateString('en-US', options)} - ${dateRange.value[1].toLocaleDateString('en-US', options)}`;
})

const formatDateRange = (dates) => {
  if (dates.length !== 2) return '';
  return `${dates[0].toLocaleDateString()} - ${dates[1].toLocaleDateString()}`;
}

const fetchStats = () => {
  if (!dateRange.value || dateRange.value.length !== 2) return;
  
  const startDate = dateRange.value[0].toISOString().split('T')[0];
  const endDate = dateRange.value[1].toISOString().split('T')[0];

  router.get(route('db-viewer.dashboard'), {
    startDate,
    endDate
  }, {
    preserveState: true,
    preserveScroll: true
  });
}

const maxRows = computed(() =>
  Math.max(...(props.stats.table_stats ?? []).map((t) => t.row_count), 1)
)

const barWidth = (count) => Math.max(2, (count / maxRows.value) * 100)

const getChartOptions = (chart) => {
  return {
    chart: {
      id: 'chart-' + chart.label,
      toolbar: { show: false },
      zoom: { enabled: false },
      background: 'transparent',
    },
    theme: { mode: 'dark' },
    stroke: { curve: 'smooth', width: 2, colors: [chart.color] },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.45,
        opacityTo: 0.05,
        stops: [20, 100],
        colorStops: [
          { offset: 0, color: chart.color, opacity: 0.4 },
          { offset: 100, color: chart.color, opacity: 0 }
        ]
      }
    },
    markers: { size: 4, colors: [chart.color], strokeColors: '#111827', strokeWidth: 2 },
    grid: { show: true, borderColor: '#1f2937', strokeDashArray: 4 },
    xaxis: {
      type: 'datetime',
      labels: { style: { colors: '#9ca3af', fontSize: '10px' } },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      labels: { style: { colors: '#9ca3af', fontSize: '10px' } },
    },
    tooltip: { theme: 'dark' },
    dataLabels: { enabled: false },
    colors: [chart.color],
  }
}
</script>

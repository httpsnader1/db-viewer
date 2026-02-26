<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')" />
        
        <div class="relative w-full max-w-3xl bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <div class="flex items-center gap-3">
              <FunnelIcon class="w-5 h-5 text-violet-400" />
              <h2 class="text-lg font-bold text-white">Advanced Filters</h2>
            </div>
            <button @click="$emit('close')" class="text-gray-500 hover:text-white"><XMarkIcon class="w-5 h-5" /></button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div v-if="localFilters.length === 0" class="text-center py-8 border-2 border-dashed border-gray-800 rounded-xl">
              <p class="text-gray-500 text-sm">No filters applied. Click 'Add Filter' to start.</p>
            </div>

            <div v-for="(filter, index) in localFilters" :key="index" class="flex items-start gap-2 bg-gray-800/40 p-3 rounded-xl border border-gray-800">
              <!-- Column Selection -->
              <div class="flex-1">
                <label class="block text-[10px] uppercase font-bold text-gray-600 mb-1 ml-1">Column</label>
                <select v-model="filter.column" class="w-full bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-violet-500">
                  <option v-for="col in columns" :key="col.name" :value="col.name">{{ col.name }}</option>
                </select>
              </div>

              <!-- Operator Selection -->
              <div class="w-40">
                <label class="block text-[10px] uppercase font-bold text-gray-600 mb-1 ml-1">Operator</label>
                <select v-model="filter.operator" class="w-full bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-violet-500">
                  <option v-for="op in getOperators()" :key="op.id" :value="op.id">{{ op.label }}</option>
                </select>
              </div>

              <!-- Value Input -->
              <div class="flex-[1.5]">
                <label class="block text-[10px] uppercase font-bold text-gray-600 mb-1 ml-1">Value</label>
                <input 
                  v-if="!['null', 'not_null'].includes(filter.operator)"
                  v-model="filter.value" 
                  type="text" 
                  class="w-full bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-violet-500"
                  placeholder="Value..."
                />
                <div v-else class="h-[38px] flex items-center px-3 text-xs text-gray-500 italic">No value needed</div>
              </div>

              <!-- Remove btn -->
              <button @click="removeFilter(index)" class="mt-6 p-2 text-gray-500 hover:text-red-400 transition-colors">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>

            <button @click="addFilter" class="w-full py-3 border-2 border-dashed border-gray-800 rounded-xl text-gray-500 hover:text-violet-400 hover:border-violet-400/30 transition-all text-sm font-medium flex items-center justify-center gap-2">
              <PlusIcon class="w-4 h-4" /> Add Filter Rule
            </button>
          </div>

          <!-- Footer -->
          <div class="p-6 border-t border-gray-800 flex items-center justify-between">
            <button @click="clearAll" class="text-sm text-gray-500 hover:text-white">Clear All</button>
            <div class="flex gap-3">
              <button @click="$emit('close')" class="px-5 py-2 text-sm font-medium text-gray-400 hover:text-white">Cancel</button>
              <button @click="apply" class="px-6 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-violet-600/20">Apply Filters</button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { FunnelIcon, XMarkIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show:    { type: Boolean, required: true },
  columns: { type: Array,   required: true },
  filters: { type: Array,   default: () => [] }
})

const emit = defineEmits(['close', 'apply'])

const localFilters = ref([])

// Sync with props when modal opens
watch(() => props.show, (isShown) => {
  if (isShown) {
    localFilters.value = props.filters.length > 0 ? JSON.parse(JSON.stringify(props.filters)) : []
  }
})

const addFilter = () => {
  localFilters.value.push({
    column:   props.columns[0]?.name,
    operator: '=',
    value:    ''
  })
}

const removeFilter = (index) => {
  localFilters.value.splice(index, 1)
}

const clearAll = () => {
  localFilters.value = []
}

const apply = () => {
  emit('apply', localFilters.value)
  emit('close')
}

const getOperators = () => {
  return [
    { id: '=', label: '=' },
    { id: '!=', label: '!=' },
    { id: 'null', label: 'Is NULL' },
    { id: 'not_null', label: 'Is NOT NULL' },
    { id: '>', label: '>' },
    { id: '<', label: '<' },
    { id: '>=', label: '>=' },
    { id: '<=', label: '<=' },
    { id: 'like', label: 'LIKE' },
    { id: 'not_like', label: 'NOT LIKE' },
    { id: 'starts_with', label: 'STARTS WITH' },
    { id: 'ends_with', label: 'ENDS WITH' },
  ]
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

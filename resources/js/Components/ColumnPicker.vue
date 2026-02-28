<template>
  <!-- Trigger Button -->
  <button
    @click="open = true"
    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-violet-500/40 text-sm text-gray-300 hover:text-white transition-all duration-150"
  >
    <AdjustmentsHorizontalIcon class="w-4 h-4" />
    {{ $t('columns') }}
    <span class="text-xs bg-violet-500/20 text-violet-400 px-1.5 py-0.5 rounded-full font-medium">
      {{ visibleCount }}/{{ columns.length }}
    </span>
  </button>

  <!-- Dropdown Panel -->
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="open"
        class="fixed z-50"
        :style="{ top: panelY + 'px', right: panelX + 'px' }"
      >
        <div class="w-64 bg-gray-900 border border-gray-700 rounded-xl shadow-2xl shadow-black/50 overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
            <span class="text-sm font-semibold text-white">{{ $t('column_visibility') }}</span>
            <div class="flex items-center gap-2">
              <button @click="toggleAll(true)"  class="text-xs text-violet-400 hover:text-violet-300">{{ $t('all') }}</button>
              <span class="text-gray-700">·</span>
              <button @click="toggleAll(false)" class="text-xs text-gray-500 hover:text-gray-300">{{ $t('none') }}</button>
              <button @click="open = false" class="ml-2 text-gray-500 hover:text-white">
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Column list -->
          <ul class="max-h-72 overflow-y-auto py-2 divide-y divide-gray-800/50">
            <li
              v-for="col in columns"
              :key="col.name"
              class="flex items-center gap-3 px-4 py-2 hover:bg-gray-800/60 cursor-pointer transition-colors"
              @click="toggle(col.name)"
            >
              <div :class="['w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors',
                visible[col.name] !== false
                  ? 'bg-violet-600 border-violet-500'
                  : 'border-gray-600 bg-transparent']">
                <CheckIcon v-if="visible[col.name] !== false" class="w-2.5 h-2.5 text-white" />
              </div>
              <span class="text-sm text-gray-300 font-mono truncate">{{ col.name }}</span>
              <span class="ml-auto text-xs text-gray-600">{{ col.type }}</span>
            </li>
          </ul>

          <!-- Reset -->
          <div class="px-4 py-2 border-t border-gray-800">
            <button
              @click="reset"
              class="w-full text-xs text-gray-500 hover:text-white transition-colors text-center py-1"
            >
              {{ $t('reset_to_defaults') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Click outside to close -->
    <div v-if="open" class="fixed inset-0 z-40" @click="open = false" />
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import {
  AdjustmentsHorizontalIcon,
  XMarkIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  columns:     { type: Array,  required: true },
  table:       { type: String, required: true },
  modelValue:  { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

const open    = ref(false)
const panelY  = ref(64)
const panelX  = ref(16)
const visible = ref({ ...props.modelValue })

const STORAGE_KEY = computed(() => `db_viewer_cols_${props.table}`)

const visibleCount = computed(() =>
  props.columns.filter((c) => visible.value[c.name] !== false).length
)

// Persist to localStorage
watch(visible, (v) => {
  try {
    localStorage.setItem(STORAGE_KEY.value, JSON.stringify(v))
  } catch {}
  emit('update:modelValue', { ...v })
}, { deep: true })

// Load from localStorage on mount
onMounted(() => {
  try {
    const saved = localStorage.getItem(STORAGE_KEY.value)
    if (saved) {
      visible.value = JSON.parse(saved)
    }
  } catch {}
})

const toggle = (name) => {
  visible.value = {
    ...visible.value,
    [name]: visible.value[name] === false ? true : false,
  }
}

const toggleAll = (state) => {
  const next = {}
  props.columns.forEach((c) => { next[c.name] = state })
  visible.value = next
}

const reset = () => {
  visible.value = {}
  try { localStorage.removeItem(STORAGE_KEY.value) } catch {}
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s, transform 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px) scale(0.98); }
</style>

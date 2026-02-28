<template>
  <!-- Backdrop -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="$emit('close')"
      >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')" />

        <!-- Panel -->
        <div class="relative z-10 w-full max-w-2xl max-h-[85vh] bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl shadow-black/50 flex flex-col overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-violet-500/20 border border-violet-500/30 flex items-center justify-center">
                <EyeIcon class="w-4 h-4 text-violet-400" />
              </div>
              <div>
                <h2 class="text-base font-semibold text-white">{{ $t('row_details') }}</h2>
                <p class="text-xs text-gray-500">{{ Object.keys(row ?? {}).length }} {{ $t('fields') }}</p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="w-8 h-8 rounded-lg bg-gray-800 hover:bg-gray-700 flex items-center justify-center text-gray-400 hover:text-white transition-colors"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>

          <!-- Show hidden toggle -->
          <div class="px-6 py-2 border-b border-gray-800/60 flex items-center justify-between bg-gray-900/50">
            <span class="text-xs text-gray-500">{{ $t('showing_all') }} {{ Object.keys(row ?? {}).length }} {{ $t('columns') }}</span>
            <label class="flex items-center gap-2 cursor-pointer">
              <span class="text-xs text-gray-400">{{ $t('format_values') }}</span>
              <div
                @click="formatted = !formatted"
                :class="['relative w-9 h-5 rounded-full transition-colors cursor-pointer', formatted ? 'bg-violet-600' : 'bg-gray-700']"
              >
                <div :class="['absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform', formatted ? 'translate-x-4' : 'translate-x-0.5']" />
              </div>
            </label>
          </div>

          <!-- Fields -->
          <div class="flex-1 overflow-y-auto p-4 space-y-1">
            <div
              v-for="[key, value] in Object.entries(row ?? {})"
              :key="key"
              class="group flex gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-800/60 transition-colors"
            >
              <!-- Key -->
              <div class="w-40 flex-shrink-0 flex items-start pt-0.5">
                <span class="text-xs font-mono font-medium text-violet-400 truncate">{{ key }}</span>
              </div>

              <!-- Value -->
              <div class="flex-1 min-w-0">
                <!-- NULL -->
                <span v-if="value === null || value === undefined"
                  class="text-xs text-gray-600 italic font-mono">NULL</span>

                <!-- Boolean -->
                <span
                  v-else-if="typeof value === 'boolean' || (formatted && isBoolLike(value))"
                  :class="['inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium',
                    toBool(value) ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400']"
                >
                  <span :class="['w-1.5 h-1.5 rounded-full', toBool(value) ? 'bg-emerald-400' : 'bg-red-400']" />
                  {{ toBool(value) ? 'true' : 'false' }}
                </span>

                <!-- JSON -->
                <pre
                  v-else-if="formatted && isJson(value)"
                  class="text-xs font-mono text-emerald-300 bg-gray-800 rounded-lg p-2 overflow-x-auto whitespace-pre-wrap break-all"
                >{{ prettyJson(value) }}</pre>

                <!-- Long text -->
                <p
                  v-else
                  class="text-sm text-gray-200 break-words font-mono leading-relaxed"
                >{{ formatted ? formatValue(key, value) : value }}</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-3 border-t border-gray-800 flex justify-end flex-shrink-0">
            <button
              @click="$emit('close')"
              class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-sm text-gray-300 hover:text-white transition-colors"
            >
              {{ $t('close') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { EyeIcon, XMarkIcon } from '@heroicons/vue/24/outline'

defineEmits(['close'])
defineProps({
  show: { type: Boolean, default: false },
  row:  { type: Object,  default: () => ({}) },
})

const formatted = ref(true)

const isJson = (val) => {
  if (typeof val !== 'string') return false
  const str = val.trim()
  return (str.startsWith('{') || str.startsWith('[')) && (() => {
    try { JSON.parse(str); return true } catch { return false }
  })()
}

const prettyJson = (val) => {
  try { return JSON.stringify(JSON.parse(val), null, 2) } catch { return val }
}

const isBoolLike = (val) => [0, 1, '0', '1', 'true', 'false'].includes(val)

const toBool = (val) => [1, '1', 'true', true].includes(val)

const formatValue = (key, val) => {
  // Detect timestamp-ish keys
  const dateKeys = ['created_at', 'updated_at', 'deleted_at', 'verified_at', 'date', 'time']
  if (dateKeys.some((k) => key.toLowerCase().includes(k)) && val) {
    const d = new Date(val)
    if (!isNaN(d)) return d.toLocaleString()
  }
  return val
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease;
}
.modal-enter-from .relative {
  transform: scale(0.95) translateY(8px);
}
</style>

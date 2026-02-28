<template>
  <!-- Text / String -->
  <input
    v-if="column.type === 'string'"
    v-model="localValue"
    type="text"
    :placeholder="$t('filter_placeholder')"
    class="w-full px-2 py-1 bg-gray-800 border border-gray-700 rounded text-xs text-gray-300 placeholder-gray-600 focus:outline-none focus:border-violet-500"
    @input="emit('update:modelValue', localValue || undefined)"
  />

  <!-- Number -->
  <input
    v-else-if="column.type === 'integer' || column.type === 'float'"
    v-model="localValue"
    type="number"
    :placeholder="$t('equals_value')"
    class="w-full px-2 py-1 bg-gray-800 border border-gray-700 rounded text-xs text-gray-300 placeholder-gray-600 focus:outline-none focus:border-violet-500"
    @input="emit('update:modelValue', localValue || undefined)"
  />

  <!-- Boolean -->
  <select
    v-else-if="column.type === 'boolean'"
    v-model="localValue"
    class="w-full px-2 py-1 bg-gray-800 border border-gray-700 rounded text-xs text-gray-300 focus:outline-none focus:border-violet-500"
    @change="emit('update:modelValue', localValue === '' ? undefined : localValue)"
  >
    <option value="">{{ $t('any') }}</option>
    <option value="1">{{ $t('true') }}</option>
    <option value="0">{{ $t('false') }}</option>
  </select>

  <!-- DateTime: range from/to -->
  <div v-else-if="column.type === 'datetime'" class="flex flex-col gap-0.5">
    <input
      v-model="rangeFrom"
      type="date"
      class="w-full px-2 py-1 bg-gray-800 border border-gray-700 rounded text-xs text-gray-300 focus:outline-none focus:border-violet-500"
      :placeholder="$t('from')"
      @change="emitRange"
    />
    <input
      v-model="rangeTo"
      type="date"
      class="w-full px-2 py-1 bg-gray-800 border border-gray-700 rounded text-xs text-gray-300 focus:outline-none focus:border-violet-500"
      :placeholder="$t('to')"
      @change="emitRange"
    />
  </div>

  <!-- Fallback: no filter -->
  <span v-else class="block text-xs text-gray-700 italic">—</span>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  column:     { type: Object, required: true },
  modelValue: { default: undefined },
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue ?? '')
const rangeFrom  = ref('')
const rangeTo    = ref('')

const emitRange = () => {
  if (!rangeFrom.value && !rangeTo.value) {
    emit('update:modelValue', undefined)
  } else {
    emit('update:modelValue', { from: rangeFrom.value, to: rangeTo.value })
  }
}
</script>

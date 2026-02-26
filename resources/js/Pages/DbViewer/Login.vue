<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-950 p-4 font-sans antialiased text-gray-100">
    <div class="relative w-full max-w-md">
      <!-- Glow background -->
      <div class="absolute -top-24 -left-24 w-64 h-64 bg-violet-600/10 rounded-full blur-[100px]" />
      <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-indigo-600/10 rounded-full blur-[100px]" />

      <!-- Card -->
      <div class="relative bg-gray-900/50 backdrop-blur-xl border border-gray-800 rounded-3xl p-8 shadow-2xl shadow-black/50">
        <!-- Logo -->
        <div class="flex flex-col items-center gap-4 mb-8">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-2xl shadow-violet-500/20">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4" />
            </svg>
          </div>
          <div class="text-center">
            <h1 class="text-2xl font-bold tracking-tight text-white">DB Viewer</h1>
            <p class="text-sm text-gray-400 mt-1">Please enter your password to continue</p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <div class="space-y-2">
            <label for="password" class="text-xs font-semibold uppercase tracking-wider text-gray-500 ml-1">Password</label>
            <div class="relative group">
              <input
                id="password"
                v-model="form.password"
                type="password"
                autofocus
                :class="[
                  'w-full bg-gray-950 border rounded-2xl px-5 py-4 text-white placeholder-gray-600 outline-none transition-all duration-300',
                  errors.password ? 'border-red-500/50 focus:border-red-500' : 'border-gray-800 focus:border-violet-500 group-hover:border-gray-700'
                ]"
                placeholder="••••••••"
              />
              <div v-if="errors.password" class="absolute -bottom-6 left-1 text-[11px] text-red-500 font-medium">
                {{ errors.password }}
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="relative w-full group overflow-hidden bg-white text-gray-950 font-bold py-4 rounded-2xl transition-all duration-300 hover:bg-violet-50 active:scale-[0.98] disabled:opacity-50"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-violet-500 to-indigo-500 opacity-0 group-hover:opacity-10 transition-opacity" />
            <span v-if="!form.processing">Login to Dashboard</span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-4 w-4 text-gray-950" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Verifying...
            </span>
          </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800/50">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[9px] text-gray-600 font-bold uppercase tracking-wider">Made With <span v-text="`❤️`" class="animate-pulse"/> By</span>
              <span class="text-xs font-bold text-gray-400">Mohamed Nader</span>
            </div>
            <div class="flex items-center gap-3">
              <a href="https://github.com/httpsnader1" target="_blank" class="text-gray-600 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.041-1.416-4.041-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
              </a>
              <a href="https://www.linkedin.com/in/a-mohamed-nader/" target="_blank" class="text-gray-600 hover:text-[#0a66c2] transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.238 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
              </a>
            </div>
          </div>
          <p class="text-center text-[9px] text-gray-700 mt-4 uppercase tracking-[0.2em]">
            Secured Session · DB Viewer v1.0
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  errors: Object
})

const form = useForm({
  password: ''
})

const submit = () => {
  form.post(route('db-viewer.authenticate'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

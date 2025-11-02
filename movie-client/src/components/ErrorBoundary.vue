<template>
  <div v-if="hasError" class="error-boundary">
    <q-banner class="bg-negative text-white">
      <template v-slot:avatar>
        <q-icon name="error" />
      </template>
      Something went wrong. Please try again.
      <template v-slot:action>
        <q-btn flat label="Try Again" @click="handleReset" />
      </template>
    </q-banner>
  </div>
  <template v-else>
    <slot />
  </template>
</template>

<script>
import { defineComponent, ref, onErrorCaptured } from 'vue'

export default defineComponent({
  name: 'ErrorBoundary',

  setup() {
    const hasError = ref(false)

    onErrorCaptured((err) => {
      console.error('Error caught by boundary:', err)
      hasError.value = true
      return false
    })

    const handleReset = () => {
      hasError.value = false
      // Optionally reload the page for a full reset
      // window.location.reload()
    }

    return {
      hasError,
      handleReset,
    }
  },
})
</script>

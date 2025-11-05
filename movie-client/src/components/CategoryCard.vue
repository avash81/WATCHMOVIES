<template>
  <div class="category-card" @click="$emit('click')">
    <q-icon :name="category.icon" size="2rem" class="mb-2" />
    <span class="font-semibold">{{ category.name }}</span>
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'CategoryCard',
  props: {
    category: {
      type: Object,
      required: true,
    },
  },
  emits: ['click'],
})
</script>

<template>
  <q-page class="q-pa-md">
    <h1 class="text-4xl font-bold mb-8 text-primary">Authorized Movie List</h1>

    <div v-if="loading" class="text-center">
      <q-spinner-hourglass color="primary" size="3em" />
      <div class="q-mt-md">Loading films...</div>
    </div>

    <div v-else-if="error" class="text-red-600">Error loading movies: {{ error.message }}</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <q-card v-for="movie in movies" :key="movie.id" class="shadow-lg border border-gray-200">
        <q-card-section>
          <div class="text-xl font-semibold q-mb-sm">{{ movie.title }}</div>
          <q-badge color="secondary">{{ movie.genre }} ({{ movie.release_year }})</q-badge>
        </q-card-section>

        <q-card-section>
          {{ movie.description }}
        </q-card-section>

        <q-card-actions align="right">
          <q-btn
            color="primary"
            :href="movie.external_link"
            target="_blank"
            label="Watch/Download (Legal)"
            flat
          />
        </q-card-actions>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Reactive variables for state management
const movies = ref([])
const loading = ref(true)
const error = ref(null)

// Function to fetch data from the Laravel API
const fetchMovies = async () => {
  try {
    // Using the proxy configured in quasar.config.js
    const response = await axios.get('/api/movies')
    movies.value = response.data.data
  } catch (err) {
    console.error('API Fetch Error:', err)
    error.value = err
  } finally {
    loading.value = false
  }
}

// Fetch movies when the component is mounted
onMounted(fetchMovies)
</script>

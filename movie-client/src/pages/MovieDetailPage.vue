<template>
  <q-page class="q-pa-md">
    <!-- Header/Title section -->
    <div class="text-h4 q-mb-md text-weight-bold text-primary">Movie Detail</div>

    <q-card v-if="loading" class="q-pa-lg text-center">
      <q-spinner-dots color="primary" size="3em" />
      <div class="q-mt-md">Loading movie details...</div>
    </q-card>

    <q-card v-else-if="movie" class="shadow-2 q-pa-lg">
      <!-- Movie Poster and Main Info Grid -->
      <div class="row q-col-gutter-lg">
        <div class="col-xs-12 col-sm-4 col-md-3">
          <!-- Placeholder for Movie Poster -->
          <q-img
            :src="movie.poster || 'https://placehold.co/300x450/1e293b/ffffff?text=No+Poster'"
            :alt="`${movie.title} Poster`"
            fit="cover"
            spinner-color="white"
            style="border-radius: 8px"
            class="shadow-4"
          />
        </div>

        <div class="col-xs-12 col-sm-8 col-md-9">
          <!-- Movie Title, Year, and Rating -->
          <div class="text-h3 text-weight-bolder q-mb-sm text-secondary">
            {{ movie.title }} ({{ movie.release_year }})
          </div>

          <div class="q-gutter-md q-mb-lg">
            <q-badge color="amber-8" text-color="black" class="text-body1 q-pa-sm">
              <q-icon name="star" class="q-mr-xs" />
              {{ movie.rating }} / 10
            </q-badge>
            <q-badge color="grey-7" text-color="white" class="text-body1 q-pa-sm">
              {{ movie.genre }}
            </q-badge>
            <q-badge color="grey-7" text-color="white" class="text-body1 q-pa-sm">
              {{ movie.runtime }} minutes
            </q-badge>
          </div>

          <!-- Synopsis/Overview -->
          <div class="text-h6 text-grey-8 q-mb-sm">Synopsis</div>
          <div class="text-body1 q-mb-lg">
            {{ movie.summary }}
          </div>

          <!-- Director and Cast -->
          <div class="text-h6 text-grey-8 q-mb-sm">Cast & Crew</div>
          <div class="text-body1"><strong>Director:</strong> {{ movie.director }}</div>
          <div class="text-body1"><strong>Starring:</strong> {{ movie.cast.join(', ') }}</div>
        </div>
      </div>

      <q-separator spaced class="q-mt-xl" />

      <!-- Back Button -->
      <q-btn
        color="primary"
        icon="arrow_back"
        label="Back to Movies"
        @click="$router.push('/')"
        class="q-mt-md"
        flat
      />
    </q-card>

    <q-card v-else class="q-pa-lg text-center bg-red-1">
      <div class="text-h5 text-negative">Movie Not Found</div>
      <div class="q-mt-sm">Could not load details for Movie ID: {{ movieId }}.</div>
      <q-btn
        color="primary"
        label="Go Home"
        @click="$router.push('/')"
        class="q-mt-md"
        unelevated
      />
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

// Get the current route object from Vue Router
const route = useRoute()

// State variables
const movieId = ref(route.params.id) // Holds the ID extracted from the URL
const movie = ref(null) // Holds the fetched movie object
const loading = ref(true) // Loading state indicator

/**
 * Dummy function to simulate fetching movie details from a database/API.
 * In a real application, you would replace this with an actual API call
 * using 'fetch' or 'axios' to an endpoint like '/api/movies/:id'.
 */
const fetchMovieDetails = async (id) => {
  // Simulate API delay
  await new Promise((resolve) => setTimeout(resolve, 1500))

  // --- Start of Mock Data ---
  // In a real application, this data would come from Firestore or a backend API.
  const mockData = {
    m1: {
      id: 'm1',
      title: 'The Quasar Odyssey',
      release_year: 2024,
      rating: 9.2,
      genre: 'Sci-Fi, Adventure',
      runtime: 145,
      director: 'Ava Vue',
      cast: ['Quentin Quasar', 'Sarah Setup', 'Leo Layout'],
      summary:
        'A breathtaking journey across the vast expanse of the component universe, where a lone developer must initialize the final app and conquer the dreaded hydration error. Featuring stunning custom components and optimized rendering performance.',
      poster: 'https://placehold.co/300x450/374151/ffffff?text=The+Quasar+Odyssey',
    },
    m2: {
      id: 'm2',
      title: 'The Firebase Fury',
      release_year: 2023,
      rating: 8.5,
      genre: 'Action, Thriller',
      runtime: 110,
      director: 'Jake Jetstream',
      cast: ['Fiona Flash', 'Corey Conflict', 'Dave Debug'],
      summary:
        'When a critical bug threatens the integrity of the NoSQL data, an elite team of engineers must race against time to implement proper security rules and prevent a catastrophic data leak.',
      poster: 'https://placehold.co/300x450/475569/ffffff?text=The+Firebase+Fury',
    },
    // Add more mock movies here
  }
  // --- End of Mock Data ---

  return mockData[id] || null
}

onMounted(async () => {
  // 1. Get the ID from the route parameter
  const id = route.params.id

  // Update state for template display
  movieId.value = id

  // 2. Fetch the data
  try {
    const movieDetails = await fetchMovieDetails(id)
    movie.value = movieDetails // Set the movie state
  } catch (error) {
    // Handle any fetch errors
    console.error('Error fetching movie details:', error)
    movie.value = null // Ensure the UI shows "Not Found" on error
  } finally {
    loading.value = false // Always stop loading regardless of success/fail
  }
})
</script>

<style scoped>
.text-primary {
  color: #1976d2; /* Quasar Blue */
}
.text-secondary {
  color: #26a69a; /* Quasar Teal */
}
</style>

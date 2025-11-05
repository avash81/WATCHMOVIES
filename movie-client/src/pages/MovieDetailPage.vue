<template>
  <q-page class="q-pa-md bg-black text-white">
    <!-- Enhanced Loading State -->
    <div v-if="loading" class="text-center q-pa-xl">
      <q-spinner-grid color="primary" size="50px" />
      <div class="q-mt-md text-h6">Loading Movie...</div>
      <div class="q-mt-sm text-caption">This will be instant! ⚡</div>
    </div>

    <!-- Enhanced Error State -->
    <div v-else-if="error" class="text-center q-pa-xl">
      <q-icon name="error_outline" color="negative" size="60px" />
      <div class="q-mt-md text-h5">Oops! Something went wrong</div>
      <div class="q-mt-sm text-body1">{{ error }}</div>
      <q-btn
        class="q-mt-lg"
        color="primary"
        label="Try Again"
        @click="loadMovieData"
        icon="refresh"
      />
    </div>

    <!-- Movie Content -->
    <q-card dark v-else-if="movie" class="fade-in">
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-md-4">
          <q-img
            :src="posterUrl"
            class="shadow-8"
            placeholder-src="https://placehold.co/300x450/111/fff?text=Loading..."
            @error="handleImageError"
            loading="eager"
          />
        </div>
        <div class="col-12 col-md-8">
          <h1 class="text-h3 q-mb-sm">{{ movie.title }} ({{ getYear(movie.release_date) }})</h1>

          <div class="q-gutter-sm q-mb-md">
            <q-badge color="red">⭐ {{ movie.vote_average || 'N/A' }}</q-badge>
            <q-badge color="teal" v-if="movie.genre_ids && movie.genre_ids.length">
              {{ getGenreNames(movie.genre_ids) }}
            </q-badge>
            <q-badge>{{ movie.vote_count }} votes</q-badge>
          </div>

          <p class="q-mb-lg text-body1">{{ movie.overview }}</p>

          <!-- Download Buttons -->
          <div class="q-gutter-sm q-mb-lg">
            <q-btn color="red" icon="download" label="480p" @click="showDownloadMessage" />
            <q-btn color="orange" icon="download" label="720p" @click="showDownloadMessage" />
            <q-btn color="deep-orange" icon="download" label="1080p" @click="showDownloadMessage" />
          </div>

          <!-- Performance Info (Dev only) -->
          <q-banner v-if="isDev" class="bg-info text-white q-mt-md">
            <template v-slot:avatar>
              <q-icon name="speed" />
            </template>
            Loaded in {{ performanceTime }} • Source: {{ movie.source || 'API' }}
          </q-banner>
        </div>
      </div>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { apiService } from 'src/services/api'
import { useQuasar } from 'quasar'

const route = useRoute()
const $q = useQuasar()
const movie = ref(null)
const posterUrl = ref('')
const loading = ref(true)
const error = ref(null)
const performanceTime = ref('')

// Computed properties
const isDev = computed(() => process.env.DEV)

// Safe image error handler
const handleImageError = (event) => {
  console.log('Image failed to load, using placeholder')
  event.target.src = 'https://placehold.co/300x450/111/fff?text=No+Image'
}

// Helper functions
const getYear = (dateString) => {
  return new Date(dateString).getFullYear()
}

const getGenreNames = (genreIds) => {
  if (!genreIds || !genreIds.length) return 'Unknown'
  return genreIds
    .slice(0, 2)
    .map((id) => getGenreName(id))
    .join(', ')
}

const getGenreName = (genreId) => {
  const genres = {
    28: 'Action',
    12: 'Adventure',
    16: 'Animation',
    35: 'Comedy',
    80: 'Crime',
    18: 'Drama',
    10751: 'Family',
    14: 'Fantasy',
    36: 'History',
    27: 'Horror',
    10402: 'Music',
    9648: 'Mystery',
    10749: 'Romance',
    878: 'Sci-Fi',
    10770: 'TV Movie',
    53: 'Thriller',
    10752: 'War',
    37: 'Western',
  }
  return genres[genreId] || 'Unknown'
}

const showDownloadMessage = () => {
  $q.notify({
    message: 'Download feature coming soon!',
    color: 'info',
    icon: 'info',
    position: 'top',
  })
}

// Load movie data using FAST endpoint
const loadMovieData = async () => {
  const startTime = Date.now()
  loading.value = true
  error.value = null

  try {
    console.log('🚀 Loading movie details for ID:', route.params.id)

    // Use the FAST endpoint with caching
    const response = await apiService.getMovieDetailsWithCache(route.params.id)

    if (response.success) {
      movie.value = response.data
      performanceTime.value = response.response_time || `${Date.now() - startTime}ms`

      console.log('✅ Movie data loaded:', movie.value.title, 'in', performanceTime.value)

      // Set poster URL
      if (movie.value.poster_path) {
        posterUrl.value = `https://image.tmdb.org/t/p/w500${movie.value.poster_path}`
      } else {
        posterUrl.value = 'https://placehold.co/300x450/111/fff?text=No+Image'
      }
    } else {
      error.value = response.message || 'Failed to load movie'
    }
  } catch (err) {
    console.error('❌ Error loading movie:', err)
    error.value = err.message || 'Network error occurred'
  } finally {
    loading.value = false
  }
}

// Load data when component mounts
onMounted(() => {
  loadMovieData()
})

// Reload when route ID changes
watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      console.log('🔄 Route ID changed to:', newId)
      loadMovieData()
    }
  },
)
</script>

<style scoped>
.fade-in {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.q-card {
  background: #1a1a1a;
  border-radius: 12px;
  transition: transform 0.2s ease;
}

.q-card:hover {
  transform: translateY(-2px);
}

.text-h3 {
  font-weight: 600;
  line-height: 1.2;
}

.q-img {
  border-radius: 8px;
  transition: transform 0.3s ease;
}

.q-img:hover {
  transform: scale(1.02);
}
</style>

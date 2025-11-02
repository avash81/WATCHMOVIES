<template>
  <q-page class="q-pa-md bg-black text-white">
    <!-- Hero Banner (Featured Movie) -->
    <div class="hero q-mb-xl" v-if="featured">
      <q-img :src="featured.poster_url" height="400px" class="rounded-borders">
        <div class="absolute-bottom text-center bg-black bg-opacity-70 q-pa-sm">
          <h2 class="text-h4">{{ featured.title }}</h2>
          <q-btn color="red" label="Watch Now" :to="`/movie/${featured.id}`" flat />
        </div>
      </q-img>
    </div>

    <!-- Search + Category -->
    <div class="row q-mb-lg items-center">
      <div class="col-12 col-md-6">
        <q-input v-model="search" dark filled placeholder="Search movies..." @input="fetch">
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-6 q-mt-md md:q-mt-0">
        <q-btn-toggle
          v-model="category"
          toggle-color="red"
          :options="cats"
          @update:model-value="fetch"
          spread
        />
      </div>
    </div>

    <!-- Movie Grid -->
    <div class="row q-gutter-md">
      <div v-for="m in movies" :key="m.id" class="col-6 col-sm-4 col-md-3 col-lg-2">
        <q-card dark class="movie-card cursor-pointer" @click="$router.push(`/movie/${m.id}`)">
          <q-img :src="m.poster_url" :ratio="2 / 3" spinner-color="red">
            <template v-slot:error>
              <div class="absolute-full flex flex-center bg-grey-9 text-white">No Image</div>
            </template>
            <div class="absolute-bottom text-center text-subtitle2 bg-black bg-opacity-70 q-pa-xs">
              {{ m.title }}
            </div>
          </q-img>
          <q-card-section class="q-pa-xs text-center">
            <q-badge color="red">{{ m.release_year }}</q-badge>
            <q-badge color="orange" class="q-ml-xs">⭐ {{ m.rating || 'N/A' }}</q-badge>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Pagination -->
    <q-pagination
      v-model="page"
      :max="totalPages"
      @update:model-value="fetch"
      class="q-my-xl"
      color="red"
    />
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const movies = ref([])
const featured = ref(null)
const search = ref('')
const category = ref('all')
const page = ref(1)
const totalPages = ref(1)
const placeholder = 'https://placehold.co/300x450/111/fff?text=No+Image'

const cats = [
  { label: 'All', value: 'all' },
  { label: 'Bollywood', value: 'Bollywood' },
  { label: 'Hollywood', value: 'Hollywood' },
  { label: 'Web Series', value: 'Web Series' },
]

const fetch = async () => {
  const params = { page: page.value }
  if (search.value) params.search = search.value
  if (category.value !== 'all') params.category = category.value

  try {
    const res = await axios.get('/api/movies', { params })
    const rawMovies = res.data.data || []

    const moviePromises = rawMovies.map(async (movie) => {
      let poster = placeholder

      try {
        await new Promise((r) => setTimeout(r, 100))
        poster = await fetchTMDBPoster(movie.title, movie.release_year)
      } catch {
        poster = placeholder
      }

      return {
        ...movie,
        poster_url: poster,
      }
    })

    movies.value = await Promise.all(moviePromises)
    totalPages.value = res.data.pagination?.total_pages || 1
    featured.value = movies.value[0]
  } catch {
    console.error('API Error:')
  }
}

const fetchTMDBPoster = async (title, year = null) => {
  const key = import.meta.env.VITE_TMDB_API_KEY
  if (!key) return placeholder

  try {
    const params = { api_key: key, query: title }
    if (year) params.year = year

    for (let i = 0; i < 3; i++) {
      try {
        await new Promise((r) => setTimeout(r, i * 200))
        const res = await axios.get('https://api.themoviedb.org/3/search/movie', {
          params,
          timeout: 5000,
        })

        const movie = res.data.results?.[0]
        if (movie?.poster_path) {
          return `https://image.tmdb.org/t/p/w500${movie.poster_path}`
        }
      } catch (e) {
        if (i === 2) console.warn('TMDB poster failed:', title, e)
      }
    }
  } catch {
    return placeholder
  }

  return placeholder
}

onMounted(fetch)
watch([search, category, page], fetch)
</script>

<style scoped>
.hero {
  border-radius: 12px;
  overflow: hidden;
}
.movie-card:hover {
  transform: scale(1.05);
  transition: 0.3s;
}
</style>

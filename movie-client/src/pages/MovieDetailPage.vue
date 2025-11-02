<template>
  <q-page class="q-pa-md bg-black text-white">
    <q-card dark v-if="movie">
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-md-4">
          <q-img :src="movie.poster_url || fetchTMDBPoster(movie.title)" class="shadow-8" />
        </div>
        <div class="col-12 col-md-8">
          <h1 class="text-h3 q-mb-sm">{{ movie.title }} ({{ movie.release_year }})</h1>

          <div class="q-gutter-sm q-mb-md">
            <q-badge color="red">⭐ {{ movie.rating || 'N/A' }}</q-badge>
            <q-badge color="teal">{{ movie.genre }}</q-badge>
            <q-badge>{{ movie.runtime || 'N/A' }} min</q-badge>
          </div>

          <p class="q-mb-lg">{{ movie.description }}</p>

          <!-- Download Buttons -->
          <div class="q-gutter-sm q-mb-lg">
            <q-btn
              v-if="movie.download_480p"
              color="red"
              icon="download"
              label="480p"
              :href="movie.download_480p"
              target="_blank"
            />
            <q-btn
              v-if="movie.download_720p"
              color="orange"
              icon="download"
              label="720p"
              :href="movie.download_720p"
              target="_blank"
            />
            <q-btn
              v-if="movie.download_1080p"
              color="deep-orange"
              icon="download"
              label="1080p"
              :href="movie.download_1080p"
              target="_blank"
            />
          </div>

          <!-- Trailer -->
          <div v-if="movie.trailer_url">
            <h3 class="q-mb-sm">Trailer</h3>
            <iframe
              width="100%"
              height="315"
              :src="movie.trailer_url"
              frameborder="0"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const movie = ref(null)

const fetchTMDBPoster = async (title) => {
  const tmdbKey = import.meta.env.VITE_TMDB_API_KEY || 'YOUR_TMDB_KEY'
  if (!tmdbKey) return 'https://placehold.co/300x450/111/fff?text=No+Image'

  try {
    const res = await axios.get(`https://api.themoviedb.org/3/search/movie`, {
      params: { api_key: tmdbKey, query: title },
    })
    const movieData = res.data.results[0]
    return movieData
      ? `https://image.tmdb.org/t/p/w500${movieData.poster_path}`
      : 'https://placehold.co/300x450/111/fff?text=No+Image'
  } catch (err) {
    console.error('TMDB Error:', err)
    return 'https://placehold.co/300x450/111/fff?text=No+Image'
  }
}

onMounted(async () => {
  const res = await axios.get(`/api/movies/${route.params.id}`)
  movie.value = {
    ...res.data.data,
    poster_url: res.data.data.poster_url || (await fetchTMDBPoster(res.data.data.title)),
  }
})
</script>

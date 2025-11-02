<template>
  <q-card class="movie-card cursor-pointer" flat @click="$emit('click', movie)">
    <q-img :src="posterUrl" :alt="movie.title" ratio="2/3" class="movie-poster">
      <template v-slot:error>
        <div class="absolute-full flex flex-center bg-grey-8 text-white">
          <q-icon name="local_movies" size="xl" />
        </div>
      </template>

      <div class="absolute-top-right q-pa-xs">
        <q-badge color="primary" transparent> ⭐ {{ rating }} </q-badge>
      </div>

      <div class="absolute-bottom overlay-gradient">
        <div class="q-pa-sm">
          <div class="text-h6 text-white text-weight-bold ellipsis">
            {{ movie.title }}
          </div>
          <div class="text-caption text-grey-4">
            {{ releaseYear }}
          </div>
        </div>
      </div>
    </q-img>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  movie: {
    type: Object,
    required: true,
  },
})

defineEmits(['click'])

const posterUrl = computed(() => {
  if (props.movie.poster_path) {
    return `${import.meta.env.VITE_TMDB_IMAGE_BASE_URL}/w500${props.movie.poster_path}`
  }
  return '/images/placeholder-movie.png'
})

const rating = computed(() => {
  return props.movie.vote_average?.toFixed(1) || 'N/A'
})

const releaseYear = computed(() => {
  if (props.movie.release_date) {
    return new Date(props.movie.release_date).getFullYear()
  }
  return 'TBA'
})
</script>

<style scoped>
.movie-card {
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
  border-radius: 12px;
  overflow: hidden;
}

.movie-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.movie-poster {
  border-radius: 12px;
}

.overlay-gradient {
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 100%);
}
</style>

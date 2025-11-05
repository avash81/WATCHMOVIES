<template>
  <div class="movie-card group cursor-pointer" @click="$router.push(`/movie/${movie.id}`)">
    <div class="relative overflow-hidden">
      <q-img
        :src="movie.poster"
        :alt="movie.title"
        class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300"
        ratio="2/3"
      />
      <div
        class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      ></div>
      <div class="absolute top-2 left-2 flex gap-2">
        <span v-if="movie.isNew" class="quality-badge bg-green-500">NEW</span>
        <span class="quality-badge">{{ movie.quality[0] }}</span>
      </div>
      <div class="absolute bottom-2 left-2">
        <div class="rating-badge">
          <q-icon name="star" size="12px" />
          <span>{{ movie.imdbRating }}</span>
        </div>
      </div>
      <div
        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      >
        <q-btn
          class="btn-primary transform scale-90 group-hover:scale-100 transition-transform"
          icon="play_arrow"
          label="Watch Now"
          @click.stop="$router.push(`/movie/${movie.id}`)"
        />
      </div>
    </div>
    <div class="p-4">
      <h3 class="font-semibold text-white mb-2 truncate">{{ movie.title }}</h3>
      <div class="flex justify-between items-center text-sm text-gray-400">
        <span>{{ movie.year }}</span>
        <span>{{ movie.duration }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'MovieCard',
  props: {
    movie: {
      type: Object,
      required: true,
    },
  },
})
</script>

<template>
  <q-page class="bg-gray-900">
    <!-- Hero Section -->
    <section
      class="relative h-96 lg:h-[500px] bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 overflow-hidden"
    >
      <div class="absolute inset-0 bg-black/50 z-10"></div>
      <div
        class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1489599809505-7c8e1c75ce13?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')] bg-cover bg-center"
      ></div>

      <div class="relative z-20 container mx-auto px-4 h-full flex items-center">
        <div class="max-w-2xl text-center mx-auto">
          <h1 class="text-4xl lg:text-6xl font-bold text-white mb-4">
            Unlimited <span class="text-gradient">Movies</span>, TV Shows & More
          </h1>
          <p class="text-xl text-gray-400 mb-8">
            Stream HD content in multiple languages with premium quality. Latest releases updated
            daily.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <q-btn
              label="Explore Latest"
              class="btn-primary"
              icon="play_arrow"
              @click="$router.push('/movies')"
            />
            <q-btn
              label="Trending Now"
              class="btn-secondary"
              icon="whatshot"
              @click="$router.push('/movies?filter=trending')"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Security Alert -->
    <div class="bg-gradient-to-r from-red-600 to-orange-500 border-b border-red-600/50">
      <div class="container mx-auto px-4 py-3">
        <div
          class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm font-medium"
        >
          <div class="flex items-center gap-2">
            <q-icon name="security" />
            <span
              >Avoid FAKE copies of WATCHMOVIES. Always use official domain with VPN for
              security.</span
            >
          </div>
          <q-btn
            flat
            dense
            class="bg-white/20 hover:bg-white/30 transition-colors"
            icon="fab fa-discord"
            label="Join Discord"
          />
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
      <!-- Latest Releases -->
      <section class="mb-12">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-3xl font-bold text-white mb-2">Latest Releases</h2>
          <router-link
            to="/movies?filter=latest"
            class="text-red-500 hover:text-orange-500 transition-colors font-semibold flex items-center gap-2"
          >
            View All
            <q-icon name="arrow_forward" />
          </router-link>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <movie-card v-for="movie in latestMovies" :key="movie.id" :movie="movie" />
        </div>
      </section>

      <!-- Popular Movies -->
      <section class="mb-12">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-3xl font-bold text-white mb-2">Trending Now</h2>
          <router-link
            to="/movies?filter=popular"
            class="text-red-500 hover:text-orange-500 transition-colors font-semibold flex items-center gap-2"
          >
            View All
            <q-icon name="arrow_forward" />
          </router-link>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <movie-card v-for="movie in popularMovies" :key="movie.id" :movie="movie" />
        </div>
      </section>

      <!-- Categories -->
      <section class="mb-12">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-3xl font-bold text-white mb-2">Browse Categories</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <category-card
            v-for="category in categories"
            :key="category.name"
            :category="category"
            @click="$router.push(category.route)"
          />
        </div>
      </section>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue'
import MovieCard from 'components/MovieCard.vue'
import CategoryCard from 'components/CategoryCard.vue'

export default defineComponent({
  name: 'IndexPage',
  components: {
    MovieCard,
    CategoryCard,
  },

  setup() {
    const latestMovies = ref([
      {
        id: 1,
        title: 'Bahubali: The Epic',
        year: 2025,
        poster: 'https://via.placeholder.com/300x450/1a1a1a/ffffff?text=Bahubali+2025',
        quality: ['1080p', '720p', '480p'],
        audio: ['Hindi', 'English'],
        imdbRating: 8.2,
        duration: '2h 28m',
        genre: ['Action', 'Adventure', 'Drama'],
        isNew: true,
      },
      // Add more movies...
    ])

    const popularMovies = ref([
      {
        id: 6,
        title: 'Spectre',
        year: 2015,
        poster: 'https://via.placeholder.com/300x450/1a1a1a/ffffff?text=Spectre',
        quality: ['1080p', '720p', '480p'],
        audio: ['Hindi', 'English'],
        imdbRating: 6.8,
        duration: '2h 28m',
        genre: ['Action', 'Adventure', 'Thriller'],
      },
      // Add more movies...
    ])

    const categories = ref([
      { name: 'Bollywood', icon: 'movie', route: '/movies?category=bollywood' },
      { name: 'Hollywood', icon: 'language', route: '/movies?category=hollywood' },
      { name: 'South Indian', icon: 'public', route: '/movies?category=south' },
      { name: 'Hindi Dubbed', icon: 'translate', route: '/movies?category=dubbed' },
      { name: 'Web Series', icon: 'tv', route: '/series' },
      { name: 'All Genres', icon: 'theater_comedy', route: '/genre' },
    ])

    return {
      latestMovies,
      popularMovies,
      categories,
    }
  },
})
</script>

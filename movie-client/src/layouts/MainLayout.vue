<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-gray-900 backdrop-blur-glass border-b border-gray-800">
      <q-toolbar>
        <!-- Logo -->
        <q-toolbar-title>
          <router-link to="/" class="flex items-center space-x-3 no-underline">
            <div
              class="w-10 h-10 bg-gradient-to-r from-red-600 to-orange-500 rounded-lg flex items-center justify-center"
            >
              <q-icon name="play_arrow" class="text-white" />
            </div>
            <span class="text-2xl font-bold text-gradient">WATCHMOVIES</span>
          </router-link>
        </q-toolbar-title>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center space-x-1">
          <router-link to="/" class="nav-link" :class="{ active: $route.path === '/' }">
            Home
          </router-link>

          <q-btn flat class="nav-link" :class="{ active: $route.path.includes('/movies') }">
            Movies
            <q-menu>
              <q-list class="bg-gray-800 border border-gray-700 rounded-xl">
                <q-item clickable v-close-popup @click="$router.push('/movies?category=bollywood')">
                  <q-item-section>Bollywood</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="$router.push('/movies?category=hollywood')">
                  <q-item-section>Hollywood</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="$router.push('/movies?category=south')">
                  <q-item-section>South Indian</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="$router.push('/movies?category=dubbed')">
                  <q-item-section>Hindi Dubbed</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>

          <router-link to="/series" class="nav-link" :class="{ active: $route.path === '/series' }">
            TV Series
          </router-link>

          <router-link to="/genre" class="nav-link" :class="{ active: $route.path === '/genre' }">
            Genre
          </router-link>

          <router-link
            to="/request"
            class="nav-link"
            :class="{ active: $route.path === '/request' }"
          >
            Request
          </router-link>
        </div>

        <!-- Search Bar -->
        <div class="hidden md:block flex-1 max-w-md mx-8" ref="searchRef">
          <div class="relative">
            <q-input
              v-model="searchQuery"
              placeholder="Search movies, TV shows..."
              dense
              class="search-input"
              @update:model-value="performSearch"
              @focus="showSearchResults = true"
            >
              <template v-slot:append>
                <q-icon
                  name="search"
                  class="cursor-pointer text-gray-400 hover:text-white transition-colors"
                />
              </template>
            </q-input>

            <!-- Search Results -->
            <div
              v-if="showSearchResults && searchResults.length > 0"
              class="absolute top-full left-0 right-0 mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl max-h-96 overflow-y-auto z-50"
            >
              <q-item
                v-for="movie in searchResults"
                :key="movie.id"
                clickable
                v-close-popup
                @click="goToMovie(movie.id)"
                class="hover:bg-gray-700 transition-colors border-b border-gray-700 last:border-b-0"
              >
                <q-item-section avatar>
                  <q-img :src="movie.poster" class="w-12 h-16 object-cover rounded-lg" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="font-semibold text-white truncate">{{
                    movie.title
                  }}</q-item-label>
                  <q-item-label caption class="flex items-center space-x-2 mt-1">
                    <span class="text-gray-400">{{ movie.year }}</span>
                    <span class="quality-badge text-xs">{{ movie.quality[0] }}</span>
                    <div class="rating-badge">
                      <q-icon name="star" size="12px" />
                      <span>{{ movie.imdbRating }}</span>
                    </div>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </div>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          class="lg:hidden"
          @click="toggleLeftDrawer"
        />
      </q-toolbar>
    </q-header>

    <!-- Mobile Drawer -->
    <q-drawer
      v-model="leftDrawerOpen"
      side="left"
      bordered
      class="bg-gray-900 text-white lg:hidden"
    >
      <q-list padding class="menu-list">
        <q-item-label header class="text-white">Navigation</q-item-label>

        <q-item clickable v-ripple to="/" @click="leftDrawerOpen = false">
          <q-item-section avatar>
            <q-icon name="home" />
          </q-item-section>
          <q-item-section>Home</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/movies" @click="leftDrawerOpen = false">
          <q-item-section avatar>
            <q-icon name="movie" />
          </q-item-section>
          <q-item-section>Movies</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/series" @click="leftDrawerOpen = false">
          <q-item-section avatar>
            <q-icon name="live_tv" />
          </q-item-section>
          <q-item-section>TV Series</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/genre" @click="leftDrawerOpen = false">
          <q-item-section avatar>
            <q-icon name="category" />
          </q-item-section>
          <q-item-section>Genre</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/request" @click="leftDrawerOpen = false">
          <q-item-section avatar>
            <q-icon name="request_quote" />
          </q-item-section>
          <q-item-section>Request</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>

    <!-- Footer -->
    <q-footer class="bg-gray-800 border-t border-gray-700 text-white">
      <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <!-- Brand -->
          <div>
            <div class="flex items-center space-x-3 mb-4">
              <div
                class="w-10 h-10 bg-gradient-to-r from-red-600 to-orange-500 rounded-lg flex items-center justify-center"
              >
                <q-icon name="play_arrow" class="text-white" />
              </div>
              <span class="text-2xl font-bold text-gradient">WATCHMOVIES</span>
            </div>
            <p class="text-gray-400 mb-4">
              Your ultimate destination for HD movies, TV shows, and web series in multiple
              languages and quality formats.
            </p>
            <div class="flex space-x-4">
              <q-btn flat round icon="fab fa-discord" class="text-gray-400 hover:text-red-500" />
              <q-btn flat round icon="fab fa-telegram" class="text-gray-400 hover:text-red-500" />
              <q-btn flat round icon="fab fa-twitter" class="text-gray-400 hover:text-red-500" />
            </div>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="text-white font-semibold mb-4 text-lg">Quick Links</h3>
            <ul class="space-y-2">
              <li>
                <router-link to="/" class="text-gray-400 hover:text-white transition-colors"
                  >Home</router-link
                >
              </li>
              <li>
                <router-link to="/movies" class="text-gray-400 hover:text-white transition-colors"
                  >Movies</router-link
                >
              </li>
              <li>
                <router-link to="/series" class="text-gray-400 hover:text-white transition-colors"
                  >TV Series</router-link
                >
              </li>
              <li>
                <router-link to="/genre" class="text-gray-400 hover:text-white transition-colors"
                  >Genres</router-link
                >
              </li>
              <li>
                <router-link to="/request" class="text-gray-400 hover:text-white transition-colors"
                  >Request Content</router-link
                >
              </li>
            </ul>
          </div>

          <!-- Categories -->
          <div>
            <h3 class="text-white font-semibold mb-4 text-lg">Categories</h3>
            <ul class="space-y-2">
              <li>
                <router-link
                  to="/movies?category=bollywood"
                  class="text-gray-400 hover:text-white transition-colors"
                  >Bollywood</router-link
                >
              </li>
              <li>
                <router-link
                  to="/movies?category=hollywood"
                  class="text-gray-400 hover:text-white transition-colors"
                  >Hollywood</router-link
                >
              </li>
              <li>
                <router-link
                  to="/movies?category=south"
                  class="text-gray-400 hover:text-white transition-colors"
                  >South Indian</router-link
                >
              </li>
              <li>
                <router-link
                  to="/movies?category=dubbed"
                  class="text-gray-400 hover:text-white transition-colors"
                  >Hindi Dubbed</router-link
                >
              </li>
              <li>
                <router-link to="/series" class="text-gray-400 hover:text-white transition-colors"
                  >Web Series</router-link
                >
              </li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h3 class="text-white font-semibold mb-4 text-lg">Support</h3>
            <ul class="space-y-2">
              <li>
                <router-link
                  to="/how-to-download"
                  class="text-gray-400 hover:text-white transition-colors"
                  >How to Download</router-link
                >
              </li>
              <li>
                <router-link
                  to="/disclaimer"
                  class="text-gray-400 hover:text-white transition-colors"
                  >Disclaimer</router-link
                >
              </li>
              <li>
                <router-link to="/contact" class="text-gray-400 hover:text-white transition-colors"
                  >Contact Us</router-link
                >
              </li>
              <li>
                <router-link to="/dmca" class="text-gray-400 hover:text-white transition-colors"
                  >DMCA</router-link
                >
              </li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
          <p class="text-gray-400">
            &copy; 2024 WATCHMOVIES. All rights reserved. | Use VPN for secure streaming
          </p>
          <p class="text-gray-400 text-sm mt-2">
            Disclaimer: We do not host any files on our server. All content provided is for
            promotional purposes only.
          </p>
        </div>
      </div>
    </q-footer>

    <!-- Scroll to Top -->
    <q-page-sticky position="bottom-right" :offset="[18, 18]" v-if="showScrollTop">
      <q-btn
        fab
        icon="keyboard_arrow_up"
        class="bg-gradient-to-r from-red-600 to-orange-500 text-white"
        @click="scrollToTop"
      />
    </q-page-sticky>
  </q-layout>
</template>

<script>
import { defineComponent, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { onClickOutside } from '@vueuse/core'

export default defineComponent({
  name: 'MainLayout',

  setup() {
    const leftDrawerOpen = ref(false)
    const searchQuery = ref('')
    const searchResults = ref([])
    const showSearchResults = ref(false)
    const showScrollTop = ref(false)
    const searchRef = ref(null)
    const router = useRouter()

    // Sample movie data
    const movieData = [
      {
        id: 1,
        title: 'Bahubali: The Epic',
        year: 2025,
        poster: 'https://via.placeholder.com/300x450/1a1a1a/ffffff?text=Bahubali+2025',
        quality: ['1080p', '720p', '480p'],
        imdbRating: 8.2,
      },
      {
        id: 2,
        title: 'Kantara - A Legend Chapter: 1',
        year: 2025,
        poster: 'https://via.placeholder.com/300x450/1a1a1a/ffffff?text=Kantara+2025',
        quality: ['4K', '1080p', '720p', '480p'],
        imdbRating: 8.7,
      },
      {
        id: 3,
        title: 'War 2',
        year: 2025,
        poster: 'https://via.placeholder.com/300x450/1a1a1a/ffffff?text=War+2',
        quality: ['4K', '1080p', '720p', '480p'],
        imdbRating: 7.9,
      },
    ]

    // Use onClickOutside from @vueuse/core
    onClickOutside(searchRef, () => {
      showSearchResults.value = false
    })

    const toggleLeftDrawer = () => {
      leftDrawerOpen.value = !leftDrawerOpen.value
    }

    const performSearch = () => {
      if (searchQuery.value.length < 2) {
        searchResults.value = []
        return
      }

      const query = searchQuery.value.toLowerCase()
      searchResults.value = movieData
        .filter((movie) => movie.title.toLowerCase().includes(query))
        .slice(0, 8)
    }

    const goToMovie = (id) => {
      router.push(`/movie/${id}`)
      showSearchResults.value = false
      searchQuery.value = ''
    }

    const scrollToTop = () => {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    const handleScroll = () => {
      showScrollTop.value = window.scrollY > 500
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll)
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
    })

    return {
      leftDrawerOpen,
      searchQuery,
      searchResults,
      showSearchResults,
      showScrollTop,
      searchRef,
      toggleLeftDrawer,
      performSearch,
      goToMovie,
      scrollToTop,
    }
  },
})
</script>

<style scoped>
.menu-list .q-item {
  color: #b3b3b3;
}

.menu-list .q-item:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}
</style>

import { api } from 'boot/axios'

class ApiService {
  constructor() {
    this.cache = new Map()
    this.CACHE_DURATION = 5 * 60 * 1000 // 5 minutes
  }

  async request(method, endpoint, data = null, timeout = 10000) {
    const controller = new AbortController()
    const timeoutId = setTimeout(() => controller.abort(), timeout)

    try {
      const response = await api({
        method,
        url: endpoint,
        data: method !== 'get' ? data : null,
        params: method === 'get' ? data : null,
        signal: controller.signal,
        timeout: timeout,
      })

      clearTimeout(timeoutId)
      return response.data
    } catch (error) {
      clearTimeout(timeoutId)

      if (error.code === 'ECONNABORTED' || error.name === 'AbortError') {
        console.error('Request timeout:', endpoint)
        throw new Error('Request timeout - please try again')
      }

      console.error('API request failed:', error)
      throw new Error(error.response?.data?.message || 'Network error occurred')
    }
  }

  // ULTRA-FAST: Get movie details from FAST endpoint
  async getMovieDetails(movieId) {
    console.log('🚀 Fetching FAST movie details for:', movieId)
    try {
      // Use FAST endpoint with short timeout
      const data = await this.request('get', `/fast/movies/${movieId}`, null, 3000)
      console.log('✅ FAST movie details received for:', movieId, 'in', data.response_time)
      return data
    } catch (error) {
      console.log('⚠️ Fast endpoint failed, trying regular:', error.message)
      // Fallback to regular endpoint
      return this.request('get', `/movies/${movieId}`, null, 8000)
    }
  }

  // ULTRA-FAST: Get movies list from FAST endpoint
  async getMovies(category = 'popular', page = 1) {
    console.log('🚀 Fetching FAST movies list')
    try {
      const data = await this.request('get', '/fast/movies', { category, page }, 3000)
      console.log('✅ FAST movies list received in', data.response_time)
      return data
    } catch (error) {
      console.log('⚠️ Fast list failed, trying regular:', error.message)
      return this.request('get', '/movies', { category, page }, 8000)
    }
  }

  async searchMovies(query, page = 1) {
    return this.request('get', '/movies/search', { query, page }, 10000)
  }

  async getFilterOptions() {
    return this.request('get', '/movies/filter-options', null, 10000)
  }

  async filterMovies(filters = {}) {
    return this.request('get', '/movies/filter', filters, 10000)
  }

  async getGenres() {
    return this.request('get', '/genres', null, 10000)
  }

  async getMoviesByGenre(genreId, page = 1) {
    return this.request('get', `/genres/${genreId}/movies`, { page }, 10000)
  }

  async getTrendingMovies() {
    return this.request('get', '/movies/trending', null, 10000)
  }

  async quickSearch(query) {
    return this.request('get', '/movies/quick-search', { q: query }, 5000)
  }

  // Frontend caching for instant navigation
  getCached(key) {
    const cached = this.cache.get(key)
    if (cached && Date.now() - cached.timestamp < this.CACHE_DURATION) {
      return cached.data
    }
    return null
  }

  setCached(key, data) {
    this.cache.set(key, {
      data,
      timestamp: Date.now(),
    })
  }

  // Cache movie details for instant back navigation
  async getMovieDetailsWithCache(movieId) {
    const cacheKey = `movie_${movieId}`
    const cached = this.getCached(cacheKey)

    if (cached) {
      console.log('📦 Serving movie from frontend cache:', movieId)
      return cached
    }

    const data = await this.getMovieDetails(movieId)
    if (data.success) {
      this.setCached(cacheKey, data)
    }

    return data
  }

  // Preload data for better UX
  async preloadPopularData() {
    const endpoints = [
      { url: '/fast/movies', params: { category: 'popular' } },
      { url: '/movies/filter-options' },
      { url: '/genres' },
    ]

    // Preload in background
    endpoints.forEach(({ url, params }) => {
      this.request('get', url, params, 5000).catch(() => {
        // Silent fail - preload is optional
      })
    })
  }
}

export const apiService = new ApiService()

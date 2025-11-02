import { api } from 'boot/axios'

class ApiService {
  async request(method, endpoint, data = null) {
    try {
      const response = await api({
        method,
        url: endpoint,
        data: method !== 'get' ? data : null,
        params: method === 'get' ? data : null,
      })

      return response.data
    } catch (error) {
      console.error('API request failed:', error)

      if (error.response?.status === 401) {
        // Handle unauthorized access
        window.location.href = '/login'
      }

      throw new Error(error.response?.data?.message || 'Network error occurred')
    }
  }

  async getMovies(category = 'popular', page = 1) {
    return this.request('get', '/movies', { category, page })
  }

  async getMovieDetails(movieId) {
    return this.request('get', `/movies/${movieId}`)
  }

  async searchMovies(query, page = 1) {
    return this.request('get', '/movies/search', { query, page })
  }
}

export const apiService = new ApiService()

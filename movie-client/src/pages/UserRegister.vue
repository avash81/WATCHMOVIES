<template>
  <q-page class="flex flex-center bg-black text-white">
    <q-card class="login-card" style="width: 400px; max-width: 90vw">
      <q-card-section>
        <div class="text-h4 text-center q-mb-md">
          <q-icon name="movie" size="3rem" class="q-mr-sm" />
          WATCHMOVIES
        </div>
        <div class="text-h6 text-center q-mb-md">Create your account</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit.prevent="register">
          <q-input
            v-model="form.name"
            label="Full Name"
            filled
            dark
            :error="$v.form.name.$error"
            :error-message="$v.form.name.$error ? 'Name is required' : ''"
          >
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>

          <q-input
            v-model="form.email"
            type="email"
            label="Email"
            filled
            dark
            :error="$v.form.email.$error"
            :error-message="$v.form.email.$error ? 'Please enter a valid email' : ''"
          >
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>

          <q-input
            v-model="form.password"
            type="password"
            label="Password"
            filled
            dark
            :error="$v.form.password.$error"
            :error-message="$v.form.password.$error ? 'Password must be at least 8 characters' : ''"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
          </q-input>

          <q-btn
            type="submit"
            color="red"
            label="Register"
            class="full-width q-mt-md"
            :loading="loading"
            unelevated
          />
        </q-form>
      </q-card-section>

      <q-card-section class="text-center">
        <p>
          Already have an account?
          <router-link to="/login" class="text-red">Login here</router-link>
        </p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'

const router = useRouter()
const $q = useQuasar()

const form = ref({
  name: '',
  email: '',
  password: '',
})

const loading = ref(false)

const validateForm = () => {
  return form.value.name && form.value.email && form.value.password.length >= 8
}

const register = async () => {
  if (!validateForm()) {
    $q.notify({
      color: 'negative',
      message: 'Please fill all fields correctly',
    })
    return
  }

  loading.value = true
  try {
    const response = await axios.post('/api/register', form.value)
    const { token, user } = response.data

    // Save token
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    $q.notify({
      color: 'positive',
      message: `Account created, ${user.name}! Welcome!`,
    })

    router.push('/')
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Registration failed',
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-card {
  background: #1a1a1a;
  border-radius: 12px;
}
</style>

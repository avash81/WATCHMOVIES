<!-- movie-client/src/layouts/MainLayout.vue -->
<template>
  <q-layout view="lHh Lpr lFf">
    <!-- Clean Header -->
    <q-header class="bg-black text-white">
      <q-toolbar>
        <q-toolbar-title class="text-h6">
          <q-icon name="movie" class="q-mr-sm" />
          WATCHMOVIES
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <!-- Page Content -->
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
// ← Removed: import { useQuasar } from 'quasar'
// ← Removed: const $q = useQuasar()
</script>

<style scoped>
.q-toolbar {
  min-height: 60px;
}
</style>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

onMounted(() => {
  const token = route.query.token
  const user = JSON.parse(decodeURIComponent(route.query.user))

  if (token) {
    auth.token = token
    auth.user = user
    localStorage.setItem('token', token)

    // Redirect to dashboard
    router.push('/dashboard')
  } else {
    console.error('Authentication failed!')
    router.push('/login')
  }
})
</script>

<template>
  <div>Authenticating...</div>
</template>

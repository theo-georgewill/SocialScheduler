<script setup>
import { useTheme } from 'vuetify'

const { global } = useTheme()

const authProviders = [
  { icon: 'bxl-facebook', color: '#4267b2', colorInDark: '#4267b2', provider: 'facebook' },
  { icon: 'bxl-twitter', color: '#1da1f2', colorInDark: '#1da1f2', provider: 'twitter' },
  { icon: 'bxl-github', color: '#272727', colorInDark: '#fff', provider: 'github' },
  { icon: 'bxl-google', color: '#db4437', colorInDark: '#db4437', provider: 'google' },
  { icon: 'bxl-reddit', color: '#ff4500', colorInDark: '#ff4500', provider: 'reddit' },
]

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000' // Ensure API URL is correct

const redirectToAuth = async provider => {
  try {
    const response = await fetch(`${API_BASE_URL}/api/auth/${provider}/redirect`);
    const data = await response.json();
    
    if (data.url) {
      window.location.href = data.url; // Redirect to provider's auth page
    } else {
      console.error("Error: No redirect URL received from API");
    }
  } catch (error) {
    console.error("Auth redirection failed:", error);
  }
}
</script>

<template>
  <VBtn
    v-for="link in authProviders"
    :key="link.icon"
    :icon="link.icon"
    variant="text"
    :color="global.name.value === 'dark' ? link.colorInDark : link.color"
    @click="redirectToAuth(link.provider)"
  />
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

onMounted(() => {
	const provider = route.query.provider;
  	const token = route.query.token;
	const accessToken = route.query.accessToken;
  	const user = JSON.parse(decodeURIComponent(route.query.user));

  if (token) {
	auth.token = token;
	auth.user = user;
	
	localStorage.setItem('user', JSON.stringify(user));
	localStorage.setItem('accessToken', JSON.stringify(accessToken));
	localStorage.setItem('token', JSON.stringify(token));

	// Redirect to dashboard
	router.push('/dashboard');
  } else {
	console.error('Authentication failed!');
	router.push('/login');
  }
})
</script>

<template>
  <div>Authenticating...</div>
</template>

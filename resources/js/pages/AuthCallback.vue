<script setup>
import { useAuthStore } from '@/stores/auth';
import { useSocialAccounts } from '@/stores/socialAccounts';
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();


onMounted(() => {
	const provider = route.query.provider;
	const user = JSON.parse(decodeURIComponent(route.query.user));
	const accessToken = route.query.accessToken;
	const token = route.query.token;

	if (token) {
		auth.token = token;
		auth.user = user;
		
		auth.handleCallback(provider, token, accessToken, user)
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

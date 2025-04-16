<script setup>
import { useAuthStore } from '@/stores/auth';
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

onMounted(() => {
	auth.logout()
	// Redirect to dashboard
	router.push('/login');
})
</script>

<template>
  <div>Logging out...</div>
</template>

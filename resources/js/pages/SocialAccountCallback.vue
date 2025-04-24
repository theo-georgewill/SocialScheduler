<script setup>
import { useSocialAccounts } from '@/stores/socialAccounts';
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const socialAccounts = useSocialAccounts();


onMounted(() => {
	// Redirect to dashboard
	router.push('/account-config');
})
</script>

<template>
  <div>Authenticating...</div>
</template>

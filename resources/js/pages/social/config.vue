<script setup>
import { useSocialAccounts } from '@/stores/socialAccounts'; // Import the Pinia store
import { onMounted, watch } from 'vue';

const socialAccountsStore = useSocialAccounts(); // Initialize store

// Fetch connected social accounts when the component is mounted
onMounted(() => {
	socialAccountsStore.fetchAccounts();
});
// Watch for changes and persist selectedAccounts to localStorage
watch(
	() => socialAccountsStore.selectedAccounts,
	(newVal) => {
		localStorage.setItem('selectedAccounts', JSON.stringify(newVal));
	},
	{ deep: true }
);
</script>

<template>
	<v-container>
		<v-card class="py-3">
			<v-card-title class="py-3">Connected Social Media Accounts</v-card-title>
			<v-card-text>
				<!-- Display list of connected accounts -->
				<v-list v-if="socialAccountsStore.accounts.length">
					<v-list-item v-for="account in socialAccountsStore.accounts" :key="account.id">
						<div class="d-flex">
							<v-list-item-content>
								<v-list-item-title>{{ account.provider }}</v-list-item-title>
								<v-list-item-subtitle>{{ account.username }}</v-list-item-subtitle>
							</v-list-item-content>

							<v-list-item-action class="ms-auto">
								<!-- Checkbox for selecting accounts -->
								<v-checkbox
									v-model="socialAccountsStore.selectedAccounts"
									:value="account.id"
									label="Use for Posting"
									class="me-3"
								></v-checkbox>

								<v-btn color="error" class="ms-3" @click="socialAccountsStore.disconnectAccount(account.provider, account.id)">
									Disconnect
								</v-btn>
							</v-list-item-action>
						</div>
					</v-list-item>
				</v-list>
				<v-alert v-else type="info">No connected accounts</v-alert>
			</v-card-text>
		</v-card>

		<div class="justify-space-evenly d-flex">
			<!-- Buttons to connect social accounts -->
			<v-btn class="mt-4" color="primary" @click="socialAccountsStore.connectAccount('facebook')">
				Connect Facebook
			</v-btn>
			<v-btn class="mt-4 ml-2" color="primary" @click="socialAccountsStore.connectAccount('twitter')">
				Connect Twitter
			</v-btn>
			<v-btn class="mt-4 ml-2" color="primary" @click="socialAccountsStore.connectAccount('reddit')">
				Connect Reddit
			</v-btn>
		</div>


		<!-- Snackbar Component for Notifications -->
		<v-snackbar v-model="socialAccountsStore.snackbar.show" :color="socialAccountsStore.snackbar.color" timeout="3000">
			{{ socialAccountsStore.snackbar.message }}
			<template v-slot:actions>
				<v-btn color="white" @click="socialAccountsStore.snackbar.show = false">Close</v-btn>
			</template>
		</v-snackbar>
	</v-container>
</template>

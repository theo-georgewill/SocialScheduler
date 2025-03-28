<script setup>
import { ref, onMounted } from 'vue';
import { useSocialAccounts } from '@/stores/socialAccounts'; // Import the Pinia store

const socialAccountsStore = useSocialAccounts(); // Initialize store

// Fetch connected social accounts when the component is mounted
onMounted(() => {
	socialAccountsStore.fetchAccounts();
});
</script>

<template>
	<v-container>
		<v-card>
			<v-card-title>Connected Social Accounts</v-card-title>
			<v-card-text>
				<!-- Display list of connected accounts -->
				<v-list v-if="socialAccountsStore.accounts.length">
					<v-list-item v-for="account in socialAccountsStore.accounts" :key="account.id">
						<v-list-item-content>
							<v-list-item-title>{{ account.provider }}</v-list-item-title>
							<v-list-item-subtitle>{{ account.username }}</v-list-item-subtitle>
						</v-list-item-content>
						<v-list-item-action>
							<!-- Checkbox for selecting accounts -->
							<v-checkbox
								v-model="socialAccountsStore.selectedAccounts"
								:value="account.id"
								label="Use for Posting"
							></v-checkbox>
							<v-btn color="red" @click="socialAccountsStore.disconnectAccount(account.id)">
								Disconnect
							</v-btn>
						</v-list-item-action>
					</v-list-item>
				</v-list>
				<v-alert v-else type="info">No connected accounts</v-alert>
			</v-card-text>
		</v-card>

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

		<!-- Save Selected Accounts Button -->
		<v-btn class="mt-4 ml-2" color="success" @click="socialAccountsStore.saveSelectedAccounts">
			Save Selected Accounts
		</v-btn>

		<!-- Snackbar Component for Notifications -->
		<v-snackbar v-model="socialAccountsStore.snackbar.show" :color="socialAccountsStore.snackbar.color" timeout="3000">
			{{ socialAccountsStore.snackbar.message }}
			<template v-slot:actions>
				<v-btn color="white" @click="socialAccountsStore.snackbar.show = false">Close</v-btn>
			</template>
		</v-snackbar>
	</v-container>
</template>

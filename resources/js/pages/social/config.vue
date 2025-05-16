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

	function getProviderIcon(provider) {
		switch (provider) {
			case 'facebook':
				return 'bxl-facebook';
			case 'reddit':
				return 'bxl-reddit';
			default:
				return 'mdi-account';
		}
	}

	function getProviderStyle(provider) {
		let background = '#999'; // Default gray
		switch (provider.toLowerCase()) {
			case 'facebook':
				background = '#1877F2'; // Facebook blue
				break;
			case 'reddit':
				background = '#FF4500'; // Reddit orange
				break;
		}
		return {
			backgroundColor: background,
			color: 'white',
			borderRadius: '50%',
			padding: '1px',
			position: 'absolute',
			bottom: '-4px',
			right: '-4px',
			boxShadow: '0 0 2px rgba(0, 0, 0, 0.2)'
		};
	}
</script>

<template>
	<v-container>
		<v-card class="py-3 rounded border">
			<v-card-title class="py-3">Connected Social Media Accounts</v-card-title>
			<v-card-text>
				<!-- Display list of connected accounts -->
				<v-list v-if="socialAccountsStore.accounts.length">
					<v-list-item v-for="account in socialAccountsStore.accounts" :key="account.id">
						<div class="d-flex align-center pa-2 rounded border">
							<div
								class="position-relative mx-2"
								style="width: 36px; height: 36px;"
							>
								<v-avatar size="36">
									<v-img :src="`/storage/${account.avatar}`" alt="Profile Picture" cover />
								</v-avatar>

								<v-icon
									:icon="getProviderIcon(account.provider)"
									:style="getProviderStyle(account.provider)"
									class="provider-icon"
									size="16"
									color="blue-grey-darken-1"
								/>
							</div>
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

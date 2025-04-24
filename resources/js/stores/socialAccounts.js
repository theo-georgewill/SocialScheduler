import api from '@/api'; // Import Axios instance
import { defineStore } from 'pinia';

export const useSocialAccounts = defineStore('socialAccounts', {
	state: () => ({
		accounts: [],
		selectedAccounts: JSON.parse(localStorage.getItem('selectedAccounts')) || [],
		loading: false,
		snackbar: { show: false, message: '', color: '' },
	}),

	actions: {
		//method to fetch social accounts using users id from localstorage
		async fetchAccounts() {
			this.loading = true;
			try {
				const user = JSON.parse(localStorage.getItem('user'));
				const userId = user?.id;
				const token = localStorage.getItem('token');
		
				if (!userId) throw new Error('No user ID found in localStorage');
		
				const response = await api.get(`/social-accounts?user_id=${userId}`, {
					headers: {
						'Authorization': `Bearer ${token}`, // Use the appropriate token if needed
					},
				});
				console.log(JSON.stringify(response.data));
		
				// Assuming the response contains the social accounts and the authenticated user
				if (response.data && Array.isArray(response.data.social_accounts)) {
					this.accounts = response.data.social_accounts;
					
					const authenticatedUser = response.data.authenticated_user;
					console.log('Authenticated User:', authenticatedUser);
				}
			} catch (error) {
				console.error("Fetch error:", error);
				this.showSnackbar('Failed to fetch social accounts', 'error');            
			} finally {
				this.loading = false;
			}
		},
		
		async connectAccount(provider) {
			this.loading = true;
			try {
				const user = JSON.parse(localStorage.getItem('user'));
				const userId = user?.id;


				const response = await api.get(`/auth/${provider}/redirect?user_id=${userId}`);
				
				const data = response.data;
			  
				// Redirect user to social login page
				window.location.href = data.url;
			} catch (error) {
				console.error("Connection failed:", error);
				this.showSnackbar('Error connecting account', 'error');
			} finally {
				this.loading = false;
			}
		},

		async disconnectAccount(provider, id) {
			this.loading = true;
			try {
				const userId = JSON.parse(localStorage.getItem('user'))?.id; // Assumes user object is stored in localStorage
		
				if (!userId) {
					this.showSnackbar('User not found in localStorage', 'error');
					return;
				}
		
				const response = await api.delete(`/disconnect/${provider}/${id}`, {
					params: { user_id: userId }
				});
		
				if (response.status === 200) {
					this.accounts = this.accounts.filter(acc => acc.id !== id);
					this.selectedAccounts = this.selectedAccounts.filter(accId => accId !== id);
					localStorage.setItem('selectedAccounts', JSON.stringify(this.selectedAccounts));
					this.showSnackbar('Account disconnected successfully', 'success');
				}
			} catch (error) {
				console.error("Disconnect failed:", error);
				this.showSnackbar('Error disconnecting account', 'error');
			} finally {
				this.loading = false;
			}
		},
		

		showSnackbar(message, color = 'info') {
			this.snackbar.message = message;
			this.snackbar.color = color;
			this.snackbar.show = true;
			setTimeout(() => {
				this.snackbar.show = false;
			}, 3000);
		}
	},
});

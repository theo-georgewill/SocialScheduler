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

				if (!userId) throw new Error('No user ID found in localStorage');

				const response = await api.get(`/social-accounts?user_id=${userId}`);
				console.log(JSON.stringify(response.data));
				if (Array.isArray(response.data)) {
				this.accounts = response.data;
				}
			} catch (error) {
				console.error("Fetch error:", error);
				this.showSnackbar('Failed to fetch social accounts', 'error');			
			} finally {
				this.loading = false;
			}
		},
		async connectAccount(accountData) {
			this.loading = true;
			try {
				const response = await api.post('/social-accounts', accountData);
				if (response.status === 200) {
				this.accounts.push(response.data.account);
				this.showSnackbar('Account connected successfully', 'success');
				}
			} catch (error) {
				console.error("Connection failed:", error);
				this.showSnackbar('Error connecting account', 'error');
			} finally {
				this.loading = false;
			}
		},

		async disconnectAccount(id) {
			this.loading = true;
			try {
				const response = await api.delete(`/social-accounts/${id}`); // Token is auto-attached
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

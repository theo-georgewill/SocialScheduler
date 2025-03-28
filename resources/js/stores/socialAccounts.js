import { defineStore } from 'pinia';
import { useAuthStore } from './auth';
import api from '@/api'; // Import Axios instance

export const useSocialAccounts = defineStore('socialAccounts', {
  state: () => ({
    accounts: [],
    selectedAccounts: JSON.parse(localStorage.getItem('selectedAccounts')) || [],
    loading: false,
    snackbar: { show: false, message: '', color: '' },
  }),

  actions: {
    async fetchAccounts() {
      this.loading = true;
      try {
        const response = await api.get('/social-accounts'); // No need to add headers manually
        if (Array.isArray(response.data)) {
          this.accounts = response.data;
        }
      } catch (error) {
        console.error("Fetch error:", error);
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

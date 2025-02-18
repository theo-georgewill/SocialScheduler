<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const accounts = ref([]);
const selectedAccounts = ref([]); // Stores selected accounts
const loading = ref(false);
const router = useRouter();

// Snackbar state
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('');

// Function to show snackbar
const showSnackbar = (message, color = 'info') => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  snackbar.value = true;
};

// Fetch connected accounts
const fetchAccounts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/social-accounts');
    if (response?.data && Array.isArray(response.data)) {
      accounts.value = response.data;
      loadSelectedAccounts(); // Load user-selected accounts
    }
  } catch (error) {
    console.error("Fetch error:", error);
    showSnackbar('Failed to fetch accounts', 'error');
  } finally {
    loading.value = false;
  }
};

// Load selected accounts from local storage
const loadSelectedAccounts = () => {
  const storedSelection = localStorage.getItem('selectedAccounts');
  if (storedSelection) {
    selectedAccounts.value = JSON.parse(storedSelection);
  }
};

// Handle OAuth connection
const connectAccount = (provider) => {
  window.location.href = `/auth/${provider}/redirect`;
};

// Handle disconnect
const disconnectAccount = async (id) => {
  loading.value = true;
  try {
    await axios.delete(`/api/social-accounts/${id}`);
    accounts.value = accounts.value.filter(acc => acc.id !== id);
    selectedAccounts.value = selectedAccounts.value.filter(accId => accId !== id); // Remove from selected
    saveSelectedAccounts(); // Update selection storage
    showSnackbar('Account disconnected', 'success');
  } catch (error) {
    showSnackbar('Failed to disconnect', 'error');
  } finally {
    loading.value = false;
  }
};

// Save selected accounts
const saveSelectedAccounts = () => {
  localStorage.setItem('selectedAccounts', JSON.stringify(selectedAccounts.value));
  showSnackbar('Selected accounts saved!', 'success');
};

onMounted(fetchAccounts);
</script>

<template>
  <v-container>
    <v-card>
      <v-card-title>Connected Social Accounts</v-card-title>
      <v-card-text>
        <v-list v-if="accounts.length">
          <v-list-item v-for="account in accounts" :key="account.id">
            <v-list-item-content>
              <v-list-item-title>{{ account.provider }}</v-list-item-title>
              <v-list-item-subtitle>{{ account.username }}</v-list-item-subtitle>
            </v-list-item-content>
            <v-list-item-action>
              <!-- Checkbox for selecting accounts -->
              <v-checkbox
                v-model="selectedAccounts"
                :value="account.id"
                label="Use for Posting"
              ></v-checkbox>
              <v-btn color="red" @click="disconnectAccount(account.id)">Disconnect</v-btn>
            </v-list-item-action>
          </v-list-item>
        </v-list>
        <v-alert v-else type="info">No connected accounts</v-alert>
      </v-card-text>
    </v-card>

    <v-btn class="mt-4" color="primary" @click="connectAccount('facebook')">
      Connect Facebook
    </v-btn>
    <v-btn class="mt-4 ml-2" color="primary" @click="connectAccount('twitter')">
      Connect Twitter
    </v-btn>

    <!-- Save Selected Accounts Button -->
    <v-btn class="mt-4 ml-2" color="success" @click="saveSelectedAccounts">
      Save Selected Accounts
    </v-btn>

    <!-- Snackbar Component -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarMessage }}
      <template v-slot:actions>
        <v-btn color="white" @click="snackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

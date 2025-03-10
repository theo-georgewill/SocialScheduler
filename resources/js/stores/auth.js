import { router } from '@/plugins/router';
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),

  actions: {
    async login(userData) {
      try {
        // 1️⃣ Request CSRF cookie to initialize CSRF protection
        await fetch('/sanctum/csrf-cookie', {
          method: 'GET',
          credentials: 'include', // ensures cookies are sent and stored
        });

        // 2️⃣ Send the login request with credentials
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include', // important: uses cookie-based session auth
          body: JSON.stringify(userData),
        });

        if (!response.ok) {
          throw new Error('Invalid credentials');
        }

        // 3️⃣ After successful login, fetch the authenticated user details
        await this.fetchUser();
      } catch (error) {
        console.error('Login failed:', error);
      }
    },

    async logout() {
      try {
        // Send logout request to invalidate the session on the backend
        await fetch('/api/logout', {
          method: 'POST',
          credentials: 'include', // sends cookies to properly end the session
        });

        this.user = null;
        router.push('/login');
      } catch (error) {
        console.error('Logout failed:', error);
      }
    },

    async fetchUser() {
      try {
        // Fetch user details using the session cookie
        const response = await fetch('/api/user', {
          credentials: 'include', // ensures the cookie is used for auth
          headers: {
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error(`User fetch failed: ${response.statusText}`);
        }

        this.user = await response.json();
      } catch (error) {
        console.error('Failed to fetch user:', error);
      }
    },

    async register(userData) {
      try {
        const response = await fetch('/api/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(userData),
        });
    
        if (!response.ok) {
          throw new Error(data.message || 'Registration failed'); // Use API error message if available
        }

        const data = await response.json(); // Parse JSON response
        console.log('Registration successful', data);
        return data; // Return success response if needed

      } catch (error) {
        console.error('Error:', error);
      }
    },
    
  },
});

import { router } from '@/plugins/router';
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
	state: () => ({
		user: null,
	}),

	actions: {
		async register(userData) {
			try {
			  // 1️⃣ Fetch CSRF token
			  await fetch('/sanctum/csrf-cookie', {
				method: 'GET',
				credentials: 'include', // Important for handling cookies
			  });
		  
			  // 2️⃣ Send registration request
			  const response = await fetch('/api/register', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'include', // Ensures cookies are sent
				body: JSON.stringify(userData),
			  });
		  
			  if (!response.ok) {
				const errorData = await response.json();
				throw new Error(errorData.message || 'Registration failed');
			  }
		  
			  // 3️⃣ Fetch user details after successful registration
			  await this.fetchUser();
		  
			  console.log('Registration successful, Logging in...');
			  router.push('/dashboard'); // Redirect user
			} catch (error) {
			  console.error('Error:', error);
			}
		},
		  

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

		
		
	},
});

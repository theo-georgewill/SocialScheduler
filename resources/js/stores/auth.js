import api from '@/api'; // Import Axios instance
import { router } from '@/plugins/router';
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
	state: () => ({
		user: JSON.parse(localStorage.getItem('user')) || null, //get the user from local storage if it exists
		token: localStorage.getItem('token') || null, //get the user from local storage if it exists
	}),

	actions: {
		async loginWithProvider(provider) {
			try {
			  const response = await api.get(`/auth/${provider}/redirect`);
			  const data = await response.json();
			  
			  // Redirect user to social login page
			  window.location.href = data.url;
			} catch (error) {
			  console.error("Login failed:", error);
			}
		},
	  
		async handleCallback(provider, token, accessToken, user) {
			try {
				// Log the received data for debugging
				console.log('Callback received:', { provider, token, accessToken, user });
			
				// Save token and user info in state
				this.token = token;
				this.access_token = accessToken;
				this.user = user;
			
				// Store token and user in localStorage
				localStorage.setItem("token", token);
				localStorage.setItem("access_token", accessToken);
				localStorage.setItem("user", JSON.stringify(user));
			
				console.log("Stored in localStorage:", localStorage.getItem("token"), localStorage.getItem("user"));
			
				// Redirect to the dashboard (or any protected page)
				router.push("/dashboard");
			} catch (error) {
				console.error("Callback handling failed:", error);
			}
		},
		  
	  
		//register user
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
		  
		//login user
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

		//logout user
		async logout() {
			try {
				// Send logout request to invalidate the session on the backend
				await fetch('/api/logout', {
					method: 'POST',
					credentials: 'include', // sends cookies to properly end the session
				});

				this.user = null;
				localStorage.removeItem('user'); // Clear stored user data
				localStorage.removeItem('access_token'); // Clear stored social auth data
				localStorage.removeItem('token'); // Clear stored token data
				router.push('/login');

			} catch (error) {
				console.error('Logout failed:', error);
			}
		},

		//fetch authenticated user details
		async fetchUser() {
			try {
				// Fetch user details using the session cookie
				const response = await fetch('/api/user', {
					credentials: 'include', // ensures the cookie is used for auth
					headers: {
						'Accept': 'application/json',
					},
				});

        		// Check if response is OK and has content
				if (!response.ok) {
					throw new Error(`User fetch failed: ${response.statusText}`);
				}

				this.user = await response.json();
				// Store user in localStorage for persistence
				localStorage.setItem('user', JSON.stringify(this.user));
			} catch (error) {
				console.error('Failed to fetch user:', error);
			}
		},

		//fetch authenticated user's social accounts 
		async fetchSocialAccounts() {
			try {
			  const response = await fetch('/api/social-accounts', {
				credentials: 'include',
				headers: { 'Accept': 'application/json' },
			  });
		  
			  if (!response.ok) throw new Error('Failed to fetch accounts');
		  
			  this.socialAccounts = await response.json();
			  localStorage.setItem('socialAccounts', JSON.stringify(this.socialAccounts));
			} catch (error) {
			  console.error('Error fetching accounts:', error);
			}
		},
		  
		hydrateFromLocalStorage() {
			const storedAccounts = localStorage.getItem('socialAccounts');
			if (storedAccounts) {
			  this.socialAccounts = JSON.parse(storedAccounts);
			}
		}
		  
		
	},
});

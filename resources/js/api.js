import axios from 'axios';

// Create Axios instance
const api = axios.create({
  baseURL: "http://localhost:8000/api", 
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json', 
  },

});

// Attach Authorization token automatically to every request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
  return Promise.reject(error);
});

export default api;

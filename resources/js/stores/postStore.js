import api from '@/api';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const usePostStore = defineStore('postStore', () => {
    // State
    const posts = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Step-specific data for creating posts
    const text = ref('');
    const files = ref([]);
    const scheduledTime = ref('');
    
    // Getters
    const getPostById = (id) => computed(() => posts.value.find(post => post.id === id));

    // Actions
    const fetchPosts = async (filters = {}) => {
        loading.value = true;
        const user = JSON.parse(localStorage.getItem('user'));
        const userId = user?.id;
        try {
            const response = await api.get('/posts', { params: filters, userId });
            posts.value = response.data.data; // assuming the posts are inside the data property
        } catch (err) {
            error.value = err.response ? err.response.data : err.message;
        } finally {
            loading.value = false;
        }
    };

    const createPost = async (postData) => {
        loading.value = true;
        try {
            // Prepare the data (handling files and text)
            const formData = new FormData();
            if (postData.text) formData.append('content', postData.text);

            if (postData.files) {
                postData.files.forEach((file) => formData.append('files[]', file));
            }

            if (postData.scheduledTime) formData.append('scheduled_at', postData.scheduledTime);
            
            if (postData.selectedAccounts && postData.selectedAccounts.length > 0) {
                postData.selectedAccounts.forEach((accountId) => {
                    formData.append('social_account_ids[]', accountId);
                });
            }

            const user = JSON.parse(localStorage.getItem('user'));
            const userId = user?.id;

            if (userId) {
                formData.append('user_id', userId); // Append the user ID to the form data
            } else {
                throw new Error('User not found in localStorage');
            }

            const response = await api.post('/posts', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            posts.value.push(response.data); // add the new post to the store
        } catch (err) {
            error.value = err.response ? err.response.data : err.message;
        } finally {
            loading.value = false;
        }
    };

    const retryPost = async (postId) => {
        loading.value = true;
        try {
            const post = posts.value.find(p => p.id === postId);
            if (!post) throw new Error('Post not found');

            const response = await api.post(`/posts/${postId}/retry`);
            const index = posts.value.findIndex(p => p.id === postId);
            posts.value[index] = response.data; // Update the post status after retry
        } catch (err) {
            error.value = err.response ? err.response.data : err.message;
        } finally {
            loading.value = false;
        }
    };

    const deletePost = async (postId) => {
        loading.value = true;
        try {
            await api.delete(`/posts/${postId}`);
            posts.value = posts.value.filter(post => post.id !== postId); // Remove from the store
        } catch (err) {
            error.value = err.response ? err.response.data : err.message;
        } finally {
            loading.value = false;
        }
    };

    // Helper functions for handling the create post form data
    const setText = (newText) => {
        text.value = newText;
    };

    const addFile = (uploadedFiles) => {
        files.value = [...files.value, ...uploadedFiles];
    };

    const removeFile = (index) => {
        files.value.splice(index, 1);
    };

    const setScheduledTime = (time) => {
        scheduledTime.value = time;
    };

    return {
        posts,
        loading,
        error,
        fetchPosts,
        createPost,
        retryPost,
        deletePost,
        getPostById,
        // Create post specific actions
        setText,
        addFile,
        removeFile,
        setScheduledTime,
        text,
        files,
        scheduledTime,
    };
});

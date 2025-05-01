<script setup>
	import { onMounted } from 'vue';
	import { usePostStore } from '@/stores/postStore'; // adjust the path if needed

	const postStore = usePostStore();
	const { posts, fetchPosts } = postStore;

	// Fetch posts when component is mounted
	onMounted(() => {
		fetchPosts({ status: 'scheduled' }); // Pass filter if needed to get only scheduled posts
	});

	// Handle edit
	const editPost = (post) => {
		console.log('Edit post:', post);
	};
</script>

<template>
	<v-container fluid>
		<h2>Scheduled Posts</h2>

		<v-row v-if="posts.length">
			<v-col cols="12" md="6" lg="4" v-for="post in posts" :key="post.id">
				<v-card>
					<v-img :src="post.image || 'https://via.placeholder.com/150'" height="150px" cover />
					<v-card-title>{{ post.date || 'No Date' }}</v-card-title>
					<v-card-subtitle>{{ post.time || 'No Time' }}</v-card-subtitle>
					<v-card-text>{{ post.content }}</v-card-text>
					<v-card-actions>
						<v-btn color="green" @click="editPost(post)">
							<v-icon start>mdi-pencil</v-icon>
							Edit
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-col>
		</v-row>

		<v-alert v-else type="info" border="left" color="blue" elevation="2">
			No scheduled posts found.
		</v-alert>
	</v-container>
</template>

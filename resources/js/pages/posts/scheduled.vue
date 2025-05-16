<script setup>
import { usePostStore } from '@/stores/postStore';
import { computed, onMounted, ref, watch } from 'vue';

	const postStore = usePostStore();
	const selectedStatus = ref('all');
	const selectedProvider = ref('all');
	

	const fetchFilteredPosts = () => {
		postStore.fetchPosts({
			status: selectedStatus.value,
			provider: selectedProvider.value
		});
	};

	// Fetch posts
	onMounted(() => {
		fetchFilteredPosts();
	});

	// Watch both filters
	watch([selectedStatus, selectedProvider], fetchFilteredPosts);


	// Filter scheduled posts
	const scheduledPosts = computed(() =>
		postStore.posts.filter(post => !!post.scheduled_at)
	);
	
	const filteredPosts = computed(() => {
		return postStore.posts.filter(post => {
			if (selectedStatus.value === 'scheduled') {
				return !!post.scheduled_at;
			}
			if (selectedStatus.value === 'published') {
				return !!post.scheduled_at;
			}
			return true; // 'all' – show everything
		});
	});

	const editPost = (post) => {
		console.log('Edit post:', post);
	};

	function getDate(datetime) {
		return datetime?.split(" ")[0] || 'No Date';
	}

	function getTime(datetime) {
		if (!datetime) return 'No Time';
		const timePart = datetime.split(" ")[1];
		if (!timePart) return 'No Time';

		const [hour, minute] = timePart.split(':');
		const hourNum = parseInt(hour);
		const period = hourNum >= 12 ? 'PM' : 'AM';
		const hour12 = hourNum % 12 || 12;

		return `${hour12}:${minute} ${period}`;
	}


	function formatDate(datetime) {
		if (!datetime) return 'No Date';
		const [year, month, day] = datetime.split(' ')[0].split('-');
		const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
							"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		return `${day} ${monthNames[parseInt(month) - 1]}, ${year}`;
	}


	function getProviderIcon(provider) {
		switch (provider) {
			case 'facebook':
				return 'bxl-facebook';
			case 'reddit':
				return 'bxl-reddit';
			default:
				return 'mdi-account';
		}
	}

	function getProviderStyle(provider) {
		let background = '#999'; // Default gray
		switch (provider.toLowerCase()) {
			case 'facebook':
				background = '#1877F2'; // Facebook blue
				break;
			case 'reddit':
				background = '#FF4500'; // Reddit orange
				break;
		}
		return {
			backgroundColor: background,
			color: 'white',
			borderRadius: '50%',
			padding: '1px',
			position: 'absolute',
			bottom: '-4px',
			right: '-4px',
			boxShadow: '0 0 2px rgba(0, 0, 0, 0.2)'
		};
	}


</script>


<template>
	<v-container fluid>

		<v-row>
			<h2 class="ps-4">Posts</h2>

			<v-col class="ms-auto" cols="6" md="4">
				<v-select
					v-model="selectedProvider"
					:items="['all', 'Reddit', 'Facebook']"
					label="Platform"
					density="compact"
					variant="outlined"
					hide-details
				></v-select>
			</v-col>

			<v-col class="ms-auto" cols="6" md="4">
				<v-select
					v-model="selectedStatus"
					:items="['all', 'Published', 'Scheduled', 'Failed']"
					label="Status"
					density="compact"
					variant="outlined"
					hide-details
				></v-select>
			</v-col>
		</v-row>
		
		<v-row v-if="filteredPosts.length">
			<v-col cols="12" md="6" lg="4" 
				v-for="post in filteredPosts" :key="post.id"
			>
				<v-card>
					<div v-if="post.image">
						<v-img :src="post.image" height="150px" cover />
					</div>

					<div class="d-flex align-center justify-space-between px-4 pt-2">
						<v-card-subtitle class="pa-0 text-body-2">
							{{ formatDate(post.scheduled_at) }}
						</v-card-subtitle>
						<v-card-subtitle class="pa-0 text-body-2">
							{{ getTime(post.scheduled_at) }}
						</v-card-subtitle>
					</div>

					<v-chip color="primary" variant="flat" class="ms-4 rounded-pill text-white px-4">
						Video
					</v-chip>

					<v-card-text>{{ post.content }}</v-card-text>

					<v-card-text class="d-flex align-center px-4 pt-2">
						<div
							v-for="account in post.social_accounts" 
							:key="account.id"
							class="position-relative mx-2"
							style="width: 36px; height: 36px;"
						>
							<v-avatar size="36">
								<v-img :src="`/storage/${account.avatar}`" alt="Profile Picture" cover />
							</v-avatar>

							<v-icon
								:icon="getProviderIcon(account.provider)"
  								:style="getProviderStyle(account.provider)"
								class="provider-icon"
								size="16"
								color="blue-grey-darken-1"
							/>
						</div>
					</v-card-text>

					<!--
					<v-card-actions>
						--<v-btn 
							color="green" 
							@click="editPost(post)"
						>
							<v-icon start color="success">mdi-pencil</v-icon>
							Edit
						</v-btn>--

						<div class="d-flex flex-column ga-2" v-for="account in post.social_accounts" :key="account.id">
							<v-btn 
								v-if="account.provider === 'reddit'"
								@click="postStore.postToReddit(post.id)"
								color="primary"
								variant="elevated"
							>
								<v-icon start>bxl-reddit</v-icon>
								Post to Reddit
							</v-btn>

							<v-btn 
								v-if="account.provider === 'facebook'"
								@click="postStore.postToFacebook(post.id)"
								color="primary"
								variant="elevated"
							>
								<v-icon start>bxl-facebook</v-icon>
								Post to Facebook
							</v-btn>
						</div>
					</v-card-actions>
				-->
				</v-card>
			</v-col>
		</v-row>

		<v-alert v-else type="info" border="left" color="blue" elevation="2">
			No scheduled posts found.
		</v-alert>
	</v-container>
</template>



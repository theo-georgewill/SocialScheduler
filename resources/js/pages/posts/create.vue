<script setup>
	import AccountsConfig from '@/pages/social/config.vue'
	import { usePostStore } from '@/stores/postStore'
	import { useSocialAccounts } from '@/stores/socialAccounts'
	import { computed, ref } from 'vue'
	import { useRouter } from 'vue-router'

	const router = useRouter() 
	const postStore = usePostStore()
	const socialAccountsStore = useSocialAccounts()

	const step = ref(1) // Track current step
	const tab = ref(null)

	// Handle adding/removing files through the store
	const hasUploadedFile = computed(
		() => postStore.files.length > 0
	)

	// Step-specific actions
	const validateAndProceed = (goToNextStep) => {
		if (step.value === 1) {
			// Ensure user has either text or media files
			if (!hasUploadedFile.value && postStore.text.trim() === '') {
				alert("Please add a media file or text before proceeding.")
				return
			}
		}

		if (step.value === 3 && !postStore.scheduledTime) {
			alert("Please select a schedule time before proceeding.")
			return
		}

	// Move to the next step if validation passes
		goToNextStep()
	}

	const buildPlatformMetadata = (account) => {
		const provider = account.provider
		let metadata = {}

		switch (provider) {
			case 'facebook':
				metadata = {
					linkPreview: postStore.facebookLinkPreview || null,
					hashtags: postStore.facebookHashtags || [],
				}
				break
			case 'reddit':
				metadata = {
					subreddit: postStore.redditSubreddit || '',
					title: postStore.redditTitle || postStore.text.substring(0, 100),
				}
				break
			// Add more providers here
			default:
				metadata = {}
		}
		return metadata
	}

	// Action to create the post when ready
	const submitPost = async () => {
		console.log("Submitting post...") 
		const postData = {
			text: postStore.text,
			files: postStore.files,
			scheduledTime: postStore.scheduledTime,
			selectedAccounts: socialAccountsStore.selectedAccounts,
			//metadata: buildPlatformMetadata(account),
		}
		await postStore.createPost(postData)
		router.push('/posts/scheduled') 
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
  	<v-stepper v-model="step" :items="['Create Post', 'Choose Accounts', 'Final Details', 'Review']">
		<!-- step 1 -->
		<template v-slot:item.1>
			<v-card flat>
				<v-tabs v-model="tab" align-tabs="center" color="deep-purple-accent-4">
					<v-tab value="1">Media Post</v-tab>
					<v-tab value="2">Text Post</v-tab>
				</v-tabs>

				<v-tabs-window v-model="tab">
					<!-- Media Post Tab -->
					<v-tabs-window-item value="1">
						<v-container fluid>
							<div class="uploadContainer">
								<v-file-upload 
									v-model="postStore.files" 
									@remove="postStore.handleRemove" 
									accept="image/*,video/*" 
									multiple 
									show-size 
									counter 
									chips 
								/>
							</div>
						</v-container>
					</v-tabs-window-item>

					<!-- Text Post Tab -->
					<v-tabs-window-item value="2">
						<v-container fluid>
							<VForm @submit.prevent="validateAndProceed">
								<VRow>
									<VCol cols="12">
										<VRow no-gutters>
											<VCol cols="12" md="12">
												<v-textarea
													v-model="postStore.text"
													label="Start writing your post here..."
												/>
											</VCol>
										</VRow>
									</VCol>
								</VRow>
							</VForm>
						</v-container>
					</v-tabs-window-item>
				</v-tabs-window>
			</v-card>
		</template>

		<!-- step 2 -->
		<template v-slot:item.2>
			<v-card flat>
				<AccountsConfig />
			</v-card>
		</template>

		<!-- step 3 -->
		<template v-slot:item.3>
			<v-card flat>
				<h3>Schedule or Publish Now</h3>
				<v-text-field 
					v-model="postStore.scheduledTime" 
					label="Pick a time (leave empty for immediate posting)" 
					type="datetime-local" 
				/>
			</v-card>
		</template>

		<!-- step 4 -->
		<template v-slot:item.4>
			<v-card flat>
				<h3>Review & Publish</h3>

				<div class="pa-2 rounded border my-4" v-if="postStore.files.length > 0" >
					<strong>Files: </strong>
					<ul>
						<li v-for="(file, index) in postStore.files" :key="index">{{ file.name }}</li>
					</ul>
				</div>

				<div v-if="postStore.text" class="pa-2 rounded border my-4">
					<strong>Caption: </strong>
					<p>{{ postStore.text }}</p>
				</div>

				<div class="my-4 pa-2 rounded border">
					<strong>Publishing to:</strong>

					<!-- Display list of connected accounts side by side -->
					<div v-if="socialAccountsStore.selectedAccounts.length" class="d-flex flex-wrap">
						<div
							v-for="id in socialAccountsStore.selectedAccounts"
							:key="id"
							class="d-flex align-center mx-2 my-1 pa-2"
							style="min-width: 250px;"
						>
							<div class="position-relative mr-3" style="width: 36px; height: 36px;">
								<v-avatar size="36">
									<v-img
										:src="`/storage/${socialAccountsStore.accounts.find(account => account.id === id)?.avatar}`"
										alt="Profile Picture"
										cover
									/>
								</v-avatar>

								<v-icon
									:icon="getProviderIcon(socialAccountsStore.accounts.find(account => account.id === id)?.provider)"
									:style="getProviderStyle(socialAccountsStore.accounts.find(account => account.id === id)?.provider)"
									class="provider-icon"
									size="16"
									color="blue-grey-darken-1"
									style="position: absolute; bottom: -4px; right: -4px;"
								/>
							</div>

							<div>
								<div class="font-weight-medium">
									{{ socialAccountsStore.accounts.find(account => account.id === id)?.provider }}
								</div>
								<div class="text-subtitle-2">
									{{ socialAccountsStore.accounts.find(account => account.id === id)?.username }}
								</div>
							</div>
						</div>
					</div>


					<p v-if="!socialAccountsStore.selectedAccounts.length">None selected</p>
				</div>
			</v-card>
		</template>

		<!-- Custom Previous Button -->
		<template v-slot:prev="{ props }">
			<v-btn @click="props.onClick" color="secondary" variant="outlined">
				Previous
			</v-btn>
		</template>

		<!-- Custom Next Button -->
		<template v-slot:next="{ props }">
			<v-btn
				v-if="step === 4"
				@click="submitPost"
				color="primary"
  				v-bind="{ ...props, disabled: false }"
				variant="elevated"
			>
				Submit Post
			</v-btn>
			<v-btn
				v-else
				@click="validateAndProceed(props.onClick)"
				color="primary"
				variant="tonal"
			>
				Next
			</v-btn>
		</template>

	</v-stepper>
</template>

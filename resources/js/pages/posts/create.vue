<script setup>
	import { ref, computed } from 'vue'
	import { useRouter } from 'vue-router'
	import axios from 'axios'
	import AccountsSocialConfig from '@/views/pages/account-settings/AccountsSocialConfig.vue'

	const router = useRouter() 
	const step = ref(1) // Track current step
	const tab = ref(null)

	const files = ref([]) // Step 1 input
	const text = ref('') // Step 1 input
	const scheduledTime = ref('') // Step 3 input

	const validExtensions = ['.jpg', '.jpeg', '.png', '.mp4', '.avi', '.mov']


	const hasUploadedFile = computed(() => files.value.length > 0);


	//test to check if files have been bound
	const checkFiles = () => console.log(files.value);
	const checkText = () => console.log(text.value);
	const checkTime = () => {
		console.log(scheduledTime.value);
		console.log(step.value)
	}

	// Handle adding files
	const handleAdd = (uploadedFiles) => {
		uploadedFiles.forEach((file) => {
		const fileExtension = file.name.split('.').pop().toLowerCase();
		if (validExtensions.includes(`.${fileExtension}`)) {
			files.value.push(file);
		} else {
			alert(`File type .${fileExtension} is not allowed!`);
		}
		});
	};

	// Handle file removal
	const handleRemove = (index) => {
		files.value.splice(index, 1)
	};

	// Validation before moving to the next step
	const validateAndProceed = (goToNextStep) => {
		if (step.value === 1) {
			// Ensure user has either text or media files
			if (!hasUploadedFile.value && text.value.trim() === '') {
				alert("Please add a media file or text before proceeding.");
				return;
			}
		}

		if (step.value === 3 && !scheduledTime.value) {
			alert("Please select a schedule time before proceeding.");
			return;
		}

		// Move to the next step if validation passes
		goToNextStep();
	};
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
								<v-file-upload v-model="files" @remove="handleRemove" accept="image/*,video/*" multiple show-size counter chips />
								<button @click="checkFiles">Check File</button>
							</div>
						</v-container>
					</v-tabs-window-item>

					<!-- Text Post Tab -->
					<v-tabs-window-item value="2">
						<v-container fluid>
							<!-- text form -->
							<VForm @submit.prevent>
								<VRow>
									<VCol cols="12">
										<VRow no-gutters>
											<VCol cols="12" md="12">
												<v-textarea
													v-model="text"
													label="Start writing your post here..."
												/>
											</VCol>
										</VRow>
									</VCol>

									<!-- 👉 Submit button -->
									<VCol cols="12">
										<VRow no-gutters>
											<VCol cols="12" md="9">
												<VBtn type="submit" class="me-4">
													Next
												</VBtn>
												<button @click="checkText">Check Text</button>
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
				<AccountsSocialConfig/>
			</v-card>
		</template>

		<!-- step 3 -->
		<template v-slot:item.3>
			<v-card flat>
				<h3>Schedule or Publish Now</h3>
				<v-text-field v-model="scheduledTime" label="Pick a time (leave empty for immediate posting)" type="datetime-local" />
				<button @click="checkTime">Check Time</button>
			</v-card>
		</template>

		<!-- step 4 -->
		<template v-slot:item.4>
			<v-card flat>
				<h3>Review Your Post</h3>
				<!-- <p>Accounts: {{ selectedAccounts.join(', ') }}</p>
				<p>Scheduled Time: {{ scheduledTime || 'Now' }}</p>-->
			</v-card>
		</template>

		<!-- Custom Next Button -->
		<template v-slot:next="{ props }">
			<v-btn @click="validateAndProceed(props.onClick)" color="primary">
				Next
			</v-btn>
		</template>

		<!-- Custom Previous Button -->
		<template v-slot:prev="{ props }">
			<v-btn @click="props.onClick" color="secondary">
				Previous
			</v-btn>
		</template>
	</v-stepper>
</template>

<style scoped>
/deep/ .v-file-upload.v-sheet {
	border: none;
	background-color: transparent;
	inline-size: 100% !important;
}

.uploadContainer {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 20px;
	border: 2px dashed #ccc;
}

.uploadContainer button {
	border: none;
	background-color: #4caf50;
	color: white;
	cursor: pointer;
	margin-block-start: 20px;
	padding-block: 10px;
	padding-inline: 20px;
}

.uploadContainer button:hover {
	background-color: #45a049;
}
</style>

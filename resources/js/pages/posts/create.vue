<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import PostForm from '@/views/pages/form-layouts/PostForm.vue'
import AccountsSocialConfig from '@/views/pages/account-settings/AccountsSocialConfig.vue'

const router = useRouter()
const tab = ref(null)
const files = ref([])
const text = ref('')
const validExtensions = ['.jpg', '.jpeg', '.png', '.mp4', '.avi', '.mov']

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

</script>

<template>
	<v-stepper :items="['Create Post', 'Choose Accounts', 'Final Details', 'Review']">
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
								<!-- <button @click="submitPost">Next</button> -->
							</div>
						</v-container>
					</v-tabs-window-item>

					<!-- Text Post Tab -->
					<v-tabs-window-item value="2">
						<v-container fluid>
							<PostForm @submitTextPost="handleSubmit"/>
						</v-container>
					</v-tabs-window-item>
				</v-tabs-window>
			</v-card>
		</template>

		<template v-slot:item.2>
			<v-card flat>
				<AccountsSocialConfig/>
			</v-card>
		</template>

		<template v-slot:item.3>
			<v-card flat>
				<h3>Schedule or Publish Now</h3>
				<v-text-field v-model="scheduledTime" label="Pick a time (leave empty for immediate posting)" type="datetime-local" />
			</v-card>
		</template>

		<template v-slot:item.4>
			<v-card flat>
				<h3>Review Your Post</h3>
				<!-- <p>Accounts: {{ selectedAccounts.join(', ') }}</p>
				<p>Scheduled Time: {{ scheduledTime || 'Now' }}</p>-->
			</v-card>
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

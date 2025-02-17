<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

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

// Upload media or text post and then go to scheduler
const submitPost = async () => {
  try {
    if (tab.value === "1" && files.value.length === 0) {
      alert('Please select at least one media file before proceeding.');
      return;
    }
    if (tab.value === "2" && !text.value.trim()) {
      alert('Post content cannot be empty!');
      return;
    }

    let payload = new FormData()
    if (tab.value === "1") {
      files.value.forEach(file => payload.append('files[]', file))
    } else {
      payload.append('text', text.value)
    }
    
    // Redirect to step 2 of the scheduler
    router.push('/posts/scheduler?step=2')
  } catch (error) {
    alert('Upload failed!')
    console.error('Upload error:', error.response?.data || error)
  }
};
</script>

<template>
  <v-card>
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
            <button @click="submitPost">Next</button>
          </div>
        </v-container>
      </v-tabs-window-item>

      <!-- Text Post Tab -->
      <v-tabs-window-item value="2">
        <v-container fluid>
          <VForm @submit.prevent="submitPost">
            <VRow>
              <VCol cols="12">
                <v-textarea v-model="text" label="Start writing your post here..." />
              </VCol>
              <VCol cols="12">
                <VBtn type="submit">Next</VBtn>
              </VCol>
            </VRow>
          </VForm>
        </v-container>
      </v-tabs-window-item>
    </v-tabs-window>
  </v-card>
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

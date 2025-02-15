<script setup>
import PostForm from '@/views/pages/form-layouts/PostForm.vue'
import { ref } from 'vue'
import axios from 'axios'

const tab = ref(null)
const files = ref([])

// Allowed file types
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
}

// Upload files to the backend
const uploadFiles = async () => {
  console.log('Selected files:', files.value); // DEBUGGING STEP
  
  if (!files.value || files.value.length === 0) {
    alert('Please select at least one file before uploading.');
    return;
  }

  const formData = new FormData();
  files.value.forEach((file) => {
    formData.append('files[]', file);
  });

  try {
    const response = await axios.post('/api/upload-files', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    console.log('Files uploaded successfully!', response.data);
    alert('Files uploaded successfully!');
    files.value = []; // Clear files after upload
  } catch (error) {
    console.error('Error uploading files:', error.response ? error.response.data : error);
    alert(`Upload failed: ${error.response?.data?.error || 'Unknown error'}`);
  }
};


</script>

<template>
  <v-card>
    <v-tabs
      v-model="tab"
      align-tabs="center"
      color="deep-purple-accent-4"
    >
        <v-tab value="1">Media Post</v-tab>
        <v-tab value="2">Text Post</v-tab>
    </v-tabs>

    <v-tabs-window v-model="tab">
        <v-tabs-window-item value="1">
            <v-container fluid>
                <div class="uploadContainer">
                    <v-file-upload 
                      v-model="files" 
                      @remove="handleRemove"
                      accept="image/*,video/*"
                      multiple
                      show-size
                      counter
                      chips
                    />
                    <button @click="uploadFiles">Upload Files</button>
                </div>
            </v-container>
        </v-tabs-window-item>

        <v-tabs-window-item value="2">
            <v-container fluid>
                <div>
                    <VRow>
                        <VCol
                            cols="12"
                            md="12"
                        >
                            <!-- 👉 Horizontal Form -->
                            <VCard title="Create a post">
                                <VCardText>
                                    <PostForm />
                                </VCardText>
                            </VCard>
                        </VCol>
                    </VRow>
                </div>
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

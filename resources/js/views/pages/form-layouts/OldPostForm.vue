<script setup>
import { ref } from 'vue'
import axios from 'axios'

const text = ref('')

const submitTextPost = async () => {
  if (!text.value.trim()) {
    alert("Post content cannot be empty!")
    return
  }

  try {
    const response = await axios.post('/api/upload-files', {
      text: text.value,
    })

    console.log("Text post uploaded successfully!", response.data)
    alert("Text post uploaded!")
    text.value = "" // Clear the textarea after posting
  } catch (error) {
    console.error("Error uploading text post:", error.response?.data || error)
    alert("Failed to upload text post.")
  }
}
</script>

<template>
  <VForm @submit.prevent="submitTextPost">
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
              Post
            </VBtn>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </VForm>
</template>

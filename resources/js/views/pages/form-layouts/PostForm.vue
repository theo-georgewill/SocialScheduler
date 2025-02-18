<script setup>
import { ref, defineEmits } from 'vue'

const text = ref('')
const emit = defineEmits(['submitTextPost'])

const submitTextPost = () => {
  emit('submitTextPost', text.value)
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

      <!-- 👉 Submit button 
      <VCol cols="12">
        <VRow no-gutters>
          <VCol cols="12" md="9">
            <VBtn type="submit" class="me-4">
              Next
            </VBtn>
          </VCol>
        </VRow>
      </VCol>-->
    </VRow>
  </VForm>
</template>

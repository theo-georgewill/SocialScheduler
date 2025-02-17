<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const step = ref(Number(route.query.step) || 2)

// Step data
const selectedAccounts = ref([])
const scheduledTime = ref('')
const reviewData = ref({})

// Go to next step
const nextStep = () => {
  if (step.value === 2 && selectedAccounts.value.length === 0) {
    alert('Please select at least one social media account.');
    return;
  }
  step.value++
  router.push(`/posts/scheduler?step=${step.value}`)
}

// Go to previous step
const prevStep = () => {
  if (step.value > 2) {
    step.value--
    router.push(`/posts/scheduler?step=${step.value}`)
  } else {
    router.push('/posts/create') // Back to post creation
  }
}

// Submit final post
const submitPost = () => {
  alert('Post successfully scheduled!')
  router.push('/dashboard') // Redirect to dashboard
}
</script>

<template>
  <VCard>
    <VCardTitle>Post Scheduler</VCardTitle>
    <VCardText>
      
      <!-- Step 2: Select Social Media Accounts -->
      <div v-if="step === 2">
        <h3>Select Social Media Accounts</h3>
        <v-checkbox v-model="selectedAccounts" label="Facebook" value="facebook" />
        <v-checkbox v-model="selectedAccounts" label="Instagram" value="instagram" />
        <v-checkbox v-model="selectedAccounts" label="Twitter" value="twitter" />
        <VBtn @click="prevStep">Back</VBtn>
        <VBtn @click="nextStep">Next</VBtn>
      </div>

      <!-- Step 3: Scheduling Options -->
      <div v-if="step === 3">
        <h3>Schedule or Publish Now</h3>
        <v-text-field v-model="scheduledTime" label="Pick a time (leave empty for immediate posting)" type="datetime-local" />
        <VBtn @click="prevStep">Back</VBtn>
        <VBtn @click="nextStep">Next</VBtn>
      </div>

      <!-- Step 4: Review and Submit -->
      <div v-if="step === 4">
        <h3>Review Your Post</h3>
        <p>Accounts: {{ selectedAccounts.join(', ') }}</p>
        <p>Scheduled Time: {{ scheduledTime || 'Now' }}</p>
        <VBtn @click="prevStep">Back</VBtn>
        <VBtn @click="submitPost">Post Now</VBtn>
      </div>

    </VCardText>
  </VCard>
</template>

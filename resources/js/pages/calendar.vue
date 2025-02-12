<script setup>
import { ref } from 'vue';

const today = new Date()

const events = ref([
  { 
    title: 'Meeting', 
    start: new Date(), 
    end: new Date(today.getTime() + 60 * 60 * 1000), // 1 hour later
    color: 'blue' 
  },
  { 
    title: 'Lunch',  
    start: new Date(today.setHours(12, 0)), 
    end: new Date(today.setHours(13, 0)), 
    color: 'green' 
  },
]);
</script>

<template>
  <div>
    <RouterView />
    <VContainer>
      <VCard>
        <VCardTitle>Vuetify Calendar</VCardTitle>
        <VCardText>
          <VCalendar ref="calendar" color="primary" :events="events" :hide-week-number="true" />
        </VCardText>
      </VCard>
    </VContainer>
  </div>
</template>

<style>
.layout-wrapper.layout-blank {
  flex-direction: column;
}

.v-calendar-month__weeknumber {
  display: none !important;
}

</style>

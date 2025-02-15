<script setup>
import { ref } from 'vue';

const scheduledPosts = ref([
  {
    id: 1,
    image: 'https://via.placeholder.com/150',
    date: 'Apr 21, 2025',
    time: '00:10',
    text: 'Lorem ipsum',
  },
]);

const editPost = (post) => {
  console.log('Edit post:', post);
};
</script>

<template>
  <v-container fluid>
    <h2>Scheduled Posts</h2>
    <v-row>
      <v-col cols="12" md="6" lg="4" v-for="post in scheduledPosts" :key="post.id">
        <v-card>
          <v-img :src="post.image" height="150px" cover />
          <v-card-title>{{ post.date }}</v-card-title>
          <v-card-subtitle>{{ post.time }}</v-card-subtitle>
          <v-card-text>{{ post.text }}</v-card-text>
          <v-card-actions>
            <v-btn color="green" prepend-icon="bx-pencil" @click="editPost(post)">Edit</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

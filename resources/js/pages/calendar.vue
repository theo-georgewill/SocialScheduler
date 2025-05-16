<script setup>
	import { onMounted, computed } from 'vue'
	import { usePostStore } from '@/stores/postStore'

	const postStore = usePostStore()

	onMounted(() => {
		postStore.fetchPosts({'status': 'all','provider': 'all'})
	})

	const calendarEvents = computed(() => {
		return postStore.posts.filter(post => !!post.scheduled_at)
			.map(post => ({
				title: post.content || 'Scheduled Post',
				start: new Date(post.scheduled_at),
				end: new Date(post.scheduled_at),
				color: '#3f51b5',
			}))
	})
</script>

<template>
	<div>
		<RouterView />
		<VContainer>
			<VCard>
				<VCardTitle>Scheduled Posts</VCardTitle>
				<VCardText>
					<VCalendar 
						ref="calendar" 
						color="primary" 
						:events="calendarEvents" 
						:hide-week-number="true" 
					/>
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

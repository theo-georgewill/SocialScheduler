<script setup>
	import avatar1 from '@images/avatars/avatar-1.png'
	import { useAuthStore } from '@/stores/auth'
	import { useRouter } from 'vue-router'

	const authStore = useAuthStore()
	const router = useRouter()

	const logout = async () => {
		try {
			await authStore.logout()
			router.push('/login') // Redirect user to login page after logout
		} catch (error) {
			console.error('Logout failed:', error)
		}
	}
</script>

<template>
	<VAvatar
		class="cursor-pointer"
		color="primary"
		variant="tonal"
	>
		<VImg :src="authStore.user.avatar" />

		<!-- SECTION Menu -->
		<VMenu
			activator="parent"
			width="230"
			location="bottom end"
			offset="14px"
		>
			<VList>
				<!-- 👉 User Avatar & Name -->
				<VListItem>
					<template #prepend>
						<VListItemAction start>
							<VAvatar
								color="primary"
								variant="tonal"
							>
								<VImg :src="authStore.user.avatar" />
							</VAvatar>
						</VListItemAction>
					</template>

					<VListItemTitle class="font-weight-semibold">
						{{authStore.user.name}}
					</VListItemTitle>
					<VListItemSubtitle>{{authStore.user.email}}</VListItemSubtitle>
				</VListItem>
				<VDivider class="my-2" />

				<!-- 👉 Profile -->
				<VListItem link>
					<template #prepend>
						<VIcon
							class="me-2"
							icon="bx-user"
							size="22"
						/>
					</template>

					<VListItemTitle>Profile</VListItemTitle>
				</VListItem>

				<!-- 👉 Settings -->
				<VListItem to="/account-settings">
					<template #prepend>
						<VIcon
							class="me-2"
							icon="bx-cog"
							size="22"
						/>
					</template>

					<VListItemTitle>Settings</VListItemTitle>
				</VListItem>


				<!-- Divider -->
				<VDivider class="my-2" />

				<!-- 👉 Logout -->
				<VListItem link @click="logout">
					<template #prepend>
						<VIcon
							class="me-2"
							icon="bx-log-out"
							size="22"
						/>
					</template>

					<VListItemTitle>Logout</VListItemTitle>
				</VListItem>
			</VList>
		</VMenu>
		<!-- !SECTION -->
	</VAvatar>
</template>

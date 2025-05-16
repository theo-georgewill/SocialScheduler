<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue';
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue';
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'; 
</script>

<template>
  <!-- 👉 Dashboards -->
  <VerticalNavGroup
    :item="{
      title: 'Dashboards',
      icon: 'bx-home-smile',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Analytics',
        to: '/dashboard',
      }"
    />
  </VerticalNavGroup>


  <!-- 👉 Apps & Pages -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Content',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Content Calendar',
      icon: 'bx-calendar',
      to: '/calendar',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Create Posts',
      icon: 'bx-file',
      to: '/posts/create',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Posts',
      icon: 'bx-folder-plus',
      to: '/posts/scheduled',
    }"
  />

  <!-- 👉 Configuration -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Configuration',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Accounts',
      icon: 'bx-group',
      to: '/account-config',
    }"
  />

</template>

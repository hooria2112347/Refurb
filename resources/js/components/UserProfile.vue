<template>
    <div class="user-profile">
      <h1>User Profile</h1>
      <div v-if="user">
        <p><strong>Name:</strong> {{ user.name }}</p>
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Role:</strong> {{ user.role }}</p>
      </div>
      <div v-else>
        <p>Loading user profile...</p>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        user: null,
      };
    },
    mounted() {
      this.fetchUserProfile();
    },
    methods: {
      async fetchUserProfile() {
        const userId = this.$route.params.id;
        try {
          const response = await axios.get(`/api/users/${userId}`);
          this.user = response.data;
        } catch (error) {
          console.error('Error fetching user profile:', error);
          alert('Failed to load user profile.');
        }
      },
    },
  };
  </script>
  
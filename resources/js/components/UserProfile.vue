<template>
  <div class="user-profile">
    <h1>User Profile</h1>
    <div v-if="user" class="profile-card">
      <p><strong>Name:</strong> {{ user.name }}</p>
      <p><strong>Email:</strong> {{ user.email }}</p>
      <p><strong>Role:</strong> {{ user.role }}</p>
    </div>
    <div v-else class="loading">
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

<style scoped>
.user-profile {
  max-width: 500px;
  margin: 40px auto;
  font-family: "Helvetica Neue", Arial, sans-serif;
  color: #333;
}

.user-profile h1 {
  text-align: center;
  margin-bottom: 30px;
  font-weight: 600;
  color: #444;
}

.profile-card {
  background-color: #fff;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-card p {
  margin: 12px 0;
  font-size: 16px;
  line-height: 1.5;
}

.profile-card p strong {
  color: #555;
}

.loading {
  text-align: center;
  font-size: 18px;
  color: #666;
}
</style>

<template>
    <div class="dashboard">
      <h2>User Dashboard</h2>
      <p>Welcome, {{ user.name }}!</p>
      <router-link to="/collaborations">View Collaboration Projects</router-link>
      <router-link to="/portfolio">View My Portfolio</router-link>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        user: {
          name: "User Name"
        }
      };
    }
  };
  </script>
  
  <style scoped>
  /* Dashboard styles */
  </style>
  
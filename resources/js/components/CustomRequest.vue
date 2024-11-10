<template>
    <div class="custom-request">
      <h2>Custom Refurbishment Requests</h2>
      <form @submit.prevent="submitRequest">
        <label>Request Title:</label>
        <input type="text" v-model="title" required />
        <label>Description:</label>
        <textarea v-model="description" required></textarea>
        <button type="submit">Submit Request</button>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        title: '',
        description: ''
      };
    },
    methods: {
      submitRequest() {
        // Handle custom request submission
        console.log("Submitting request:", this.title, this.description);
      }
    }
  };
  </script>
  
  <style scoped>
  /* Custom requests page styles */
  </style>
  
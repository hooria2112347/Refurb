<template>
    <div class="feedback">
      <h2>Leave Feedback</h2>
      <form @submit.prevent="submitFeedback">
        <label>Rating:</label>
        <select v-model="rating" required>
          <option value="5">5 - Excellent</option>
          <option value="4">4 - Good</option>
          <option value="3">3 - Average</option>
          <option value="2">2 - Poor</option>
          <option value="1">1 - Very Poor</option>
        </select>
        <label>Feedback:</label>
        <textarea v-model="feedback" required></textarea>
        <button type="submit">Submit Feedback</button>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        rating: 5,
        feedback: ''
      };
    },
    methods: {
      submitFeedback() {
        // Submit feedback logic
        console.log("Feedback:", this.rating, this.feedback);
      }
    }
  };
  </script>
  
  <style scoped>
  /* Feedback page styles */
  </style>
  
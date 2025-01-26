<template>
    <div class="admin-feedback">
      <h2>Feedback Management</h2>
      <ul class="feedback-list">
        <li v-for="feedback in feedbackList" :key="feedback.id">
          <p><strong>Project:</strong> {{ feedback.project.title }}</p>
          <p><strong>User:</strong> {{ feedback.user.name }}</p>
          <p><strong>Rating:</strong> {{ feedback.rating }} ⭐</p>
          <p><strong>Comment:</strong> {{ feedback.comment }}</p>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    name: "AdminFeedback",
    data() {
      return {
        feedbackList: []
      };
    },
    async mounted() {
      try {
        const res = await axios.get("/api/admin/feedback");
        this.feedbackList = res.data;
      } catch (error) {
        console.error(error);
      }
    }
  };
  </script>
  
  <style scoped>
  .admin-feedback {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
  }
  
  .feedback-list {
    list-style: none;
    padding: 0;
  }
  
  .feedback-list li {
    border-bottom: 1px solid #ddd;
    padding: 16px 0;
  }
  
  .feedback-list p {
    margin: 5px 0;
    font-size: 1rem;
  }
  
  .feedback-list strong {
    color: #3c552d;
  }
  </style>
  
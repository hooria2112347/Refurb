<template>
    <div class="custom-requests-page">
      <h1>Your Custom Requests</h1>
      <div v-if="loading" class="loading">Loading...</div>
      <div v-else-if="requests.length === 0" class="no-requests">
        <p>You have not made any custom requests yet.</p>
      </div>
      <div v-else class="requests-list">
        <div v-for="request in requests" :key="request.id" class="request-item">
          <h3>{{ request.description }}</h3>
          <p><strong>Materials:</strong> {{ request.materials || 'N/A' }}</p>
          <p><strong>Dimensions:</strong> {{ request.dimensions || 'N/A' }}</p>
          <p><strong>Budget:</strong> {{ request.budget ? `$${request.budget}` : 'N/A' }}</p>
          <p><strong>Deadline:</strong> {{ request.deadline || 'N/A' }}</p>
          <p><strong>Status:</strong> {{ request.status || 'Pending' }}</p>
          <p><strong>Submitted On:</strong> {{ new Date(request.created_at).toLocaleDateString() }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        requests: [], // Stores fetched requests
        loading: true, // Tracks loading state
      };
    },
    methods: {
      async fetchRequests() {
        try {
          const response = await axios.get("/api/custom-requests", {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
          });
          this.requests = response.data.data;
        } catch (error) {
          console.error("Error fetching custom requests:", error);
          alert("Failed to fetch custom requests. Please try again later.");
        } finally {
          this.loading = false;
        }
      },
    },
    mounted() {
      this.fetchRequests();
    },
  };
  </script>
  
  <style scoped>
  .custom-requests-page {
    max-width: 800px;
    margin: auto;
    padding: 20px;
  }
  
  .loading {
    text-align: center;
    font-size: 1.2em;
  }
  
  .no-requests {
    text-align: center;
    color: #555;
  }
  
  .requests-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  .request-item {
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
  }
  
  .request-item h3 {
    margin: 0 0 10px;
  }
  </style>
  
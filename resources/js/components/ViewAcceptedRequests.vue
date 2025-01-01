<template><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <div class="view-accepted-requests">
      <h1>Accepted Custom Requests</h1>
  
      <!-- Loading Indicator -->
      <div v-if="loading" class="loading">Loading...</div>
  
      <!-- Error Message -->
      <div v-if="error" class="error-message">
        <p>{{ error }}</p>
      </div>
  
      <!-- Requests List -->
      <div v-else class="requests-list">
        <div v-for="request in requests" :key="request.id" class="request-card">
          <div class="request-header">
            <h3>{{ request.description }}</h3>
            <span :class="['status', request.status.toLowerCase()]">{{ request.status }}</span>
          </div>
          <div class="request-info">
            <!-- Description by User -->
            <p class="description-by">By <em>{{ request.user.name }}</em> </p>
            <!-- Budget in PKR -->
            <p><strong>Budget:</strong> ₨{{ request.budget ? request.budget : 'N/A' }}</p>
            <!-- Deadline with Calendar Icon -->
            <p><i class="fas fa-calendar-alt"></i> <strong>Deadline:</strong> {{ formatDate(request.deadline) }}</p>
            <!-- Submitted On with Clock Icon -->
            <p><i class="fas fa-clock"></i><strong>Submitted On:</strong> {{ formatDate(request.created_at) }}</p>
            <!-- Other request details -->
            <p v-if="request.materials"><strong>Materials:</strong> {{ request.materials }}</p>
            <p v-if="request.dimensions"><strong>Dimensions:</strong> {{ request.dimensions }}</p>
            <p v-if="request.style_preferences"><strong>Style Preferences:</strong> {{ request.style_preferences }}</p>
            <p v-if="request.artist_expertise"><strong>Artist Expertise:</strong> {{ request.artist_expertise }}</p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'ViewAcceptedRequests',
    data() {
      return {
        requests: [],
        loading: true,
        error: null,
      };
    },
    methods: {
      async fetchAcceptedRequests() {
        try {
          const token = localStorage.getItem('access_token');
          if (!token) {
            this.error = 'You must be logged in to view accepted requests.';
            this.loading = false;
            return;
          }
  
          const response = await axios.get('/api/custom-requests/accepted', {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
  
          this.requests = response.data.data;
        } catch (err) {
          console.error('Error fetching accepted requests:', err);
          this.error = 'Failed to load accepted requests.';
        } finally {
          this.loading = false;
        }
      },
      formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        return new Date(dateStr).toLocaleDateString();
      },
    },
    mounted() {
      this.fetchAcceptedRequests();
    },
  };
  </script>
  
  <style scoped>
  /* General styles */
  .view-accepted-requests {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    font-size: 14px; /* Smaller font size for the content */
  }
  
  h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
    font-size: 1.8rem; /* Slightly larger for the title */
  }
  
  /* Loading and Error Messages */
  .loading,
  .error-message {
    text-align: center;
    font-size: 1.2em;
    color: #e74c3c;
  }
  
  /* Requests List */
  .requests-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  /* Request Card */
  .request-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  .request-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .request-header h3 {
    margin: 0;
    font-size: 1.5em;
    color: #34495e;
  }
  
  .status {
    padding: 5px 10px;
    border-radius: 5px;
    color: #fff;
    font-weight: bold;
    text-transform: uppercase;
  }
  
  .status.pending {
    background-color: #f1c40f;
  }
  
  .status.accepted {
    background-color: #2ecc71;
  }
  
  .status.declined {
    background-color: #e74c3c;
  }
  
  .request-info p {
    margin: 5px 0;
    color: #7f8c8d;
  }
  
  /* Adding Icon Styles */
  i {
    margin-right: 5px;
    color: #7f8c8d;
  }
  
  /* Description Styling */
  .description-by {
    font-style: italic;
    color: #aab7b8; /* Light font color */
  }
  
  /* Button Styling */
  button {
    background-color: #2ecc71;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 14px; /* Smaller font size for buttons */
  }
  
  button:hover {
    background-color: #27ae60;
  }
  </style>
  
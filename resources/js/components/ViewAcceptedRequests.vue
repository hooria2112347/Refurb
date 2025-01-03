<template>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <div class="custom-requests-page">
    <h1>Your Accepted Custom Requests</h1>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">Loading...</div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>

    <!-- Requests List -->
    <div v-else class="requests-list">
      <div v-for="request in paginatedRequests" :key="request.id" class="request-card" @click="goToRequestDetail(request.id)" :class="{ 'completed-card': request.status === 'Completed' }">
        <div class="request-header">
          <h3 class="request-description">{{ request.description }}</h3>
          <div class="request-status-container">
            <p class="request-status" :class="{ 'accepted': request.status === 'Accepted', 'completed': request.status === 'Completed' }">
              {{ request.status === 'Completed' ? 'Completed' : request.status === 'Accepted' ? 'Accepted' : 'Pending' }} <!-- Show Accepted or Completed -->
            </p>
            <!-- Complete Button -->
            <button v-if="request.status === 'Accepted'" @click.stop="completeRequest(request.id)" class="complete-button">Completed</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls">
      <button @click="changePage('previous')" :disabled="currentPage === 1">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="changePage('next')" :disabled="currentPage === totalPages">Next</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ViewAcceptedRequests',
  data() {
    return {
      requests: [], // Stores fetched requests
      loading: true, // Tracks loading state
      error: null, // Stores error message
      currentPage: 1, // Current page for pagination
      requestsPerPage: 5, // Number of requests per page
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.requests.length / this.requestsPerPage);
    },
    paginatedRequests() {
      const startIndex = (this.currentPage - 1) * this.requestsPerPage;
      const endIndex = startIndex + this.requestsPerPage;
      return this.requests.slice(startIndex, endIndex);
    },
  },
  methods: {
    async getAcceptedRequests() {
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

    goToRequestDetail(requestId) {
      this.$router.push({ name: 'RequestDetailPage', params: { id: requestId } });
    },

    async completeRequest(requestId) {
      if (!confirm("Are you sure you want to mark this request as completed?")) return;

      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.post(`/api/custom-requests/${requestId}/complete`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (response.data && response.data.message) {
          this.$toast.success(response.data.message); // Show success message
        }

        // Update the status locally
        const request = this.requests.find(req => req.id === requestId);
        if (request) {
          request.status = "Completed"; // Update the request's status to completed
        }

      } catch (error) {
        console.error("Error completing request:", error);
        this.$toast.error(error.response?.data?.error || "Failed to complete request.");
      }
    },

    changePage(direction) {
      if (direction === 'previous' && this.currentPage > 1) {
        this.currentPage--;
      } else if (direction === 'next' && this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
  },
  mounted() {
    this.getAcceptedRequests();
  },
};
</script>

<style scoped>
.custom-requests-page { font-family: Arial, sans-serif;
  max-width: 800px;
  margin: 10px auto;
  padding: 5px;
  background-color: #ffffff;
}
 
.custom-requests-page h1 { font-family: Arial, sans-serif;
  text-align: center;
  color: #5d5b5b;
  margin-bottom: 30px;
  font-size: 2rem;
  font-weight: bold;
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
  gap: 15px;
}

/* Request Card */ 
.request-card {
  border: 1px solid #ddd;
  border-radius: 15px;
  padding: 15px;
  background-color: #f3fff0;
  width: 100%;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
  font-size: 0.5rem; font-family: Arial, sans-serif;
}

/* .request-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
} */

/* When the request status is Completed */
.completed-card {
  background-color: #e1ffe0; /* Light green background */
}

/* Request Header */
.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center; font-family: Arial, sans-serif;
}

.request-description {
  font-size: 1.2rem;
  /* font-weight: bold; */
  color: #6e7277; font-family: Arial, sans-serif;
  margin: 0;
}

.request-status-container {
  display: flex;
  align-items: center;
  gap: 15px;
}

.request-status {
  font-size: 1rem;
  margin: 0;
  color: #3498db;
}

.request-status.accepted {
  color: green;
}

.request-status.completed {
  color: #2ecc71; /* Green for completed status */
}

/* Complete Button */
.complete-button {
  padding: 5px 10px;
  background-color: #2ecc71;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
}

.complete-button:hover {
  background-color: #27ae60;
}

/* Pagination Controls */
.pagination-controls {
  display: flex;
  justify-content: center;
  margin-top: 20px;
  gap: 10px;
}

.pagination-controls button {
  padding: 8px 16px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.pagination-controls button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.pagination-controls button:hover:not(:disabled) {
  background-color: #2980b9;
}

.pagination-controls span {
  font-size: 1.1rem;
  align-self: center;
}
</style>

<template>
  <div class="artist-view-custom-requests">
    <h1>All Custom Requests</h1>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">Loading...</div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>

    <!-- Requests List -->
    <div v-else class="requests-list">
      <div v-for="request in requests" :key="request.id" class="request-card" @click="goToRequestDetail(request.id)">
        <div class="request-header">
          <h3>{{ request.description }}</h3>
        </div>
        <div class="request-info">
          <p><strong>Budget:</strong> {{ request.budget ? `$${request.budget}` : 'N/A' }}</p>
          <p><strong>Deadline:</strong> {{ formatDate(request.deadline) }}</p>
        </div>
        <div class="request-actions">
          <!-- Only show Accept and Decline buttons for Pending requests -->
          <button
            v-if="request.status === 'Pending'"
            @click.stop="acceptRequest(request.id)"
            class="accept-btn"
          >
            Accept
          </button>
          <button
            v-if="request.status === 'Pending'"
            @click.stop="declineRequest(request.id)"
            class="decline-btn"
          >
            Decline
          </button>
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
import axios from "axios";

export default {
  data() {
    return {
      requests: [],
      loading: true,
      error: null,
      currentPage: 1,
      requestsPerPage: 5,
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
    // Removed Toast and replaced with simple alert
    async fetchRequests() {
      try {
        const response = await axios.get("/api/custom-requests", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.requests = response.data.data;
      } catch (error) {
        console.error("Error fetching custom requests:", error);
        this.error = "Failed to load custom requests.";
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return "N/A";
      return new Date(dateStr).toLocaleDateString();
    },
    goToRequestDetail(requestId) {
      // Navigating to the request detail page
      this.$router.push({ name: "RequestDetailPage", params: { id: requestId } });
    },
    async acceptRequest(id) {
      if (!confirm("Are you sure you want to accept this request?")) return;
      try {
        const token = localStorage.getItem("access_token");
        const response = await axios.post(`/api/custom-requests/${id}/accept`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        // Update the request's status after accepting
        const request = this.requests.find(req => req.id === id);
        if (request) {
          request.status = "Accepted";
        }

        // Simple alert for success
        alert("Request accepted successfully.");
      } catch (error) {
        console.error("Error accepting request:", error);
        // Simple alert for error
        alert("Failed to accept request.");
      }
    },
    async declineRequest(id) {
      if (!confirm("Are you sure you want to decline this request?")) return;
      try {
        const token = localStorage.getItem("access_token");
        const response = await axios.post(`/api/custom-requests/${id}/decline`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        // Update the request's status after declining
        const request = this.requests.find(req => req.id === id);
        if (request) {
          request.status = "Declined";
        }

        // Simple alert for success
        alert("Request declined successfully.");
      } catch (error) {
        console.error("Error declining request:", error);
        // Simple alert for error
        alert("Failed to decline request.");
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
    this.fetchRequests();
  },
};
</script>

<style scoped>
.artist-view-custom-requests {
  max-width: 1200px;
  margin: 40px auto;
  padding: 20px;
}

.artist-view-custom-requests h1 {
  text-align: center;
  margin-bottom: 30px;
  color: #2c3e50;
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

.request-info p {
  margin: 5px 0;
  color: #7f8c8d;
}

.request-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.accept-btn,
.decline-btn {
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  color: #fff;
  transition: background-color 0.3s;
}

.accept-btn {
  background-color: #2ecc71;
}

.accept-btn:hover {
  background-color: #27ae60;
}

.decline-btn {
  background-color: #e74c3c;
}

.decline-btn:hover {
  background-color: #c0392b;
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

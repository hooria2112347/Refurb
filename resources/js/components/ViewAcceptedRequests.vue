<template>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    rel="stylesheet"
  />
  <div class="custom-requests-page">
    <h1>Accepted Requests</h1>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">Loading...</div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>

    <!-- Requests List -->
    <div v-else class="requests-list">
      <div
        v-for="request in paginatedRequests"
        :key="request.id"
        class="request-card"
        @click="goToRequestDetail(request.id)"
      >
        <div class="request-header">
          <h3 class="request-description">{{ request.description }}</h3>
          <div class="request-status-container">
            <p
              class="request-status"
              :class="{
                accepted: request.status === 'Accepted'
              }"
            >
              {{ request.status === 'Accepted' ? 'Accepted' : 'Pending' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls">
      <button @click="changePage('previous')" :disabled="currentPage === 1">
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button
        @click="changePage('next')"
        :disabled="currentPage === totalPages"
      >
        Next
      </button>
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
/* Container - mirroring MyInvitations styling */
.custom-requests-page {
  max-width: 700px;
  margin: 40px auto;
  padding: 0 16px;
}

/* Page Header - similar to MyInvitations h1 */
.custom-requests-page h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3B1E54;
  margin-bottom: 30px;
  font-weight: 600;
}

/* Loading and Error Messages */
.loading,
.error-message {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-top: 20px;
}

/* Requests List - mirroring .invitations-list */
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Request Card - mirroring .invitation-item */
.request-card {
  background-color: #fff;
  padding: 16px;
  border-left: 4px solid #3C552D;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  cursor: default;
  transition: transform 0.3s, box-shadow 0.3s;
}

.request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Header and Description - matching MyInvitations text styling */
.request-header h3.request-description {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: 500;
}

.request-status-container {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Status text styling */
.request-status {
  font-size: 1rem;
  color: #555;
  margin: 0;
}

.request-status.accepted {
  color: #27ae60;
}

/* Pagination Controls - mirroring MyInvitations .pagination-controls */
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 15px;
}

.pagination-controls button {
  padding: 8px 16px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.95rem;
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
  font-weight: bold;
}

/* Responsive styling - same approach as MyInvitations */
@media (max-width: 768px) {
  .custom-requests-page {
    margin: 20px auto;
    padding: 0 12px;
  }

  .custom-requests-page h1 {
    font-size: 1.5rem;
    margin-bottom: 24px;
  }

  .request-card {
    padding: 12px;
  }

  .pagination-controls button {
    padding: 6px 14px;
    font-size: 0.85rem;
  }
}
</style>

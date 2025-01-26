<template>
  <div class="my-invitations">
    <!-- Header -->
    <h1>All Custom Requests</h1>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">
      <p>Loading...</p>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>

    <!-- Requests List -->
    <div v-else class="invitations-list">
      <div 
        v-for="request in paginatedRequests" 
        :key="request.id" 
        class="invitation-item"
      >
        <!-- Title / Request Info -->
        <h3>
          {{ request.description || 'No description provided.' }}
        </h3>

        <!-- Request Details -->
        <p>
          <strong>Budget:</strong> {{ request.budget ? `${request.budget} PKR` : 'N/A' }}
        </p>
        <p>
          <strong>Deadline:</strong> {{ formatDate(request.deadline) }}
        </p>

        <!-- Accept/Decline Buttons (if pending) -->
        <div class="action-buttons" v-if="request.status === 'Pending'">
          <button 
            @click="acceptRequest(request.id)" 
            class="accept-btn"
          >
            Accept
          </button>
          <button 
            @click="declineRequest(request.id)" 
            class="reject-btn"
          >
            Decline
          </button>
        </div>

        <!-- Status Labels -->
        <div v-else-if="request.status === 'Declined'" class="status-label rejected">
          <p>Declined</p>
        </div>
        <div v-else-if="request.status === 'Accepted'" class="status-label accepted">
          <p>Accepted</p>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls" v-if="totalPages > 1">
      <button @click="changePage('previous')" :disabled="currentPage === 1">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="changePage('next')" :disabled="currentPage === totalPages">Next</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ArtistViewCustomRequests",
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
/* 
  CONTAINER FOR THE PAGE 
  - Keeping it consistent with MyInvitations.
*/
.my-invitations {
  max-width: 700px;
  margin: 40px auto;
  padding: 0 16px;
}

/* 
  PAGE HEADER 
  - A simple, centered title with the same accent color.
*/
.my-invitations h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3B1E54;
  margin-bottom: 30px;
  font-weight: 600;
}

/* LOADING AND ERROR MESSAGES */
.loading p,
.error-message p {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-top: 20px;
}

/* 
  REQUESTS LIST 
  - Similar to invitations-list with vertical stacking and gaps.
*/
.invitations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* 
  INVITATION ITEM 
  - Mirroring invitation-item styling.
*/
.invitation-item {
  background-color: #fff;
  padding: 16px;
  border-left: 4px solid #3C552D;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
}

.invitation-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* REQUEST TITLE */
.invitation-item h3 {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: 500;
}

.invitation-item strong {
  color: #3C552D;
}

/* REQUEST DETAILS */
.invitation-item p {
  font-size: 1rem;
  color: #555;
  margin: 4px 0;
}

/* 
  ACTION BUTTONS 
  - Consistent with MyInvitations action-buttons.
*/
.action-buttons {
  margin-top: 12px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.accept-btn,
.reject-btn {
  padding: 8px 18px;
  font-size: 0.95rem;
  font-weight: 500;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: opacity 0.3s ease;
}

.accept-btn {
  background-color: #27ae60;
}

.accept-btn:hover {
  opacity: 0.9;
}

.reject-btn {
  background-color: #e74c3c;
}

.reject-btn:hover {
  opacity: 0.9;
}

/* 
  STATUS LABELS 
  - Consistent styling with MyInvitations.
*/
.status-label {
  margin-top: 12px;
  text-align: center;
  padding: 8px 0;
  border-radius: 6px;
  font-size: 0.95rem;
  font-weight: 500;
}

.status-label.accepted {
  background-color: #27ae60;
  color: #fff;
}

.status-label.rejected {
  background-color: #e74c3c;
  color: #fff;
}

/* 
  PAGINATION CONTROLS 
  - Styled similarly for consistency.
*/
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

/* 
  RESPONSIVE DESIGN 
  - Consistent with MyInvitations.
*/
@media (max-width: 768px) {
  .my-invitations {
    margin: 20px auto;
    padding: 0 12px;
  }

  .my-invitations h1 {
    font-size: 1.5rem;
    margin-bottom: 24px;
  }
  
  .invitation-item {
    padding: 12px;
  }

  .accept-btn,
  .reject-btn {
    padding: 6px 14px;
    font-size: 0.85rem;
  }

  .pagination-controls button {
    padding: 6px 14px;
    font-size: 0.85rem;
  }
}
</style>

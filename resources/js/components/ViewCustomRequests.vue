<template>
  <div class="custom-requests-page">
    <h1>Your Custom Requests</h1>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">Loading...</div>

    <!-- No Requests Message -->
    <div v-else-if="requests.length === 0" class="no-requests">
      <p>You have not made any custom requests yet.</p>
    </div>

    <!-- Requests List -->
    <div v-else class="requests-list">
      <div
        v-for="request in paginatedRequests"
        :key="request.id"
        class="request-card"
        @click="goToRequestDetail(request.id)"
        :class="{ 'accepted-card': request.status === 'Accepted' }"
      >
        <div class="request-header">
          <h3 class="request-description">{{ request.description }}</h3>
          <div class="request-status-container">
            <p
              class="request-status"
              :class="{ accepted: request.status === 'Accepted', pending: request.status === 'Pending' }"
            >
              {{ request.status }}
            </p>
          </div>
        </div>
        <div class="actions">
          <button class="edit-button" @click.stop="editRequest(request)">Edit</button>
          <button class="delete-button" @click.stop="deleteRequest(request.id)">Delete</button>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls">
      <button @click="changePage('previous')" :disabled="currentPage === 1">
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="changePage('next')" :disabled="currentPage === totalPages">
        Next
      </button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "CustomRequests",
  data() {
    return {
      requests: [], // Stores fetched requests
      loading: true, // Tracks loading state
      currentPage: 1, // Current page for pagination
      requestsPerPage: 5, // Number of requests per page
    };
  },
  computed: {
    // Calculate the total number of pages
    totalPages() {
      return Math.ceil(this.requests.length / this.requestsPerPage);
    },
    // Get the requests to display on the current page
    paginatedRequests() {
      const startIndex = (this.currentPage - 1) * this.requestsPerPage;
      const endIndex = startIndex + this.requestsPerPage;
      return this.requests.slice(startIndex, endIndex);
    },
  },
  methods: {
    async acceptRequest(id) {
      if (!confirm("Are you sure you want to accept this request?")) return;
      try {
        const token = localStorage.getItem("access_token");
        const response = await axios.post(
          `/api/custom-requests/${id}/accept`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        console.log("Accept Response: ", response); // Log the response to verify its structure
        if (response.data && response.data.message) {
          this.$toast.success(response.data.message); // Show success message from response
        }

        const request = this.requests.find((req) => req.id === id);
        if (request) {
          request.status = "Accepted";
        }
      } catch (error) {
        console.error("Error accepting request:", error);
        this.$toast.error(
          error.response?.data?.error || "Failed to accept request."
        );
      }
    },
    async declineRequest(id) {
      if (!confirm("Are you sure you want to decline this request?")) return;
      try {
        const token = localStorage.getItem("access_token");
        const response = await axios.post(
          `/api/custom-requests/${id}/decline`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        console.log("Decline Response: ", response); // Log the response to verify its structure
        if (response.data && response.data.message) {
          this.$toast.success(response.data.message); // Show success message from response
        }

        const request = this.requests.find((req) => req.id === id);
        if (request) {
          request.status = "Declined";
        }
      } catch (error) {
        console.error("Error declining request:", error);
        this.$toast.error(
          error.response?.data?.error || "Failed to decline request."
        );
      }
    },
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
        this.$toast.error("Failed to fetch custom requests.");
      } finally {
        this.loading = false;
      }
    },
    goToRequestDetail(requestId) {
      // Navigating to the request detail page
      this.$router.push({ name: "RequestDetailPage", params: { id: requestId } });
    },
    editRequest(request) {
      // Edit request logic (if any)
      console.log("Editing request", request);
      // Example: Navigate to an edit page
      this.$router.push({ name: "EditRequestPage", params: { id: request.id } });
    },
    async deleteRequest(requestId) {
      if (!confirm("Are you sure you want to delete this request?")) return;
      try {
        const token = localStorage.getItem("access_token");
        const response = await axios.delete(`/api/custom-requests/${requestId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        console.log("Delete Response: ", response); // Log the response to verify its structure
        if (response.data && response.data.message) {
          this.$toast.success(response.data.message); // Show success message from response
        }

        // Remove the deleted request from the list
        this.requests = this.requests.filter((req) => req.id !== requestId);
      } catch (error) {
        console.error("Error deleting request:", error);
        this.$toast.error(
          error.response?.data?.error || "Failed to delete request."
        );
      }
    },
    changePage(direction) {
      if (direction === "previous" && this.currentPage > 1) {
        this.currentPage--;
      } else if (
        direction === "next" &&
        this.currentPage < this.totalPages
      ) {
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
.custom-requests-page {
  max-width: 900px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.custom-requests-page h1 {
  text-align: center;
  color: #333333;
  margin-bottom: 30px;
  font-size: 2rem;
  font-weight: 700;
}

/* Loading Indicator */
.loading {
  text-align: center;
  font-size: 1.5em;
  color: #555555;
}

/* No Requests Message */
.no-requests {
  text-align: center;
  color: #555555;
  font-size: 1.2em;
  padding: 20px 0;
  border: 1px dashed #cccccc;
  border-radius: 8px;
  background-color: #f9f9f9;
}

/* Requests List */
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Request Card */
.request-card {
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
  background-color: #fafafa;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.request-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Accepted Request Card */
.accepted-card {
  background-color: #e8f5e9; /* Light green background */
  border-color: #a5d6a7;
}

/* Request Header */
.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.request-description {
  font-size: 1.3rem;
  color: #333333;
  margin: 0;
  flex: 1;
  margin-right: 10px;
  word-wrap: break-word;
}

.request-status-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Request Status */
.request-status {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: center;
}

.request-status.accepted {
  background-color: #a5d6a7;
  color: #1b5e20;
}

.request-status.pending {
  background-color: #fff59d;
  color: #f57f17;
}

.request-status.declined {
  background-color: #ef9a9a;
  color: #c62828;
}

/* Actions */
.actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
}

.edit-button,
.delete-button {
  flex: 0.1;
  padding: 10px 0;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: background-color 0.3s, transform 0.2s;
}

.edit-button {
  background-color: #42a5f5;
  color: #ffffff;
}

.edit-button:hover {
  background-color: #1e88e5;
  transform: translateY(-2px);
}

.delete-button {
  background-color: #ef5350;
  color: #ffffff;
}

.delete-button:hover {
  background-color: #e53935;
  transform: translateY(-2px);
}

/* Pagination Controls */
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 30px;
  gap: 15px;
}

.pagination-controls button {
  padding: 10px 20px;
  background-color: #42a5f5;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: background-color 0.3s, transform 0.2s;
}

.pagination-controls button:hover:not(:disabled) {
  background-color: #1e88e5;
  transform: translateY(-2px);
}

.pagination-controls button:disabled {
  background-color: #90caf9;
  cursor: not-allowed;
}

.pagination-controls span {
  font-size: 1.1rem;
  color: #555555;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
  .custom-requests-page {
    margin: 20px;
    padding: 15px;
  }

  .request-description {
    font-size: 1.1rem;
  }

  .actions {
    flex-direction: column;
  }

  .edit-button,
  .delete-button {
    font-size: 0.9rem;
    padding: 8px 0;
  }

  .pagination-controls button {
    padding: 8px 16px;
    font-size: 0.9rem;
  }

  .pagination-controls span {
    font-size: 1rem;
  }
}

@media screen and (max-width: 480px) {
  .custom-requests-page {
    margin: 10px;
    padding: 10px;
  }

  .request-description {
    font-size: 1rem;
  }

  .actions {
    gap: 8px;
  }

  .edit-button,
  .delete-button {
    font-size: 0.8rem;
    padding: 6px 0;
  }

  .pagination-controls {
    flex-direction: column;
    gap: 10px;
  }

  .pagination-controls button {
    width: 100%;
  }
}
</style>

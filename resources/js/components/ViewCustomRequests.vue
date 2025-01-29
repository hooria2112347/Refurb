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
              :class="{ 'accepted': request.status === 'Accepted' }"
            >
              {{ request.status === 'Accepted' ? 'Accepted' : 'Pending' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls" v-if="requests.length > 0">
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
export default {
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
    // (Optional) Keep these if you plan to handle accept/decline in the future
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
        if (response.data && response.data.message) {
          this.$toast.success(response.data.message);
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
        if (response.data && response.data.message) {
          this.$toast.success(response.data.message);
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
        alert("Failed to fetch custom requests.");
      } finally {
        this.loading = false;
      }
    },
    goToRequestDetail(requestId) {
      // Navigating to the request detail page
      this.$router.push({ name: "RequestDetailPage", params: { id: requestId } });
    },
    changePage(direction) {
      if (direction === "previous" && this.currentPage > 1) {
        this.currentPage--;
      } else if (direction === "next" && this.currentPage < this.totalPages) {
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
/* Container */
.custom-requests-page {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  margin: 40px auto; 
  padding: 20px;
  max-width: 600px; /* Restrict width for a cleaner layout */
  background: linear-gradient(135deg, #f8f8f8, #ffffff);
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Page Title */
.custom-requests-page h1 {
  text-align: center;
  color: #333;
  margin-bottom: 30px;
  font-size: 1.8rem;
  font-weight: 600;
}

/* Loading Indicator */
.loading {
  text-align: center;
  font-size: 1.2rem;
  color: #555;
  margin-top: 20px;
}

/* No Requests Message */
.no-requests {
  text-align: center;
  color: #555;
  font-size: 1rem;
  margin-top: 20px;
}

/* Requests List */
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Request Card */
.request-card {
  background-color: #fff;
  border-radius: 10px;
  padding: 15px;
  width: 100%;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.request-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

/* Accepted Card Background */
.accepted-card {
  background-color: #effff0;
}

/* Request Header */
.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.request-description {
  font-size: 1rem;
  color: #555;
  margin: 0;
}

/* Status Container */
.request-status-container {
  display: flex;
  align-items: center;
}

/* Status Text */
.request-status {
  font-size: 0.9rem;
  margin: 0;
  font-weight: 500;
  color: #3498db;
}

.request-status.accepted {
  color: #27ae60; /* Green for Accepted */
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
  border-radius: 20px;
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
  font-size: 1rem;
  align-self: center;
  color: #555;
}
</style>

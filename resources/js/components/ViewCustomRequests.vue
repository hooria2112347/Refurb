<template><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
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
      <div v-for="request in paginatedRequests" :key="request.id" class="request-card" @click="goToRequestDetail(request.id)" :class="{ 'accepted-card': request.status === 'Accepted' }">
        <div class="request-header">
          <h3 class="request-description">{{ request.description }}</h3>
          <div class="request-status-container">
            <p class="request-status" :class="{ 'accepted': request.status === 'Accepted' }">
              {{ request.status === 'Accepted' ? 'Accepted' : 'Pending' }} <!-- Show Accepted or Pending -->
            </p>
            <!-- Edit and Delete Icons -->
            <span @click.stop="editRequest(request)" class="edit-icon">
              <i class="fa fa-pencil-alt" aria-hidden="true"></i>
            </span>
            <span @click.stop="deleteRequest(request.id)" class="delete-icon">
              <i class="fa fa-trash-alt" aria-hidden="true"></i>
            </span>
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
  methods: {async acceptRequest(id) {
  if (!confirm("Are you sure you want to accept this request?")) return;
  try {
    const token = localStorage.getItem("access_token");
    const response = await axios.post(`/api/custom-requests/${id}/accept`, {}, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    console.log("Accept Response: ", response);  // Log the response to verify its structure
    if (response.data && response.data.message) {
      this.$toast.success(response.data.message);  // Show success message from response
    }

    const request = this.requests.find(req => req.id === id);
    if (request) {
      request.status = "Accepted";
    }

  } catch (error) {
    console.error("Error accepting request:", error);
    this.$toast.error(error.response?.data?.error || "Failed to accept request.");
  }
}

,
async declineRequest(id) {
  if (!confirm("Are you sure you want to decline this request?")) return;
  try {
    const token = localStorage.getItem("access_token");
    const response = await axios.post(`/api/custom-requests/${id}/decline`, {}, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    console.log("Decline Response: ", response);  // Log the response to verify its structure
    if (response.data && response.data.message) {
      this.$toast.success(response.data.message);  // Show success message from response
    }

    const request = this.requests.find(req => req.id === id);
    if (request) {
      request.status = "Declined";
    }

  } catch (error) {
    console.error("Error declining request:", error);
    this.$toast.error(error.response?.data?.error || "Failed to decline request.");
  }
}
,

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
    editRequest(request) {
      // Edit request logic (if any)
      console.log("Editing request", request);
    },
    deleteRequest(requestId) {
      // Delete request logic
      console.log("Deleting request", requestId);
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
.custom-requests-page { font-family: Arial, sans-serif;
  /* max-width: 800px; Reduce the max width for a smaller layout */
  margin: 40px;
  padding: 5px;
  background-color: #ffffff;
}

.custom-requests-page h1 {
  text-align: center;
  color: #5d5b5b;
  margin-bottom: 30px;
  font-size: 1.5rem; /* Smaller font size */
  font-weight: bold; 
}

/* Loading Indicator */
.loading {
  text-align: center;
  font-size: 1.5em;
  color: #555;
}

/* No Requests Message */
.no-requests {
  text-align: center;
  color: #555;
  font-size: 1.2em;
}

/* Requests List */
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Request Card */
.request-card {
  /* border: 1px solid #ddd; */
  border-radius: 15px;
  padding: 15px;
  
  width: 100%;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
  font-size: 0.5rem; font-family: Arial, sans-serif;
}

.request-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* When the request status is Accepted */
.accepted-card {
  background-color: #f9fff8; /* Pastel green background */
}

/* Request Header */
.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.request-description {
  font-size: 1.3rem; /* Slightly smaller font size */
  /* font-weight: bold; */
  color: #6e7277;
  margin: 0;
}

.request-status-container {
  display: flex;
  align-items: center;
  gap: 15px; /* Add padding between status and icons */
}

.request-status {
  font-size: 1rem;
  margin: 0;
  color: #3498db;
}

.request-status.accepted {
  color: green; /* Green for Accepted status */
}

.actions {
  margin-top: 10px;
  display: flex;
  gap: 10px;
  align-items: center;
}

/* Edit Icon */
.edit-icon,
.delete-icon {
  font-size: 1.3rem;
  cursor: pointer;
  color: #3498db;
  transition: color 0.3s ease;
}

.edit-icon:hover {
  color: #2980b9;
}

.delete-icon:hover {
  color: #e74c3c;
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

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
      <div
        v-for="request in requests"
        :key="request.id"
        class="request-card"
        @click="goToRequestDetail(request.id)"
      >
        <div class="request-header">
          <h3>{{ request.description }}</h3>
        </div>
        <div class="request-info">
          <p><strong>Budget:</strong> {{ request.budget ? `${request.budget}  PKR` : 'N/A' }}</p>
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

    <!-- =========================== MODALS =========================== -->

    <!-- 1) Confirm Accept Modal -->
    <div v-if="showConfirmAcceptModal" class="modal-overlay" @click.self="closeConfirmAcceptModal">
      <div class="modal-content">
        <h2>Confirm</h2>
        <p>Are you sure you want to accept this request?</p>
        <div class="modal-buttons">
          <button class="ok-btn" @click="doAcceptRequest">Yes</button>
          <button class="cancel-btn" @click="closeConfirmAcceptModal">No</button>
        </div>
      </div>
    </div>

    <!-- 2) Accept Success Modal -->
    <div v-if="showAcceptSuccessModal" class="modal-overlay" @click.self="closeAcceptSuccessModal">
      <div class="modal-content">
        <h2>Notification</h2>
        <p>Request accepted successfully.</p>
        <div class="modal-buttons">
          <button class="ok-btn" @click="closeAcceptSuccessModal">OK</button>
        </div>
      </div>
    </div>

    <!-- 3) Confirm Decline Modal -->
    <div v-if="showConfirmDeclineModal" class="modal-overlay" @click.self="closeConfirmDeclineModal">
      <div class="modal-content">
        <h2>Confirm</h2>
        <p>Are you sure you want to decline this request?</p>
        <div class="modal-buttons">
          <button class="ok-btn" @click="doDeclineRequest">Yes</button>
          <button class="cancel-btn" @click="closeConfirmDeclineModal">No</button>
        </div>
      </div>
    </div>

    <!-- 4) Decline Success Modal -->
    <div v-if="showDeclineSuccessModal" class="modal-overlay" @click.self="closeDeclineSuccessModal">
      <div class="modal-content">
        <h2>Notification</h2>
        <p>Request declined successfully.</p>
        <div class="modal-buttons">
          <button class="ok-btn" @click="closeDeclineSuccessModal">OK</button>
        </div>
      </div>
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

      // ======== NEW MODAL STATES ========
      showConfirmAcceptModal: false,
      showAcceptSuccessModal: false,
      showConfirmDeclineModal: false,
      showDeclineSuccessModal: false,

      // We'll store the ID of the request we're acting upon
      currentRequestId: null,
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

    // ===================================================
    // ================ ACCEPT LOGIC ======================
    // ===================================================
    async acceptRequest(id) {
      // Original:
      // if (!confirm("Are you sure you want to accept this request?")) return;
      // We'll open a confirm accept modal instead:
      this.currentRequestId = id;
      this.showConfirmAcceptModal = true;
    },
    // Called when user clicks "Yes" in the confirm accept modal
    async doAcceptRequest() {
      this.showConfirmAcceptModal = false; // close the confirm modal
      try {
        const token = localStorage.getItem("access_token");
        // Original: the entire block inside acceptRequest
        //   if (!confirm("Are you sure you want to accept this request?")) return;
        //   ...
        //   alert("Request accepted successfully.");
        // Now we do that logic here:
        await axios.post(
          `/api/custom-requests/${this.currentRequestId}/accept`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        const request = this.requests.find((req) => req.id === this.currentRequestId);
        if (request) request.status = "Accepted";

        // alert("Request accepted successfully.");
        // => Replaced by a success modal:
        this.showAcceptSuccessModal = true;
      } catch (error) {
        console.error("Error accepting request:", error);
        alert("Failed to accept request."); // Keep original alert
      }
    },
    closeConfirmAcceptModal() {
      this.showConfirmAcceptModal = false;
      this.currentRequestId = null;
    },
    closeAcceptSuccessModal() {
      this.showAcceptSuccessModal = false;
    },

    // ===================================================
    // ================ DECLINE LOGIC =====================
    // ===================================================
    async declineRequest(id) {
      // Original:
      // if (!confirm("Are you sure you want to decline this request?")) return;
      // We'll open a confirm decline modal instead:
      this.currentRequestId = id;
      this.showConfirmDeclineModal = true;
    },
    // Called when user clicks "Yes" in the confirm decline modal
    async doDeclineRequest() {
      this.showConfirmDeclineModal = false; // close the confirm modal
      try {
        const token = localStorage.getItem("access_token");
        // Original logic from declineRequest:
        // if (!confirm("Are you sure you want to decline this request?")) return;
        // ...
        // alert("Request declined successfully.");
        // Moved here:
        await axios.post(
          `/api/custom-requests/${this.currentRequestId}/decline`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        const request = this.requests.find((req) => req.id === this.currentRequestId);
        if (request) request.status = "Declined";

        // alert("Request declined successfully.");
        // => Replaced by a success modal:
        this.showDeclineSuccessModal = true;
      } catch (error) {
        console.error("Error declining request:", error);
        alert("Failed to decline request."); // Keep original alert
      }
    },
    closeConfirmDeclineModal() {
      this.showConfirmDeclineModal = false;
      this.currentRequestId = null;
    },
    closeDeclineSuccessModal() {
      this.showDeclineSuccessModal = false;
    },

    // ===================================================
    // ================ PAGINATION LOGIC ==================
    // ===================================================
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
/* Container (mirroring .my-invitations styling) */
.artist-view-custom-requests {
  max-width: 700px;
  margin: 40px auto;
  padding: 0 16px;
}

/* Header */
.artist-view-custom-requests h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3B1E54;
  margin-bottom: 30px;
  font-weight: 600;
}

/* Loading and Error Messages (mirroring .loading and .error-message) */
.loading {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-top: 20px;
}

.error-message {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-top: 20px;
}

/* Requests List (mirroring .invitations-list) */
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Request Card (mirroring .invitation-item) */
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

/* Request Header & Title */
.request-header h3 {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: 500;
}

/* Request Info (mirroring invitation-item p) */
.request-info p {
  font-size: 1rem;
  color: #555;
  margin: 4px 0;
}

/* Request Actions (mirroring .action-buttons) */
.request-actions {
  margin-top: 12px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

/* Accept & Decline Buttons (mirroring .accept-btn, .reject-btn) */
.accept-btn,
.decline-btn {
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

.decline-btn {
  background-color: #e74c3c;
}

.decline-btn:hover {
  opacity: 0.9;
}

/* Pagination Controls (mirroring the same style from .pagination-controls) */
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

/* RESPONSIVE STYLING */
@media (max-width: 768px) {
  .artist-view-custom-requests {
    margin: 20px auto;
    padding: 0 12px;
  }

  .artist-view-custom-requests h1 {
    font-size: 1.5rem;
    margin-bottom: 24px;
  }

  .request-card {
    padding: 12px;
  }

  .accept-btn,
  .decline-btn {
    padding: 6px 14px;
    font-size: 0.85rem;
  }

  .pagination-controls button {
    padding: 6px 14px;
    font-size: 0.85rem;
  }
}

/* ======= MODAL STYLES ======= */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background-color: #fff;
  padding: 2rem;
  width: 90%;
  max-width: 400px;
  border-radius: 8px;
  text-align: left;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

/* Shared button styles for modals */
.ok-btn,
.cancel-btn {
  padding: 0.6rem 1rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  color: #fff;
}

.ok-btn {
  background-color: #27ae60; /* or #d9534f if you want red for "delete" */
}

.ok-btn:hover {
  opacity: 0.9;
}

.cancel-btn {
  background-color: #6c757d;
}

.cancel-btn:hover {
  opacity: 0.9;
}
</style>

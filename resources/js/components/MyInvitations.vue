<template>
  <div class="my-invitations">
    <!-- Header -->
    <h1>My Invitations</h1>

    <!-- No Invitations Message -->
    <div v-if="invitations.length === 0 && !loading && !error" class="no-invitations">
      <p>No pending invitations.</p>
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">
      <p>Loading...</p>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>

    <!-- Invitations List -->
    <div v-else class="invitations-list">
      <div 
        v-for="invite in paginatedInvitations" 
        :key="invite.id" 
        class="invitation-item"
      >
        <!-- Title / Project Info -->
        <h3 v-if="invite.project">
          You have been invited to collaborate on 
          <strong>{{ invite.project.title }}</strong>
        </h3>
        <h3 v-else>
          You have an invitation without project data.
        </h3>

        <!-- Inviter Info -->
        <p>
          Invited by: 
          <strong>{{ invite.inviter ? invite.inviter.name : 'N/A' }}</strong>
        </p>

        <!-- Accept/Reject Buttons (if pending) -->
        <div class="action-buttons" v-if="invite.status === 'pending'">
          <button 
            @click="respondToInvitation(invite.id, 'accepted')" 
            class="accept-btn"
          >
            Accept
          </button>
          <button 
            @click="respondToInvitation(invite.id, 'rejected')" 
            class="reject-btn"
          >
            Decline
          </button>
        </div>

        <!-- Status Labels -->
        <div v-else-if="invite.status === 'rejected'" class="status-label rejected">
          <p>Rejected</p>
        </div>
        <div v-else-if="invite.status === 'accepted'" class="status-label accepted">
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
  name: "MyInvitations",
  data() {
    return {
      invitations: [],
      loading: true,
      error: null,
      currentPage: 1,
      invitationsPerPage: 5, // Adjust as needed
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.invitations.length / this.invitationsPerPage);
    },
    paginatedInvitations() {
      const startIndex = (this.currentPage - 1) * this.invitationsPerPage;
      const endIndex = startIndex + this.invitationsPerPage;
      return this.invitations.slice(startIndex, endIndex);
    },
  },
  methods: {
    async fetchInvitations() {
      try {
        // Fetch invitations from the backend
        const response = await axios.get("/api/my-invitations");
        this.invitations = response.data;
      } catch (error) {
        console.error("Failed to fetch invitations:", error);
        this.error = "Failed to load invitations.";
      } finally {
        this.loading = false;
      }
    },
    async respondToInvitation(inviteId, newStatus) {
      try {
        // Post the response to the server
        const response = await axios.post(`/api/invitations/${inviteId}/respond`, {
          status: newStatus,
        });

        // Updated invitation from server response
        const updatedInvite = response.data.invitation;

        // Update local list (replace old invitation with updated one)
        this.invitations = this.invitations.map((invite) =>
          invite.id === inviteId ? { ...invite, status: updatedInvite.status } : invite
        );
      } catch (error) {
        console.error("Error responding to invitation:", error);
        alert("Failed to respond to the invitation.");
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
  async created() {
    await this.fetchInvitations();
  },
};
</script>

<style scoped>
/* 
  CONTAINER FOR THE PAGE 
  - Consistent with ArtistViewCustomRequests.
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

/* NO INVITATIONS MESSAGE */
.no-invitations p {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-top: 20px;
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
  INVITATIONS LIST 
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
  cursor: default;
  transition: transform 0.3s, box-shadow 0.3s;
}

.invitation-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* INVITATION TITLE */
.invitation-item h3 {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: 500;
}

.invitation-item strong {
  color: #3C552D;
}

/* INVITATION PARAGRAPHS */
.invitation-item p {
  font-size: 1rem;
  color: #555;
  margin: 4px 0;
}

/* 
  ACTION BUTTONS 
  - Consistent with ArtistViewCustomRequests action-buttons.
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
  - Consistent styling with pill-shaped backgrounds and white text.
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
  - Consistent with ArtistViewCustomRequests.
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

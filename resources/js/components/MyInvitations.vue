<template>
  <div class="my-invitations">
    <!-- Header -->
    <h1>My Invitations</h1>

    <!-- No Invitations Message -->
    <div v-if="invitations.length === 0" class="no-invitations">
      <p>No pending invitations.</p>
    </div>

    <!-- Invitations List -->
    <div v-else class="invitations-list">
      <div 
        v-for="invite in invitations" 
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
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "MyInvitations",
  data() {
    return {
      invitations: [],
    };
  },
  async created() {
    try {
      // Fetch invitations from the backend
      const response = await axios.get("/api/my-invitations");
      this.invitations = response.data;
    } catch (error) {
      console.error("Failed to fetch invitations:", error);
    }
  },
  methods: {
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
      }
    },
  },
};
</script>

<style scoped>
/* 
  CONTAINER FOR THE PAGE 
  - Keeping it simple, no background color or heavy shadow. 
  - Just some top margin and a max-width for nice centering.
*/
.my-invitations {
  max-width: 700px;
  margin: 40px auto;
  font-family: 'Poppins', sans-serif;
  padding: 0 16px;
}

/* 
  PAGE HEADER 
  - A simple, centered title with a calm accent color.
*/
.my-invitations h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3C552D;
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

/* 
  INVITATIONS LIST 
  - We stack the invitation cards in a vertical list with small gaps. 
*/
.invitations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* 
  INVITATION ITEM 
  - A simple "card" with a subtle border on the left for a visual accent. 
  - Light box-shadow for depth. 
*/
.invitation-item {
  background-color: #fff;
  padding: 16px;
  border-left: 4px solid #3C552D;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* Invitation Title */
.invitation-item h3 {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: 500;
}
.invitation-item strong {
  color: #3C552D;
}

/* Invitation Paragraphs */
.invitation-item p {
  font-size: 1rem;
  color: #555;
}

/* 
  ACTION BUTTONS 
  - Buttons for Accept/Decline with color-coded backgrounds 
*/
.action-buttons {
  margin-top: 12px;
  display: flex;
  gap: 10px;
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
  - Displayed when the invitation is accepted or rejected. 
  - A small colored pill-like shape with text in white.
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
  RESPONSIVE 
  - Slightly reduce padding on smaller screens for a more comfortable fit. 
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
}
</style>

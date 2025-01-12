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
      <div v-for="invite in invitations" :key="invite.id" class="invitation-item">
        <!-- Project Invitation Title -->
        <h3 v-if="invite.project">
          You have been invited to collaborate on
          <strong>{{ invite.project.title }}</strong>
        </h3>
        <h3 v-else>
          You have an invitation without project data.
        </h3>

        <!-- Inviter Info -->
        <p>Invited by: <strong>{{ invite.inviter ? invite.inviter.name : 'N/A' }}</strong></p>

        <!-- Accept/Reject Buttons for Pending Invitations -->
        <div class="action-buttons" v-if="invite.status === 'pending'">
          <button @click="respondToInvitation(invite.id, 'accepted')" class="accept-btn">Accept</button>
          <button @click="respondToInvitation(invite.id, 'rejected')" class="reject-btn">Decline</button>
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

        // Get the updated invitation from the response
        const updatedInvite = response.data.invitation;

        // Update the local invitations list by replacing the old invitation with the updated one
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
/* MAIN WRAPPER */
.my-invitations {
  max-width: 800px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
}

/* HEADER STYLING */
.my-invitations h1 {
  text-align: center;
  font-size: 2rem;
  color: #3C552D;
  margin-bottom: 30px;
  font-weight: bold;
}

/* NO INVITATIONS MESSAGE */
.no-invitations p {
  text-align: center;
  font-size: 1.2rem;
  color: #888;
  margin-top: 20px;
}

/* INVITATIONS LIST */
.invitations-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* INVITATION ITEM */
.invitation-item {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* INVITATION TITLE */
.invitation-item h3 {
  font-size: 1.3rem;
  color: #333;
  margin-bottom: 10px;
}

.invitation-item p {
  font-size: 1rem;
  color: #555;
}

/* ACTION BUTTONS */
.action-buttons {
  margin-top: 15px;
  display: flex;
  gap: 10px;
}

.accept-btn,
.reject-btn {
  padding: 10px 20px;
  font-size: 1rem;
  font-weight: bold;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

/* ACCEPT BUTTON */
.accept-btn {
  background-color: #27ae60;
}

.accept-btn:hover {
  background-color: #218c53;
}

/* REJECT BUTTON */
.reject-btn {
  background-color: #e74c3c;
}

.reject-btn:hover {
  background-color: #c0392b;
}

/* STATUS LABELS */
.status-label {
  padding: 10px;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: bold;
  text-align: center;
}

.status-label.accepted {
  background-color: #27ae60;
  color: #ffffff;
}

.status-label.rejected {
  background-color: #e74c3c;
  color: #ffffff;
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .my-invitations {
    padding: 15px;
  }

  .my-invitations h1 {
    font-size: 1.8rem;
  }

  .action-buttons button {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
}
</style>

<template>
  <div class="my-sent-invitations">
    <!-- Header -->
    <h1>Invitations I Sent</h1>

    <!-- No Invitations Message -->
    <div v-if="invitations.length === 0" class="no-invitations">
      <p>You have not sent any invitations.</p>
    </div>

    <!-- Invitations List -->
    <div v-else class="invitations-list">
      <div
        v-for="invite in invitations"
        :key="invite.id"
        class="invitation-item"
      >
        <!-- Checkbox for selecting invitation -->
        <input
          type="checkbox"
          v-model="selectedInvites"
          :value="invite.id"
        />

        <!-- Invitation Details -->
        <div class="invitation-details">
          <h3>
            Invitation sent to:
            <strong>
              {{ invite.invitee ? invite.invitee.name : `User ID: ${invite.invitee_id}` }}
            </strong>
          </h3>
          <p v-if="invite.project">
            Project: <strong>{{ invite.project.title }}</strong>
          </p>
          <p>Status: {{ invite.status }}</p>
        </div>
      </div>

      <!-- Delete Selected Button -->
      <button
        v-if="selectedInvites.length"
        @click="deleteSelectedInvitations"
        class="delete-button"
      >
        Delete Selected
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'MySentInvitations',
  data() {
    return {
      invitations: [],
      selectedInvites: [] // Array to store IDs of selected invitations
    };
  },
  async created() {
    await this.fetchSentInvitations();
  },
  methods: {
    async fetchSentInvitations() {
      try {
        const response = await axios.get('/api/my-sent-invitations');
        this.invitations = response.data;
      } catch (error) {
        console.error('Failed to fetch sent invitations:', error);
      }
    },
    async deleteSelectedInvitations() {
      try {
        // Loop over selected invites and send DELETE requests
        for (const inviteId of this.selectedInvites) {
          await axios.delete(`/api/invitations/${inviteId}`);
        }
        // After deletion, clear selected invites and refresh list
        this.selectedInvites = [];
        await this.fetchSentInvitations();
      } catch (error) {
        console.error('Failed to delete selected invitations:', error);
      }
    }
  }
};
</script>

<style scoped>
/* MAIN WRAPPER */
.my-sent-invitations {
  max-width: 800px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* HEADER STYLING */
.my-sent-invitations h1 {
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
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.invitation-item input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #CA7373;
  cursor: pointer;
}

/* INVITATION DETAILS */
.invitation-details h3 {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 5px;
}

.invitation-details p {
  font-size: 1rem;
  color: #555;
  margin-bottom: 5px;
}

/* DELETE BUTTON */
.delete-button {
  padding: 10px 20px;
  font-size: 1rem;
  font-weight: bold;
  color: #ffffff;
  background-color: #CA7373;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  align-self: flex-start;
}

.delete-button:hover {
  background-color: #D7B26D;
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .my-sent-invitations {
    padding: 15px;
  }

  .my-sent-invitations h1 {
    font-size: 1.8rem;
  }

  .delete-button {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
}
</style>

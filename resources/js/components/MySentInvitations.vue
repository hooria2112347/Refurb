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
/* Container - mirroring MyInvitations styling */
.my-sent-invitations {
  max-width: 700px;
  margin: 40px auto;
  padding: 0 16px;
}

/* Header */
.my-sent-invitations h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3B1E54;
  margin-bottom: 30px;
  font-weight: 600;
}

/* No Invitations Message */
.no-invitations p {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-top: 20px;
}

/* Invitations List */
.invitations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Invitation Item */
.invitation-item {
  background-color: #fff;
  padding: 16px;
  border-left: 4px solid #3C552D;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  cursor: default;
  transition: transform 0.3s, box-shadow 0.3s;
  display: flex;
  align-items: flex-start;
  gap: 15px;
}

.invitation-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Checkbox */
.invitation-item input[type="checkbox"] {
  width: 20px;
  height: 20px;
  accent-color: #CA7373;
  cursor: pointer;
  margin-top: 3px; /* Slight alignment tweak */
}

/* Invitation Details */
.invitation-details h3 {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 4px;
  font-weight: 500;
}

.invitation-details strong {
  color: #3C552D;
}

.invitation-details p {
  font-size: 1rem;
  color: #555;
  margin: 4px 0;
}

/* Delete Button (mirroring a 'reject' style) */
.delete-button {
  padding: 8px 18px;
  font-size: 0.95rem;
  font-weight: 500;
  color: #3B1E54;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: opacity 0.3s ease;
  background-color: #D4BEE4;
  align-self: flex-start;
}

.delete-button:hover {
  opacity: 0.9;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .my-sent-invitations {
    margin: 20px auto;
    padding: 0 12px;
  }

  .my-sent-invitations h1 {
    font-size: 1.5rem;
    margin-bottom: 24px;
  }

  .invitation-item {
    padding: 12px;
  }

  .delete-button {
    padding: 6px 14px;
    font-size: 0.85rem;
  }
}
</style>

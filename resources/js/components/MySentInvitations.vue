<template>
    <div>
      <h1>Invitations I Sent</h1>
      
      <div v-if="invitations.length === 0">
        <p>You have not sent any invitations.</p>
      </div>
      <div v-else>
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
        
        <!-- Delete Selected Button -->
        <button 
          v-if="selectedInvites.length" 
          @click="deleteSelectedInvitations"
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
        selectedInvites: []  // Array to store IDs of selected invitations
      };
    },
    async created() {
      await this.fetchSentInvitations();
      // Removed auto-refresh and refresh button as per request
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
  .invitation-item {
    border: 1px solid #ddd;
    margin-bottom: 1rem;
    padding: 1rem;
    display: flex;
    align-items: center;
  }
  .invitation-item input[type="checkbox"] {
    margin-right: 1rem;
  }
  button {
    margin-top: 1rem;
  }
  </style>
  
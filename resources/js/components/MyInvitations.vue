<template>
    <div>
      <h1>My Invitations</h1>
      <div v-if="invitations.length === 0">
        <p>No pending invitations.</p>
      </div>
      <div v-else>
        <div 
  v-for="invite in invitations" 
  :key="invite.id" 
  class="invitation-item"
>
  <h3 v-if="invite.project">
    You have been invited to collaborate on 
    <strong>{{ invite.project.title }}</strong>
  </h3>
  <h3 v-else>
    You have an invitation without project data.
  </h3>

  <!-- Inviter Info -->
  <p>Invited by: {{ invite.inviter ? invite.inviter.name : 'N/A' }}</p>

  <!-- Accept/Reject Buttons if pending -->
  <div v-if="invite.status === 'pending'">
    <button @click="respondToInvitation(invite.id, 'accepted')">
      Accept
    </button>
    <button @click="respondToInvitation(invite.id, 'rejected')">
      Decline
    </button>
  </div>

  <!-- If status = rejected/accepted, show a label or do something else -->
  <div v-else-if="invite.status === 'rejected'">
    <p style="color: red;">Rejected</p>
  </div>
  <div v-else-if="invite.status === 'accepted'">
    <p style="color: green;">Accepted</p>
  </div>
</div>

    </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'MyInvitations',
    data() {
      return {
        invitations: []
      };
    },
    async created() {
      try {
        // If your back-end route is /api/my-invitations
        const response = await axios.get('/api/my-invitations');
        this.invitations = response.data;
      } catch (error) {
        console.error('Failed to fetch invitations:', error);
      }
    },
    methods: {
  async respondToInvitation(inviteId, newStatus) {
    try {
      // Post the response to the server
      const response = await axios.post(`/api/invitations/${inviteId}/respond`, {
        status: newStatus
      });

      // Get the updated invitation from the response
      const updatedInvite = response.data.invitation;

      // Update the local invitations list by replacing the old invitation with the updated one
      this.invitations = this.invitations.map(invite =>
        invite.id === inviteId ? { ...invite, status: updatedInvite.status } : invite
      );

    } catch (error) {
      console.error('Error responding to invitation:', error);
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
  }
  </style>
  
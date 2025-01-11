<template>
  <div v-if="project">
    <h1>{{ project.title }}</h1>
    <p>{{ project.description }}</p>
    <p>Status: {{ project.status }}</p>

    <h2>Collaborators</h2>
    <ul>
      <li v-for="collab in project.collaborators" :key="collab.id">
        {{ collab.user.name }} - {{ collab.role }}
      </li>
    </ul>

    <!-- If the project is active and the user is the owner, show "Invite" UI -->
    <div v-if="isOwner && project.status === 'active'">
      <h3>Invite an Artist</h3>

      <div>
        <label for="artistSelect">Select Artist: </label>
        <select id="artistSelect" v-model="selectedArtistId">
          <option value="">-- Choose an Artist --</option>
          <option 
            v-for="artist in availableArtists" 
            :key="artist.id" 
            :value="artist.id"
          >
            {{ artist.name }} (ID: {{ artist.id }})
          </option>
        </select>
      </div>

      <button @click="sendInvite" :disabled="!selectedArtistId">
        Invite
      </button>
    </div>

    <!-- If user is NOT the owner, show "Request to Join" -->
    <div v-else-if="!isCollaborator && project.status === 'active'">
      <button @click="requestJoin">Request to Join</button>
    </div>

    <!-- Mark as Completed -->
    <div v-if="isOwner && project.status === 'active'">
      <button @click="markAsCompleted">Mark as Completed</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "ProjectDetail",
  data() {
    return {
      project: null,
      currentUserId: null,

      // We'll fetch a list of available artists from the API
      availableArtists: [],
      selectedArtistId: ''
    };
  },
  computed: {
    isOwner() {
      return this.project && this.project.owner_id === this.currentUserId;
    },
    isCollaborator() {
      if (!this.project) return false;
      return this.project.collaborators.some(
        collab => collab.user_id === this.currentUserId
      );
    }
  },
  async created() {
    // Retrieve current user from localStorage
    const session = localStorage.getItem('userSession');
    if (session) {
      const userData = JSON.parse(session);
      this.currentUserId = userData.id;
    }

    // Fetch project details
    await this.fetchProject();

    // If the user is the owner, fetch available artists
    if (this.isOwner && this.project.status === 'active') {
      await this.fetchAvailableArtists();
    }
  },
  methods: {
    // 1) Get project details
    async fetchProject() {
      try {
        const res = await axios.get(`/api/projects/${this.$route.params.id}`);
        this.project = res.data;
      } catch (error) {
        console.error(error);
      }
    },

    // 2) Fetch artists who are not in this project yet
    async fetchAvailableArtists() {
      try {
        // Example route: /api/projects/:id/available-artists
        const res = await axios.get(
          `/api/projects/${this.$route.params.id}/available-artists`
        );
        this.availableArtists = res.data;
      } catch (error) {
        console.error(error);
      }
    },

    // 3) Invite the chosen artist
    async sendInvite() {
      try {
        if (!this.selectedArtistId) {
          alert('Please select an artist before inviting.');
          return;
        }
        await axios.post(`/api/projects/${this.project.id}/invite`, {
          invitee_id: this.selectedArtistId
        });
        alert('Invitation sent!');
        
        // Optionally remove the invited artist from the dropdown
        this.availableArtists = this.availableArtists.filter(
          a => a.id !== parseInt(this.selectedArtistId)
        );
        this.selectedArtistId = '';
      } catch (error) {
        console.error(error);
      }
    },

    // 4) Non-owner: Request to join the project
    async requestJoin() {
      try {
        await axios.post(`/api/projects/${this.project.id}/request-join`);
        alert('Request to join sent!');
      } catch (error) {
        console.error(error);
      }
    },

    // 5) Owner: Mark project as completed
    async markAsCompleted() {
      try {
        await axios.post(`/api/projects/${this.project.id}/complete`);
        this.project.status = 'completed';
        alert('Project marked as completed!');
      } catch (error) {
        console.error(error);
      }
    }
  }
};
</script>

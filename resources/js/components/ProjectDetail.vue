<template>
  <div class="project-detail-page" v-if="project">
    <div class="project-card">
      <!-- Title & Basic Info -->
      <h1 class="project-title">{{ project.title }}</h1>
      <p class="project-description">{{ project.description }}</p>
      <p class="project-status">
        Status: <strong>{{ project.status }}</strong>
      </p>

      <!-- Collaborators -->
      <div class="section">
        <h2>Collaborators</h2>
        <ul class="collaborators-list">
          <li v-for="collab in project.collaborators" :key="collab.id">
            {{ collab.user.name }}
            <span class="role">({{ collab.role }})</span>
          </li>
        </ul>
      </div>

      <!-- Invite an Artist (Only if owner, project active, and artists available) -->
      <div 
        class="section invite-section" 
        v-if="isOwner && project.status === 'active' && availableArtists.length > 0"
      >
        <h2>Invite an Artist</h2>
        <div class="form-group">
          <label for="artistSelect">Select Artist:</label>
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
        <button 
          class="primary-button" 
          @click="sendInvite"
          :disabled="!selectedArtistId"
        >
          Invite
        </button>
      </div>

      <!-- Mark as Completed (Only if owner & active) -->
      <div class="section" v-if="isOwner && project.status === 'active'">
        <button class="complete-button" @click="markAsCompleted">
          Mark as Completed
        </button>
      </div>
    </div>
  </div>

  <div v-else>
    <p>Loading project details...</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ProjectDetail",
  data() {
    return {
      project: null,
      currentUserId: null,
      availableArtists: [],
      selectedArtistId: "",
    };
  },
  computed: {
    isOwner() {
      return this.project && this.project.owner_id === this.currentUserId;
    },
  },
  async created() {
    // Load current user if available
    const session = localStorage.getItem("userSession");
    if (session) {
      const userData = JSON.parse(session);
      this.currentUserId = userData.id;
    }

    // Fetch project details
    await this.fetchProject();

    // If user is owner & project is active, fetch possible invitees
    if (this.isOwner && this.project.status === "active") {
      await this.fetchAvailableArtists();
    }
  },
  methods: {
    async fetchProject() {
      try {
        const res = await axios.get(`/api/projects/${this.$route.params.id}`);
        this.project = res.data;
      } catch (error) {
        console.error(error);
      }
    },
    async fetchAvailableArtists() {
      try {
        const res = await axios.get(
          `/api/projects/${this.$route.params.id}/available-artists`
        );
        this.availableArtists = res.data;
      } catch (error) {
        console.error(error);
      }
    },
    async sendInvite() {
      try {
        if (!this.selectedArtistId) {
          alert("Please select an artist first.");
          return;
        }
        await axios.post(`/api/projects/${this.project.id}/invite`, {
          invitee_id: this.selectedArtistId,
        });
        alert("Invitation sent!");

        // Remove invited artist from the list
        this.availableArtists = this.availableArtists.filter(
          (a) => a.id !== parseInt(this.selectedArtistId)
        );
        this.selectedArtistId = "";
      } catch (error) {
        console.error(error);
      }
    },
    async markAsCompleted() {
      try {
        await axios.post(`/api/projects/${this.project.id}/complete`);
        this.project.status = "completed";
        alert("Project marked as completed!");
      } catch (error) {
        console.error(error);
      }
    },
  },
};
</script>

<style scoped>
/* Container for entire detail page */
.project-detail-page {
  max-width: 700px;
  margin: 40px auto;
  font-family: "Poppins", sans-serif;
  padding: 0 16px;
}

/* Main card holding project info */
.project-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  padding: 24px;
}

/* Title, description, status */
.project-title {
  font-size: 1.8rem;
  color: #3c552d;
  margin-bottom: 8px;
  font-weight: 600;
}
.project-description {
  font-size: 1rem;
  color: #555;
  margin-bottom: 6px;
  line-height: 1.4;
}
.project-status {
  margin-bottom: 16px;
  color: #333;
}
.project-status strong {
  color: #3c552d;
}

/* Section grouping (Collaborators, Invite, etc.) */
.section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}
.section h2 {
  font-size: 1.2rem;
  color: #3c552d;
  margin-bottom: 10px;
  font-weight: 600;
}

/* Collaborators list */
.collaborators-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.collaborators-list li {
  background-color: #fafafa;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 8px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
  color: #333;
  font-size: 0.95rem;
}
.collaborators-list .role {
  font-style: italic;
  color: #777;
}

/* Form group styling */
.form-group {
  margin-bottom: 12px;
}
.form-group label {
  margin-right: 8px;
  font-weight: 500;
}
.form-group select {
  padding: 8px;
  font-size: 1rem;
  border-radius: 4px;
  border: 1px solid #ccc;
}

/* Buttons */
.primary-button,
.complete-button {
  display: inline-block;
  padding: 10px 16px;
  font-size: 1rem;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  color: #fff;
  background-color: #5d9b8b;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.primary-button:hover,
.complete-button:hover {
  background-color: #537f73;
}
.primary-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Responsive tweaks */
@media (max-width: 480px) {
  .project-title {
    font-size: 1.5rem;
  }
  .project-detail-page {
    margin: 20px auto;
    padding: 0 10px;
  }
}
</style>

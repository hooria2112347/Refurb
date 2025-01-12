<template>
  <div class="project-detail-page" v-if="project">
    <!-- Project Title and Description -->
    <h1>{{ project.title }}</h1>
    <p>{{ project.description }}</p>
    <p>Status: <strong>{{ project.status }}</strong></p>

    <!-- Collaborators Section -->
    <h2>Collaborators</h2>
    <ul class="collaborators-list">
      <li v-for="collab in project.collaborators" :key="collab.id">
        {{ collab.user.name }} - {{ collab.role }}
      </li>
    </ul>

    <!-- Invite an Artist Section (Only for Owners) -->
    <div class="invite-section" v-if="isOwner && project.status === 'active'">
      <h3>Invite an Artist</h3>
      <div>
        <label for="artistSelect">Select Artist:</label>
        <select id="artistSelect" v-model="selectedArtistId">
          <option value="">-- Choose an Artist --</option>
          <option v-for="artist in availableArtists" :key="artist.id" :value="artist.id">
            {{ artist.name }} (ID: {{ artist.id }})
          </option>
        </select>
      </div>
      <button @click="sendInvite" :disabled="!selectedArtistId">Invite</button>
    </div>

    <!-- Request to Join Section (For Non-Owners) -->
    <div v-else-if="!isCollaborator && project.status === 'active'">
      <button @click="requestJoin">Request to Join</button>
    </div>

    <!-- Mark as Completed Button (Only for Owners) -->
    <div v-if="isOwner && project.status === 'active'">
      <button @click="markAsCompleted">Mark as Completed</button>
    </div>
  </div>

  <!-- Loading Indicator -->
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
    isCollaborator() {
      if (!this.project) return false;
      return this.project.collaborators.some(
        (collab) => collab.user_id === this.currentUserId
      );
    },
  },
  async created() {
    const session = localStorage.getItem("userSession");
    if (session) {
      const userData = JSON.parse(session);
      this.currentUserId = userData.id;
    }

    await this.fetchProject();

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
          alert("Please select an artist before inviting.");
          return;
        }
        await axios.post(`/api/projects/${this.project.id}/invite`, {
          invitee_id: this.selectedArtistId,
        });
        alert("Invitation sent!");

        this.availableArtists = this.availableArtists.filter(
          (a) => a.id !== parseInt(this.selectedArtistId)
        );
        this.selectedArtistId = "";
      } catch (error) {
        console.error(error);
      }
    },
    async requestJoin() {
      try {
        await axios.post(`/api/projects/${this.project.id}/request-join`);
        alert("Request to join sent!");
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
/* MAIN WRAPPER */
.project-detail-page {
  max-width: 900px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  font-family: "Poppins", sans-serif;
}

/* HEADER STYLING */
.project-detail-page h1 {
  font-size: 2rem;
  color: #3c552d;
  margin-bottom: 20px;
  font-weight: bold;
}

.project-detail-page h2 {
  font-size: 1.5rem;
  color: #3c552d;
  margin-top: 30px;
  font-weight: bold;
}

/* PROJECT DESCRIPTION */
.project-detail-page p {
  font-size: 1rem;
  color: #555;
  margin-bottom: 10px;
}

/* INVITE ARTIST SECTION */
.invite-section {
  margin-top: 20px;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 8px;
}

.invite-section label {
  font-weight: bold;
  margin-right: 10px;
}

.invite-section select {
  padding: 10px;
  font-size: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  background-color: #ffffff;
}

/* BUTTONS */
.project-detail-page button {
  padding: 10px 20px;
  font-size: 1rem;
  font-weight: bold;
  color: #ffffff;
  background-color: #ca7373;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 10px;
}

.project-detail-page button:hover {
  background-color: #d7b26d;
}

.project-detail-page button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* COLLABORATORS LIST */
.collaborators-list {
  list-style: none;
  padding: 0;
}

.collaborators-list li {
  font-size: 1rem;
  color: #555;
  padding: 8px;
  background-color: #f9f9f9;
  border-radius: 8px;
  margin-bottom: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .project-detail-page {
    padding: 15px;
  }

  .project-detail-page h1 {
    font-size: 1.8rem;
  }

  .project-detail-page h2 {
    font-size: 1.4rem;
  }

  .project-detail-page button {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
}
</style>

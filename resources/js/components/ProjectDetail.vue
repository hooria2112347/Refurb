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
      <div class="section collaborators-section">
        <h2>Collaborators</h2>
        <ul class="collaborators-list">
          <li v-for="collab in project.collaborators" :key="collab.id">
            {{ collab.user.name }}
            <span class="role">({{ collab.role }})</span>
          </li>
        </ul>
      </div>

      <!-- Invite an Artist -->
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

      <!-- Mark as Completed -->
      <div class="section completion-section" v-if="isOwner && project.status === 'active'">
        <button class="complete-button" @click="markAsCompleted">
          Mark as Completed
        </button>
      </div>

      <!-- Feedback Section -->
      <div v-if="project.status === 'completed'" class="section feedback-section">
        <h2>Submit Feedback</h2>
        <div class="form-group">
          <label for="rating">Rating:</label>
          <select id="rating" v-model="feedback.rating">
            <option value="">-- Select Rating --</option>
            <option value="1">1 - Poor</option>
            <option value="2">2 - Fair</option>
            <option value="3">3 - Good</option>
            <option value="4">4 - Very Good</option>
            <option value="5">5 - Excellent</option>
          </select>
        </div>
        <div class="form-group">
          <label for="comment">Comment:</label>
          <textarea
            id="comment"
            v-model="feedback.comment"
            placeholder="Leave a comment..."
          ></textarea>
        </div>
        <button
          class="primary-button"
          @click="submitFeedback"
          :disabled="!feedback.rating || !feedback.comment"
        >
          Submit Feedback
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
      feedback: {
        rating: "",
        comment: ""
      },
      allFeedback: []
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

    // Fetch feedback if project is completed
    if (this.project.status === "completed") {
      await this.fetchFeedback();
    }
  },
  methods: {
    async fetchProject() {
      try {
        const res = await axios.get(`/api/projects/${this.$route.params.id}`);
        this.project = res.data;
      } catch (error) {
        console.error("Error fetching project details:", error);
        alert("Failed to load project details.");
      }
    },
    async fetchAvailableArtists() {
      try {
        const res = await axios.get(
          `/api/projects/${this.$route.params.id}/available-artists`
        );
        this.availableArtists = res.data;
      } catch (error) {
        console.error("Error fetching available artists:", error);
        alert("Failed to load available artists.");
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

        // Remove the invited artist from the available list
        this.availableArtists = this.availableArtists.filter(
          (a) => a.id !== parseInt(this.selectedArtistId)
        );
        this.selectedArtistId = "";
      } catch (error) {
        console.error("Error sending invite:", error);
        alert("Failed to send invitation.");
      }
    },
    async markAsCompleted() {
      if (!confirm("Are you sure you want to mark this project as completed?")) {
        return;
      }
      try {
        await axios.post(`/api/projects/${this.project.id}/complete`);
        this.project.status = "completed";
        alert("Project marked as completed!");

        // Optionally fetch feedback if needed
        await this.fetchFeedback();
      } catch (error) {
        console.error("Error marking project as completed:", error);
        alert("Failed to mark project as completed.");
      }
    },
    async submitFeedback() {
      if (!this.feedback.rating || !this.feedback.comment) {
        alert("Please provide both a rating and a comment.");
        return;
      }
      try {
        await axios.post(`/api/projects/${this.project.id}/feedback`, this.feedback);
        alert("Feedback submitted successfully!");

        // Reset feedback form
        this.feedback.rating = "";
        this.feedback.comment = "";

        // Optionally fetch all feedback again
        await this.fetchFeedback();
      } catch (error) {
        console.error("Error submitting feedback:", error);
        alert("Failed to submit feedback.");
      }
    },
    async fetchFeedback() {
      try {
        const res = await axios.get(`/api/projects/${this.project.id}/feedback`);
        this.allFeedback = res.data;
      } catch (error) {
        console.error("Error fetching feedback:", error);
        alert("Failed to load feedback.");
      }
    }
  },
};
</script>

<style scoped>
/* Container for entire detail page */
.project-detail-page {
  max-width: 700px;
  margin: 40px auto;
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
  color: #3B1E54;
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
  color: #3B1E54;
}

/* Section grouping (Collaborators, Invite, etc.) */
.section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}
.section h2 {
  font-size: 1.2rem;
  color: #3B1E54;
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
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
}
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 8px;
  font-size: 1rem;
  border-radius: 4px;
  border: 1px solid #ccc;
  box-sizing: border-box;
}
.form-group select:focus,
.form-group textarea:focus {
  border-color: #5d9b8b;
  outline: none;
  box-shadow: 0 0 5px rgba(93, 155, 139, 0.5);
}

/* Textarea specific styling */
.form-group textarea {
  resize: vertical;
  min-height: 100px;
  max-height: 300px;
  font-family: inherit;
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
  color: #3B1E54;
  background-color: #D4BEE4;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}
.primary-button:hover,
.complete-button:hover {
  background-color: #537f73;
  transform: translateY(-2px);
}
.primary-button:disabled,
.complete-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Specific button adjustments */
.complete-button {
  background-color: #ff6b6b;
}
.complete-button:hover {
  background-color: #e65c5c;
}

/* Feedback Section Specific Styling */
.section.feedback-section {
  background-color: #f9f9f9;
  padding: 16px;
  border-radius: 6px;
}
.section.feedback-section h2 {
  margin-bottom: 14px;
}
.section.feedback-section .form-group {
  margin-bottom: 16px;
}
.section.feedback-section .primary-button {
  width: 100%;
}

/* Invite Section Specific Styling */
.section.invite-section {
  background-color: #fdfdfd;
  padding: 16px;
  border-radius: 6px;
}
.section.invite-section h2 {
  margin-bottom: 14px;
}

/* Completion Section Specific Styling */
.section.completion-section {
  display: flex;
  justify-content: flex-end;
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

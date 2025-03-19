<template>
  <div class="project-detail-page" v-if="project">
    <div class="project-card">
      <!-- Header Section -->
      <div class="header-section">
        <h1 class="project-title">{{ project.title }}</h1>
        <span :class="['status-badge', project.status]">
          {{ capitalize(project.status) }}
        </span>
      </div>
      
      <!-- Project Description -->
      <p class="project-description">{{ project.description }}</p>

      <!-- Project Details -->
      <div class="details-section">
        <div class="detail-item">
          <strong>Required Roles:</strong> {{ project.required_roles }}
        </div>
        <div class="detail-item">
          <strong>Skills Required:</strong> {{ project.skills_required }}
        </div>
        <div class="detail-item">
          <strong>Deadline:</strong> {{ formattedDeadline }} 
          <span v-if="timeRemaining" class="countdown">({{ timeRemaining }})</span>
        </div>
        <div class="detail-item" v-if="project.budget">
          <strong>Budget:</strong> PKR {{ project.budget }}
        </div>
      </div>

      <!-- Collaborators -->
      <div class="section collaborators-section">
        <h2>Collaborators</h2>
        <ul class="collaborators-list">
          <li v-for="collab in project.collaborators" :key="collab.id">
            <span class="name">{{ collab.user.name }}</span>
            <!-- <span class="role">({{ collab.role }})</span> -->
          </li>
        </ul>
      </div>

      <!-- Invitation Response (for invited artists) -->
      <div v-if="userInvitation && userInvitation.status === 'pending' && project.status === 'active'" class="section invitation-response-section">
        <h2>Invitation to Collaborate</h2>
        <p>
          You have been invited by <strong>{{ userInvitation.inviter.name }}</strong> to collaborate on this project.
        </p>
        <div class="action-buttons">
          <button @click="respondToInvitation('accepted')" class="accept-btn">Accept</button>
          <button @click="respondToInvitation('rejected')" class="reject-btn">Decline</button>
        </div>
      </div>

      <!-- Invite an Artist (for project owners) -->
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
        <button class="complete-button" @click="openConfirmCompleteModal">
          Mark as Completed
        </button>
      </div>
    </div>
  </div>

  <div v-else class="loading">
    <p>Loading project details...</p>
  </div>

  <!-- ==================== MODALS ==================== -->

  <!-- 1) Confirm Completion Modal -->
  <div v-if="showConfirmCompleteModal" class="modal-overlay" @click.self="cancelCompletion">
    <div class="modal-content">
      <h2>Confirmation</h2>
      <p>Are you sure you want to mark this project as completed?</p>
      <div class="modal-buttons">
        <button class="ok-btn" @click="confirmMarkAsCompleted">Yes</button>
        <button class="cancel-btn" @click="cancelCompletion">No</button>
      </div>
    </div>
  </div>

  <!-- 2) Project Completed Success Modal -->
  <div v-if="showCompleteSuccessModal" class="modal-overlay" @click.self="closeCompleteSuccessModal">
    <div class="modal-content">
      <h2>Notification</h2>
      <p>Project marked as completed!</p>
      <div class="modal-buttons">
        <button class="ok-btn" @click="closeCompleteSuccessModal">OK</button>
      </div>
    </div>
  </div>

  <!-- 3) Invitation Sent Success Modal -->
  <div v-if="showInvitationSuccessModal" class="modal-overlay" @click.self="closeInvitationSuccessModal">
    <div class="modal-content">
      <h2>Notification</h2>
      <p>Invitation sent!</p>
      <div class="modal-buttons">
        <button class="ok-btn" @click="closeInvitationSuccessModal">OK</button>
      </div>
    </div>
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
      deadlineTimer: null,
      timeRemaining: "",
      userInvitation: null, // To track user's invitation status

      // ========= MODAL CONTROLS =========
      showConfirmCompleteModal: false,     // "Are you sure...?" for completion
      showCompleteSuccessModal: false,     // "Project marked as completed!"
      showInvitationSuccessModal: false,   // "Invitation sent!"
    };
  },
  computed: {
    isOwner() {
      return this.project && this.project.owner_id === this.currentUserId;
    },
    formattedDeadline() {
      if (!this.project.deadline) return "N/A";
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(this.project.deadline).toLocaleDateString(undefined, options);
    },
  },
  watch: {
    project(newProject) {
      if (newProject && newProject.deadline) {
        this.startCountdown(newProject.deadline);
      }
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

    // Check if the user has an invitation for this project
    await this.checkUserInvitation();

    // Start countdown if deadline exists
    if (this.project && this.project.deadline) {
      this.startCountdown(this.project.deadline);
    }
  },
  beforeDestroy() {
    if (this.deadlineTimer) {
      clearInterval(this.deadlineTimer);
    }
  },
  methods: {
    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },

    async fetchProject() {
      try {
        const res = await axios.get(`/api/projects/${this.$route.params.id}`);
        this.project = res.data;
      } catch (error) {
        console.error("Error fetching project details:", error);
        alert("Failed to load project details."); // Keep existing alert
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
        alert("Failed to load available artists."); // Keep existing alert
      }
    },

    // =========================================================
    // =============== INVITATION RELATED LOGIC ================
    // =========================================================
    async sendInvite() {
      try {
        if (!this.selectedArtistId) {
          alert("Please select an artist first."); // Keep existing alert
          return;
        }
        await axios.post(`/api/projects/${this.project.id}/invite`, {
          invitee_id: this.selectedArtistId,
        });
        // --- Original code: alert("Invitation sent!");
        //     We'll replace it with a success modal instead:
        // alert("Invitation sent!");

        // Show invitation success modal:
        this.showInvitationSuccessModal = true;

        // Remove the invited artist from the available list
        this.availableArtists = this.availableArtists.filter(
          (a) => a.id !== parseInt(this.selectedArtistId)
        );
        this.selectedArtistId = "";
      } catch (error) {
        console.error("Error sending invite:", error);
        alert("Failed to send invitation."); // Keep existing alert
      }
    },

    // close the "Invitation sent!" modal
    closeInvitationSuccessModal() {
      this.showInvitationSuccessModal = false;
    },

    // =========================================================
    // ============ MARK PROJECT AS COMPLETED LOGIC ============
    // =========================================================

    // The button calls this to open the "Are you sure?" modal
    openConfirmCompleteModal() {
      // --- Original code:
      // if (!confirm("Are you sure you want to mark this project as completed?")) {
      //   return;
      // }
      // Instead, show a modal:
      this.showConfirmCompleteModal = true;
    },

    // If user clicks NO, just close the modal
    cancelCompletion() {
      this.showConfirmCompleteModal = false;
    },

    // If user clicks YES in the modal, proceed
    async confirmMarkAsCompleted() {
      try {
        await axios.post(`/api/projects/${this.project.id}/complete`);
        this.project.status = "completed";

        // --- Original code: alert("Project marked as completed!");
        // We'll show a success modal instead:
        this.showCompleteSuccessModal = true;
      } catch (error) {
        console.error("Error marking project as completed:", error);
        alert("Failed to mark project as completed."); // Keep existing alert
      } finally {
        // Always close the confirm modal
        this.showConfirmCompleteModal = false;
      }
    },

    // Close the success modal after the project is completed
    closeCompleteSuccessModal() {
      this.showCompleteSuccessModal = false;
    },

    // =========================================================
    // ============ DEADLINE COUNTDOWN LOGIC ===================
    // =========================================================
    startCountdown(deadline) {
      const updateCountdown = () => {
        const now = new Date();
        const deadlineDate = new Date(deadline);
        const diff = deadlineDate - now;

        if (diff <= 0) {
          this.timeRemaining = "Deadline passed";
          clearInterval(this.deadlineTimer);
          return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
          (diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

        this.timeRemaining = `${days}d ${hours}h ${minutes}m`;
      };

      updateCountdown();
      this.deadlineTimer = setInterval(updateCountdown, 60000); // Update every minute
    },

    // =========================================================
    // =========== CHECK / RESPOND TO USER INVITATION ==========
    // =========================================================
    async checkUserInvitation() {
      try {
        const res = await axios.get(`/api/projects/${this.project.id}/invitations/${this.currentUserId}`);
        this.userInvitation = res.data;
      } catch (error) {
        console.error("Error fetching user invitation:", error);
        // If no invitation, we do nothing
      }
    },

    async respondToInvitation(newStatus) {
      try {
        await axios.post(`/api/invitations/${this.userInvitation.id}/respond`, {
          status: newStatus,
        });
        this.userInvitation.status = newStatus;
        alert(`Invitation ${newStatus}!`); // Keep existing alert
      } catch (error) {
        console.error("Error responding to invitation:", error);
        alert("Failed to respond to the invitation."); // Keep existing alert
      }
    },
  },
};
</script>

<style scoped>
/* CONTAINER FOR THE PAGE */
.project-detail-page {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* MAIN CARD */
.project-card {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 32px;
}

/* HEADER SECTION */
.header-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
}

.project-title {
  font-size: 2rem;
  color: #3B1E54;
  margin-bottom: 8px;
  font-weight: 700;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: capitalize;
  color: #fff;
}

.status-badge.active {
  background-color: #5d9b8b;
}

.status-badge.completed {
  background-color: #4CAF50;
}

/* PROJECT DESCRIPTION */
.project-description {
  font-size: 1rem;
  color: #555;
  margin: 16px 0;
  line-height: 1.6;
}

/* DETAILS SECTION */
.details-section {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.detail-item {
  flex: 1 1 45%;
  font-size: 0.95rem;
  color: #333;
}

.countdown {
  font-size: 0.9rem;
  color: #FF6B6B;
  margin-left: 8px;
}

/* COLLABORATORS SECTION */
.collaborators-section {
  margin-top: 24px;
}

.collaborators-section h2 {
  font-size: 1.5rem;
  color: #3B1E54;
  margin-bottom: 16px;
  font-weight: 600;
}

.collaborators-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.collaborators-list li {
  display: flex;
  align-items: center;
  background-color: #f9f9f9;
  padding: 8px 12px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  font-size: 0.95rem;
  color: #333;
}

.collaborators-list .name {
  margin-right: 4px;
}

.collaborators-list .role {
  font-style: italic;
  color: #777;
}

/* SECTION HEADINGS */
.section h2 {
  font-size: 1.4rem;
  color: #3B1E54;
  margin-bottom: 12px;
  font-weight: 600;
}

/* INVITE SECTION */
.invite-section {
  margin-top: 24px;
  padding: 16px;
  background-color: #fdfdfd;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.invite-section .form-group {
  margin-bottom: 16px;
}

.invite-section label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #3B1E54;
}

.invite-section select {
  width: 100%;
  padding: 10px;
  font-size: 0.95rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fafafa;
  transition: border-color 0.3s ease, background-color 0.3s ease;
}

.invite-section select:focus {
  border-color: #5d9b8b;
  outline: none;
  background-color: #fff;
}

/* STYLING THE "INVITE" BUTTON */
.primary-button {
  display: inline-block;
  padding: 10px 16px;
  font-size: 0.95rem;
  font-weight: 600;
  background-color: #8D6E97;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.primary-button:hover {
  background-color: #6F5880;
  transform: translateY(-2px);
}

/* COMPLETION SECTION */
.completion-section {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
}

.complete-button {
  padding: 10px 20px;
  font-size: 1rem;
  font-weight: 600;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.complete-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
}

/* INVITATION RESPONSE SECTION */
.invitation-response-section {
  margin-top: 24px;
  padding: 16px;
  background-color: #fefefe;
  border: 2px solid #FFEB3B;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.invitation-response-section h2 {
  margin-bottom: 12px;
  color: #FF6F00;
}

.invitation-response-section p {
  font-size: 1rem;
  color: #555;
  margin-bottom: 16px;
}

.action-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-start;
}

.accept-btn,
.reject-btn {
  padding: 8px 16px;
  font-size: 0.95rem;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: opacity 0.3s ease;
}

.accept-btn {
  background-color: #27ae60;
  color: #fff;
}

.accept-btn:hover {
  opacity: 0.9;
}

.reject-btn {
  background-color: #e74c3c;
  color: #fff;
}

.reject-btn:hover {
  opacity: 0.9;
}

/* LOADING STATE */
.loading {
  text-align: center;
  padding: 50px;
  font-size: 1.2rem;
  color: #555;
}

/* ========= MODAL STYLES ========= */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-content {
  background-color: #fff;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
  border-radius: 8px;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  text-align: left;
}

.modal-content h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #3B1E54;
}

.modal-buttons {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

/* Shared styling for modal buttons */
.ok-btn,
.cancel-btn {
  padding: 0.6rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  border: none;
  color: #3B1E54;
}

.ok-btn {
  background-color: #D4BEE4; /* or your preferred success color */
}

.ok-btn:hover {
  opacity: 0.9;
}

.cancel-btn {
  background-color: #D4BEE4;
}

.cancel-btn:hover {
  opacity: 0.9;
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .details-section {
    flex-direction: column;
  }

  .detail-item {
    flex: 1 1 100%;
  }

  .header-section {
    flex-direction: column;
    align-items: flex-start;
  }

  .status-badge {
    margin-top: 8px;
  }
}

@media (max-width: 480px) {
  .project-card {
    padding: 20px;
  }

  .project-title {
    font-size: 1.6rem;
  }

  .status-badge {
    font-size: 0.8rem;
    padding: 4px 10px;
  }

  .collaborators-list li {
    flex: 1 1 45%;
    justify-content: center;
  }

  .invite-section {
    padding: 12px;
  }

  .complete-button,
  .primary-button,
  .accept-btn,
  .reject-btn,
  .ok-btn,
  .cancel-btn {
    width: 100%;
    padding: 10px;
  }
}
</style>

<template>
  <div class="project-detail-page">
    <!-- Loading/Error States -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading project details...</p>
    </div>
    
    <div v-else-if="errorMessage" class="error-container">
      <p>{{ errorMessage }}</p>
      <button @click="fetchProject" class="retry-button">Retry</button>
    </div>

    <!-- Main Content -->
    <div v-else-if="project" class="project-card">
      <!-- Project Header -->
      <div class="header-section">
        <div class="title-status">
          <h1 class="project-title">{{ project.title }}</h1>
          <span :class="['status-badge', project.status]">{{ capitalize(project.status) }}</span>
        </div>
        <div class="owner-info">
          <span>Created by: <strong>{{ project.owner.name }}</strong></span>
        </div>
      </div>
      
      <p class="project-description">{{ project.description }}</p>

      <!-- Project Details -->
      <div class="details-section">
        <div class="detail-item" v-for="(detail, index) in projectDetails" :key="index">
          <strong>{{ detail.label }}:</strong> {{ detail.value }}
          <span v-if="detail.extra" :class="['countdown', isDeadlineNear ? 'urgent' : '']">
            ({{ detail.extra }})
          </span>
        </div>
      </div>

      <!-- Collaborators -->
      <div class="section collaborators-section">
        <h2>Collaborators</h2>
        <div v-if="project.collaborators && project.collaborators.length > 0">
          <ul class="collaborators-list">
            <li v-for="collab in project.collaborators" :key="collab.id" class="collaborator-card">
              <div class="collaborator-avatar">{{ collab.user.name.charAt(0).toUpperCase() }}</div>
              <div class="collaborator-info">
                <span class="name">{{ collab.user.name }}</span>
                <span class="role" v-if="collab.role">({{ collab.role }})</span>
              </div>
            </li>
          </ul>
        </div>
        <div v-else class="empty-state">
          <p>No collaborators yet</p>
        </div>
      </div>

      <!-- Invitation Response (for invited artists) -->
      <div v-if="userInvitation?.status === 'pending' && project.status === 'active'" class="section invitation-response-section">
        <h2>Invitation to Collaborate</h2>
        <p>You have been invited by <strong>{{ userInvitation.inviter.name }}</strong> to collaborate on this project.</p>
        <div class="action-buttons">
          <button @click="respondToInvitation('accepted')" class="accept-btn">Accept</button>
          <button @click="respondToInvitation('rejected')" class="reject-btn">Decline</button>
        </div>
      </div>

      <!-- Invite an Artist (for project owners) -->
      <div class="section invite-section" v-if="isOwner && project.status === 'active'">
        <h2>Team Management</h2>
        <button class="primary-button" @click="openInviteModal">
          <i class="fas fa-user-plus"></i> Invite Artists
        </button>
      </div>

      <!-- Project Actions -->
      <div class="section actions-section" v-if="project.status === 'active' && isOwner">
        <button class="complete-button" @click="openConfirmCompleteModal">Mark as Completed</button>
      </div>

      <!-- Feedback Section -->
      <div class="section feedback-section" v-if="project.status === 'completed'">
        <h2>Project Feedback</h2>
        
        <!-- Leave Feedback Form -->
        <div v-if="!userHasSubmittedFeedback && (isOwner || isCollaborator)" class="feedback-form">
          <h3>Leave Your Feedback</h3>
          <div class="rating-container">
            <label>Rating:</label>
            <div class="star-rating">
              <span v-for="star in 5" :key="star" 
                    :class="[star <= feedbackForm.rating ? 'star-filled' : 'star-empty']"
                    @click="feedbackForm.rating = star">★</span>
            </div>
          </div>
          <div class="form-group">
            <label for="feedbackComment">Comment:</label>
            <textarea id="feedbackComment" v-model="feedbackForm.comment" 
                     placeholder="Share your experience working on this project..."></textarea>
          </div>
          <button class="submit-feedback-btn" 
                 :disabled="feedbackForm.rating === 0 || feedbackSubmitting"
                 @click="submitFeedback">
            {{ feedbackSubmitting ? 'Submitting...' : 'Submit Feedback' }}
          </button>
        </div>
        
        <!-- Feedback List -->
        <div class="feedback-list">
          <h3 v-if="projectFeedback.length > 0">Project Reviews</h3>
          <div v-if="feedbackLoading" class="loading-spinner small"></div>
          <div v-else-if="projectFeedback.length === 0" class="empty-state">
            <p>No feedback yet</p>
          </div>
          <div v-else>
            <div v-for="feedback in projectFeedback" :key="feedback.id" class="feedback-item">
              <div class="feedback-header">
                <div class="feedback-rating">
                  <span v-for="star in 5" :key="star" 
                        :class="[star <= feedback.rating ? 'star-filled' : 'star-empty']">★</span>
                </div>
                <div class="feedback-author">{{ getFeedbackAuthorName(feedback.user_id) }}</div>
              </div>
              <div class="feedback-comment">{{ feedback.comment }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODALS -->
  <!-- Artist Invite Modal -->
  <app-modal v-if="showInviteModal" title="Invite Artists" @close="closeInviteModal">
    <template #content>
      <div class="search-container">
        <input 
          type="text" 
          v-model="artistSearchQuery" 
          placeholder="Search by name or ID..." 
          class="search-input"
          @input="searchArtists"
        />
      </div>
      
      <div v-if="inviteLoading" class="loading-spinner small"></div>
      
      <div v-else-if="filteredArtists.length === 0" class="empty-state">
        <p>No artists found.</p>
      </div>
      
      <div v-else class="artists-list">
        <div 
          v-for="artist in filteredArtists" 
          :key="artist.id" 
          class="artist-item"
          :class="{ 'selected': selectedArtistId === artist.id }"
          @click="selectArtist(artist.id)"
        >
          <div class="artist-avatar">{{ artist.name.charAt(0).toUpperCase() }}</div>
          <div class="artist-details">
            <div class="artist-name">{{ artist.name }}</div>
            <div class="artist-id">ID: {{ artist.id }}</div>
          </div>
          <div class="select-indicator">
            <i class="fas fa-check" v-if="selectedArtistId === artist.id"></i>
          </div>
        </div>
      </div>
    </template>
    <template #actions>
      <button 
        class="primary-button" 
        @click="sendInvite" 
        :disabled="!selectedArtistId || inviteLoading"
      >
        <span v-if="inviteLoading">Sending...</span>
        <span v-else>Send Invitation</span>
      </button>
      <button class="cancel-btn" @click="closeInviteModal">Cancel</button>
    </template>
  </app-modal>

  <app-modal v-if="showConfirmCompleteModal" title="Confirmation" @close="cancelCompletion">
    <template #content>
      <p>Are you sure you want to mark this project as completed?</p>
      <p class="modal-info">This will add the project to all collaborators' portfolios.</p>
    </template>
    <template #actions>
      <button class="ok-btn" @click="confirmMarkAsCompleted" :disabled="completeLoading">
        {{ completeLoading ? 'Processing...' : 'Yes, Complete Project' }}
      </button>
      <button class="cancel-btn" @click="cancelCompletion">Cancel</button>
    </template>
  </app-modal>

  <app-modal v-if="showCompleteSuccessModal" title="Success!" @close="closeCompleteSuccessModal">
    <template #content>
      <p>Project has been marked as completed!</p>
      <p>The project has been added to all collaborators' portfolios.</p>
    </template>
    <template #actions>
      <button class="ok-btn" @click="closeCompleteSuccessModal">OK</button>
    </template>
  </app-modal>

  <app-modal v-if="showInvitationSuccessModal" title="Success!" @close="closeInvitationSuccessModal">
    <template #content>
      <p>Invitation sent successfully!</p>
    </template>
    <template #actions>
      <button class="ok-btn" @click="closeInvitationSuccessModal">OK</button>
    </template>
  </app-modal>

  <app-modal v-if="showFeedbackSuccessModal" title="Thank you!" @close="closeFeedbackSuccessModal">
    <template #content>
      <p>Your feedback has been submitted successfully!</p>
    </template>
    <template #actions>
      <button class="ok-btn" @click="closeFeedbackSuccessModal">OK</button>
    </template>
  </app-modal>
</template>

<script>
import axios from "axios";

export default {
  name: "ProjectDetail",
  components: {
    AppModal: {
      props: ['title'],
      template: `
        <div class="modal-overlay" @click.self="$emit('close')">
          <div class="modal-content">
            <h2>{{ title }}</h2>
            <slot name="content"></slot>
            <div class="modal-buttons">
              <slot name="actions"></slot>
            </div>
          </div>
        </div>
      `
    }
  },
  data() {
    return {
      project: null,
      currentUserId: null,
      availableArtists: [],
      selectedArtistId: null,
      deadlineTimer: null,
      timeRemaining: "",
      userInvitation: null,
      projectFeedback: [],
      loading: true,
      inviteLoading: false,
      completeLoading: false,
      feedbackLoading: false,
      feedbackSubmitting: false,
      errorMessage: null,
      feedbackForm: { rating: 0, comment: "" },
      showConfirmCompleteModal: false,
      showCompleteSuccessModal: false,
      showInvitationSuccessModal: false,
      showFeedbackSuccessModal: false,
      showInviteModal: false,
      artistSearchQuery: "",
      filteredArtists: [],
      userHasSubmittedFeedback: false // New flag to track submission
    };
  },
  computed: {
    isOwner() {
      return this.project && this.project.owner_id === this.currentUserId;
    },
    isCollaborator() {
      if (!this.project?.collaborators) return false;
      return this.project.collaborators.some(c => c.user_id === this.currentUserId);
    },
    formattedDeadline() {
      if (!this.project?.deadline) return "N/A";
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(this.project.deadline).toLocaleDateString(undefined, options);
    },
    isDeadlineNear() {
      if (!this.project?.deadline) return false;
      const now = new Date();
      const deadline = new Date(this.project.deadline);
      const diffDays = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24));
      return diffDays <= 3 && diffDays > 0;
    },
    projectDetails() {
      if (!this.project) return [];
      return [
        { label: "Required Roles", value: this.project.required_roles },
        { label: "Skills Required", value: this.project.skills_required },
        { label: "Deadline", value: this.formattedDeadline, extra: this.timeRemaining },
        ...(this.project.budget ? [{ label: "Budget", value: `PKR ${this.formatCurrency(this.project.budget)}` }] : [])
      ];
    }
  },
  watch: {
    project(newProject) {
      if (newProject?.deadline) this.startCountdown(newProject.deadline);
      if (newProject?.status === "completed") this.fetchProjectFeedback();
    },
    projectFeedback: {
      handler(newFeedback) {
        // Update userHasSubmittedFeedback whenever projectFeedback changes
        if (this.currentUserId) {
          this.userHasSubmittedFeedback = newFeedback.some(f => f.user_id === this.currentUserId);
        }
      },
      immediate: true
    }
  },
  async created() {
    try {
      // Load current user if available
      const session = localStorage.getItem("userSession");
      if (session) this.currentUserId = JSON.parse(session).id;

      await this.fetchProject();

      // Conditional fetches based on project state
      if (this.project) {
        if (this.isOwner && this.project.status === "active") await this.fetchAvailableArtists();
        if (this.currentUserId) await this.checkUserInvitation();
        if (this.project.status === "completed") await this.fetchProjectFeedback();
      }
    } catch (err) {
      console.error("Error during initialization:", err);
      this.errorMessage = "Failed to initialize page. Please try again.";
    } finally {
      this.loading = false;
    }
  },
  beforeDestroy() {
    if (this.deadlineTimer) clearInterval(this.deadlineTimer);
  },
  methods: {
    capitalize(str) {
      if (!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1);
    },

    formatCurrency(amount) {
      return amount.toLocaleString('en-PK');
    },

    getFeedbackAuthorName(userId) {
      if (!this.project) return "Anonymous User";
      if (this.project.owner_id === userId) return `${this.project.owner.name} (Owner)`;
      const collaborator = this.project.collaborators.find(c => c.user_id === userId);
      return collaborator ? collaborator.user.name : "Anonymous User";
    },

    async fetchData(url, errorMsg, stateKey = null) {
      try {
        const res = await axios.get(url);
        if (stateKey) this[stateKey] = res.data;
        return res.data;
      } catch (error) {
        console.error(errorMsg, error);
        return null;
      }
    },

    async fetchProject() {
      try {
        this.loading = true;
        this.errorMessage = null;
        const data = await this.fetchData(
          `/api/projects/${this.$route.params.id}`, 
          "Error fetching project details:", 
          "project"
        );
        if (!data) this.errorMessage = "Failed to load project details. Please try again.";
      } finally {
        this.loading = false;
      }
    },

    async fetchAvailableArtists() {
      this.inviteLoading = true;
      try {
        const response = await axios.get(`/api/projects/${this.$route.params.id}/available-artists`);
        this.availableArtists = response.data;
        this.filteredArtists = [...this.availableArtists];
      } catch (error) {
        console.error("Error fetching available artists:", error);
      } finally {
        this.inviteLoading = false;
      }
    },

    async fetchProjectFeedback() {
      this.feedbackLoading = true;
      try {
        const feedback = await this.fetchData(
          `/api/projects/${this.project.id}/feedback`, 
          "Error fetching project feedback:", 
          "projectFeedback"
        );
        
        // Check if current user has already submitted feedback
        if (feedback && this.currentUserId) {
          this.userHasSubmittedFeedback = feedback.some(f => f.user_id === this.currentUserId);
        }
      } catch (error) {
        console.error("Error fetching project feedback:", error);
      } finally {
        this.feedbackLoading = false;
      }
    },

    // Invite Modal Methods
    openInviteModal() {
      this.showInviteModal = true;
      this.artistSearchQuery = "";
      this.selectedArtistId = null;
      this.fetchAvailableArtists().then(() => {
        this.filteredArtists = [...this.availableArtists];
      });
    },
    
    closeInviteModal() {
      this.showInviteModal = false;
    },
    
    selectArtist(artistId) {
      this.selectedArtistId = artistId;
    },
    
    searchArtists() {
      if (!this.artistSearchQuery.trim()) {
        this.filteredArtists = [...this.availableArtists];
        return;
      }
      
      const query = this.artistSearchQuery.toLowerCase();
      this.filteredArtists = this.availableArtists.filter(artist => 
        artist.name.toLowerCase().includes(query) || 
        artist.id.toString().includes(query)
      );
    },

    async sendInvite() {
      try {
        if (!this.selectedArtistId) {
          alert("Please select an artist first.");
          return;
        }
        
        this.inviteLoading = true;
        await axios.post(`/api/projects/${this.project.id}/invite`, {
          invitee_id: this.selectedArtistId,
        });

        this.showInvitationSuccessModal = true;
        this.closeInviteModal();
        
        // Update the available artists list
        this.availableArtists = this.availableArtists.filter(a => a.id !== this.selectedArtistId);
        this.selectedArtistId = null;
      } catch (error) {
        console.error("Error sending invite:", error);
        alert("Failed to send invitation.");
      } finally {
        this.inviteLoading = false;
      }
    },

    openConfirmCompleteModal() {
      this.showConfirmCompleteModal = true;
    },

    cancelCompletion() {
      this.showConfirmCompleteModal = false;
    },

    async confirmMarkAsCompleted() {
      try {
        this.completeLoading = true;
        await axios.post(`/api/projects/${this.project.id}/complete`);
        this.project.status = "completed";
        await this.fetchProjectFeedback();
        this.showCompleteSuccessModal = true;
      } catch (error) {
        console.error("Error marking project as completed:", error);
        alert("Failed to mark project as completed.");
      } finally {
        this.completeLoading = false;
        this.showConfirmCompleteModal = false;
      }
    },

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
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

        this.timeRemaining = `${days}d ${hours}h ${minutes}m`;
      };

      updateCountdown();
      this.deadlineTimer = setInterval(updateCountdown, 60000); // Update every minute
    },

    async checkUserInvitation() {
      await this.fetchData(
        `/api/projects/${this.project.id}/invitations/${this.currentUserId}`, 
        "Error fetching user invitation:", 
        "userInvitation"
      );
    },

    async respondToInvitation(newStatus) {
      try {
        await axios.post(`/api/invitations/${this.userInvitation.id}/respond`, {
          status: newStatus,
        });
        this.userInvitation.status = newStatus;
        
        if (newStatus === 'accepted') await this.fetchProject();
        alert(`Invitation ${newStatus}!`);
      } catch (error) {
        console.error("Error responding to invitation:", error);
        alert("Failed to respond to the invitation.");
      }
    },
    
    async submitFeedback() {
      try {
        if (this.feedbackForm.rating === 0) {
          alert("Please provide a rating.");
          return;
        }
        
        this.feedbackSubmitting = true;
        const response = await axios.post(`/api/projects/${this.project.id}/feedback`, {
          rating: this.feedbackForm.rating,
          comment: this.feedbackForm.comment
        });
        
        // Add the new feedback to the list immediately
        if (response.data && response.data.feedback) {
          this.projectFeedback.push(response.data.feedback);
        }
        
        // Mark user as having submitted feedback
        this.userHasSubmittedFeedback = true;
        
        // Reset form
        this.feedbackForm = { rating: 0, comment: "" };
        this.showFeedbackSuccessModal = true;
      } catch (error) {
        console.error("Error submitting feedback:", error);
        alert("Failed to submit feedback. Please try again.");
      } finally {
        this.feedbackSubmitting = false;
      }
    },
    
    // Modal close handlers
    closeInvitationSuccessModal() { this.showInvitationSuccessModal = false; },
    closeCompleteSuccessModal() { this.showCompleteSuccessModal = false; },
    closeFeedbackSuccessModal() { this.showFeedbackSuccessModal = false; }
  },
};
</script>

<style>
/* Main Layout & Container Styles */
.project-detail-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
  color: #333;
  line-height: 1.5;
}

/* Loading States */
.loading-container, .loading-spinner, .loading-spinner.small {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.loading-container { padding: 60px 0; }

.loading-spinner {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

.loading-spinner.small {
  width: 20px;
  height: 20px;
  border-width: 3px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Error State */
.error-container {
  text-align: center;
  background-color: #fff5f5;
  border: 1px solid #ffcccc;
  border-radius: 5px;
  padding: 30px;
  margin: 40px 0;
}

/* Common Button Styles */
.retry-button, .primary-button, .accept-btn, .reject-btn, .complete-button, 
.submit-feedback-btn, .ok-btn, .cancel-btn {
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.retry-button, .primary-button, .ok-btn { 
  background-color: #D4BEE4; 
  color: #3B1E54;
}

.accept-btn, .complete-button { 
  background-color: #D4BEE4; 
  color: #3B1E54;
}

.reject-btn, .cancel-btn { 
  background-color: #EEEEEE; 
  color: #3B1E54;
}

.primary-button:disabled, .submit-feedback-btn:disabled {
  background-color: #b3b3b3;
  cursor: not-allowed;
}

/* Project Card */
.project-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 25px;
  margin-bottom: 30px;
}

/* Header Section */
.header-section {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
}

.title-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.project-title {
  font-size: 24px;
  font-weight: bold;
  margin: 0;
  color: #2c3e50;
  flex: 1;
}

/* Status Badge Styles */
.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: bold;
  text-transform: uppercase;
}

.active { background-color: #e1f5fe; color: #0288d1; }
.completed { background-color: #e8f5e9; color: #388e3c; }
.cancelled { background-color: #ffebee; color: #d32f2f; }

/* Project Sections */
.project-description {
  font-size: 16px;
  margin-bottom: 25px;
  line-height: 1.6;
}

.details-section {
  background-color: #f9f9f9;
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 25px;
}

.detail-item { margin-bottom: 10px; }
.detail-item:last-child { margin-bottom: 0; }

.countdown {
  display: inline-block;
  margin-left: 10px;
  font-weight: normal;
  color: #666;
}

.countdown.urgent {
  color: #e74c3c;
  font-weight: bold;
}

/* Section Headers */
.section {
  margin-bottom: 30px;
}

.section h2 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #2c3e50;
  border-bottom: 2px solid #eee;
  padding-bottom: 8px;
}

/* Collaborators */
.collaborators-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.collaborator-card {
  display: flex;
  align-items: center;
  background-color: #f5f5f5;
  padding: 10px 15px;
  border-radius: 5px;
  min-width: 200px;
}

.collaborator-avatar {
  width: 40px;
  height: 40px;
  background-color: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: bold;
  font-size: 18px;
  margin-right: 15px;
}

/* Invitation/Feedback Sections */
.invitation-response-section {
  background-color: #fff8e1;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #ffe082;
}

.action-buttons {
  display: flex;
  gap: 15px;
  margin-top: 15px;
}

.invite-section, .feedback-form {
  background-color: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group select, .form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

/* Star Ratings */
.star-rating {
  display: inline-block;
  font-size: 24px;
  cursor: pointer;
  margin-left: 10px;
}

.star-filled { color: #f1c40f; }
.star-empty { color: #ddd; }

/* Feedback List */
.feedback-item {
  background-color: #f9f9f9;
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 15px;
  border-left: 3px solid #3498db;
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  align-items: center;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 5px;
  color: #666;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 25px;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-info {
  color: #666;
  font-style: italic;
  margin: 15px 0;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 25px;
}

/* Artist Selection Modal */
.search-container {
  margin-bottom: 20px;
}

.search-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  box-sizing: border-box;
}

.artists-list {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #eee;
  border-radius: 4px;
}

.artist-item {
  display: flex;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background-color 0.2s;
}

.artist-item:last-child {
  border-bottom: none;
}

.artist-item:hover {
  background-color: #f5f5f5;
}

.artist-item.selected {
  background-color: #e1f5fe;
}

.artist-avatar {
  width: 40px;
  height: 40px;
  background-color: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: bold;
  font-size: 18px;
  margin-right: 15px;
}

.artist-details {
  flex-grow: 1;
}

.artist-name {
  font-weight: bold;
  margin-bottom: 5px;
}

.artist-id {
  font-size: 12px;
  color: #666;
}

.select-indicator {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Feedback Styles */
.rating-container {
  margin-bottom: 15px;
  display: flex;
  align-items: center;
}

.feedback-rating {
  margin-right: 10px;
}

.feedback-author {
  font-weight: bold;
}

.feedback-comment {
  margin-top: 10px;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .project-title {
    margin-bottom: 10px;
  }
  
  .title-status {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .collaborators-list {
    flex-direction: column;
  }
  
  .collaborator-card {
    width: 100%;
    max-width: none;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .action-buttons button {
    width: 100%;
  }
  
  .modal-content {
    width: 95%;
    padding: 15px;
  }
  
  .modal-buttons {
    flex-direction: column;
  }
  
  .modal-buttons button {
    width: 100%;
    margin-top: 10px;
  }
}

/* Additional Utility Classes */
.owner-info {
  color: #666;
  margin-top: 5px;
}

.collaborator-info {
  display: flex;
  flex-direction: column;
}

.collaborator-info .name {
  font-weight: bold;
}

.collaborator-info .role {
  font-size: 12px;
  color: #666;
}

/* Button Hover Effects */
.retry-button:hover, .primary-button:hover, .ok-btn:hover {
  background-color: #EEEEEE;
}

.accept-btn:hover, .complete-button:hover {
  background-color: #EEEEEE;
}

.reject-btn:hover, .cancel-btn:hover {
  background-color: #D4BEE4;
}

/* Focus States for Accessibility */
button:focus, input:focus, textarea:focus {
  outline: 2px solid #3498db;
  outline-offset: 2px;
}

/* Make sure buttons have proper spacing */
.actions-section button {
  margin-right: 10px;
  margin-bottom: 10px;
}

/* Feedback success styling */
.submit-feedback-btn {
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 10px 20px;
  width: 100%;
  margin-top: 10px;
}

.submit-feedback-btn:hover {
  background-color: #EEEEEE;
}
</style>
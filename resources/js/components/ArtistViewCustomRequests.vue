<!-- src/components/ArtistViewCustomRequests.vue -->

<template>
    <div class="artist-view-custom-requests">
      <h1>All Custom Requests</h1>
  
      <!-- Loading Indicator -->
      <div v-if="loading" class="loading">Loading...</div>
  
      <!-- Error Message -->
      <div v-if="error" class="error-message">
        <p>{{ error }}</p>
      </div>
  
      <!-- Requests List -->
      <div v-else class="requests-list">
        <div v-for="request in requests" :key="request.id" class="request-card">
          <div class="request-header">
            <h3>{{ request.description }}</h3>
            <span :class="['status', request.status.toLowerCase()]">{{ request.status }}</span>
          </div>
          <div class="request-info">
            <p><strong>User:</strong> {{ request.user.name }} ({{ request.user.email }})</p>
            <p><strong>Budget:</strong> {{ request.budget ? `$${request.budget}` : 'N/A' }}</p>
            <p><strong>Deadline:</strong> {{ formatDate(request.deadline) }}</p>
            <p><strong>Submitted On:</strong> {{ formatDate(request.created_at) }}</p>
            <p v-if="request.materials"><strong>Materials:</strong> {{ request.materials }}</p>
            <p v-if="request.dimensions"><strong>Dimensions:</strong> {{ request.dimensions }}</p>
            <p v-if="request.style_preferences"><strong>Style Preferences:</strong> {{ request.style_preferences }}</p>
            <p v-if="request.artist_expertise"><strong>Artist Expertise:</strong> {{ request.artist_expertise }}</p>
          </div>
          <div class="request-actions">
            <button
              v-if="request.status === 'Pending'"
              @click="acceptRequest(request.id)"
              class="accept-btn"
            >
              Accept
            </button>
            <button
              v-if="request.status === 'Pending'"
              @click="declineRequest(request.id)"
              class="decline-btn"
            >
              Decline
            </button>
            <button @click="openCommentModal(request)" class="comment-btn">
              Comment
            </button>
          </div>
          <div v-if="request.comments && request.comments.length" class="comments-section">
            <h4>Comments:</h4>
            <div v-for="comment in request.comments" :key="comment.id" class="comment">
              <p><strong>{{ comment.artist.name }}:</strong> {{ comment.comment }}</p>
              <p class="comment-date">{{ formatDate(comment.created_at) }}</p>
            </div>
          </div>
          <div class="images-gallery" v-if="request.images && request.images.length">
            <h4>Images:</h4>
            <div class="images">
              <img
                v-for="image in request.images"
                :key="image.id"
                :src="image.url"
                alt="Custom Request Image"
                @click="openLightbox(image)"
                class="thumbnail"
              />
            </div>
          </div>
        </div>
      </div>
  
      <!-- Comment Modal -->
      <div v-if="isCommentModalOpen" class="modal-overlay" @click.self="closeCommentModal">
        <div class="comment-modal">
          <span class="close-btn" @click="closeCommentModal">&times;</span>
          <h2>Add Comment</h2>
          <form @submit.prevent="submitComment">
            <textarea v-model="newComment" placeholder="Enter your comment here..." required></textarea>
            <button type="submit" class="submit-button">Submit</button>
          </form>
        </div>
      </div>
  
      <!-- Lightbox Modal -->
      <div v-if="isLightboxOpen" class="lightbox-overlay" @click.self="closeLightbox">
        <div class="lightbox-content">
          <span class="close-btn" @click="closeLightbox">&times;</span>
          <img :src="currentImage.url" alt="Lightbox Image" class="lightbox-image" />
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    name: "ArtistViewCustomRequests",
    data() {
      return {
        requests: [],
        loading: true,
        error: null,
        isCommentModalOpen: false,
        selectedRequest: null,
        newComment: "",
        isLightboxOpen: false,
        currentImage: null,
        userName: "", // To display artist's name in comments
      };
    },
    methods: {
      async fetchRequests() {
        try {
          const token = localStorage.getItem("access_token");
          if (!token) {
            this.error = "You must be logged in to view custom requests.";
            this.loading = false;
            return;
          }
  
          const response = await axios.get("/api/custom-requests", {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
          this.requests = response.data.data;
        } catch (err) {
          console.error("Error fetching custom requests:", err);
          this.error = "Failed to load custom requests.";
        } finally {
          this.loading = false;
        }
      },
      formatDate(dateStr) {
        if (!dateStr) return "N/A";
        return new Date(dateStr).toLocaleDateString();
      },
      async acceptRequest(id) {
  if (!confirm("Are you sure you want to accept this request?")) return;
  try {
    const token = localStorage.getItem("access_token");
    const response = await axios.post(`/api/custom-requests/${id}/accept`, {}, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    // Assuming the response is successful, update the request status
    const request = this.requests.find(req => req.id === id);
    if (request) {
      request.status = "Accepted";
    }

    this.$toast.success("Request accepted successfully.");
  } catch (err) {
    console.error("Error accepting request:", err);
    // Safely handle the error message to prevent 'undefined' access
    const errorMessage = err.response?.data?.error || "Failed to accept request.";
    this.$toast.error(errorMessage);
  }
}
,
      async declineRequest(id) {
        if (!confirm("Are you sure you want to decline this request?")) return;
        try {
          const token = localStorage.getItem("acess_token");
          await axios.post(`/api/custom-requests/${id}/decline`, {}, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
          // Update the specific request's status without refetching all
          const request = this.requests.find(req => req.id === id);
          if (request) {
            request.status = "Declined";
          }
          this.$toast.success("Request declined successfully.");
        } catch (err) {
          console.error("Error declining request:", err);
          this.$toast.error("Failed to decline request.");
        }
      },
      openCommentModal(request) {
        this.selectedRequest = request;
        this.isCommentModalOpen = true;
      },
      closeCommentModal() {
        this.selectedRequest = null;
        this.newComment = "";
        this.isCommentModalOpen = false;
      },
      async submitComment() {
        if (!this.newComment.trim()) {
          this.$toast.error("Comment cannot be empty.");
          return;
        }
        try {
          const token = localStorage.getItem("access_token");
          const response = await axios.post(`/api/custom-requests/${this.selectedRequest.id}/comments`, {
            comment: this.newComment,
          }, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
          this.$toast.success("Comment added successfully.");
          this.closeCommentModal();
          // Add the new comment to the request's comments array
          const updatedRequest = this.requests.find(req => req.id === this.selectedRequest.id);
          if (updatedRequest) {
            updatedRequest.comments.push(response.data.data);
          }
        } catch (err) {
          console.error("Error adding comment:", err);
          this.$toast.error("Failed to add comment.");
        }
      },
      openLightbox(image) {
        this.currentImage = image;
        this.isLightboxOpen = true;
      },
      closeLightbox() {
        this.currentImage = null;
        this.isLightboxOpen = false;
      },
      getUserName() {
        const session = localStorage.getItem("userSession");
        if (session) {
          try {
            const userData = JSON.parse(session);
            this.userName = userData.name;
          } catch (e) {
            console.error("Error parsing session data:", e);
          }
        }
      },
    },
    mounted() {
      this.getUserName();
      this.fetchRequests();
    },
  };
  </script>
  
  <style scoped>
  .artist-view-custom-requests {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
  }
  
  .artist-view-custom-requests h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
  }
  
  /* Loading and Error Messages */
  .loading,
  .error-message {
    text-align: center;
    font-size: 1.2em;
    color: #e74c3c;
  }
  
  /* Requests List */
  .requests-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  /* Request Card */
  .request-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  .request-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .request-header h3 {
    margin: 0;
    font-size: 1.5em;
    color: #34495e;
  }
  
  .status {
    padding: 5px 10px;
    border-radius: 5px;
    color: #fff;
    font-weight: bold;
    text-transform: uppercase;
  }
  
  .status.pending {
    background-color: #f1c40f;
  }
  
  .status.accepted {
    background-color: #2ecc71;
  }
  
  .status.declined {
    background-color: #e74c3c;
  }
  
  .request-info p {
    margin: 5px 0;
    color: #7f8c8d;
  }
  
  .request-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
  }
  
  .accept-btn,
  .decline-btn,
  .comment-btn {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    color: #fff;
    transition: background-color 0.3s;
  }
  
  .accept-btn {
    background-color: #2ecc71;
  }
  
  .accept-btn:hover {
    background-color: #27ae60;
  }
  
  .decline-btn {
    background-color: #e74c3c;
  }
  
  .decline-btn:hover {
    background-color: #c0392b;
  }
  
  .comment-btn {
    background-color: #3498db;
  }
  
  .comment-btn:hover {
    background-color: #2980b9;
  }
  
  /* Comments Section */
  .comments-section {
    margin-top: 20px;
  }
  
  .comments-section h4 {
    margin-bottom: 10px;
    color: #34495e;
  }
  
  .comment {
    background-color: #ecf0f1;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 5px;
  }
  
  .comment-date {
    font-size: 0.8em;
    color: #95a5a6;
  }
  
  /* Images Gallery */
  .images-gallery {
    margin-top: 15px;
  }
  
  .images-gallery h4 {
    margin-bottom: 10px;
    color: #34495e;
  }
  
  .images {
    display: flex;
    gap: 10px;
  }
  
  .thumbnail {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    transition: transform 0.3s;
  }
  
  .thumbnail:hover {
    transform: scale(1.05);
  }
  
  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
  }
  
  .comment-modal,
  .lightbox-content {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    position: relative;
    max-width: 500px;
    width: 90%;
  }
  
  .comment-modal h2 {
    margin-top: 0;
    color: #34495e;
  }
  
  .comment-modal textarea {
    width: 100%;
    height: 100px;
    padding: 10px;
    border: 1px solid #bdc3c7;
    border-radius: 5px;
    resize: vertical;
    margin-bottom: 15px;
  }
  
  .submit-button {
    padding: 10px 20px;
    background-color: #2ecc71;
    border: none;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .submit-button:hover {
    background-color: #27ae60;
  }
  
  .close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 1.5em;
    cursor: pointer;
    color: #7f8c8d;
  }
  
  .lightbox-image {
    width: 100%;
    border-radius: 5px;
  }
  </style>
  
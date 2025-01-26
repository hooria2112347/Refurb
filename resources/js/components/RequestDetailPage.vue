<template>
  <div class="request-detail-page" v-if="request">
    <div class="container mt-5">
      <div class="row">
        <!-- Left Side: Image Section (Enhanced Carousel) -->
        <div class="col-md-6 mb-4">
          <div id="requestCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
              <button
                v-for="(image, index) in request.images"
                :key="image.id"
                type="button"
                :data-bs-target="'#requestCarousel'"
                :data-bs-slide-to="index"
                :class="{ active: index === 0 }"
                :aria-current="index === 0 ? 'true' : 'false'"
                :aria-label="`Slide ${index + 1}`"
              ></button>
            </div>
            <div class="carousel-inner">
              <div
                v-for="(image, index) in request.images"
                :key="image.id"
                class="carousel-item"
                :class="{ active: index === 0 }"
              >
                <img
                  :src="image.url"
                  class="d-block w-100 request-image img-fluid rounded"
                  alt="Request Image"
                />
              </div>
            </div>
            <button
              class="carousel-control-prev"
              type="button"
              data-bs-target="#requestCarousel"
              data-bs-slide="prev"
            >
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button
              class="carousel-control-next"
              type="button"
              data-bs-target="#requestCarousel"
              data-bs-slide="next"
            >
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>

        <!-- Right Side: Description, Request Details, and Comments Section -->
        <div class="col-md-6">
          <div class="request-info-section">
            <h1 class="request-title">{{ request.description }}</h1>

            <div class="request-info">
              <div class="info-item">
                <i class="fas fa-tools me-2 text-muted"></i>
                <strong>Materials:</strong> {{ request.materials || 'N/A' }}
              </div>
              <div class="info-item">
                <i class="fas fa-ruler-combined me-2 text-muted"></i>
                <strong>Dimensions:</strong> {{ request.dimensions || 'N/A' }}
              </div>
              <div class="info-item">
                <i class="fas fa-wallet me-2 text-muted"></i>
                <strong>Budget:</strong> {{ request.budget ? `PKR ${request.budget}` : 'N/A' }}
              </div>
              <div class="info-item">
                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                <strong>Deadline:</strong> {{ formatDate(request.deadline) }}
              </div>
              <div class="info-item">
                <i :class="statusIcon(request.status)" class="me-2"></i>
                <strong>Status:</strong> {{ request.status || 'Pending' }}
              </div>
              <div class="info-item">
                <i class="fas fa-clock me-2 text-muted"></i>
                <strong>Submitted On:</strong> {{ formatDate(request.created_at) }}
              </div>
              <!-- User Name with hyperlink to profile -->
              <div class="info-item">
                <i class="fas fa-user me-2 text-muted"></i>
                <strong>By:</strong>
                <router-link :to="'/user-profile/' + request.user.id" class="user-link">
                  {{ request.user.name }}
                </router-link>
              </div>

              <!-- Display artist information if the logged-in user is the request creator -->
              <div v-if="request.user.id === currentUser.id && request.artist" class="info-item">
                <i class="fas fa-user-check me-2 text-muted"></i>
                <strong>Accepted by:</strong>
                <router-link :to="'/user-profile/' + request.artist.id" class="user-link">
                  {{ request.artist.name }}
                </router-link>
              </div>
            </div>

            <!-- Comments Section -->
            <div class="comments-section">
              <h4 class="comments-title">Comments</h4>
              <div class="comments-list">
                <div v-for="comment in request.comments" :key="comment.id" class="comment-item d-flex">
                  <img
                    :src="comment.artist.avatar || defaultAvatar"
                    alt="User Avatar"
                    class="avatar me-3"
                  />
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                      <strong>{{ comment.artist.name }}</strong>
                      <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
                    </div>
                    <p class="mb-1">{{ comment.comment }}</p>
                    <!-- Delete button (only visible if the comment was made by the logged-in user) -->
                    <div v-if="comment.artist.id === currentUser.id" class="text-end">
                      <button @click="deleteComment(comment.id)" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash-alt"></i> Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Comment Form -->
              <div class="comment-form mt-4">
                <textarea
                  v-model="newComment"
                  placeholder="Add a comment..."
                  class="form-control mb-2"
                  rows="3"
                ></textarea>
                <button
                  @click="addComment(request.id)"
                  class="btn btn-primary"
                  :disabled="isSubmitting"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  Add Comment
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Success and Error Alerts -->
    <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
    </div>
    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
    </div>
  </div>
  <div v-else class="loading-container">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading request details...</span>
    </div>
    <p>Loading request details...</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      request: null, // Store the selected request
      newComment: "",
      currentUser: JSON.parse(localStorage.getItem("userSession")), // Get the current logged-in user
      isSubmitting: false, // To handle loading state for comment submission
      successMessage: "",
      errorMessage: "",
      defaultAvatar: "https://via.placeholder.com/50", // Default avatar image
    };
  },
  methods: {
    async fetchRequestDetail() {
      const requestId = this.$route.params.id; // Get the request ID from the route params
      try {
        const response = await axios.get(`/api/custom-requests/${requestId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.request = response.data; // Store the request details
      } catch (error) {
        console.error("Error fetching request details:", error);
        this.errorMessage = "Failed to load request details.";
      }
    },
    formatDate(dateStr) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateStr).toLocaleDateString(undefined, options);
    },
    statusIcon(status) {
      switch (status.toLowerCase()) {
        case 'completed':
          return 'fas fa-check-circle text-success';
        case 'in progress':
          return 'fas fa-spinner text-primary';
        case 'cancelled':
          return 'fas fa-times-circle text-danger';
        default:
          return 'fas fa-hourglass-half text-warning';
      }
    },
    async addComment(requestId) {
      if (!this.newComment.trim()) return;
      this.isSubmitting = true;
      try {
        await axios.post(
          `/api/custom-requests/${requestId}/comments`,
          { comment: this.newComment },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("access_token")}`,
            },
          }
        );
        this.newComment = ""; // Clear the comment input after submission
        this.successMessage = "Comment added successfully!";
        this.fetchRequestDetail(); // Refetch to show the new comment
      } catch (error) {
        console.error("Error adding comment:", error);
        this.errorMessage = "Failed to add comment.";
      } finally {
        this.isSubmitting = false;
      }
    },
    async deleteComment(commentId) {
      if (!confirm("Are you sure you want to delete this comment?")) return;
      try {
        await axios.delete(`/api/custom-requests/comments/${commentId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.successMessage = "Comment deleted successfully!";
        this.fetchRequestDetail(); // Refetch to show the updated comments
      } catch (error) {
        console.error("Error deleting comment:", error);
        this.errorMessage = "Failed to delete comment.";
      }
    },
  },
  mounted() {
    this.fetchRequestDetail(); // Fetch the request details when the component mounts
  },
};
</script>

<style scoped>
/* MAIN WRAPPER FOR REQUEST DETAIL PAGE */
.request-detail-page {
  max-width: 1200px;
  margin: 40px auto;
  padding: 30px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
}

/* HEADER STYLING */
.request-title {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 25px;
  font-weight: 700;
}

/* IMAGE CAROUSEL STYLING */
.carousel-inner .carousel-item img {
  max-width: 100%;
  height: 450px;
  object-fit: cover;
  border-radius: 15px;
  transition: transform 0.5s ease;
}

.carousel-inner .carousel-item img:hover {
  transform: scale(1.05);
}

/* REQUEST INFO SECTION */
.request-info-section {
  padding: 25px;
  background-color: #fdfdfd;
  border-radius: 15px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}

.request-info .info-item {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  font-size: 1.1rem;
  color: #34495e;
}

.request-info .info-item i {
  font-size: 1.2rem;
}

.user-link {
  color: #3498db;
  text-decoration: none;
}

.user-link:hover {
  text-decoration: underline;
}

/* COMMENTS SECTION */
.comments-section {
  margin-top: 40px;
}

.comments-title {
  font-size: 1.8rem;
  color: #2c3e50;
  margin-bottom: 20px;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 10px;
}

.comment-item {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 10px;
  transition: background-color 0.3s ease;
}

.comment-item:hover {
  background-color: #e9ecef;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

/* DELETE BUTTON */
.btn-outline-danger {
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-outline-danger:hover {
  background-color: #ffe6e6;
}

/* COMMENT FORM */
.comment-form textarea {
  resize: none;
  border-radius: 10px;
  border: 1px solid #ced4da;
}

.comment-form .btn {
  align-self: flex-end;
}

/* ALERTS */
.alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1050;
  max-width: 300px;
}

/* LOADING CONTAINER */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 80vh;
  color: #3498db;
}

.loading-container p {
  margin-top: 15px;
  font-size: 1.2rem;
}

/* RESPONSIVE DESIGN */
@media (max-width: 992px) {
  .carousel-inner .carousel-item img {
    height: 350px;
  }

  .request-title {
    font-size: 2rem;
  }

  .comments-list {
    max-height: 300px;
  }
}

@media (max-width: 768px) {
  .carousel-inner .carousel-item img {
    height: 250px;
  }

  .request-info-section {
    padding: 20px;
  }

  .comments-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 576px) {
  .carousel-inner .carousel-item img {
    height: 200px;
  }

  .request-title {
    font-size: 1.8rem;
  }

  .comments-title {
    font-size: 1.3rem;
  }
}
</style>

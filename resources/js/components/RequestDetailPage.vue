<template>
    <div class="request-detail-page" v-if="request">
      <div class="container mt-4">
        <div class="row">
          <!-- Left Side: Image Section (Carousel for multiple images, smaller image size) -->
          <div class="col-md-6 mb-4">
            <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
              <div class="carousel-inner">
                <!-- Loop through images and display them in the carousel -->
                <div v-for="(image, index) in request.images" :key="image.id" class="carousel-item" :class="{'active': index === 0}">
                  <img :src="image.url" class="d-block w-100 request-image img-fluid rounded" alt="Request Image" />
                </div>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          </div>
  
          <!-- Right Side: Description, Request Details, and Comments Section -->
          <div class="col-md-6">
            <div class="request-info-section">
              <h1>{{ request.description }}</h1>
  
              <div class="request-info">
                <div class="info-item">
                  <p><strong>Materials:</strong> {{ request.materials || 'N/A' }}</p>
                </div>
                <div class="info-item">
                  <p><strong>Dimensions:</strong> {{ request.dimensions || 'N/A' }}</p>
                </div>
                <div class="info-item">
                  <p><strong>Budget:</strong> {{ request.budget ? `$${request.budget}` : 'N/A' }}</p>
                </div>
                <div class="info-item">
                  <p><strong>Deadline:</strong> {{ request.deadline || 'N/A' }}</p>
                </div>
                <div class="info-item">
                  <p><strong>Status:</strong> {{ request.status || 'Pending' }}</p>
                </div>
                <div class="info-item">
                  <p><strong>Submitted On:</strong> {{ formatDate(request.created_at) }}</p>
                </div>
              </div>
  
              <!-- Comments Section -->
              <div class="comments-section">
                <h4>Comments:</h4>
                <div class="comments-list">
                  <div v-for="comment in request.comments" :key="comment.id" class="comment-item">
                    <p><strong>{{ comment.artist.name }}:</strong> {{ comment.comment }}</p>
                    <p class="comment-date">{{ formatDate(comment.created_at) }}</p>
  
                    <!-- Delete button (only visible if the comment was made by the logged-in user) -->
                    <div v-if="comment.artist.id === currentUser.id" class="delete-btn-container">
                      <button @click="deleteComment(comment.id)" class="delete-comment-btn btn btn-sm btn-danger">Delete</button>
                    </div>
                  </div>
                </div>
  
                <!-- Add Comment Form -->
                <div v-if="request.status !== 'Accepted'" class="comment-form">
                  <textarea v-model="newComment" placeholder="Add a comment..." class="comment-input form-control"></textarea>
                  <button @click="addComment(request.id)" class="comment-btn btn">Add Comment</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else>Loading request details...</div>
  </template>
  
  
  
  
<script>
import axios from "axios";

export default {
  data() {
    return {
      request: null, // Store the selected request
      newComment: "",
      currentUser: JSON.parse(localStorage.getItem("userSession")), // Get the current logged-in user
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
        alert("Failed to load request details.");
      }
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleDateString();
    },
    async addComment(requestId) {
      if (!this.newComment.trim()) return;
      try {
        await axios.post(`/api/custom-requests/${requestId}/comments`, {
          comment: this.newComment,
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.newComment = ""; // Clear the comment input after submission
        this.fetchRequestDetail(); // Refetch to show the new comment
      } catch (error) {
        console.error("Error adding comment:", error);
        alert("Failed to add comment.");
      }
    },
    async deleteComment(commentId) {
      try {
        await axios.delete(`/api/custom-requests/comments/${commentId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.fetchRequestDetail(); // Refetch to show the updated comments
      } catch (error) {
        console.error("Error deleting comment:", error);
        alert("Failed to delete comment.");
      }
    },
  },
  mounted() {
    this.fetchRequestDetail(); // Fetch the request details when the component mounts
  },
};
</script><style scoped>
.request-detail-page {
  max-width: 1200px;
  margin: 40px auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.request-info p {
  margin: 8px 0;
  font-size: 1rem;
  color: #555;
}

.request-info p strong {
  color: #333;
}

.carousel-inner .carousel-item img {
  max-width: 100%;
  height: auto;
  border-radius: 15px;
}

.request-info {
  padding: 20px;
  background-color: #f9f9f9;
}

.info-item {
  font-size: 1rem;
  color: #555;
}

.comment-form {
  display: flex;
  flex-direction: column;
}

.comment-input {
  width: 100%;
  padding: 12px;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 15px;
  margin-bottom: 15px;
}

.comment-btn {
  padding: 10px 20px;
  background-color: #CA7373;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.comment-btn:hover {
  background-color: #D7B26D;
}

.comments-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-height: 300px;
  overflow-y: auto;
}

.comment-item {
  background-color: #f9f9f9;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 10px;
}

.comment-date {
  font-size: 0.9rem;
  color: #888;
}

.delete-btn-container {
  position: relative;
}

.delete-comment-btn {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background-color: #e74c3c;
  color: white;
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.85rem;
}

.delete-comment-btn:hover {
  background-color: #c0392b;
}

/* Ensure the image is not too large */
.request-image {
  max-width: 100%;
  height: auto;
}
</style>

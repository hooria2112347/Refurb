<template>  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

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
                <p><strong>Budget:</strong> {{ request.budget ? `PKR ${request.budget}` : 'N/A' }}</p>
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
              <!-- User Name with hyperlink to profile -->
              <div class="info-item">
                <p class="description-by">
                  By <em>
                    <router-link :to="'/user-profile/' + request.user.id">{{ request.user.name }}</router-link>
                  </em>
                </p>
              </div>

              <!-- Display artist information if the logged-in user is the request creator -->
              <div v-if="request.user.id === currentUser.id && request.artist" class="info-item">
                <p>Accepted by 
                  <em>
                    <router-link :to="'/user-profile/' + request.artist.id">{{ request.artist.name }}</router-link>
                  </em>
                </p>
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
              <div v-if="true" class="comment-form">
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
        console.log(response.data); // Log the response to verify if artist info is present
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
</script>



<style scoped>
/* MAIN WRAPPER FOR REQUEST DETAIL PAGE */
.request-detail-page {
  max-width: 1200px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
}

/* HEADER STYLING */
.request-detail-page h1 {
  font-size: 2rem;
  color: #3C552D;
  margin-bottom: 20px;
  font-weight: bold;
}

/* IMAGE CAROUSEL STYLING */
.carousel-inner .carousel-item img {
  max-width: 100%;
  height: auto;
  border-radius: 15px;
  object-fit: cover;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* REQUEST INFO SECTION */
.request-info-section {
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.request-info p {
  margin: 8px 0;
  font-size: 1rem;
  color: #555;
}

.request-info p strong {
  color: #CA7373;
}

.info-item {
  font-size: 1rem;
  color: #3A3D40;
  margin-bottom: 10px;
}

/* COMMENTS SECTION */
.comments-section {
  margin-top: 30px;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.comments-section h4 {
  font-size: 1.5rem;
  color: #3C552D;
  margin-bottom: 15px;
}

/* COMMENTS LIST */
.comments-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
  max-height: 300px;
  overflow-y: auto;
  padding-right: 10px;
}

.comment-item {
  background-color: #fbfbfb;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.comment-item p {
  margin: 0;
  font-size: 1rem;
}

.comment-date {
  font-size: 0.85rem;
  color: #888;
  margin-top: 5px;
}

/* DELETE BUTTON */
.delete-btn-container {
  position: relative;
}

.delete-comment-btn {
  position: absolute;
  bottom: 5px;
  right: 5px;
  background-color: #ff4d4f;
  color: #fff;
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.3s ease;
}

.delete-comment-btn:hover {
  background-color: #c0392b;
}

/* COMMENT FORM */
.comment-form {
  display: flex;
  flex-direction: column;
  margin-top: 20px;
}

.comment-input {
  width: 100%;
  padding: 12px;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 10px;
  margin-bottom: 10px;
  resize: vertical;
}

.comment-btn {
  padding: 10px 20px;
  background-color: #CA7373;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.comment-btn:hover {
  background-color: #D7B26D;
}

/* LINKS STYLING */
a {
  color: #3498db;
  text-decoration: none;
  font-weight: bold;
}

a:hover {
  text-decoration: underline;
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .request-detail-page {
    padding: 15px;
  }

  .request-info-section {
    padding: 15px;
  }

  .comment-btn {
    font-size: 0.9rem;
    padding: 8px 16px;
  }
}

@media (max-width: 480px) {
  .carousel-inner .carousel-item img {
    height: 200px;
  }

  .comment-input {
    font-size: 0.9rem;
  }

  .comment-btn {
    font-size: 0.85rem;
  }
}
</style>

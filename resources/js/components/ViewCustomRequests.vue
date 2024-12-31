<template> 
  <div class="custom-requests-page">
    <h1>Your Custom Requests</h1>
    
    <!-- Loading Indicator -->
    <div v-if="loading" class="loading">Loading...</div>
    
    <!-- No Requests Message -->
    <div v-else-if="requests.length === 0" class="no-requests">
      <p>You have not made any custom requests yet.</p>
    </div>
    
    <!-- Requests List -->
    <div v-else class="requests-list">
      <div v-for="request in requests" :key="request.id" class="request-card">
        <div class="request-header">
          <h3 class="request-description">{{ request.description }}</h3>
          <button @click="openImageModal(request)" class="view-images-btn">
            View Images ({{ request.images.length }})
          </button>
        </div>
        <div class="request-info">
          <p><strong>Materials:</strong> {{ request.materials || 'N/A' }}</p>
          <p><strong>Dimensions:</strong> {{ request.dimensions || 'N/A' }}</p>
          <p><strong>Budget:</strong> {{ request.budget ? `$${request.budget}` : 'N/A' }}</p>
          <p><strong>Deadline:</strong> {{ request.deadline || 'N/A' }}</p>
          <p><strong>Status:</strong> {{ request.status || 'Pending' }}</p>
          <p><strong>Submitted On:</strong> {{ formatDate(request.created_at) }}</p>
        </div>
      </div>
    </div>
    
    <!-- Image Modal -->
    <div v-if="isImageModalOpen" class="modal-overlay" @click.self="closeImageModal">
      <div class="image-modal">
        <span class="close-btn" @click="closeImageModal">&times;</span>
        <h2>Uploaded Images</h2>
        <div v-if="selectedRequest.images.length > 0" class="images-gallery">
          <div v-for="(image, index) in selectedRequest.images" :key="index" class="image-item">
            <img :src="image.url" :alt="'Image ' + (index + 1)" />
          </div>
        </div>
        <div v-else class="no-images">
          <p>No images uploaded for this request.</p>
        </div>
      </div>
    </div>

    <!-- Lightbox Modal -->
    <div v-if="isLightboxOpen" class="lightbox-overlay" @click.self="closeLightbox">
      <div class="lightbox-content">
        <span class="close-btn" @click="closeLightbox">&times;</span>
        <img :src="currentImage.url" :alt="'Image ' + (currentImageIndex + 1)" class="lightbox-image" />
        <button v-if="currentImageIndex > 0" @click="prevImage" class="nav-btn prev-btn">‹</button>
        <button v-if="currentImageIndex < selectedRequest.images.length - 1" @click="nextImage" class="nav-btn next-btn">›</button>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";

export default {
  data() {
    return {
      requests: [], // Stores fetched requests
      loading: true, // Tracks loading state
      isImageModalOpen: false, // Controls image modal visibility
      selectedRequest: null, // Currently selected request for image viewing
      isLightboxOpen: false, // Controls lightbox visibility
      currentImageIndex: 0, // Index of the currently displayed image in lightbox
    };
  },
  methods: {
    async fetchRequests() {
      try {
        const response = await axios.get("/api/custom-requests", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        });
        // Ensure the API returns images as an array of objects with 'url' properties
        this.requests = response.data.data.map(request => ({
          ...request,
          images: request.images || [], // Ensure images is an array
        }));
      } catch (error) {
        console.error("Error fetching custom requests:", error);
        alert("Failed to fetch custom requests. Please try again later.");
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleDateString();
    },
    openImageModal(request) {
      this.selectedRequest = request;
      this.isImageModalOpen = true;
    },
    closeImageModal() {
      this.selectedRequest = null;
      this.isImageModalOpen = false;
      this.isLightboxOpen = false;
    },
    openLightbox(index) {
      this.currentImageIndex = index;
      this.isLightboxOpen = true;
    },
    closeLightbox() {
      this.isLightboxOpen = false;
    },
    prevImage() {
      if (this.currentImageIndex > 0) {
        this.currentImageIndex--;
      }
    },
    nextImage() {
      if (this.currentImageIndex < this.selectedRequest.images.length - 1) {
        this.currentImageIndex++;
      }
    },
  },
  computed: {
    currentImage() {
      return this.selectedRequest.images[this.currentImageIndex];
    },
  },
  mounted() {
    this.fetchRequests();
  },
};
</script>
<style scoped>
.custom-requests-page {
  max-width: 1000px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.custom-requests-page h1 {
  text-align: center;
  color: #333;
  margin-bottom: 30px;
  font-size: 2.5rem;
  font-weight: bold;
}

/* Loading Indicator */
.loading {
  text-align: center;
  font-size: 1.5em;
  color: #555;
}

/* No Requests Message */
.no-requests {
  text-align: center;
  color: #555;
  font-size: 1.2em;
}

/* Requests List */
.requests-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 25px;
}

/* Request Card */
.request-card {
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 20px;
  background-color: #fefefe;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s, box-shadow 0.3s;
}

.request-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.request-description {
  font-size: 1.5rem;
  font-weight: bold;
  color: #2c3e50;
  margin: 0;
}

.view-images-btn {
  background-color: #3498db;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  transition: background-color 0.3s, transform 0.2s;
}

.view-images-btn:hover {
  background-color: #2980b9;
  transform: translateY(-2px);
}

.request-info p {
  margin: 8px 0;
  font-size: 1rem;
  color: #555;
}

.request-info p strong {
  color: #333;
}

/* Image Modal */
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
  z-index: 1000;
}

.image-modal {
  background-color: #fff;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  padding: 30px;
  position: relative;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.image-modal h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #333;
  font-size: 2rem;
}

.close-btn {
  position: absolute;
  top: 20px;
  right: 25px;
  font-size: 2.5rem;
  color: #aaa;
  cursor: pointer;
  transition: color 0.3s;
}

.close-btn:hover {
  color: #555;
}

/* Images Gallery */
.images-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.image-item {
  border: 1px solid #ddd;
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
}

.image-item img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}

.image-item:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* No Images Message in Modal */
.no-images {
  text-align: center;
  color: #555;
  font-size: 1.2em;
}

/* Lightbox Overlay */
.lightbox-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1500;
}

/* Lightbox Content */
.lightbox-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.lightbox-image {
  width: 100%;
  height: auto;
  border-radius: 10px;
  display: block;
}

.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: rgba(255, 255, 255, 0.7);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  cursor: pointer;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s;
}

.nav-btn:hover {
  background-color: rgba(255, 255, 255, 1);
}

.prev-btn {
  left: -50px;
}

.next-btn {
  right: -50px;
}

/* Adjust navigation buttons for smaller screens */
@media (max-width: 768px) {
  .prev-btn {
    left: -30px;
  }

  .next-btn {
    right: -30px;
  }
}

@media (max-width: 480px) {
  .prev-btn,
  .next-btn {
    width: 30px;
    height: 30px;
    font-size: 1.2rem;
  }

  .prev-btn {
    left: -25px;
  }

  .next-btn {
    right: -25px;
  }
}
</style>

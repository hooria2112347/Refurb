<template>
  <div class="user-profile">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading profile...</p>
    </div>

    <!-- Profile Content -->
    <div v-else-if="user" class="profile-container">
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="profile-avatar">
          <div class="avatar-circle">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
        </div>
        
        <div class="profile-info">
          <h1 class="profile-name">{{ user.name }}</h1>
          <p class="profile-email">{{ user.email }}</p>
          <div class="profile-badges">
            <span class="role-badge" :class="user.role.toLowerCase()">
              {{ formatRole(user.role) }}
            </span>
            <span class="join-date">
              Member since {{ formatDate(user.created_at) }}
            </span>
          </div>
        </div>

        <!-- Rating Display -->
        <div class="rating-section">
          <div class="rating-display">
            <div class="stars">
              <span 
                v-for="star in 5" 
                :key="star"
                class="star"
                :class="{ active: star <= averageRating }"
              >
                ★
              </span>
            </div>
            <span class="rating-text">
              {{ averageRating.toFixed(1) }} ({{ totalRatings }} {{ totalRatings === 1 ? 'review' : 'reviews' }})
            </span>
          </div>
          
          <!-- Rate User Button (only if not current user) -->
          <button 
            v-if="canRateUser" 
            @click="showRatingModal = true"
            class="rate-button"
          >
            Rate User
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">{{ userProducts.length }}</div>
          <div class="stat-label">Products Listed</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ averageRating.toFixed(1) }}</div>
          <div class="stat-label">Average Rating</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ totalRatings }}</div>
          <div class="stat-label">Total Reviews</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ getMonthsSinceJoined() }}</div>
          <div class="stat-label">Months Active</div>
        </div>
      </div>

      <!-- User Products Section -->
      <div class="products-section">
        <h2 class="section-title">
          {{ user.name.split(' ')[0] }}'s Products 
          <span class="product-count">({{ userProducts.length }})</span>
        </h2>
        
        <div v-if="userProducts.length > 0" class="products-grid">
          <div 
            v-for="product in userProducts" 
            :key="product.id"
            class="product-card"
            @click="viewProduct(product.id)"
          >
            <div class="product-image">
              <img 
                v-if="product.images && product.images.length > 0" 
                :src="product.images[0]" 
                :alt="product.name"
                @error="handleImageError"
              >
              <div v-else class="no-image">
                <span>📷</span>
              </div>
            </div>
            
            <div class="product-details">
              <h3 class="product-name">{{ product.name }}</h3>
              <p class="product-description">{{ truncateText(product.description, 80) }}</p>
              <div class="product-footer">
                <span class="product-price">PKR {{ product.price }}</span>
                <span class="product-category">{{ product.category }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="no-products">
          <div class="no-products-icon">📦</div>
          <h3>No Products Yet</h3>
          <p>{{ user.name }} hasn't listed any products yet.</p>
        </div>
      </div>

      <!-- User Reviews Section -->
      <div class="reviews-section">
        <h2 class="section-title">User Reviews</h2>
        
        <div v-if="userRatings.length > 0" class="reviews-list">
          <div 
            v-for="rating in userRatings" 
            :key="rating.id"
            class="review-card"
          >
            <div class="review-header">
              <div class="reviewer-info">
                <div class="reviewer-avatar">
                  {{ rating.reviewer_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="reviewer-name">{{ rating.reviewer_name }}</div>
                  <div class="review-date">{{ formatDate(rating.created_at) }}</div>
                </div>
              </div>
              <div class="review-rating">
                <span 
                  v-for="star in 5" 
                  :key="star"
                  class="star small"
                  :class="{ active: star <= rating.rating }"
                >
                  ★
                </span>
              </div>
            </div>
            <p class="review-comment">{{ rating.comment }}</p>
          </div>
        </div>
        
        <div v-else class="no-reviews">
          <p>No reviews yet.</p>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="error-container">
      <div class="error-icon">⚠️</div>
      <h2>Profile Not Found</h2>
      <p>The user profile you're looking for doesn't exist or couldn't be loaded.</p>
      <button @click="$router.go(-1)" class="back-button">Go Back</button>
    </div>

    <!-- Rating Modal -->
    <div v-if="showRatingModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Rate {{ user.name }}</h3>
          <button @click="closeModal" class="close-button">×</button>
        </div>
        
        <div class="modal-body">
          <div class="rating-input">
            <p>How would you rate this user?</p>
            <div class="stars-input">
              <span 
                v-for="star in 5" 
                :key="star"
                class="star interactive"
                :class="{ active: star <= newRating.rating }"
                @click="newRating.rating = star"
                @mouseover="hoverRating = star"
                @mouseleave="hoverRating = 0"
              >
                ★
              </span>
            </div>
          </div>
          
          <div class="comment-input">
            <label for="comment">Your Review (Optional)</label>
            <textarea 
              id="comment"
              v-model="newRating.comment"
              placeholder="Share your experience with this user..."
              rows="4"
            ></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="closeModal" class="cancel-button">Cancel</button>
          <button 
            @click="submitRating" 
            :disabled="newRating.rating === 0 || submitting"
            class="submit-button"
          >
            {{ submitting ? 'Submitting...' : 'Submit Rating' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
// Updated methods section for your Vue component

export default {
  data() {
    return {
      user: null,
      userProducts: [],
      userRatings: [],
      loading: true,
      showRatingModal: false,
      submitting: false,
      hoverRating: 0,
      newRating: {
        rating: 0,
        comment: ''
      }
    };
  },
  
  computed: {
    averageRating() {
      return this.user?.average_rating || 0;
    },
    
    totalRatings() {
      return this.user?.rating_count || 0;
    },
    
    canRateUser() {
      // Only allow rating if user is logged in and it's not their own profile
      const currentUser = this.getCurrentUser();
      return currentUser && currentUser.id && currentUser.id !== this.user?.id;
    }
  },
  
  mounted() {
    this.fetchUserProfile();
    this.fetchUserProducts();
    this.fetchUserRatings();
  },
  
  methods: {
    // Helper methods for authentication
    getAuthHeaders() {
      const token = localStorage.getItem('access_token') || 
                    this.$store?.state?.token || 
                    sessionStorage.getItem('access_token');
      
      return token ? {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      } : {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      };
    },

    getCurrentUser() {
      // Try to get user from Vuex store first
      if (this.$store?.state?.user) {
        return this.$store.state.user;
      }
      
      // Fallback to localStorage
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          return JSON.parse(userStr);
        } catch (e) {
          console.error('Error parsing user from localStorage:', e);
          return null;
        }
      }
      
      return null;
    },

    // API methods
    async fetchUserProfile() {
      const userId = this.$route.params.id;
      try {
        const response = await axios.get(`/api/users/${userId}`, {
          headers: this.getAuthHeaders()
        });
        this.user = response.data;
        console.log('User profile loaded:', this.user); // Debug log
      } catch (error) {
        console.error('Error fetching user profile:', error);
        this.user = null;
      } finally {
        this.loading = false;
      }
    },

    async fetchUserProducts() {
      const userId = this.$route.params.id;
      try {
        const response = await axios.get(`/api/users/${userId}/products`, {
          headers: this.getAuthHeaders()
        });
        this.userProducts = response.data;
      } catch (error) {
        console.error('Error fetching user products:', error);
        this.userProducts = [];
      }
    },

    async fetchUserRatings() {
      const userId = this.$route.params.id;
      try {
        const response = await axios.get(`/api/users/${userId}/ratings`, {
          headers: this.getAuthHeaders()
        });
        
        // Your AuthController returns an object with ratings array
        this.userRatings = response.data.ratings || [];
        
        // Update user rating stats if returned
        if (this.user) {
          this.user.rating_count = response.data.total_ratings;
          this.user.average_rating = response.data.average_rating;
        }
      } catch (error) {
        console.error('Error fetching user ratings:', error);
        this.userRatings = [];
      }
    },

    async submitRating() {
      console.log('Submit rating called'); // Debug log
      
      if (this.newRating.rating === 0) {
        alert('Please select a rating');
        return;
      }
      
      const currentUser = this.getCurrentUser();
      console.log('Current user:', currentUser); // Debug log
      
      if (!currentUser || !currentUser.id) {
        alert('Please log in to rate users');
        return;
      }
      
      this.submitting = true;
      const userId = this.$route.params.id;
      
      console.log('Submitting rating:', {
        userId,
        rating: this.newRating.rating,
        comment: this.newRating.comment
      }); // Debug log
      
      try {
        const response = await axios.post(`/api/users/${userId}/rate`, {
          rating: this.newRating.rating,
          comment: this.newRating.comment || ''
        }, {
          headers: this.getAuthHeaders()
        });
        
        console.log('Rating response:', response.data); // Debug log
        
        // Update user data with new rating info
        if (response.data.new_average !== undefined) {
          this.user.average_rating = response.data.new_average;
          this.user.rating_count = response.data.total_ratings;
        }
        
        // Refresh ratings and profile
        await this.fetchUserRatings();
        await this.fetchUserProfile();
        
        this.closeModal();
        
        // Show success message
        if (this.$toast) {
          this.$toast.success('Rating submitted successfully!');
        } else {
          alert('Rating submitted successfully!');
        }
        
      } catch (error) {
        console.error('Error submitting rating:', error);
        
        let errorMessage = 'Failed to submit rating. Please try again.';
        
        if (error.response) {
          console.log('Error response:', error.response.data); // Debug log
          
          if (error.response.status === 401) {
            errorMessage = 'Please log in to rate users.';
          } else if (error.response.status === 403) {
            errorMessage = 'You cannot rate yourself.';
          } else if (error.response.data?.message) {
            errorMessage = error.response.data.message;
          }
        }
        
        alert(errorMessage);
      } finally {
        this.submitting = false;
      }
    },
    
    closeModal() {
      this.showRatingModal = false;
      this.newRating = { rating: 0, comment: '' };
      this.hoverRating = 0;
    },
    
    viewProduct(productId) {
      this.$router.push(`/products/${productId}`);
    },
    
    formatRole(role) {
      const roleMap = {
        artist: 'Artist',
        scrapSeller: 'Scrap Seller',
        general: 'General User'
      };
      return roleMap[role] || role;
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Unknown';
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    truncateText(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },
    
    getMonthsSinceJoined() {
      if (!this.user?.created_at) return 0;
      const joinDate = new Date(this.user.created_at);
      const now = new Date();
      const diffTime = Math.abs(now - joinDate);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30));
    },
    
    handleImageError(event) {
      event.target.style.display = 'none';
      event.target.parentElement.innerHTML = '<div class="no-image"><span>📷</span></div>';
    }
  
,

getAuthHeaders() {
  const token = localStorage.getItem('access_token') || 
                this.$store?.state?.token || 
                sessionStorage.getItem('access_token');
  
  return token ? {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  } : {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  };
},

getCurrentUser() {
  // Try to get user from Vuex store first
  if (this.$store?.state?.user) {
    return this.$store.state.user;
  }
  
  // Fallback to localStorage
  const userStr = localStorage.getItem('user');
  if (userStr) {
    try {
      return JSON.parse(userStr);
    } catch (e) {
      console.error('Error parsing user from localStorage:', e);
      return null;
    }
  }
  
  return null;
}
  }
};
</script>

<style scoped>
.user-profile {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Loading States */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  color: #666;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Profile Header */
.profile-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 40px;
  color: white;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  gap: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.profile-avatar .avatar-circle {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: bold;
  border: 4px solid rgba(255, 255, 255, 0.3);
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-size: 2.5rem;
  margin: 0 0 8px 0;
  font-weight: 700;
}

.profile-email {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0 0 16px 0;
}

.profile-badges {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.role-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.role-badge.artist {
  background: #fbbf24;
  color: #92400e;
}

.role-badge.scrapseller {
  background: #34d399;
  color: #065f46;
}

.role-badge.general {
  background: #a78bfa;
  color: #4c1d95;
}

.join-date {
  font-size: 0.875rem;
  opacity: 0.8;
}

/* Rating Section */
.rating-section {
  text-align: center;
}

.rating-display {
  margin-bottom: 16px;
}

.stars {
  font-size: 2rem;
  margin-bottom: 8px;
}

.star {
  color: rgba(255, 255, 255, 0.3);
  transition: color 0.2s;
}

.star.active {
  color: #fbbf24;
}

.star.small {
  font-size: 1rem;
}

.star.interactive {
  cursor: pointer;
  font-size: 2.5rem;
}

.star.interactive:hover {
  color: #fbbf24;
}

.rating-text {
  font-size: 1rem;
  opacity: 0.9;
}

.rate-button {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  padding: 10px 24px;
  border-radius: 25px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.rate-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  padding: 30px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.stat-label {
  color: #64748b;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.875rem;
}

/* Section Titles */
.section-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-count {
  background: #e2e8f0;
  color: #475569;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
}

/* Products Grid */
.products-section {
  margin-bottom: 50px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.product-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  cursor: pointer;
  border: 1px solid #f1f5f9;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.product-image {
  height: 200px;
  overflow: hidden;
  position: relative;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.no-image {
  width: 100%;
  height: 100%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #cbd5e1;
}

.product-details {
  padding: 20px;
}

.product-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 8px 0;
}

.product-description {
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 16px 0;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #059669;
}

.product-category {
  background: #e0e7ff;
  color: #3730a3;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* No Products State */
.no-products {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.no-products-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.no-products h3 {
  font-size: 1.5rem;
  margin: 0 0 8px 0;
  color: #374151;
}

/* Reviews Section */
.reviews-section {
  margin-bottom: 40px;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.review-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.reviewer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #475569;
}

.reviewer-name {
  font-weight: 600;
  color: #1e293b;
}

.review-date {
  font-size: 0.875rem;
  color: #64748b;
}

.review-comment {
  color: #374151;
  line-height: 1.6;
  margin: 0;
}

.no-reviews {
  text-align: center;
  padding: 40px;
  color: #64748b;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 24px 24px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
  color: #1e293b;
}

.close-button {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #64748b;
  padding: 0;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-button:hover {
  background: #f1f5f9;
}

.modal-body {
  padding: 24px;
}

.rating-input {
  margin-bottom: 24px;
  text-align: center;
}

.rating-input p {
  margin: 0 0 16px 0;
  color: #374151;
  font-weight: 500;
}

.stars-input {
  display: flex;
  justify-content: center;
  gap: 4px;
}

.comment-input label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.comment-input textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.2s;
}

.comment-input textarea:focus {
  outline: none;
  border-color: #3b82f6;
}

.modal-footer {
  padding: 0 24px 24px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.cancel-button, .submit-button {
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-button {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.cancel-button:hover {
  background: #e2e8f0;
}

.submit-button {
  background: #3b82f6;
  color: white;
  border: 1px solid #3b82f6;
}

.submit-button:hover:not(:disabled) {
  background: #2563eb;
}

.submit-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Error States */
.error-container {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.error-container h2 {
  color: #374151;
  margin: 0 0 8px 0;
}

.back-button {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  margin-top: 20px;
  transition: background 0.2s;
}

.back-button:hover {
  background: #2563eb;
}

/* Responsive Design */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 20px;
    padding: 30px 20px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .profile-name {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .user-profile {
    padding: 10px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .profile-header {
    padding: 20px 15px;
  }
  
  .profile-name {
    font-size: 1.75rem;
  }
}
</style>
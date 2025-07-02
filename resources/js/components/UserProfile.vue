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
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">{{ userProducts.length }}</div>
          <div class="stat-label">Products Listed</div>
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
            @click="!isCurrentUserProfile ? viewProduct(product.id) : null"
            :class="{ 'clickable': !isCurrentUserProfile }"
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
              
              <!-- Edit Button for Current User's Products -->
              <button 
                v-if="isCurrentUserProfile"
                @click.stop="editProduct(product.id)"
                class="edit-product-btn"
              >
                ✏️ Edit
              </button>
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
    </div>

    <!-- Error State -->
    <div v-else class="error-container">
      <div class="error-icon">⚠️</div>
      <h2>Profile Not Found</h2>
      <p>The user profile you're looking for doesn't exist or couldn't be loaded.</p>
      <button @click="$router.go(-1)" class="back-button">Go Back</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      user: null,
      userProducts: [],
      loading: true
    };
  },
  
  computed: {
    isCurrentUserProfile() {
      const currentUser = this.getCurrentUser();
      if (!currentUser?.id || !this.user?.id) {
        return false;
      }
      
      // Compare as strings to handle type differences
      return currentUser.id.toString() === this.user.id.toString();
    }
  },
  
  mounted() {
    this.fetchUserProfile();
    this.fetchUserProducts();
  },
  
  methods: {
    getCurrentUser() {
      // Try Vuex store first
      if (this.$store?.state?.user) {
        return this.$store.state.user;
      }
      
      // Try localStorage
      const userStr = localStorage.getItem('user');
      if (userStr) {
        try {
          return JSON.parse(userStr);
        } catch (e) {
          console.error('Error parsing user from localStorage:', e);
        }
      }
      
      // Try sessionStorage
      const sessionUserStr = sessionStorage.getItem('user');
      if (sessionUserStr) {
        try {
          return JSON.parse(sessionUserStr);
        } catch (e) {
          console.error('Error parsing user from sessionStorage:', e);
        }
      }
      
      return null;
    },

    getAuthHeaders() {
      const token = localStorage.getItem('access_token') || 
                    sessionStorage.getItem('access_token') ||
                    this.$store?.state?.token;
      
      return token ? {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      } : {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      };
    },

    async fetchUserProfile() {
      const userId = this.$route.params.id;
      try {
        const response = await axios.get(`/api/users/${userId}`, {
          headers: this.getAuthHeaders()
        });
        this.user = response.data;
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
    
    editProduct(productId) {
      this.$router.push(`/products/${productId}/edit`);
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
  }
};
</script>

<style scoped>
.debug-info {
  background: #f0f0f0;
  padding: 10px;
  border-radius: 4px;
  margin: 10px 0;
}

.inline-rating-form {
  margin-top: 10px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.rating-input-inline {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.rating-dropdown-inline {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.comment-input-inline {
  flex: 1;
  min-width: 200px;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.submit-rating-btn {
  padding: 8px 16px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.submit-rating-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-rating-btn {
  padding: 8px 16px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.edit-product-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  padding: 5px 8px;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
}

.product-card:not(.clickable) {
  cursor: default;
}

.product-image {
  position: relative;
}

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
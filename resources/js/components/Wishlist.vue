<template>
    <div class="wishlist-page">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12">
            <!-- Page Title and Description -->
            <div class="page-header mb-4">
              <h1>My Wishlist</h1>
              <p>Items you've saved for later</p>
            </div>
            
            <!-- Loading State -->
            <div v-if="loading" class="loading-state text-center my-5">
              <div class="spinner"></div>
              <p>Loading your wishlist...</p>
            </div>
            
            <!-- Error State -->
            <div v-else-if="error" class="error-state text-center my-5">
              <p class="error-message">{{ error }}</p>
              <button @click="fetchWishlist" class="retry-btn">Try Again</button>
            </div>
            
            <!-- Empty Wishlist State -->
            <div v-else-if="!wishlistItems.length" class="empty-state text-center my-5">
              <div class="empty-icon">❤️</div>
              <h3>Your wishlist is empty</h3>
              <p>Browse our products and save your favorites!</p>
              <router-link to="/scrap-items" class="browse-btn">Explore Items</router-link>
            </div>
            
            <!-- Wishlist Items -->
            <div v-else class="wishlist-items mt-4">
              <div class="row">
                <div v-for="item in wishlistItems" :key="item.id" class="col-lg-4 col-md-6 mb-4">
                  <div class="wishlist-card">
                    <!-- Product Image -->
                    <div class="image-container" @click="viewProductDetails(item.id)">
                      <img 
                        :src="item.images && item.images.length ? item.images[0] : 'default-image.jpg'" 
                        :alt="item.name" 
                        class="product-image"
                      />
                    </div>
                    
                    <!-- Product Info -->
                    <div class="product-info p-3">
                      <h3 class="product-name" @click="viewProductDetails(item.id)">{{ item.name }}</h3>
                      <p class="product-price">PKR {{ item.price }}</p>
                      
                      <!-- Action Buttons -->
                      <div class="action-buttons mt-3">
                        <button @click="removeFromWishlist(item.id)" class="remove-btn">
                          <span>Remove</span>
                        </button>
                        <button class="add-to-cart-btn">
                          <span>Add to Cart</span>
                        </button>
                      </div>
                      
                      <!-- Added Date -->
                      <div class="added-date mt-2">
                        <small>Added {{ formatDate(item.added_at) }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Confirmation Modal -->
      <div v-if="showConfirmModal" class="modal-overlay">
        <div class="modal-content">
          <h3>Remove from Wishlist?</h3>
          <p>Are you sure you want to remove this item from your wishlist?</p>
          <div class="modal-actions">
            <button @click="confirmRemove" class="confirm-btn">Yes, Remove</button>
            <button @click="cancelRemove" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>
      
      <!-- Login Modal -->
      <div v-if="showLoginModal" class="modal-overlay">
        <div class="modal-content">
          <h3>Please Log In</h3>
          <p>You need to be logged in to view your wishlist.</p>
          <div class="modal-actions">
            <router-link to="/login" class="primary-button">Log In</router-link>
            <button @click="closeLoginModal" class="secondary-button">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        wishlistItems: [],
        loading: true,
        error: null,
        showConfirmModal: false,
        itemToRemove: null,
        showLoginModal: false
      };
    },
    
    methods: {
      async fetchWishlist() {
        this.loading = true;
        this.error = null;
        
        const token = localStorage.getItem("access_token");
        if (!token) {
          this.showLoginModal = true;
          this.loading = false;
          return;
        }
        
        try {
          const response = await fetch('http://127.0.0.1:8000/api/wishlist', {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (!response.ok) {
            throw new Error('Failed to fetch wishlist');
          }
          
          const data = await response.json();
          this.wishlistItems = data;
        } catch (err) {
          console.error("Error fetching wishlist:", err);
          this.error = "Could not load your wishlist. Please try again.";
        } finally {
          this.loading = false;
        }
      },
      
      viewProductDetails(productId) {
        this.$router.push({ name: 'product-details', params: { id: productId } });
      },
      
      removeFromWishlist(productId) {
        this.itemToRemove = productId;
        this.showConfirmModal = true;
      },
      
      async confirmRemove() {
        const token = localStorage.getItem("access_token");
        
        try {
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/remove/${this.itemToRemove}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            // Remove the item from the local array
            this.wishlistItems = this.wishlistItems.filter(item => item.id !== this.itemToRemove);
          } else {
            throw new Error('Failed to remove item');
          }
        } catch (err) {
          console.error("Error removing item from wishlist:", err);
          this.error = "Could not remove item. Please try again.";
        } finally {
          this.showConfirmModal = false;
          this.itemToRemove = null;
        }
      },
      
      cancelRemove() {
        this.showConfirmModal = false;
        this.itemToRemove = null;
      },
      
      closeLoginModal() {
        this.showLoginModal = false;
        this.$router.push('/browse-scrap');
      },
      
      formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }
    },
    
    mounted() {
      this.fetchWishlist();
    }
  };
  </script>
  
  <style scoped>
  .wishlist-page {
    min-height: 80vh;
  }
  
  .page-header h1 {
    color: #333;
    font-weight: 600;
  }
  
  .page-header p {
    color: #666;
    font-size: 1.1rem;
  }
  
  .loading-state, .error-state, .empty-state {
    padding: 40px 20px;
  }
  
  .spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top: 4px solid #D4BEE4;
    width: 50px;
    height: 50px;
    margin: 0 auto 20px;
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  
  .error-message {
    color: #dc3545;
    font-size: 1.1rem;
  }
  
  .retry-btn {
    background-color: #D4BEE4;
    color: #3B1E54;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .retry-btn:hover {
    background-color: #C2A3DC;
  }
  
  .empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
  }
  
  .empty-state h3 {
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
  }
  
  .empty-state p {
    color: #666;
    margin-bottom: 20px;
  }
  
  .browse-btn {
    display: inline-block;
    background-color: #D4BEE4;
    color: #3B1E54;
    padding: 10px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s, transform 0.2s;
  }
  
  .browse-btn:hover {
    background-color: #C2A3DC;
    transform: translateY(-2px);
  }
  
  .wishlist-card {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: white;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  
  .wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
  }
  
  .image-container {
    height: 200px;
    overflow: hidden;
    cursor: pointer;
  }
  
  .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }
  
  .wishlist-card:hover .product-image {
    transform: scale(1.05);
  }
  
  .product-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }
  
  .product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    cursor: pointer;
    transition: color 0.2s;
  }
  
  .product-name:hover {
    color: #9B7EBD;
  }
  
  .product-price {
    font-size: 1.15rem;
    font-weight: 600;
    color: #9B7EBD;
    margin-bottom: 12px;
  }
  
  .action-buttons {
    display: flex;
    gap: 10px;
  }
  
  .remove-btn, .add-to-cart-btn {
    flex: 1;
    padding: 8px 0;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .remove-btn {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
  }
  
  .remove-btn:hover {
    background-color: #e9ecef;
  }
  
  .add-to-cart-btn {
    background-color: #D4BEE4;
    color: #3B1E54;
  }
  
  .add-to-cart-btn:hover {
    background-color: #C2A3DC;
  }
  
  .added-date {
    color: #6c757d;
    font-size: 0.85rem;
  }
  
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  
  .modal-content {
    background-color: white;
    border-radius: 10px;
    padding: 25px;
    max-width: 400px;
    width: 90%;
    text-align: center;
  }
  
  .modal-content h3 {
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
  }
  
  .modal-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 25px;
  }
  
  .confirm-btn, .cancel-btn, .primary-button, .secondary-button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .confirm-btn {
    background-color: #dc3545;
    color: white;
  }
  
  .confirm-btn:hover {
    background-color: #c82333;
  }
  
  .cancel-btn, .secondary-button {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
  }
  
  .cancel-btn:hover, .secondary-button:hover {
    background-color: #e9ecef;
  }
  
  .primary-button {
    background-color: #D4BEE4;
    color: #3B1E54;
    text-decoration: none;
  }
  
  .primary-button:hover {
    background-color: #C2A3DC;
  }
  
  @media (max-width: 767px) {
    .action-buttons {
      flex-direction: column;
    }
  }
  </style>
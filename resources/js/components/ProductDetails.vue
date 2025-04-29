<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <div>
    <!-- Product Detail Page -->
    <div v-if="product" class="product-detail-page">
      <div class="container mt-4">
        <div class="row">
          <!-- Left Side: Image Section -->
          <div class="col-lg-6 mb-3">
            <div class="image-gallery">
              <img 
                :src="mainImage" 
                class="product-main-image img-fluid rounded shadow-sm" 
                alt="Product Image" 
                loading="lazy"
              />
              <!-- Thumbnail Images (if multiple images are available) -->
              <div class="thumbnail-images mt-2 d-flex flex-wrap">
                <img 
                  v-for="(image, index) in product.images" 
                  :key="index" 
                  :src="image" 
                  class="thumbnail img-fluid rounded mr-2 mb-2" 
                  alt="Product Thumbnail" 
                  @click="changeMainImage(image)"
                />
              </div>
            </div>
          </div>

          <!-- Right Side: Product Details Section -->
          <div class="col-lg-6">
            <div class="product-info-section">
              <h1 class="product-name">{{ product.name }}</h1>
              <span class="price">PKR {{ product.price }}</span>

              <div class="product-details mt-3">
                <div class="details-item">
                  <p><strong>Description:</strong> {{ product.description || 'No description available' }}</p>
                </div>
              </div>

              <!-- Display user who uploaded the product -->
              <div class="info-item mt-3">
                <p class="description-by">
                  By <em>
                    <router-link :to="'/user-profile/' + product.user.id" class="seller-link">
                      {{ product.user.name }}
                    </router-link>
                  </em>
                </p>
              </div>

              <!-- Quantity Selector -->
              <div class="quantity-selector mb-3">
                <label for="quantitySelect">Quantity:</label>
                <div class="quantity-controls d-flex align-items-center">
                  <button 
                    @click="updateQuantity(-1)" 
                    :disabled="selectedQuantity <= 1"
                    class="quantity-btn"
                  >−</button>
                  <span class="quantity mx-3">{{ selectedQuantity }}</span>
                  <button 
                    @click="updateQuantity(1)" 
                    class="quantity-btn"
                  >+</button>
                </div>
              </div>
              
              <!-- Action Buttons -->
              <div class="button-group">
                <button 
                  @click="addToCart" 
                  class="add-to-cart-btn btn"
                >
                  <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>

                <button 
                  @click="toggleWishlist" 
                  class="add-to-wishlist-btn btn"
                >
                  <i :class="isInWishlist ? 'fas fa-heart' : 'far fa-heart'"></i>
                </button>
              </div>
              
              <!-- Confirmation Message -->
              <div v-if="showAddedToCartMessage" class="added-to-cart-message mt-3 alert alert-success">
                Item added to cart successfully!
              </div>
            </div>
          </div>
        </div>

        <!-- Review Tabs -->
        <div class="review-tabs mt-4">
          <ul class="nav nav-tabs" id="reviewTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="true">Reviews</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="add-review-tab" data-bs-toggle="tab" data-bs-target="#add-review" type="button" role="tab" aria-controls="add-review" aria-selected="false">Write a Review</button>
            </li>
          </ul>
          
          <div class="tab-content mt-3" id="reviewTabsContent">
            <!-- Reviews Tab -->
            <div class="tab-pane fade show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
              <div class="comments-section">
                <!-- Display existing comments -->
                <div v-if="product.comments && product.comments.length" class="reviews">
                  <div v-for="comment in paginatedComments" :key="comment.id" class="review p-3 mb-3 shadow-sm rounded">
                    <div class="d-flex justify-content-between align-items-center">
                      <strong>{{ comment.user.name }}</strong>
                      <span class="badge badge-warning">
                        <i class="fas fa-star"></i> {{ comment.rating }} / 5
                      </span>
                    </div>
                    <p class="mt-2">{{ comment.comment }}</p>
                    <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
                  </div>

                  <!-- Pagination Controls -->
                  <nav v-if="totalPages > 1" aria-label="Comments Pagination">
                    <ul class="pagination justify-content-center">
                      <li :class="['page-item', { disabled: currentPage === 1 }]">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
                      </li>
                      <li 
                        v-for="page in totalPages" 
                        :key="page" 
                        :class="['page-item', { active: currentPage === page }]"
                      >
                        <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                      </li>
                      <li :class="['page-item', { disabled: currentPage === totalPages }]">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                      </li>
                    </ul>
                  </nav>
                </div>

                <!-- Display no reviews message if no comments -->
                <div v-else class="no-reviews">
                  <p>No reviews yet. Be the first to review!</p>
                </div>
              </div>
            </div>
            
            <!-- Add Review Tab -->
            <div class="tab-pane fade" id="add-review" role="tabpanel" aria-labelledby="add-review-tab">
              <div class="comment-form p-3 bg-light rounded shadow-sm">
                <textarea 
                  v-model="newReview.comment" 
                  placeholder="Share your experience with this product..." 
                  rows="4" 
                  class="form-control mb-3"
                ></textarea>

                <!-- Inline Error Message for Review Comment -->
                <div v-if="reviewError.comment" class="error-message mb-3">
                  {{ reviewError.comment }}
                </div>

                <div class="form-group">
                  <label for="ratingSelect">Rating:</label>
                  <select v-model="newReview.rating" id="ratingSelect" class="form-control w-25">
                    <option disabled value="">Select Rating</option>
                    <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                  </select>
                </div>

                <!-- Inline Error Message for Review Rating -->
                <div v-if="reviewError.rating" class="error-message mb-3">
                  {{ reviewError.rating }}
                </div>

                <button @click="submitReview" class="submit-review-btn btn btn-sm">
                  <i class="fas fa-paper-plane mr-2"></i> Submit Review
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Error Message -->
    <div v-else-if="error" class="error-message text-center mt-4">
      <p>{{ error }}</p>
    </div>

    <!-- Loading Message -->
    <div v-else class="loading-message text-center mt-4">
      <p>Loading product details...</p>
    </div>

    <!-- Login Modal -->
    <div v-if="loginModalVisible" class="modal-overlay">
      <div class="modal-content">
        <h2>Please Log In</h2>
        <p>You need to be logged in to perform this action.</p>
        <div class="modal-actions">
          <router-link to="/login" class="primary-button">Log In</router-link>
          <button @click="closeLoginModal" class="secondary-button">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      product: null,  // Store product details
      error: null,    // Store error messages
      newReview: {
        comment: '',
        rating: '',
      }, // Store the new review
      reviewError: {
        comment: '',
        rating: '',
      }, // Store review form errors
      mainImage: '',  // To handle the main image display
      currentPage: 1, // Current page for comments
      commentsPerPage: 5, // Number of comments per page
      loginModalVisible: false, // Controls visibility of login modal
      isInWishlist: false,
      selectedQuantity: 1,
      showAddedToCartMessage: false
    };
  },
  computed: {
    // Compute the comments to display on the current page
    paginatedComments() {
      if (!this.product?.comments) return [];
      const start = (this.currentPage - 1) * this.commentsPerPage;
      const end = start + this.commentsPerPage;
      return this.product.comments.slice(start, end);
    },
    // Compute total pages based on comments
    totalPages() {
      if (!this.product?.comments) return 1;
      return Math.ceil(this.product.comments.length / this.commentsPerPage);
    }
  },
  methods: {
    // Add this to your methods in the product details component
    async addToCart() {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.showLoginModal();
        return;
      }
      
      try {
        // Default quantity is 1 if not specified
        const quantity = this.selectedQuantity || 1;
        
        const response = await fetch(`http://127.0.0.1:8000/api/cart/add/${this.product.id}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            quantity: quantity
          })
        });
        
        if (response.ok) {
          this.showAddedToCartMessage = true;
          // Hide the message after 3 seconds
          setTimeout(() => {
            this.showAddedToCartMessage = false;
          }, 3000);
        } else {
          throw new Error('Failed to add to cart');
        }
      } catch (err) {
        console.error("Error adding to cart:", err);
        this.error = "Failed to add to cart. Please try again.";
      }
    },
    updateQuantity(change) {
      const newQuantity = this.selectedQuantity + change;
      if (newQuantity >= 1) {
        this.selectedQuantity = newQuantity;
      }
    },
    // Fetch product details based on ID
    async fetchProductDetails() {
      const productId = this.$route.params.id; // Get the product ID from the route params
      try {
        const response = await axios.get(`http://127.0.0.1:8000/api/products/${productId}`);
        this.product = response.data; // Store product details
        this.mainImage = this.product.images.length
          ? this.product.images[0]
          : 'default-image.jpg';
      } catch (error) {
        console.error("Error fetching product details:", error);
        this.error = error.response?.data?.message || "Failed to load product details.";
      }
    },

    // Format date string
    formatDate(dateStr) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateStr).toLocaleDateString(undefined, options);
    },

    // Submit new review
    async submitReview() {
      // Reset previous errors
      this.reviewError.comment = '';
      this.reviewError.rating = '';

      if (!this.newReview.comment.trim()) {
        this.reviewError.comment = "Please enter a comment.";
      }
      if (!this.newReview.rating) {
        this.reviewError.rating = "Please select a rating.";
      }

      // If there are any errors, do not proceed
      if (this.reviewError.comment || this.reviewError.rating) {
        return;
      }

      try {
        // Ensure the user is authenticated before submitting a review
        const token = localStorage.getItem("access_token");
        if (!token) {
          this.showLoginModal();
          return;
        }

        await axios.post(
          `http://127.0.0.1:8000/api/products/${this.product.id}/reviews`, 
          {
            comment: this.newReview.comment,
            rating: this.newReview.rating,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.newReview.comment = ''; // Clear the form after submission
        this.newReview.rating = '';  // Reset rating
        this.fetchProductDetails();  // Reload product details to show new review
        
        // Switch to reviews tab after submission
        document.getElementById('reviews-tab').click();
      } catch (error) {
        console.error("Error submitting review:", error);
        // Display error messages inline
        this.reviewError.comment = error.response?.data?.message || "Failed to submit review.";
      }
    },

    // Change main image when thumbnail is clicked
    changeMainImage(image) {
      this.mainImage = image;
    },

    // Change the current page in comments
    changePage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
      // Scroll to comments section when page changes
      this.$nextTick(() => {
        const commentsSection = this.$el.querySelector('.comments-section');
        if (commentsSection) {
          commentsSection.scrollIntoView({ behavior: 'smooth' });
        }
      });
    },

    // Show login modal
    showLoginModal() {
      this.loginModalVisible = true;
    },

    // Close login modal
    closeLoginModal() {
      this.loginModalVisible = false;
    },
    async checkWishlistStatus() {
      const token = localStorage.getItem("access_token");
      if (!token || !this.product) return;
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/wishlist', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          const productIds = data.map(item => item.id);
          this.isInWishlist = productIds.includes(this.product.id);
        }
      } catch (err) {
        console.error("Error checking wishlist status:", err);
      }
    },
    
    // Toggle wishlist status
    async toggleWishlist() {
      const token = localStorage.getItem("access_token");
      if (!token) {
        this.showLoginModal();
        return;
      }
      
      try {
        if (this.isInWishlist) {
          // Remove from wishlist
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/remove/${this.product.id}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            this.isInWishlist = false;
          }
        } else {
          // Add to wishlist
          const response = await fetch(`http://127.0.0.1:8000/api/wishlist/add/${this.product.id}`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.ok) {
            this.isInWishlist = true;
          }
        }
      } catch (err) {
        console.error("Error updating wishlist:", err);
      }
    },
  },
  watch: {
    // Add this watch to check wishlist status when product changes
    product() {
      if (this.product) {
        this.checkWishlistStatus();
      }
    }
  },
  
  mounted() {
    this.fetchProductDetails(); // Fetch the product details when the component mounts
  },
};
</script>


<style>
/* Main Layout & Container Styles */
.product-detail-page {
  margin: 0 auto;
  padding: 20px 15px;
  font-family: 'Roboto', sans-serif;
  color: #333;
  background-color: #fcfcfc;
}

.container {
  max-width: 1140px;
  margin: 0 auto;
}

/* Consistent row height for image and product details */
.row {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
}

/* Left Side: Image Section */
.image-gallery {
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
}

.product-main-image {
  width: 100%;
  height: 380px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.product-main-image:hover {
  transform: scale(1.01);
}

.thumbnail-images {
  display: flex;
  flex-wrap: wrap;
  margin-top: 10px;
  gap: 8px;
}

.thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.3s ease;
  border: 1px solid transparent;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.thumbnail:hover {
  transform: scale(1.05);
  border: 1px solid #D4BEE4;
}

/* Right Side: Product Details Section */
.product-info-section {
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.product-name {
  font-size: 1.8rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  line-height: 1.2;
  border-bottom: 1px solid #eee;
  padding-bottom: 8px;
}

.price {
  display: inline-block;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 12px;
  padding: 4px 10px;
  border-radius: 4px;
}

/* Product Details */
.product-details {
  margin: 12px 0;
  border-radius: 6px;
  padding: 15px;
  overflow-y: auto;
  max-height: 150px;
  scrollbar-width: thin;
  scrollbar-color: #D4BEE4 #f1f1f1;
}

/* Customize scrollbar for webkit browsers */
.product-details::-webkit-scrollbar {
  width: 6px;
}

.product-details::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 6px;
}

.product-details::-webkit-scrollbar-thumb {
  background: #D4BEE4;
  border-radius: 6px;
}

.details-item {
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.details-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.details-item p {
  margin: 0;
  font-size: 0.95rem;
  color: #555;
  line-height: 1.5;
}

.details-item p strong {
  min-width: 100px;
  display: inline-block;
  font-weight: 600;
  color: #333;
}

/* Seller info */
.info-item {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #eee;
}

.description-by {
  color: #555;
  font-style: italic;
  font-size: 0.9rem;
  margin-bottom: 0;
}

.seller-link {
  color: #6c63ff;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.seller-link:hover {
  color: #5046e5;
  text-decoration: underline;
}

/* Quantity Selector */
.quantity-selector {
  margin: 15px 0;
}

.quantity-selector label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: #333;
}

.quantity-controls {
  display: flex;
  align-items: center;
}

.quantity-btn {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 700;
  background-color: #F5F5F5;
  color: #333;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.quantity-btn:hover:not(:disabled) {
  background-color: #D4BEE4;
  color: #3B1E54;
  border-color: #D4BEE4;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  font-size: 1rem;
  font-weight: 600;
  min-width: 30px;
  text-align: center;
}

/* Button Group */
.button-group {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.add-to-cart-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 15px;
  font-size: 0.95rem;
  font-weight: 500;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(108, 99, 255, 0.2);
}

.add-to-cart-btn:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(108, 99, 255, 0.3);
}

.add-to-wishlist-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  color: #CA7373;
  border: 1px solid #CA7373;
  width: 40px;
  height: 40px;
  font-size: 1.1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.add-to-wishlist-btn:hover {
  background-color: #FEF2F2;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(202, 115, 115, 0.15);
}

.add-to-wishlist-btn i.fas.fa-heart {
  color: #CA7373;
}

/* Added to cart message */
.added-to-cart-message {
  padding: 8px 12px;
  margin-top: 12px;
  background-color: #d4f8e6;
  color: #0a7f55;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 500;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  animation: fadeInOut 3s forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translateY(8px); }
  10% { opacity: 1; transform: translateY(0); }
  90% { opacity: 1; transform: translateY(0); }
  100% { opacity: 0; transform: translateY(-8px); }
}

/* Review Tabs */
.review-tabs {
  margin-top: 30px;
}

.nav-tabs {
  border-bottom: 1px solid #ddd;
}

.nav-tabs .nav-link {
  color: #555;
  font-weight: 500;
  border: none;
  padding: 8px 15px;
  border-radius: 4px 4px 0 0;
  margin-right: 5px;
  font-size: 0.95rem;
}

.nav-tabs .nav-link.active {
  color: #6c63ff;
  border-bottom: 2px solid #6c63ff;
  background-color: #f9f9f9;
}

.tab-content {
  padding: 15px 0;
}

/* Comments Section */
.comments-section {
  margin-top: 15px;
}

.reviews .review {
  padding: 15px;
  margin-bottom: 12px;
  background-color: #fff;
  border: 1px solid #eee;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.reviews .review:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
}

.reviews .review strong {
  font-size: 0.95rem;
  color: #333;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 4px 8px;
  background-color: #FFF4DE;
  color: #B07D1A;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.badge i {
  color: #FFB800;
  font-size: 0.7rem;
}

.review p {
  margin: 10px 0;
  color: #555;
  line-height: 1.5;
  font-size: 0.9rem;
}

.review small {
  color: #888;
  font-size: 0.8rem;
}

.no-reviews {
  padding: 20px;
  text-align: center;
  background-color: #f9f9f9;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.no-reviews p {
  font-size: 0.95rem;
  color: #777;
  margin: 0;
}

/* Comment Form */
.comment-form {
  padding: 20px;
  border-radius: 6px;
  background-color: #f9f9f9;
  margin-bottom: 20px;
}

.comment-form textarea {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.95rem;
  resize: vertical;
  transition: border-color 0.3s;
}

.comment-form textarea:focus {
  border-color: #6c63ff;
  box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.15);
  outline: none;
}

.form-group {
  margin-bottom: 12px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.form-control {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.form-control:focus {
  border-color: #6c63ff;
  box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.15);
  outline: none;
}

.submit-review-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 500;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(108, 99, 255, 0.2);
}

.submit-review-btn:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(108, 99, 255, 0.3);
}

.submit-review-btn i {
  font-size: 0.85rem;
}

/* Error Messages */
.error-message {
  color: #df3c3c;
  font-size: 0.85rem;
  margin-top: -8px;
  margin-bottom: 10px;
  padding-left: 2px;
}

/* Pagination Controls */
.pagination {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 20px 0 0;
  justify-content: center;
}

.pagination .page-item {
  margin: 0 2px;
}

.pagination .page-item .page-link {
  color: #6c63ff;
  background-color: #fff;
  border: 1px solid #ddd;
  padding: 6px 12px;
  border-radius: 4px;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.pagination .page-item .page-link:hover {
  background-color: #f5f5f5;
  border-color: #6c63ff;
}

.pagination .page-item.active .page-link {
  background-color: #6c63ff;
  border-color: #6c63ff;
  color: white;
  cursor: default;
}

.pagination .page-item.disabled .page-link {
  color: #aaa;
  pointer-events: none;
  background-color: #f9f9f9;
  border-color: #ddd;
}

/* Loading and Error Messages */
.loading-message,
.error-message {
  padding: 30px;
  text-align: center;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  margin: 30px auto;
  max-width: 600px;
}

.loading-message p,
.error-message p {
  font-size: 1rem;
  color: #555;
  margin: 0;
}

.error-message p {
  color: #e74c3c;
}

/* Login Modal */
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
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-width: 400px;
  width: 90%;
  text-align: center;
}

.modal-content h2 {
  color: #333;
  font-size: 1.5rem;
  margin-bottom: 15px;
}

.modal-content p {
  color: #555;
  margin-bottom: 20px;
  font-size: 0.95rem;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.primary-button,
.secondary-button {
  padding: 8px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.primary-button {
  background-color: #6c63ff;
  color: white;
  border: none;
  text-decoration: none;
  box-shadow: 0 2px 6px rgba(108, 99, 255, 0.2);
}

.primary-button:hover {
  background-color: #5046e5;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(108, 99, 255, 0.3);
}

.secondary-button {
  background-color: white;
  color: #555;
  border: 1px solid #ddd;
}

.secondary-button:hover {
  background-color: #f5f5f5;
  border-color: #ccc;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
  .product-main-image {
    height: 320px;
  }
}

@media (max-width: 767.98px) {
  .product-name {
    font-size: 1.5rem;
  }
  
  .price {
    font-size: 1.2rem;
  }
  
  .product-main-image {
    height: 280px;
  }
  
  .button-group {
    flex-direction: column;
  }
  
  .add-to-wishlist-btn {
    width: 100%;
    height: 40px;
  }
}

@media (max-width: 575.98px) {
  .product-detail-page {
    padding: 15px 10px;
  }
  
  .product-main-image {
    height: 240px;
  }
  
  .product-name {
    font-size: 1.3rem;
  }
  
  .thumbnail {
    width: 50px;
    height: 50px;
  }
  
  .product-info-section {
    padding: 15px;
  }
  
  .nav-tabs .nav-link {
    padding: 6px 10px;
    font-size: 0.85rem;
  }
}
</style>
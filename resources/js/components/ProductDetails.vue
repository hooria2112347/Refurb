<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <div>
    <!-- Product Detail Page -->
    <div v-if="product" class="product-detail-page">
      <div class="container mt-5">
        <div class="row">
          <!-- Left Side: Image Section -->
          <div class="col-lg-6 mb-4">
            <div class="image-gallery">
              <img 
                :src="mainImage" 
                class="product-main-image img-fluid rounded shadow-sm" 
                alt="Product Image" 
                loading="lazy"
              />
              <!-- Thumbnail Images (if multiple images are available) -->
              <div class="thumbnail-images mt-3 d-flex flex-wrap">
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

              <div class="product-details mt-4">
                <div class="details-item">
                  <p><strong>Material:</strong> {{ product.material || 'N/A' }}</p>
                </div>
                <div class="details-item">
                  <p><strong>Dimensions:</strong> {{ product.dimensions || 'N/A' }}</p>
                </div>
                <div class="details-item">
                  <p><strong>Description:</strong> {{ product.description || 'No description available' }}</p>
                </div>
              </div>

              <!-- Display user who uploaded the product -->
              <div class="info-item mt-4">
                <p class="description-by">
                  By <em>
                    <router-link :to="'/user-profile/' + product.user.id" class="seller-link">
                      {{ product.user.name }}
                    </router-link>
                  </em>
                </p>
              </div>


            <!-- Add this before the button group in your template -->
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
            <!-- Replace the existing button section with this: -->
<div class="button-group mt-3">
  <!-- Replace your existing add-to-cart-btn with this -->
<button 
  @click="addToCart" 
  class="add-to-cart-btn btn btn-primary btn-lg"
>
  <i class="fas fa-shopping-cart"></i>
</button>

  <button 
    @click="toggleWishlist" 
    class="add-to-wishlist-btn btn btn-outline-primary btn-lg"
  >
    <i :class="isInWishlist ? 'fas fa-heart' : 'far fa-heart'"></i>
  </button>
</div>
<!-- Add this right after your button-group -->
<div v-if="showAddedToCartMessage" class="added-to-cart-message mt-3 alert alert-success">
  Item added to cart successfully!
</div>
            </div>
          </div>
        </div>

        <!-- Comment and Review Form -->
        <div class="comment-form-section mt-5">
          <h4>Leave a Review</h4>
          <div class="comment-form p-4 bg-light rounded shadow">
            <textarea 
              v-model="newReview.comment" 
              placeholder="Leave your feedback or question here..." 
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

            <button @click="submitReview" class="submit-review-btn btn btn-success mt-3">
              <i class="fas fa-paper-plane mr-2"></i> Submit Review
            </button>
          </div>
        </div>

        <!-- Comment Section with Pagination -->
        <div class="comments-section mt-4">
          <h4>Customer Reviews & Feedback</h4>

          <!-- Display existing comments -->
          <div v-if="product.comments && product.comments.length" class="reviews mt-3">
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
          <div v-else class="no-reviews mt-3">
            <p>No reviews yet. Be the first to review!</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Error Message -->
    <div v-else-if="error" class="error-message text-center mt-5">
      <p>{{ error }}</p>
    </div>

    <!-- Loading Message -->
    <div v-else class="loading-message text-center mt-5">
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

<style scoped>
.product-detail-page {
  margin: 0 auto;
  padding: 20px;
}

.image-gallery {
  position: relative;
}

.product-main-image {
  width: 100%;
  height: auto;
}

.thumbnail-images {
  display: flex;
  flex-wrap: wrap;
}

.thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  margin-right: 10px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: transform 0.3s ease, border 0.3s ease;
  border: 2px solid transparent;
}

.thumbnail:hover {
  transform: scale(1.05);
  border: 2px solid #CA7373;
}

.product-info-section {
  padding: 20px;
}

.product-name {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}

.price {
  font-size: 1.8rem;
  color: #CA7373;
}

.product-details {
  margin-top: 1.5rem;
}

.details-item p {
  font-size: 1rem;
  color: #555;
  margin-bottom: 0.5rem;
}

.info-item {
  font-size: 1rem;
  color: #555;
}

.description-by {
  color: #333;
}

.seller-link {
  color: #CA7373;
  text-decoration: none;
  transition: color 0.3s ease;
}

.seller-link:hover {
  color: #ff6f61;
}

/* Updated button group styles */
.button-group {
  display: flex;
  gap: 15px;
  margin-top: 25px;
}

/* Updated add to cart button */
.add-to-cart-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  width: 60px;
  height: 60px;
  font-size: 1.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
  padding: 0;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.add-to-cart-btn:hover {
  background-color: #C4A6DC;
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

/* Updated wishlist button */
.add-to-wishlist-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  color: #D4BEE4;
  border: 2px solid #D4BEE4;
  width: 60px;
  height: 60px;
  font-size: 1.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.add-to-wishlist-btn:hover {
  background-color: #FEF2F2;
  color: #CA7373;
  border-color: #CA7373;
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.add-to-wishlist-btn i.fa-heart {
  color: #CA7373;
}

.comments-section h4 {
  font-size: 1.5rem;
  color: #333;
}

.reviews .review {
  background-color: #fff;
  border: 1px solid #e0e0e0;
}

.no-reviews p {
  font-size: 1rem;
  color: #777;
}

.comment-form-section {
  position: relative;
}

.submit-review-btn {
  display: flex;
  align-items: center;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 15px;
  font-size: 1rem;
  border-radius: 15px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.submit-review-btn:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
}

.error-message {
  font-size: 1rem;
  color: #ff4c3b;
  padding: 5px;
}

.loading-message {
  font-size: 1rem;
  color: #555;
  background-color: transparent;
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
  background-color: #fff;
  padding: 24px;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.modal-content h2 {
  margin-bottom: 16px;
  color: #333;
}

.modal-content p {
  margin-bottom: 24px;
  color: #555;
}

.modal-actions {
  display: flex;
  justify-content: space-around;
}

.primary-button {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  text-decoration: none;
}

.primary-button:hover {
  background-color: #EEEEEE;
}

.secondary-button {
  background-color: #ccc;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.secondary-button:hover {
  background-color: #b3b3b3;
}

/* Pagination Styles */
.pagination {
  margin-top: 20px;
}

.page-link {
  cursor: pointer;
}

.page-item.disabled .page-link {
  cursor: not-allowed;
}

.page-item.active .page-link {
  background-color: #CA7373;
  border-color: #CA7373;
  color: #fff;
}

/* Responsive Design */
@media (max-width: 992px) {
  .product-main-image {
    height: auto;
  }

  .thumbnail {
    width: 50px;
    height: 50px;
  }

  .price {
    font-size: 1.5rem;
  }
}

@media (max-width: 576px) {
  .product-info-section {
    padding: 10px;
  }

  /* Updated responsive styles for the buttons */
  .button-group {
    justify-content: center;
  }
  
  .add-to-cart-btn,
  .add-to-wishlist-btn {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }

  .submit-review-btn {
    width: 100%;
    justify-content: center;
  }

  .thumbnail-images {
    justify-content: center;
  }
}
</style>
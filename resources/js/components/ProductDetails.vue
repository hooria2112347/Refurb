<template>
  <div v-if="product" class="product-detail-page">
    <div class="container mt-4">
      <div class="row">
        <!-- Left Side: Image Section (Image display) -->
        <div class="col-md-6 mb-4">
          <img :src="product.images.length ? product.images[0] : 'default-image.jpg'" class="d-block w-100 product-image img-fluid rounded" alt="Product Image" />
        </div>

        <!-- Right Side: Product Details Section -->
        <div class="col-md-6">
          <div class="product-info-section">
            <h1>{{ product.name }}</h1>
            <span class="price">PKR {{ product.price }}</span>

            <div class="product-details">
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
            <div class="info-item">
              <p class="description-by">
                By <em>
                  <router-link :to="'/user-profile/' + product.user.id">{{ product.user.name }}</router-link>
                </em>
              </p>
            </div>

            <!-- Display artist information if the logged-in user is the creator -->
            <div v-if="product.artist" class="info-item">
              <p>Accepted by
                <em>
                  <router-link :to="'/user-profile/' + product.artist.id">{{ product.artist.name }}</router-link>
                </em>
              </p>
            </div>

            <!-- Add to Cart Button -->
            <button @click="addToCart" class="add-to-cart-btn">Add to Cart</button>
          </div>
        </div>
      </div>

      <!-- Comment Section -->
      <div class="comments-section mt-4">
        <h4>Customer Reviews & Feedback</h4>

        <!-- Display existing comments -->
        <div v-if="product.comments && product.comments.length">
          <div v-for="comment in product.comments" :key="comment.id" class="review">
            <p><strong>{{ comment.user.name }}:</strong> {{ comment.comment }}</p>
            <p class="review-rating">Rating: {{ comment.rating }} / 5</p>
          </div>
        </div>

        <!-- Display no reviews message if no comments -->
        <div v-else>
          <p>No reviews yet. Be the first to review!</p>
        </div>

        <!-- Add Review Form -->
        <div class="comment-form">
          <textarea v-model="newReview.comment" placeholder="Leave your feedback or question here..." rows="4" class="form-control"></textarea>
          <div>
            <label>Rating: </label>
            <select v-model="newReview.rating" class="form-control">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
          <button @click="submitReview" class="submit-review-btn">Submit Review</button>
        </div>
      </div>
    </div>
  </div>

  <div v-else>
    <p>Loading product details...</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      product: null,  // Store product details
      newReview: {
        comment: '',
        rating: 5,
      }, // Store the new review
    };
  },
  methods: {
    // Fetch product details based on ID
    async fetchProductDetails() {
      const productId = this.$route.params.id; // Get the product ID from the route params
      try {
        const response = await axios.get(`http://127.0.0.1:8000/api/products/${productId}`);
        this.product = response.data; // Store product details
      } catch (error) {
        console.error("Error fetching product details:", error);
        alert("Failed to load product details.");
      }
    },

    // Add to cart logic
    addToCart() {
      // Your add to cart logic here
    },

    // Submit new review
    async submitReview() {
      if (!this.newReview.comment.trim()) return; // Do not submit empty comment
      try {
        await axios.post(`/api/products/${this.product.id}/reviews`, {
          comment: this.newReview.comment,
          rating: this.newReview.rating,
        });
        this.newReview.comment = ''; // Clear the form after submission
        this.newReview.rating = 5; // Reset rating to 5
        this.fetchProductDetails(); // Reload product details to show new review
      } catch (error) {
        console.error("Error submitting review:", error);
        alert("Failed to submit review.");
      }
    },
  },
  mounted() {
    this.fetchProductDetails(); // Fetch the product details when the component mounts
  },
};
</script>

<style scoped>
.product-detail-page {
  font-family: Arial, sans-serif;
  margin: 0 auto;
  padding: 20px;
}

.product-info-section {
  padding: 20px;
}

.product-name {
  font-size: 2rem;
  font-weight: bold;
}

.price {
  font-size: 1.5rem;
  color: #CA7373;
}

.info-item {
  font-size: 1rem;
  color: #555;
}

.description-by {
  color: #333;
}

.add-to-cart-btn {
  background-color: #4CAF50;
  color: white;
  padding: 10px;
  font-size: 1.2rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.comments-section {
  margin-top: 20px;
}

.review {
  background-color: #f9f9f9;
  padding: 15px;
  margin-bottom: 15px;
  border-radius: 5px;
}

.review-rating {
  color: #CA7373;
}

.comment-form {
  display: flex;
  flex-direction: column;
  padding: 10px;
}

.comment-form textarea {
  width: 100%;
  padding: 10px;
  font-size: 1rem;
  margin-bottom: 10px;
}

.comment-form select {
  width: 100%;
  padding: 10px;
  font-size: 1rem;
  margin-bottom: 10px;
}

.submit-review-btn {
  background-color: #CA7373;
  color: white;
  padding: 10px;
  font-size: 1.2rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.submit-review-btn:hover {
  background-color: #D7B26D;
}
</style>

<template>
  <div v-if="product" class="product-detail-page">
    <div class="container mt-4">
      <div class="row">
        <!-- Left Side: Image Section -->
        <div class="col-md-6 mb-4">
          <!-- Display the first image -->
          <div v-if="product.images && product.images.length">
            <img 
              :src="product.images[0]" 
              class="d-block w-100 product-image img-fluid rounded" 
              alt="Product Image" 
            />
          </div>
          <p v-else>No image available.</p>
        </div>

        <!-- Right Side: Product Info Section -->
        <div class="col-md-6">
          <div class="product-info-section">
            <h1>{{ product.name }}</h1>
            <span class="price">${{ product.price }}</span>

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

            <!-- Add to Cart Button -->
            <button @click="addToCart" class="add-to-cart-btn">Add to Cart</button>

            <!-- Comments Section -->
            <div class="comments-section">
              <h4>Customer Reviews & Feedback</h4>
              <div v-if="product.reviews && product.reviews.length">
                <div v-for="review in product.reviews" :key="review.id" class="review">
                  <p><strong>{{ review.user.name }}:</strong> {{ review.comment }}</p>
                  <p class="review-rating">Rating: {{ review.rating }} / 5</p>
                </div>
              </div>
              <div v-else>
                <p>No reviews yet. Be the first to review!</p>
              </div>

              <!-- Add a Review Form -->
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
      </div>
    </div>
  </div>

  <div v-else>
    <p>Loading product details...</p>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  data() {
    return {
      product: null,  // Store product details
      newReview: {
        comment: '',
        rating: 5,
      },
    };
  },
  methods: {
    async fetchProductDetails() {
      const productId = this.$route.params.id; // Get the product ID from the route params
      try {
        const response = await axios.get(`http://127.0.0.1:8000/api/products/${productId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.product = response.data; // Store product details
      } catch (error) {
        console.error("Error fetching product details:", error);
        alert("Failed to load product details.");
      }
    },

    async addToCart() {
      const cart = JSON.parse(localStorage.getItem('cart')) || [];
      const cartItem = {
        id: this.product.id,
        name: this.product.name,
        price: this.product.price,
        quantity: 1,
      };

      const existingProduct = cart.find(item => item.id === cartItem.id);
      if (existingProduct) {
        existingProduct.quantity += 1;
      } else {
        cart.push(cartItem);
      }

      localStorage.setItem('cart', JSON.stringify(cart));
      alert('Product added to cart');
    },
  },
  mounted() {
    this.fetchProductDetails();
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
  margin-top: 10px;
}

.product-details {
  margin-top: 20px;
}

.details-item {
  margin-bottom: 10px;
}

.add-to-cart-btn {
  background-color: #4CAF50;
  color: white;
  padding: 10px;
  font-size: 1.2rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 20px;
}
</style>

<template>
  <div class="browse-scrap">
    <!-- Loading and Error States -->
    <div v-if="loading" class="loading">Loading products...</div>
    <div v-else-if="error" class="error">{{ error }}</div>

    <!-- Grid of Products -->
    <div v-else-if="products.length" class="products-grid">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-card" 
      >
        <!-- Cart Icon (No Click Handler) -->
        <div class="add-to-cart" title="Add to Cart">
          🛒
        </div>

        <!-- Display Image if Available -->
        <img 
          :src="product.images.length ? product.images[0] : 'default-image.jpg'" 
          alt="Product Image" 
          class="product-image" 
          @click="viewProductDetails(product)"
        />

        <!-- Product Name and Price -->
        <div class="product-info">
          <h2 class="product-name" @click="viewProductDetails(product)">
            {{ product.name }}
          </h2>
          <span class="price">{{ product.price }} PKR</span>
        </div>
      </div>
    </div>

    <!-- No Products Available Message -->
    <div v-else class="no-products">
      <p>No products found.</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      products: [],  // List of products
      loading: false, // Loading state
      error: null, // Error message if fetch fails
    };
  },
  methods: {
    // Fetch all products from the API
    async fetchProducts() {
      this.loading = true;
      this.error = null; // Reset error state before fetching
      try {
        const response = await fetch('http://127.0.0.1:8000/api/products/all');
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log(data);  // Log the fetched data to check structure
        this.products = data;
      } catch (err) {
        this.error = 'Failed to fetch products.';
        console.error(err); // Log the actual error for debugging
      } finally {
        this.loading = false;
      }
    },

    // Navigate to the product details page
    viewProductDetails(product) {
      this.$router.push({ name: 'product-details', params: { id: product.id } });
    },
  },
  mounted() {
    this.fetchProducts(); // Fetch products on component load
  },
};
</script>

<style scoped>
.browse-scrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
  /* Removed background color */
}

.loading,
.error,
.no-products {
  text-align: center;
  font-size: 1.2rem;
  color: #555;
  margin-top: 2rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Responsive columns */
  gap: 1.5rem;
  margin-top: 1rem;
}

.product-card {
  position: relative; /* For positioning the add-to-cart icon */
  /* Removed background-color */
  border: 1px solid #e0e0e0;
  padding: 1rem;
  border-radius: 12px;
  text-align: center;
  cursor: pointer;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.product-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15); /* Enhanced shadow */
  transform: translateY(-5px);
}

.add-to-cart {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: #9B7EBD ;
  color: #fff;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.add-to-cart:hover {
  background-color: #ff4c3b;
  transform: scale(1.1);
}

.product-image {
  max-width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.product-info {
  margin-top: 0.5rem;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.product-name:hover {
  color: #ff6f61;
}

.price {
  font-size: 1rem;
  color: #555;
  font-weight: bold;
}

.no-products p {
  color: #999;
}
</style>

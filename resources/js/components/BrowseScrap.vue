<template>
  <div class="browse-scrap">
    <h1>Browse Scrap</h1>
    <div v-if="loading">Loading products...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <!-- Grid of Products -->
    <div v-if="!loading && products.length" class="products-grid">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-card" 
        @click="viewProductDetails(product)"
      >
        <!-- Display Image if it's available -->
        <img :src="product.images.length ? product.images[0] : 'default-image.jpg'" alt="Product Image" class="product-image" />

        <!-- Product Name and Price below the image -->
        <div class="product-info">
          <h2 class="product-name">{{ product.name }}</h2>
          <span class="price">{{ product.price }} USD</span>
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
      try {
        const response = await fetch('http://127.0.0.1:8000/api/products/all');
        const data = await response.json();
        this.products = data;
      } catch (err) {
        this.error = 'Failed to fetch products.';
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
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* 4 products per row */
  gap: 1rem;
}

.product-card {
  border: 1px solid #ddd;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  cursor: pointer;
  transition: transform 0.3s ease;
  background-color: #f9f9f9;
}

.product-card:hover {
  transform: scale(1.05);
}

.product-image {
  max-width: 100%;
  height: auto;
  margin-bottom: 1rem;
  border-radius: 8px;
}

.product-info {
  margin-top: 0.5rem;
}

.product-name {
  font-size: 1.1rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 0.5rem;
}

.price {
  font-size: 1rem;
  color: #444;
}

.no-products {
  text-align: center;
  font-size: 1.2rem;
  color: #888;
}
</style>

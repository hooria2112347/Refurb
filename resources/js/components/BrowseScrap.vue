<template>
    <div class="browse-scrap">
      <h1>Browse Scrap</h1>
      <div v-if="loading">Loading products...</div>
      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="!loading && products.length" class="products-grid">
        <div v-for="product in products" :key="product.id" class="product-card">
          <img :src="product.image || 'default-image.jpg'" alt="Product Image" />
          <h2>{{ product.name }}</h2>
          <p>{{ product.description }}</p>
          <span class="price">{{ product.price }} USD</span>
          <span class="category">{{ product.category }}</span>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        products: [],
        loading: false,
        error: null,
      };
    },
    methods: {
      async fetchProducts() {
        this.loading = true;
        try {
          const response = await fetch('http://localhost:8000/api/products');
          const data = await response.json();
          this.products = data;
        } catch (err) {
          this.error = 'Failed to fetch products.';
        } finally {
          this.loading = false;
        }
      },
    },
    mounted() {
      this.fetchProducts();
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
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
  }
  .product-card {
    border: 1px solid #ddd;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
  }
  .product-card img {
    max-width: 100%;
    height: auto;
    margin-bottom: 1rem;
  }
  </style>
  
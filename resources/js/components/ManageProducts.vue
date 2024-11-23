<template>
    <div class="manage-products-container">
      <h1 class="page-title">Manage Products</h1>
  
      <!-- Display Products -->
      <div v-if="products.length" class="products-grid">
        <div v-for="product in products" :key="product.id" class="product-card">
          <img :src="product.image" alt="Product Image" class="product-image" />
  
          <div class="product-info">
            <h2 class="product-name">{{ product.name }}</h2>
            <p class="product-description">{{ product.description }}</p>
            <p class="product-price">Price: ${{ product.price }}</p>
          </div>
  
          <div class="product-actions">
            <button @click="editProduct(product)" class="edit-btn">Edit</button>
            <button @click="deleteProduct(product.id)" class="delete-btn">Delete</button>
          </div>
        </div>
      </div>
  
      <!-- No Products Message -->
      <div v-else class="no-products">
        <p>No products found. Add some products to display here.</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        products: [], // List of products fetched from the API
      };
    },
    methods: {
      async fetchProducts() {
        try {
          const response = await fetch('http://localhost:8000/api/products', {
            method: 'GET',
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`, // Optional: Include token if using authentication
            },
          });
  
          if (response.ok) {
            this.products = await response.json();
          } else {
            console.error('Failed to fetch products');
          }
        } catch (error) {
          console.error('Error fetching products:', error);
        }
      },
      editProduct(product) {
        // Redirect to the edit page with the product ID
        this.$router.push(`/edit-product/${product.id}`);
      },
      async deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
          try {
            const response = await fetch(`http://localhost:8000/api/products/${productId}`, {
              method: 'DELETE',
              headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`, // Optional: Include token if using authentication
              },
            });
  
            if (response.ok) {
              alert('Product deleted successfully');
              this.fetchProducts(); // Refresh the product list
            } else {
              alert('Failed to delete the product');
            }
          } catch (error) {
            console.error('Error deleting product:', error);
          }
        }
      },
    },
    mounted() {
      this.fetchProducts();
    },
  };
  </script>
  
  <style scoped>
  .manage-products-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 1.5rem;
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  .page-title {
    font-size: 1.8rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 1.5rem;
    color: #333;
  }
  
  .products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  
  .product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    background-color: #f9f9f9;
    text-align: center;
  }
  
  .product-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 1rem;
  }
  
  .product-info {
    margin-bottom: 1rem;
  }
  
  .product-name {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
  }
  
  .product-description {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
  }
  
  .product-price {
    font-size: 1rem;
    font-weight: bold;
    color: #333;
  }
  
  .product-actions {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
  }
  
  .edit-btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .edit-btn:hover {
    background-color: #45a049;
  }
  
  .delete-btn {
    background-color: #CA3E3E;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .delete-btn:hover {
    background-color: #B83232;
  }
  
  .no-products {
    text-align: center;
    margin-top: 2rem;
    font-size: 1.2rem;
    color: #666;
  }
  </style>
  
<template>
    <div class="manage-products-container">
      <h1 class="page-title">Admin - Manage All Products</h1>
  
      <!-- Filter by scrapseller (user_id) -->
      <div class="filter-by-user">
        <label for="scrapsellerId">Scrapseller (User) ID:</label>
        <input
          id="scrapsellerId"
          type="number"
          v-model="filterUserId"
          placeholder="Enter user ID to filter"
        />
        <button @click="fetchProducts">Filter</button>
        <button @click="clearFilter">Clear Filter</button>
      </div>
  
      <!-- Display Products -->
      <div v-if="products.length" class="products-grid">
        <div v-for="product in products" :key="product.id" class="product-card">
          <!-- Product Image -->
          <img
            v-if="product.image"
            :src="product.image"
            alt="Product Image"
            class="product-image"
          />
          <div v-else class="no-image">No Image</div>
  
          <!-- Product Info -->
          <div class="product-info">
            <h2 class="product-name">{{ product.name }}</h2>
            <p class="product-description">{{ product.description }}</p>
            <p class="product-price">Price: ${{ product.price }}</p>
            <p class="product-user">
              Owned by User #{{ product.user_id }}
              ({{ product.user_name || 'Unknown' }})
            </p>
          </div>
  
          <!-- Actions: Edit & Delete -->
          <div class="product-actions">
            <button
              v-if="editingProduct && editingProduct.id === product.id"
              class="edit-btn"
              @click="cancelEdit"
            >
              Close
            </button>
            <button
              v-else
              class="edit-btn"
              @click="startEdit(product)"
            >
              Edit
            </button>
  
            <button class="delete-btn" @click="deleteProduct(product)">
              Delete
            </button>
          </div>
  
          <!-- Inline Edit Form -->
          <div
            v-if="editingProduct && editingProduct.id === product.id"
            class="inline-edit-form"
          >
            <h3>Edit Product</h3>
            <form @submit.prevent="updateProduct">
              <div class="form-group">
                <label>Name:</label>
                <input type="text" v-model="editingProduct.name" />
              </div>
              <div class="form-group">
                <label>Description:</label>
                <textarea v-model="editingProduct.description"></textarea>
              </div>
              <div class="form-group">
                <label>Price:</label>
                <input type="number" v-model="editingProduct.price" />
              </div>
              <button type="submit" class="save-changes">Save Changes</button>
            </form>
          </div>
        </div>
      </div>
  
      <!-- No Products Message -->
      <div v-else class="no-products">
        <p>No products found.</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "AdminManageProducts",
    data() {
      return {
        products: [],
        editingProduct: null,
        filterUserId: "" // For optional scrapseller filtering by user ID
      };
    },
    methods: {
      // Fetch ALL products (or filtered by user_id) via admin endpoint
      async fetchProducts() {
        try {
          const token = localStorage.getItem("access_token");
          if (!token) {
            console.error("No token found. Please log in first.");
            return;
          }
  
          // Build the URL
          let url = "http://localhost:8000/api/admin/products";
          if (this.filterUserId) {
            url += `?user_id=${this.filterUserId}`;
          }
  
          const response = await fetch(url, {
            method: "GET",
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });
  
          if (response.ok) {
            this.products = await response.json();
          } else {
            console.error("Failed to fetch products");
          }
        } catch (error) {
          console.error("Error fetching products:", error);
        }
      },
  
      // Clear filter and reload
      clearFilter() {
        this.filterUserId = "";
        this.fetchProducts();
      },
  
      // Start editing (inline) for selected product
      startEdit(product) {
        this.editingProduct = { ...product };
      },
  
      // Cancel editing
      cancelEdit() {
        this.editingProduct = null;
      },
  
      // Submit updated data to the server
      async updateProduct() {
        try {
          const token = localStorage.getItem("access_token");
          if (!token) {
            alert("No token found. Please log in first.");
            return;
          }
  
          const response = await fetch(
            `http://localhost:8000/api/admin/products/${this.editingProduct.id}`,
            {
              method: "PUT",
              headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
              },
              body: JSON.stringify({
                name: this.editingProduct.name,
                description: this.editingProduct.description,
                price: this.editingProduct.price,
              }),
            }
          );
  
          if (response.ok) {
            alert("Product updated successfully!");
            this.editingProduct = null;
            this.fetchProducts(); // Reload
          } else {
            console.error("Failed to update product");
            const errorData = await response.json();
            alert(`Error: ${errorData.error || errorData.message || "Unknown"}`);
          }
        } catch (error) {
          console.error("Error updating product:", error);
        }
      },
  
      // Delete product
      async deleteProduct(product) {
        if (!confirm(`Are you sure you want to delete "${product.name}"?`)) {
          return;
        }
  
        try {
          const token = localStorage.getItem("access_token");
          if (!token) {
            alert("No token found. Please log in first.");
            return;
          }
  
          const response = await fetch(
            `http://localhost:8000/api/admin/products/${product.id}`,
            {
              method: "DELETE",
              headers: {
                Authorization: `Bearer ${token}`,
              },
            }
          );
  
          if (response.ok) {
            alert(`Product "${product.name}" deleted successfully!`);
            this.fetchProducts();
          } else {
            const error = await response.json();
            alert(`Failed to delete the product: ${error.error || error.message}`);
          }
        } catch (error) {
          console.error("Error deleting product:", error);
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
  
  .filter-by-user {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
  }
  
  .filter-by-user label {
    margin-right: 0.5rem;
  }
  
  .filter-by-user input {
    width: 100px;
    margin-right: 0.5rem;
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
  
  .no-image {
    width: 100%;
    height: 150px;
    border-radius: 8px;
    background-color: #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
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
  
  .product-user {
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.5rem;
  }
  
  .product-actions {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
  }
  
  .edit-btn {
    background-color: #4caf50;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .delete-btn {
    background-color: #ca3e3e;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .inline-edit-form {
    margin-top: 1rem;
    text-align: left;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0.8rem;
  }
  
  .inline-edit-form h3 {
    margin-top: 0;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
  }
  
  .form-group {
    margin-bottom: 0.5rem;
    display: flex;
    flex-direction: column;
  }
  
  label {
    font-size: 0.9rem;
    margin-bottom: 0.2rem;
    color: #555;
  }
  
  input[type="text"],
  input[type="number"],
  textarea {
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  
  .save-changes {
    margin-top: 1rem;
    background-color: #007bff;
    border: none;
    color: #fff;
    padding: 0.6rem 1rem;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .save-changes:hover {
    background-color: #0069d9;
  }
  
  .no-products {
    text-align: center;
    font-size: 1.2rem;
    color: #666;
  }
  </style>
  
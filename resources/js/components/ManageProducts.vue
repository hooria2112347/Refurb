<template>
  <div class="manage-products-container">
    <h1 class="page-title">Manage Products</h1>

    <!-- Display Products -->
    <div v-if="products.length" class="products-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <!-- Product Image -->
        <img
          :src="product.image"
          alt="Product Image"
          class="product-image"
        />

        <!-- Product Info -->
        <div class="product-info">
          <h2 class="product-name">{{ product.name }}</h2>
          <p class="product-description">{{ product.description }}</p>
          <p class="product-price">Price: ${{ product.price }}</p>
        </div>

        <!-- Actions: Edit & Delete -->
        <div class="product-actions">
          <!-- If we're editing this product, show "Close" instead of "Edit" -->
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

        <!-- Inline Edit Form for this product -->
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
      <p>No products found. Add some products to display here.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "ManageProducts",
  data() {
    return {
      products: [],
      editingProduct: null, // The product we are currently editing (inline)
    };
  },
  methods: {
    // Fetch all products for the authenticated user
    async fetchProducts() {
      try {
        // 1) Retrieve the token
        const token = localStorage.getItem("access_token");
        if (!token) {
          console.error("No token found. Please log in first.");
          return;
        }

        // 2) Send GET request with Authorization header
        const response = await fetch("http://localhost:8000/api/products", {
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

    // Show the inline form for this product
    startEdit(product) {
      // Clone the product so we don't mutate the original
      this.editingProduct = { ...product };
    },

    // Cancel editing and hide the form
    cancelEdit() {
      this.editingProduct = null;
    },

    // Send updated data to the server
    async updateProduct() {
      try {
        const token = localStorage.getItem("access_token");
        if (!token) {
          alert("No token found. Please log in first.");
          return;
        }

        const response = await fetch(
          `http://localhost:8000/api/products/${this.editingProduct.id}`,
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
          this.fetchProducts(); // Refresh list
        } else {
          console.error("Failed to update product");
          const errorData = await response.json();
          alert(`Error: ${errorData.message || "Failed to update product."}`);
        }
      } catch (error) {
        console.error("Error updating product:", error);
      }
    },

    // Delete product with a simple JS confirm
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
          `http://localhost:8000/api/products/${product.id}`,
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
          alert(`Failed to delete the product: ${error.message}`);
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
/* Same CSS as before */
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
  transition: background-color 0.3s;
}

.edit-btn:hover {
  background-color: #45a049;
}

.delete-btn {
  background-color: #ca3e3e;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.delete-btn:hover {
  background-color: #b83232;
}

.no-products {
  text-align: center;
  font-size: 1.2rem;
  color: #666;
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
</style>

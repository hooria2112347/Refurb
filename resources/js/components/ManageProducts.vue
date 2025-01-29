<template>
  <div class="manage-products-container">
    <h1 class="page-title">Manage Products</h1>

    <!-- Display Products -->
    <div v-if="products.length" class="products-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        
        <!-- Make Image Clickable -->
        <router-link 
          :to="{ name: 'product-details', params: { id: product.id } }" 
          class="product-link"
        >
          <img
            :src="product.image"
            alt="Product Image"
            class="product-image"
          />
        </router-link>

        <!-- Product Info -->
        <div class="product-info">
          <!-- Make Product Name Clickable -->
          <router-link 
            :to="{ name: 'product-details', params: { id: product.id } }" 
            class="product-name-link"
          >
            <h2 class="product-name">{{ product.name }}</h2>
          </router-link>
          <p class="product-description">{{ product.description }}</p>
          <p class="product-price">Price: PKR{{ product.price }}</p>
        </div>

        <!-- Actions: Edit & Delete -->
        <div class="product-actions">
          <button
            class="edit-btn"
            @click.stop="startEdit(product)"
          >
            Edit
          </button>

          <button 
            class="delete-btn"
            @click.stop="confirmDelete(product)"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- No Products Message -->
    <div v-else class="no-products">
      <p>No products found. Add some products to display here.</p>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-content">
        <h2>Edit Product</h2>
        
        <form @submit.prevent="updateProduct">
          <div class="form-group">
            <label>Name:</label>
            <input
              type="text"
              v-model="editingProduct.name"
            />
          </div>

          <div class="form-group">
            <label>Description:</label>
            <textarea v-model="editingProduct.description"></textarea>
          </div>

          <div class="form-group">
            <label>Price:</label>
            <input
              type="number"
              v-model="editingProduct.price"
            />
          </div>

          <div class="modal-buttons">
            <button type="submit" class="save-changes">Save Changes</button>
            <button type="button" class="cancel-btn" @click="closeEditModal">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal-content">
        <h2>Delete Product</h2>
        <p>
          Are you sure you want to delete 
          <strong>{{ productToDelete?.name }}</strong>?
        </p>

        <div class="modal-buttons">
          <button class="delete-confirm-btn" @click="deleteProduct">
            Yes, Delete
          </button>
          <button class="cancel-btn" @click="closeDeleteModal">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- MESSAGE MODAL (for showing success messages) -->
    <div v-if="showMessageModal" class="modal-overlay" @click.self="closeMessageModal">
      <div class="modal-content">
        <h2>Notification</h2>
        <p>{{ messageText }}</p>
        <div class="modal-buttons">
          <button class="ok-btn" @click="closeMessageModal">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ManageProducts",
  data() {
    return {
      products: [],
      editingProduct: null,
      productToDelete: null,

      // Modal controls
      showEditModal: false,
      showDeleteModal: false,
      showMessageModal: false,

      // Text for the success message modal
      messageText: "",
    };
  },
  methods: {
    // Fetch all products for the authenticated user
    async fetchProducts() {
      try {
        const token = localStorage.getItem("access_token");
        if (!token) {
          console.error("No token found. Please log in first.");
          return;
        }

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

    // Edit action
    startEdit(product) {
      this.editingProduct = { ...product }; // clone the object
      this.showEditModal = true;
    },
    closeEditModal() {
      this.showEditModal = false;
      this.editingProduct = null;
    },

    // Update product on server
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
          // Close modal, clear data
          this.closeEditModal();

          // Refresh product list
          this.fetchProducts();

          // Show success message
          this.showMessage("Product updated successfully!");
        } else {
          console.error("Failed to update product");
          const errorData = await response.json();
          alert(`Error: ${errorData.message || "Failed to update product."}`);
        }
      } catch (error) {
        console.error("Error updating product:", error);
      }
    },

    // Delete action
    confirmDelete(product) {
      this.productToDelete = product;
      this.showDeleteModal = true;
    },
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.productToDelete = null;
    },

    // Delete product on server
    async deleteProduct() {
      if (!this.productToDelete) return;

      // Capture the product name before closing the modal
      const deletedProductName = this.productToDelete.name;

      try {
        const token = localStorage.getItem("access_token");
        if (!token) {
          alert("No token found. Please log in first.");
          return;
        }

        const response = await fetch(
          `http://localhost:8000/api/products/${this.productToDelete.id}`,
          {
            method: "DELETE",
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.ok) {
          // Refresh the list
          this.fetchProducts();

          // Close the delete modal
          this.closeDeleteModal();

          // Show success message in a pop-up
          this.showMessage(`Product "${deletedProductName}" deleted successfully!`);
        } else {
          const error = await response.json();
          alert(`Failed to delete the product: ${error.message}`);
        }
      } catch (error) {
        console.error("Error deleting product:", error);
      }
    },

    // Show success message in a pop-up modal
    showMessage(message) {
      this.messageText = message;
      this.showMessageModal = true;
    },

    // Close the success message modal
    closeMessageModal() {
      this.showMessageModal = false;
      this.messageText = "";
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
  color: #3B1E54;
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
  position: relative;
}

.product-link {
  display: block;
  text-decoration: none;
  color: inherit;
}

.product-name-link {
  text-decoration: none;
  color: inherit;
}

.product-name-link:hover .product-name {
  text-decoration: underline;
}

.product-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1rem;
  cursor: pointer;
}

.product-info {
  margin-bottom: 1rem;
}

.product-name {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  cursor: pointer;
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
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.edit-btn:hover {
  background-color: #EEEEEE;
}

.delete-btn {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.delete-btn:hover {
  background-color: #EEEEEE;
}

.no-products {
  text-align: center;
  font-size: 1.2rem;
  color: #666;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-content {
  background-color: #fff;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
  border-radius: 8px;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  text-align: left;
}

.modal-content h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #3B1E54;
}

.form-group {
  margin-bottom: 1rem;
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

/* Buttons inside modal footer */
.modal-buttons {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

/* Shared styles for modal buttons */
.save-changes,
.delete-confirm-btn,
.cancel-btn,
.ok-btn {
  padding: 0.6rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  border: none;
  color: #3B1E54;
}

.save-changes {
  background-color: #D4BEE4;
}

.save-changes:hover {
  background-color: #EEEEEE;
}

.delete-confirm-btn {
  background-color: #D4BEE4;
}

.delete-confirm-btn:hover {
  background-color: #EEEEEE;
}

.cancel-btn {
  background-color: #e9f4fd;
}

.cancel-btn:hover {
  background-color: #d5bdbd;
}

.ok-btn {
  background-color: #D4BEE4;
}

.ok-btn:hover {
  background-color: #EEEEEE;
}
</style>

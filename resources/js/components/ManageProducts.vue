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

    <!-- Edit Modal -->
    <div v-if="isEditing" class="modal-overlay">
      <div class="modal">
        <h2 class="modal-title">Edit Product</h2>
        <form @submit.prevent="updateProduct" class="modal-form">
          <div class="modal-form-group">
            <label for="name" class="modal-label">Name:</label>
            <input type="text" id="name" v-model="editingProduct.name" class="modal-input" />
          </div>
          <div class="modal-form-group">
            <label for="description" class="modal-label">Description:</label>
            <textarea id="description" v-model="editingProduct.description" class="modal-input"></textarea>
          </div>
          <div class="modal-form-group">
            <label for="price" class="modal-label">Price:</label>
            <input type="number" id="price" v-model="editingProduct.price" class="modal-input" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="cancelEdit" class="modal-cancel-btn">Cancel</button>
            <button type="submit" class="modal-save-btn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      products: [], // List of products fetched from the API
      isEditing: false, // Whether the edit modal is open
      editingProduct: null, // The product being edited
    };
  },
  methods: {
    async fetchProducts() {
      try {
        const response = await fetch('http://localhost:8000/api/products', {
          method: 'GET',
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
      this.editingProduct = { ...product }; // Clone the product to avoid modifying the original
      this.isEditing = true;
    },
    async updateProduct() {
      try {
        const response = await fetch(`http://localhost:8000/api/products/${this.editingProduct.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(this.editingProduct),
        });

        if (response.ok) {
          alert('Product updated successfully');
          this.isEditing = false;
          this.fetchProducts(); // Refresh the product list
        } else {
          console.error('Failed to update product');
        }
      } catch (error) {
        console.error('Error updating product:', error);
      }
    },
    async deleteProduct(productId) {
      if (!productId) {
        console.error('Invalid product ID');
        return;
      }

      if (confirm('Are you sure you want to delete this product?')) {
        try {
          const response = await fetch(`http://localhost:8000/api/products/${productId}`, {
            method: 'DELETE',
          });

          if (response.ok) {
            alert('Product deleted successfully');
            this.fetchProducts(); // Refresh the product list
          } else {
            const error = await response.json();
            alert(`Failed to delete the product: ${error.message}`);
          }
        } catch (error) {
          console.error('Error deleting product:', error);
        }
      }
    },
    cancelEdit() {
      this.isEditing = false;
      this.editingProduct = null;
    },
  },
  mounted() {
    this.fetchProducts();
  },
};
</script>


<style scoped>
/* General Styles */
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

/* Product Grid */
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

/* Product Actions */
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

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: #fff;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  width: 500px;
  max-width: 90%;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: bold;
  text-align: center;
  margin-bottom: 1.5rem;
  color: #333;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.modal-form-group {
  display: flex;
  flex-direction: column;
}

.modal-label {
  font-size: 1rem;
  font-weight: bold;
  color: #555;
  margin-bottom: 0.5rem;
}

.modal-input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.3s;
}

.modal-input:focus {
  border-color: #4CAF50;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.modal-actions {
  display: flex;
  justify-content: space-between;
}

.modal-cancel-btn {
  background: #ddd;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.modal-cancel-btn:hover {
  background: #bbb;
}

.modal-save-btn {
  background: #4CAF50;
  color: #fff;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.modal-save-btn:hover {
  background: #45a049;
}
</style>

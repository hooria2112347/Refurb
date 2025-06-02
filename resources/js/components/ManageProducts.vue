
<template>
  <div class="manage-products-container">
    <!-- Page Header -->
    <div class="header-section">
      <h1 class="page-title">
        <i class="icon">🛍️</i>
        Manage Products
      </h1>
      <p class="page-subtitle">View and manage your product listings</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading your products...</p>
    </div>

    <!-- Products Grid -->
    <div v-else-if="products.length > 0" class="products-grid">
      <div 
        v-for="product in products" 
        :key="product.id"
        class="product-card"
      >
        <div class="product-image">
          <img 
            v-if="product.image" 
            :src="product.image" 
            :alt="product.name"
            @error="handleImageError"
          >
          <div v-else class="no-image">
            <span>📷</span>
          </div>
        </div>
        
        <div class="product-details">
          <h3 class="product-name">{{ product.name }}</h3>
          <p class="product-description">{{ truncateText(product.description, 100) }}</p>
          <div class="product-price">PKR {{ product.price }}</div>
          <div class="product-category" v-if="getCategoryName(product.category_id)">
            <i class="category-icon">📂</i>
            {{ getCategoryName(product.category_id) }}
          </div>
        </div>

        <div class="product-actions">
          <button @click="editProduct(product)" class="edit-btn">
            <span class="btn-icon">✏️</span>
            Edit
          </button>
          <button @click="deleteProduct(product.id)" class="delete-btn">
            <span class="btn-icon">🗑️</span>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>No Products Yet</h3>
      <p>Your product inventory is currently empty.</p>
      <button @click="$router.push('/add-product')" class="add-first-product-btn">
        <i class="btn-icon">➕</i>
        Add Your First Product
      </button>
    </div>

    <!-- Edit Product Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="modal-icon">✏️</i>
            Edit Product
          </h3>
          <button @click="closeModal" class="close-button">×</button>
        </div>
        
        <form @submit.prevent="submitProduct" class="product-form">
          <div class="form-grid">
            <!-- Left Column -->
            <div class="form-column">
              <div class="section-card">
                <h4 class="section-title">Basic Information</h4>
                
                <div class="form-group">
                  <label for="edit-name" class="label">
                    <i class="label-icon">🏷️</i>
                    Product Name
                    <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="edit-name"
                    v-model="productForm.name"
                    class="input-field"
                    required
                    placeholder="Enter product name..."
                  >
                </div>

                <div class="form-group">
                  <label for="edit-price" class="label">
                    <i class="label-icon">💰</i>
                    Price
                    <span class="required">*</span>
                  </label>
                  <div class="price-input-wrapper">
                    <span class="currency-symbol">PKR </span>
                    <input 
                      type="number" 
                      id="edit-price"
                      v-model="productForm.price"
                      class="input-field price-input"
                      required
                      min="0"
                      step="0.01"
                      placeholder=" 0"
                    >
                  </div>
                </div>

                <div class="form-group">
                  <label for="edit-category" class="label">
                    <i class="label-icon">📂</i>
                    Category
                    <span class="required">*</span>
                  </label>
                  <div class="select-wrapper">
                    <select 
                      id="edit-category"
                      v-model="productForm.category_id"
                      class="input-field select-field"
                      required
                    >
                      <option value="">Choose a category</option>
                      <option 
                        v-for="category in categories" 
                        :key="category.category_id"
                        :value="category.category_id"
                      >
                        {{ category.name }}
                      </option>
                    </select>
                    <i class="select-arrow">▼</i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
              <div class="section-card">
                <h4 class="section-title">Description & Additional Info</h4>
                
                <div class="form-group">
                  <label for="edit-description" class="label">
                    <i class="label-icon">📄</i>
                    Product Description
                    <span class="required">*</span>
                  </label>
                  <textarea 
                    id="edit-description"
                    v-model="productForm.description"
                    class="input-field textarea-field"
                    required
                    rows="5"
                    placeholder="Describe your product in detail..."
                  ></textarea>
                </div>

                <div class="form-group">
                  <label for="edit-additional-info" class="label">
                    <i class="label-icon">📝</i>
                    Additional Information
                  </label>
                  <textarea 
                    id="edit-additional-info"
                    v-model="productForm.additional_info"
                    class="input-field textarea-field"
                    rows="3"
                    placeholder="Any extra details about your product..."
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="cancel-btn">
              <i class="btn-icon">❌</i>
              Cancel
            </button>
            <button type="submit" :disabled="submitting" class="submit-btn">
              <i class="btn-icon">{{ submitting ? '⏳' : '✅' }}</i>
              {{ submitting ? 'Updating...' : 'Update Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="cancelDelete">
      <div class="modal-content delete-modal" @click.stop>
        <div class="modal-header delete-header">
          <h3 class="modal-title">
            <i class="modal-icon">⚠️</i>
            Confirm Delete
          </h3>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button @click="cancelDelete" class="cancel-btn">
            <i class="btn-icon">❌</i>
            Cancel
          </button>
          <button @click="confirmDelete" :disabled="deleting" class="delete-confirm-btn">
            <i class="btn-icon">{{ deleting ? '⏳' : '🗑️' }}</i>
            {{ deleting ? 'Deleting...' : 'Delete Product' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ManageProducts',
  data() {
    return {
      products: [],
      categories: [
        { category_id: 1, name: 'Aluminum' },
        { category_id: 2, name: 'Copper' },
        { category_id: 3, name: 'Brass' },
        { category_id: 4, name: 'Steel' },
        { category_id: 5, name: 'Iron' },
        { category_id: 6, name: 'Lead' },
        { category_id: 7, name: 'Other' },
      ],
      loading: true,
      showEditModal: false,
      showDeleteModal: false,
      submitting: false,
      deleting: false,
      productToDelete: null,
      editingProduct: null,
      productForm: {
        name: '',
        description: '',
        price: '',
        category_id: '',
        additional_info: ''
      }
    };
  },

  mounted() {
    this.fetchProducts();
  },

  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('access_token') || 
                    this.$store?.state?.token || 
                    sessionStorage.getItem('access_token');
      
      return token ? {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      } : {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      };
    },

    async fetchProducts() {
      try {
        const response = await axios.get('/api/products', {
          headers: this.getAuthHeaders()
        });
        this.products = response.data;
      } catch (error) {
        console.error('Error fetching products:', error);
        if (error.response?.status === 401) {
          this.$router.push('/login');
        }
      } finally {
        this.loading = false;
      }
    },

    getCategoryName(categoryId) {
      const category = this.categories.find(cat => cat.category_id === categoryId);
      return category ? category.name : '';
    },

    // Replace your existing submitProduct method with this:
async submitProduct() {
  this.submitting = true;
  
  try {
    // Create regular JSON payload instead of FormData since we're not handling file uploads here
    const payload = {
      name: this.productForm.name,
      description: this.productForm.description,
      price: parseFloat(this.productForm.price),
      category_id: parseInt(this.productForm.category_id),
      additional_info: this.productForm.additional_info || ''
    };

    const headers = this.getAuthHeaders();

    // Use PUT method directly instead of POST with override
    await axios.put(`/api/products/${this.editingProduct.id}`, payload, {
      headers: headers
    });
    
    this.showToast('Product updated successfully!', 'success');
    this.closeModal();
    await this.fetchProducts(); // Wait for fetch to complete
  } catch (error) {
    console.error('Error submitting product:', error);
    
    // Better error handling
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors;
      const errorMessage = Object.values(errors).flat().join(', ');
      this.showToast(`Validation failed: ${errorMessage}`, 'error');
    } else if (error.response?.status === 404) {
      this.showToast('Product not found or you don\'t have permission to edit it.', 'error');
    } else {
      this.showToast('Failed to save product. Please try again.', 'error');
    }
  } finally {
    this.submitting = false;
  }
},

// Also update your editProduct method to ensure proper data handling:
editProduct(product) {
  this.editingProduct = product;
  this.productForm = {
    name: product.name || '',
    description: product.description || '',
    price: product.price ? product.price.toString() : '',
    category_id: product.category_id ? product.category_id.toString() : '',
    additional_info: product.additional_info || ''
  };
  this.showEditModal = true;
},

// Update fetchProducts to handle potential data issues:
async fetchProducts() {
  try {
    this.loading = true;
    const response = await axios.get('/api/products', {
      headers: this.getAuthHeaders()
    });
    
    // Ensure products is always an array
    this.products = Array.isArray(response.data) ? response.data : [];
    
  } catch (error) {
    console.error('Error fetching products:', error);
    if (error.response?.status === 401) {
      this.$router.push('/login');
    } else {
      this.showToast('Failed to load products. Please refresh the page.', 'error');
    }
    this.products = []; // Fallback to empty array
  } finally {
    this.loading = false;
  }
},

    deleteProduct(productId) {
      this.productToDelete = productId;
      this.showDeleteModal = true;
    },

    async confirmDelete() {
      this.deleting = true;
      
      try {
        await axios.delete(`/api/products/${this.productToDelete}`, {
          headers: this.getAuthHeaders()
        });
        
        this.showToast('Product deleted successfully!', 'success');
        this.fetchProducts();
        this.cancelDelete();
      } catch (error) {
        console.error('Error deleting product:', error);
        this.showToast('Failed to delete product. Please try again.', 'error');
      } finally {
        this.deleting = false;
      }
    },

    cancelDelete() {
      this.showDeleteModal = false;
      this.productToDelete = null;
    },

    closeModal() {
      this.showEditModal = false;
      this.editingProduct = null;
      this.resetForm();
    },

    resetForm() {
      this.productForm = {
        name: '',
        description: '',
        price: '',
        category_id: '',
        additional_info: ''
      };
    },

    handleImageError(event) {
      event.target.style.display = 'none';
      event.target.parentElement.innerHTML = '<div class="no-image"><span>📷</span></div>';
    },

    truncateText(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    showToast(message, type = 'info') {
      if (this.$toast) {
        this.$toast[type](message);
      } else {
        alert(message);
      }
    }
  }
};
</script>

<style scoped>
/* Container & Layout - Matching Add Product */
.manage-products-container {
  min-height: 100vh;
  padding: 2rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.header-section {
  text-align: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #3B1E54;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.icon {
  font-size: 2rem;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #6c757d;
  margin: 0;
}

/* Loading States */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  color: #6c757d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #D4BEE4;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.product-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 15px 35px rgba(59, 30, 84, 0.08);
  transition: all 0.3s ease;
  border: 1px solid #e0e0e0;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 25px 50px rgba(59, 30, 84, 0.15);
}

.product-image {
  height: 200px;
  overflow: hidden;
  position: relative;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #fafafa 0%, #f8f9ff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #D4BEE4;
}

.product-details {
  padding: 1.5rem;
}

.product-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #3B1E54;
  margin: 0 0 0.5rem 0;
}

.product-description {
  color: #6c757d;
  line-height: 1.5;
  margin: 0 0 1rem 0;
  font-size: 0.9rem;
}

.product-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #3B1E54;
  margin-bottom: 0.5rem;
}

.product-category {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.9rem;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  width: fit-content;
}

.category-icon {
  font-size: 0.8rem;
}

.product-actions {
  padding: 1rem 1.5rem 1.5rem;
  display: flex;
  gap: 1rem;
  border-top: 1px solid #f1f5f9;
}

.edit-btn, .delete-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.edit-btn {
  background: linear-gradient(135deg, #D4BEE4 0%, #c4a8d8 100%);
  color: #3B1E54;
}

.edit-btn:hover {
  background: linear-gradient(135deg, #c4a8d8 0%, #b494cc 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(212, 190, 228, 0.3);
}

.delete-btn {
  background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
  color: white;
}

.delete-btn:hover {
  background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(229, 62, 62, 0.3);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6c757d;
  max-width: 500px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  color: #D4BEE4;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin: 0 0 0.75rem 0;
  color: #3B1E54;
  font-weight: 600;
}

.empty-state p {
  margin: 0 0 2rem 0;
  font-size: 1.1rem;
}

.add-first-product-btn {
  background: linear-gradient(135deg, #D4BEE4 0%, #c4a8d8 100%);
  color: #3B1E54;
  border: none;
  padding: 1rem 2rem;
  border-radius: 15px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 auto;
}

.add-first-product-btn:hover {
  background: linear-gradient(135deg, #c4a8d8 0%, #b494cc 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(212, 190, 228, 0.3);
}

/* Modal Styles - Matching Add Product */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(59, 30, 84, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  backdrop-filter: blur(5px);
}

.modal-content {
  background: white;
  border-radius: 20px;
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px rgba(59, 30, 84, 0.3);
  transform: scale(0.9);
  animation: modalAppear 0.3s ease forwards;
}

@keyframes modalAppear {
  to {
    transform: scale(1);
  }
}

.delete-modal {
  max-width: 450px;
}

.modal-header {
  background: linear-gradient(135deg, #D4BEE4 0%, #c4a8d8 100%);
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 20px 20px 0 0;
}

.delete-header {
  background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
}

.modal-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #3B1E54;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.delete-header .modal-title {
  color: white;
}

.modal-icon {
  font-size: 1.3rem;
}

.close-button {
  background: rgba(59, 30, 84, 0.1);
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #3B1E54;
  padding: 0.5rem;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.delete-header .close-button {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.close-button:hover {
  background: rgba(59, 30, 84, 0.2);
  transform: scale(1.1);
}

.delete-header .close-button:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Product Form - Matching Add Product */
.product-form {
  padding: 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.form-column {
  display: flex;
  flex-direction: column;
}

.section-card {
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
  border-radius: 15px;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
  height: fit-content;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #3B1E54;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #D4BEE4;
}

.form-group {
  margin-bottom: 1.5rem;
}

.label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #3B1E54;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
}

.label-icon {
  font-size: 1rem;
}

.required {
  color: #e53e3e;
  font-weight: 700;
}

.input-field {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
  font-family: inherit;
}

.input-field:focus {
  outline: none;
  border-color: #D4BEE4;
  box-shadow: 0 0 0 3px rgba(212, 190, 228, 0.1);
  transform: translateY(-2px);
}

.price-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-symbol {
  position: absolute;
  left: 1rem;
  color: #6c757d;
  font-weight: 600;
  z-index: 1;
}

.price-input {
  padding-left: 2.5rem;
}

.select-wrapper {
  position: relative;
}

.select-field {
  appearance: none;
  background: white;
  cursor: pointer;
  padding-right: 3rem;
}

.select-arrow {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  pointer-events: none;
  font-size: 0.8rem;
}

.textarea-field {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.modal-footer, .modal-body {
  padding: 0 2rem 2rem;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 2rem;
  border-top: 2px solid #e9ecef;
}

.cancel-btn, .submit-btn, .delete-confirm-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-btn {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  color: #6c757d;
  border: 2px solid #dee2e6;
}

.cancel-btn:hover {
  background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
  transform: translateY(-2px);
}

.submit-btn {
  background: linear-gradient(135deg, #D4BEE4 0%, #c4a8d8 100%);
  color: #3B1E54;
}

.submit-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #c4a8d8 0%, #b494cc 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(212, 190, 228, 0.3);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.delete-confirm-btn {
  background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
  color: white;
}

.delete-confirm-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(229, 62, 62, 0.3);
}

.delete-confirm-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .manage-products-container {
    padding: 1rem;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .modal-overlay {
    padding: 10px;
  }
  
  .page-title {
    font-size: 2rem;
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .cancel-btn, .submit-btn, .delete-confirm-btn {
    width: 100%;
    justify-content: center;
  }
}
/* Responsive Design */
@media (max-width: 768px) {
  .page-header {
    padding: 30px 20px;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .modal-overlay {
    padding: 10px;
  }
  
  .page-title {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .manage-products {
    padding: 10px;
  }
  
  .page-header {
    margin-bottom: 20px;
    padding: 25px 15px;
  }
  
  .modal-content {
    margin: 0;
  }
}
</style>
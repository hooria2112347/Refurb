<template>
  <div class="add-product-container">
    <div class="header-section">
      <h1 class="form-title">
        <i class="icon"></i>
        Add New Product
      </h1>
      <p class="form-subtitle">Fill in the details below to add your product to the marketplace</p>
    </div>

    <!-- Server-side errors displayed in a card -->
    <div v-if="serverErrors.length" class="error-card">
      <div class="error-header">
        <i class="error-icon">⚠️</i>
        <span>Please fix the following errors:</span>
      </div>
      <ul class="error-list">
        <li v-for="(error, index) in serverErrors" :key="index">{{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="submitProduct" class="form-container">
      <div class="form-grid">
        <!-- Left Column -->
        <div class="form-column">
          <div class="section-card">
            <h3 class="section-title">Basic Information</h3>
            
            <!-- Product Name -->
            <div class="form-group">
              <label for="name" class="label">
                <i class="label-icon">🏷️</i>
                Product Name
                <span class="required">*</span>
              </label>
              <input 
                type="text" 
                id="name" 
                v-model="product.name" 
                class="input-field"
                :class="{ 'input-error': clientErrors.name }"
                placeholder="Enter product name..."
              />
              <span v-if="clientErrors.name" class="inline-error">
                {{ clientErrors.name }}
              </span>
            </div>

            <!-- Product Price -->
            <div class="form-group">
              <label for="price" class="label">
                <i class="label-icon">💰</i>
                Price
                <span class="required">*</span>
              </label>
              <div class="price-input-wrapper">
                <span class="currency-symbol">PKR </span>
                <input 
                  type="number" 
                  id="price" 
                  v-model="product.price" 
                  class="input-field price-input"
                  :class="{ 'input-error': clientErrors.price }"
                  placeholder=" 0"
                  min="0"
                />
              </div>
              <span v-if="clientErrors.price" class="inline-error">
                {{ clientErrors.price }}
              </span>
            </div>

            <!-- Category -->
            <div class="form-group">
              <label for="category" class="label">
                <i class="label-icon">📂</i>
                Category
                <span class="required">*</span>
              </label>
              <div class="select-wrapper">
                <select 
                  id="category" 
                  v-model="product.category_id" 
                  class="input-field select-field"
                  :class="{ 'input-error': clientErrors.category_id }"
                >
                  <option value="" disabled>Choose a category</option>
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
              <span v-if="clientErrors.category_id" class="inline-error">
                {{ clientErrors.category_id }}
              </span>
            </div>

            <!-- Additional Info -->
            <div class="form-group">
              <label for="additional-info" class="label">
                <i class="label-icon">📝</i>
                Additional Information
              </label>
              <input
                type="text"
                id="additional-info"
                v-model="product.additionalInfo"
                class="input-field"
                placeholder="Any extra details about your product..."
              />
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="form-column">
          <div class="section-card">
            <h3 class="section-title">Description & Media</h3>
            
            <!-- Product Description -->
            <div class="form-group">
              <label for="description" class="label">
                <i class="label-icon">📄</i>
                Product Description
                <span class="required">*</span>
              </label>
              <textarea
                id="description"
                v-model="product.description"
                rows="5"
                class="input-field textarea-field"
                :class="{ 'input-error': clientErrors.description }"
                placeholder="Describe your product in detail..."
              ></textarea>
              <span v-if="clientErrors.description" class="inline-error">
                {{ clientErrors.description }}
              </span>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
              <label class="label">
                <i class="label-icon">🖼️</i>
                Product Images
                <span class="required">*</span>
              </label>
              
              <div class="image-upload-area" :class="{ 'drag-over': isDragOver }" 
                   @dragover.prevent="isDragOver = true"
                   @dragleave.prevent="isDragOver = false"
                   @drop.prevent="handleDrop">
                <input
                  type="file"
                  ref="fileInput"
                  @change="onFileChange"
                  accept="image/*"
                  class="hidden-input"
                  multiple
                />
                
                <div v-if="!product.image || !product.image.length" class="upload-placeholder">
                  <div class="upload-icon">📸</div>
                  <p class="upload-text">
                    <strong>Click to upload</strong> or drag and drop images here
                  </p>
                  <p class="upload-subtext">PNG, JPG up to 20MB each</p>
                  <button type="button" class="upload-btn" @click="triggerFileInput">
                    Choose Images
                  </button>
                </div>

                <div v-else class="image-preview-grid">
                  <div v-for="(image, index) in imagePreviewUrls" :key="index" class="image-preview-item">
                    <img :src="image.url" :alt="image.name" class="preview-image" />
                    <div class="image-overlay">
                      <span class="image-name">{{ image.name }}</span>
                      <button type="button" class="remove-image-btn" @click="removeImage(index)">
                        ❌
                      </button>
                    </div>
                  </div>
                  <div class="add-more-images" @click="triggerFileInput">
                    <div class="add-icon">➕</div>
                    <span>Add More</span>
                  </div>
                </div>
              </div>

              <span v-if="clientErrors.image" class="inline-error">
                {{ clientErrors.image }}
              </span>

              <div v-if="imageSizeError" class="error-message">
                <i class="error-icon">⚠️</i>
                One or more files exceed the 20 MB size limit.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="button" class="cancel-btn" @click="cancel">
          <i class="btn-icon"></i>
          Cancel
        </button>
        <button type="submit" class="submit-btn" :disabled="isSubmitting">
          <i class="btn-icon">{{ isSubmitting ? '⏳' : '✅' }}</i>
          {{ isSubmitting ? 'Adding Product...' : 'Add Product' }}
        </button>
      </div>
    </form>

    <!-- Success Modal -->
    <div v-if="showPopup" class="modal-overlay" @click="closePopup">
      <div class="modal-content" @click.stop>
        <div class="success-icon"></div>
        <h2 class="modal-title">Success!</h2>
        <p class="modal-message">Your product has been added successfully!</p>
        <button class="modal-btn" @click="closePopup">
          Continue
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      product: {
        name: '',
        description: '',
        price: null,
        image: [],
        category_id: '',
        additionalInfo: '',
      },
      categories: [
        { category_id: 1, name: 'Aluminum' },
        { category_id: 2, name: 'Copper' },
        { category_id: 3, name: 'Brass' },
        { category_id: 4, name: 'Steel' },
        { category_id: 5, name: 'Iron' },
        { category_id: 6, name: 'Lead' },
        { category_id: 7, name: 'Other' },
      ],
      imageSizeError: false,
      showPopup: false,
      isSubmitting: false,
      isDragOver: false,
      imagePreviewUrls: [],

      // Separate client-side vs. server-side errors
      clientErrors: {},
      serverErrors: [],
    };
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },

    handleDrop(event) {
      this.isDragOver = false;
      const files = Array.from(event.dataTransfer.files);
      this.processFiles(files);
    },

    onFileChange(event) {
      const files = Array.from(event.target.files);
      this.processFiles(files);
    },

    processFiles(files) {
      const maxSize = 20 * 1024 * 1024; // 20MB
      this.imageSizeError = false;

      // Check file sizes
      const oversizedFiles = files.filter(file => file.size > maxSize);
      if (oversizedFiles.length > 0) {
        this.imageSizeError = true;
        return;
      }

      // Add new files to existing ones
      const newFiles = [...this.product.image, ...files];
      this.product.image = newFiles;

      // Create preview URLs
      this.updateImagePreviews();
    },

    updateImagePreviews() {
      // Clean up old URLs
      this.imagePreviewUrls.forEach(preview => {
        if (preview.url.startsWith('blob:')) {
          URL.revokeObjectURL(preview.url);
        }
      });

      // Create new preview URLs
      this.imagePreviewUrls = this.product.image.map(file => ({
        url: URL.createObjectURL(file),
        name: file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name
      }));
    },

    removeImage(index) {
      // Revoke the URL to prevent memory leaks
      if (this.imagePreviewUrls[index].url.startsWith('blob:')) {
        URL.revokeObjectURL(this.imagePreviewUrls[index].url);
      }
      
      // Remove from both arrays
      this.product.image.splice(index, 1);
      this.imagePreviewUrls.splice(index, 1);
    },

    cancel() {
      this.$router.push('/');
    },

    validateForm() {
      this.clientErrors = {};

      // Name
      if (!this.product.name.trim()) {
        this.clientErrors.name = "Product name is required.";
      }
      // Price
      if (this.product.price === null || this.product.price === '' || this.product.price <= 0) {
        this.clientErrors.price = "Valid price is required.";
      }
      // Category
      if (!this.product.category_id) {
        this.clientErrors.category_id = "Please select a category.";
      }
      // Description
      if (!this.product.description.trim()) {
        this.clientErrors.description = "Product description is required.";
      }
      // Image
      if (!this.product.image || !this.product.image.length) {
        this.clientErrors.image = "At least one product image is required.";
      }
    },

    async submitProduct() {
      // Clear old errors
      this.serverErrors = [];
      this.clientErrors = {};
      this.isSubmitting = true;

      // Client-side validation
      this.validateForm();
      if (Object.keys(this.clientErrors).length > 0) {
        this.isSubmitting = false;
        return;
      }

      try {
        // Create the FormData
        const formData = new FormData();
        formData.append('name', this.product.name);
        formData.append('description', this.product.description);
        formData.append('price', this.product.price);
        formData.append('category_id', this.product.category_id);
        formData.append('additional_info', this.product.additionalInfo);

        if (this.product.image.length) {
          for (const file of this.product.image) {
            formData.append('images[]', file);
          }
        }

        // Retrieve token
        const token = localStorage.getItem('access_token');
        if (!token) {
          alert('No token found. Please log in first.');
          this.isSubmitting = false;
          return;
        }

        // Make the request
        const response = await fetch('http://127.0.0.1:8000/api/products', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
          body: formData,
        });

        if (response.ok) {
          this.showPopup = true;
        } else {
          const errorData = await response.json();
          // Show server errors
          if (errorData.errors) {
            // If it's a Laravel validation error structure
            this.serverErrors = Object.values(errorData.errors).flat();
          } else {
            this.serverErrors = [errorData.error || errorData.message || 'Unknown error'];
          }
        }
      } catch (error) {
        console.error('Error submitting product:', error);
        this.serverErrors = ['Network error. Please try again.'];
      } finally {
        this.isSubmitting = false;
      }
    },

    closePopup() {
      this.showPopup = false;
      this.resetForm();
    },

    resetForm() {
      // Clean up preview URLs
      this.imagePreviewUrls.forEach(preview => {
        if (preview.url.startsWith('blob:')) {
          URL.revokeObjectURL(preview.url);
        }
      });

      this.product = {
        name: '',
        description: '',
        price: null,
        image: [],
        category_id: '',
        additionalInfo: '',
      };
      this.imagePreviewUrls = [];
      
      // Reset file input
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
      this.imageSizeError = false;
    },
  },

  beforeUnmount() {
    // Clean up any remaining blob URLs
    this.imagePreviewUrls.forEach(preview => {
      if (preview.url.startsWith('blob:')) {
        URL.revokeObjectURL(preview.url);
      }
    });
  }
};
</script>

<style scoped>
/* Container & Layout */
.add-product-container {
 
  padding: 2rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.header-section {
  text-align: center;
  margin-bottom: 2rem;
}

.form-title {
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

.form-subtitle {
  font-size: 1.1rem;
  color: #6c757d;
  margin: 0;
}

/* Error Card */
.error-card {
  background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
  border: 2px solid #fc8181;
  border-radius: 15px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 8px 25px rgba(252, 129, 129, 0.15);
}

.error-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #c53030;
  margin-bottom: 1rem;
}

.error-list {
  margin: 0;
  padding-left: 1.5rem;
  color: #c53030;
}

/* Form Structure */
.form-container {

  margin: 0 auto;
  
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 15px 35px rgba(59, 30, 84, 0.08);
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
  font-size: 1.3rem;
  font-weight: 600;
  color: #3B1E54;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #D4BEE4;
}

/* Form Groups */
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

/* Input Styles */
.input-field {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.input-field:focus {
  outline: none;
  border-color: #D4BEE4;
  box-shadow: 0 0 0 3px rgba(212, 190, 228, 0.1);
  transform: translateY(-2px);
}

.input-error {
  border-color: #e53e3e !important;
  box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
}

.inline-error {
  color: #e53e3e;
  font-size: 0.85rem;
  margin-top: 0.25rem;
  display: block;
  font-weight: 500;
}

/* Price Input */
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

/* Select Field */
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

/* Textarea */
.textarea-field {
  resize: vertical;
  min-height: 120px;
  font-family: inherit;
}

/* Image Upload */
.image-upload-area {
  border: 3px dashed #D4BEE4;
  border-radius: 15px;
  padding: 2rem;
  text-align: center;
  background: linear-gradient(135deg, #fafafa 0%, #f8f9ff 100%);
  transition: all 0.3s ease;
  cursor: pointer;
}

.image-upload-area:hover,
.drag-over {
  border-color: #3B1E54;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  transform: translateY(-2px);
}

.hidden-input {
  display: none;
}

.upload-placeholder {
  padding: 1rem;
}

.upload-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.upload-text {
  font-size: 1.1rem;
  color: #3B1E54;
  margin-bottom: 0.5rem;
}

.upload-subtext {
  color: #6c757d;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.upload-btn {
  background: linear-gradient(135deg, #D4BEE4 0%, #c4a8d8 100%);
  color: #3B1E54;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(212, 190, 228, 0.3);
}

/* Image Preview Grid */
.image-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  padding: 0;
}

.image-preview-item {
  position: relative;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.image-preview-item:hover {
  transform: translateY(-5px);
}

.preview-image {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.image-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
  color: white;
  padding: 0.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-name {
  font-size: 0.8rem;
  flex: 1;
}

.remove-image-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.8rem;
  opacity: 0.8;
  transition: opacity 0.3s ease;
}

.remove-image-btn:hover {
  opacity: 1;
}

.add-more-images {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 120px;
  border: 2px dashed #D4BEE4;
  border-radius: 10px;
  cursor: pointer;
  background: linear-gradient(135deg, #fafafa 0%, #f8f9ff 100%);
  transition: all 0.3s ease;
}

.add-more-images:hover {
  border-color: #3B1E54;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
}

.add-icon {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #D4BEE4;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #e53e3e;
  font-size: 0.9rem;
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: #fff5f5;
  border-radius: 8px;
  border: 1px solid #fed7d7;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 2rem;
  border-top: 2px solid #e9ecef;
}

.cancel-btn, .submit-btn {
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

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(59, 30, 84, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.modal-content {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 25px 50px rgba(59, 30, 84, 0.3);
  max-width: 400px;
  width: 90%;
  transform: scale(0.9);
  animation: modalAppear 0.3s ease forwards;
}

@keyframes modalAppear {
  to {
    transform: scale(1);
  }
}

.success-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.modal-title {
  font-size: 2rem;
  font-weight: 700;
  color: #3B1E54;
  margin-bottom: 1rem;
}

.modal-message {
  color: #6c757d;
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.modal-btn {
  background: linear-gradient(135deg, #D4BEE4 0%, #c4a8d8 100%);
  color: #3B1E54;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-btn:hover {
  background: linear-gradient(135deg, #c4a8d8 0%, #b494cc 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(212, 190, 228, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
  .add-product-container {
    padding: 1rem;
  }

  .form-container {
    margin: 0;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .form-title {
    font-size: 2rem;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
    justify-content: center;
  }

  .image-preview-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  }
}
</style>
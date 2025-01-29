<template>
  <div class="add-product-container">
    <h1 class="form-title">Add Product</h1>

    <!-- Server-side errors displayed in a block if any -->
    <div v-if="serverErrors.length" class="error-messages">
      <p v-for="(error, index) in serverErrors" :key="index" class="error">{{ error }}</p>
    </div>

    <form @submit.prevent="submitProduct" class="form-grid">
      <!-- Left Column -->
      <div class="form-column">

        <!-- Product Name (Required) -->
        <div class="form-group">
          <label for="name" class="label">
            Name:<span class="required">*</span>
          </label>
          <input 
            type="text" 
            id="name" 
            v-model="product.name" 
            class="input-field"
            :class="{ 'input-error': clientErrors.name }"
          />
          <!-- Inline error message -->
          <span v-if="clientErrors.name" class="inline-error">
            {{ clientErrors.name }}
          </span>
        </div>

        <!-- Product Price (Required) -->
        <div class="form-group">
          <label for="price" class="label">
            Price:<span class="required">*</span>
          </label>
          <input 
            type="number" 
            id="price" 
            v-model="product.price" 
            class="input-field"
            :class="{ 'input-error': clientErrors.price }"
          />
          <!-- Inline error message -->
          <span v-if="clientErrors.price" class="inline-error">
            {{ clientErrors.price }}
          </span>
        </div>

        <!-- Category (Required) -->
        <div class="form-group">
          <label for="category" class="label">
            Category:<span class="required">*</span>
          </label>
          <select 
            id="category" 
            v-model="product.category_id" 
            class="input-field"
            :class="{ 'input-error': clientErrors.category_id }"
          >
            <option value="" disabled>Select a category</option>
            <option
              v-for="category in categories"
              :key="category.category_id"
              :value="category.category_id"
            >
              {{ category.name }}
            </option>
          </select>
          <!-- Inline error message -->
          <span v-if="clientErrors.category_id" class="inline-error">
            {{ clientErrors.category_id }}
          </span>
        </div>

        <!-- Additional Info (Optional) -->
        <div class="form-group">
          <label for="additional-info" class="label">Additional Info:</label>
          <input
            type="text"
            id="additional-info"
            v-model="product.additionalInfo"
            class="input-field"
            placeholder="Enter additional details..."
          />
        </div>
      </div>

      <!-- Right Column -->
      <div class="form-column">
        <!-- Product Description (Required) -->
        <div class="form-group">
          <label for="description" class="label">
            Description:<span class="required">*</span>
          </label>
          <textarea
            id="description"
            v-model="product.description"
            rows="5"
            class="input-field"
            :class="{ 'input-error': clientErrors.description }"
          ></textarea>
          <!-- Inline error message -->
          <span v-if="clientErrors.description" class="inline-error">
            {{ clientErrors.description }}
          </span>
        </div>

        <!-- Image Upload (Required) -->
        <div class="form-group">
          <label for="image" class="label">
            Upload Images:<span class="required">*</span>
          </label>
          <div class="image-upload-button">
            <input
              type="file"
              id="image"
              ref="fileInput"
              @change="onFileChange"
              accept="image/*"
              class="hidden-input"
              multiple
            />
            <button type="button" class="upload-btn" @click="triggerFileInput">
              Choose Files
            </button>
            <div class="image-preview">
              <span v-if="product.image && product.image.length">
                {{ product.image.length }} Images Selected
              </span>
              <span v-else>No Files Chosen</span>
            </div>
          </div>
          <!-- Inline error message -->
          <span v-if="clientErrors.image" class="inline-error">
            {{ clientErrors.image }}
          </span>

          <!-- Oversize error message -->
          <div v-if="imageSizeError" class="error-message">
            One or more files exceed the 20 MB size limit.
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="button" class="cancel-btn" @click="cancel">Cancel</button>
        <button type="submit" class="submit-btn">Add Product</button>
      </div>
    </form>

    <!-- Success Popup -->
    <div v-if="showPopup" class="popup-overlay">
      <div class="popup-content">
        <h2>Product Added Successfully!</h2>
        <p>Your product has been added.</p>
        <button class="close-popup-btn" @click="closePopup">Close</button>
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
      ],
      imageSizeError: false,
      showPopup: false,

      // Separate client-side vs. server-side errors
      clientErrors: {},
      serverErrors: [],
    };
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    onFileChange(event) {
      const files = Array.from(event.target.files);
      const maxSize = 20 * 1024 * 1024; // 20MB

      this.imageSizeError = false;

      files.forEach(file => {
        if (file.size > maxSize) {
          this.imageSizeError = true;
        }
      });

      if (!this.imageSizeError) {
        this.product.image = files;
      } else {
        alert('One or more files exceed the 20 MB size limit.');
      }
    },
    cancel() {
      this.$router.push('/');
    },

    validateForm() {
      this.clientErrors = {};

      // Name
      if (!this.product.name.trim()) {
        this.clientErrors.name = "Name is required.";
      }
      // Price
      if (this.product.price === null || this.product.price === '') {
        this.clientErrors.price = "Price is required.";
      }
      // Category
      if (!this.product.category_id) {
        this.clientErrors.category_id = "Category is required.";
      }
      // Description
      if (!this.product.description.trim()) {
        this.clientErrors.description = "Description is required.";
      }
      // Image
      if (!this.product.image || !this.product.image.length) {
        this.clientErrors.image = "At least one image is required.";
      }
    },

    async submitProduct() {
      // Clear old errors
      this.serverErrors = [];
      this.clientErrors = {};

      // Client-side validation
      this.validateForm();
      if (Object.keys(this.clientErrors).length > 0) {
        // If there are validation errors, don't submit
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
      }
    },
    closePopup() {
      this.showPopup = false;
      this.resetForm();
    },
    resetForm() {
      this.product = {
        name: '',
        description: '',
        price: null,
        image: [],
        category_id: '',
        additionalInfo: '',
      };
      // Reset file input
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
      this.imageSizeError = false;
    },
  },
};
</script>

<style scoped>
/* Container Styles */
.add-product-container {
  max-width: 900px;
  margin: 1rem auto;
  padding: 1.5rem;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Title Styles */
.form-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  text-align: center;
  color: #3B1E54;
}

/* SERVER ERRORS BLOCK */
.error-messages {
  margin-bottom: 1rem;
  color: red;
  background-color: #fdecec;
  padding: 1rem;
  border-radius: 8px;
  font-size: 0.95em;
}

.error {
  margin: 5px 0;
}

/* Grid Layout */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Form Column Styles */
.form-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Form Group Styles */
.form-group {
  display: flex;
  flex-direction: column;
}

.label {
  font-weight: bold;
  margin-bottom: 0.25rem;
  color: #3B1E54;
}

.required {
  color: #ff4d4f;
  margin-left: 5px;
}

.input-field {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.9rem;
}

.input-field:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

/* Inline error styling */
.inline-error {
  color: red;
  font-size: 0.85rem;
  margin-top: 4px;
}

.input-error {
  border-color: red !important;
}

/* Image Upload Styles */
.image-upload-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.hidden-input {
  display: none;
}

.upload-btn {
  padding: 0.5rem 1rem;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.upload-btn:hover {
  background-color: #EEEEEE;
}

.image-preview {
  color: #555;
  font-size: 0.85rem;
}

.error-message {
  color: red;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

/* Action Buttons */
.form-actions {
  grid-column: span 2;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

.cancel-btn {
  background: #f9f9f9;
  color: #333;
  border: 1px solid #ccc;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cancel-btn:hover {
  background-color: #ddd;
}

.submit-btn {
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-btn:hover {
  background-color: #EEEEEE;
}

/* Popup Styles */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.popup-content {
  background-color: #fff;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  width: 90%;
}

.popup-content h2 {
  margin-bottom: 1rem;
  font-size: 1.5rem;
  color: #333;
}

.popup-content p {
  margin-bottom: 1.5rem;
  color: #555;
}

.close-popup-btn {
  padding: 0.5rem 1rem;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.close-popup-btn:hover {
  background-color: #EEEEEE;
}
</style>

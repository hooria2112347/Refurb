<template>
  <div class="add-product-container">
    <!-- Username Display -->
    <div class="username-display">
      Logged in as: {{ username }}
    </div>

    <!-- Form Title -->
    <h1 class="form-title">Add Product</h1>

    <!-- Product Form -->
    <form @submit.prevent="submitProduct" class="form-grid">
      <!-- Left Column -->
      <div class="form-column">
        <!-- Product Name -->
        <div class="form-group">
          <input
            type="text"
            id="name"
            v-model="product.name"
            class="input-field"
            placeholder="Product Name"
            required
          />
        </div>

        <!-- Price and Category Container -->
        <div class="price-category-container">
          <!-- Product Price -->
          <div class="form-group small-input">
            <input
              type="number"
              id="price"
              v-model="product.price"
              class="input-field"
              placeholder="Price"
              required
            />
          </div>

          <!-- Category -->
          <div class="form-group small-input">
            <select
              id="category"
              v-model="product.category_id"
              class="input-field"
              required
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
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="form-column">
        <!-- Product Description -->
        <div class="form-group">
          <textarea
            id="description"
            v-model="product.description"
            rows="5"
            class="input-field"
            placeholder="Product Description"
            required
          ></textarea>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
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
          </div>

          <!-- Image Previews -->
          <div class="image-preview">
            <div v-if="product.image.length">
              <div
                v-for="(img, index) in imagePreviews"
                :key="index"
                class="image-thumbnail"
              >
                <img :src="img" alt="Selected Image" />
              </div>
            </div>
            <div v-else>No Files Chosen</div>
          </div>

          <!-- Error Message -->
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
        <p>Your product has been added to the database.</p>
        <button class="close-popup-btn" @click="closePopup">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    // Assuming username is passed as a prop
    username: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      product: {
        name: '',
        description: '',
        price: null,
        image: [],
        category_id: '',
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
      imagePreviews: [], // Array to hold image preview URLs
    };
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    onFileChange(event) {
      const files = Array.from(event.target.files);
      const maxSize = 20 * 1024 * 1024; // 20 MB

      this.imageSizeError = false;
      this.imagePreviews = [];

      for (const file of files) {
        if (file.size > maxSize) {
          this.imageSizeError = true;
          break;
        }
      }

      if (!this.imageSizeError) {
        this.product.image = files;

        // Generate image previews
        this.imagePreviews = files.map(file => URL.createObjectURL(file));
      } else {
        alert('One or more files exceed the 20 MB size limit.');
        this.product.image = [];
      }
    },
    cancel() {
      this.$router.push('/');
    },
    async submitProduct() {
      const formData = new FormData();
      formData.append('name', this.product.name);
      formData.append('description', this.product.description);
      formData.append('price', this.product.price);
      formData.append('category_id', this.product.category_id);

      if (this.product.image.length) {
        for (const file of this.product.image) {
          formData.append('images[]', file);
        }
      }

      const token = localStorage.getItem('access_token');

      try {
        const response = await fetch('http://127.0.0.1:8000/api/products', {
          method: 'POST',
          body: formData,
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (response.ok) {
          this.showPopup = true;
          // Reset the form
          this.resetForm();
        } else {
          const error = await response.json();
          alert(`Error: ${error.error || error.message}`);
        }
      } catch (error) {
        console.error('Error submitting product:', error);
        alert('An unexpected error occurred.');
      }
    },
    closePopup() {
      this.showPopup = false;
      this.$router.push('/');
    },
    resetForm() {
      this.product = {
        name: '',
        description: '',
        price: null,
        image: [],
        category_id: '',
      };
      this.imagePreviews.forEach(url => URL.revokeObjectURL(url));
      this.imagePreviews = [];
    },
  },
};
</script>

<style scoped>
/* Container Styles */
.add-product-container {
  /* max-width: 900px; */
  margin: 40px ;
  padding: 1.5rem;
  background-color: #ffffff;
  /* border: 1px solid #ddd; */
  /* border-radius: 8px; */
  /* box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
  font-family: Arial, sans-serif;
}

/* Username Display */
.username-display {
  text-align: right;
  font-size: 0.9rem;
  color: #555;
  margin-bottom: 0.5rem;
}

/* Title Styles */
.form-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  text-align: center;
  color: #333;
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

/* Price and Category Container */
.price-category-container {
  display: flex;
  gap: 1rem;
}

/* Smaller Input Fields */
.small-input {
  flex: 1;
}

/* Input Field Styles */
.input-field {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.9rem;
}

.input-field:focus {
  outline: none;
  border-color: #4caf50;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
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
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.upload-btn:hover {
  background-color: #45a049;
}

.image-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.image-thumbnail {
  width: 100px;
  height: 100px;
  overflow: hidden;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.image-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
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
  padding: 12px 25px;
  background-color: #ff6b6b;
  color: #fff;
  border: none;
  border-radius: 15px;
  cursor: pointer;
  font-size: 1em;
  transition: background-color 0.3s;
}

.submit-btn:hover {
  background-color: #e55a5a;
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
  background-color: #4caf50;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.close-popup-btn:hover {
  background-color: #45a049;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .price-category-container {
    flex-direction: column;
  }

  .small-input {
    width: 100%;
  }

  .form-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .submit-btn {
    width: 100%;
  }

  .cancel-btn {
    width: 100%;
  }
}
</style>

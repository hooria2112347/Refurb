<template>
  <div class="add-product-container">
    <h1 class="form-title">Add Product</h1>
    <form @submit.prevent="submitProduct" class="form-grid">
      <!-- Left Column -->
      <div class="form-column">
        <!-- Product Name -->
        <div class="form-group">
          <label for="name" class="label">Name:</label>
          <input type="text" id="name" v-model="product.name" class="input-field" required />
        </div>

        <!-- Product Price -->
        <div class="form-group">
          <label for="price" class="label">Price:</label>
          <input type="number" id="price" v-model="product.price" class="input-field" required />
        </div>

        <!-- Category -->
        <div class="form-group">
          <label for="category" class="label">Category:</label>
          <select id="category" v-model="product.category_id" class="input-field" required>
            <option value="" disabled>Select a category</option>
            <option v-for="category in categories" :key="category.category_id" :value="category.category_id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Additional Info -->
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
        <!-- Product Description -->
        <div class="form-group">
          <label for="description" class="label">Description:</label>
          <textarea
            id="description"
            v-model="product.description"
            rows="5"
            class="input-field"
            required
          ></textarea>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
          <label for="image" class="label">Upload Images:</label>
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
            <button type="button" class="upload-btn" @click="triggerFileInput">Choose Files</button>
            <div class="image-preview">
              <span v-if="product.image && product.image.length">{{ product.image.length }} Images Selected</span>
              <span v-else>No Files Chosen</span>
            </div>
            <div v-if="imageSizeError" class="error-message">
              One or more files exceed the 20 MB size limit.
            </div>
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
    };
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    onFileChange(event) {
      const files = Array.from(event.target.files);
      const maxSize = 20 * 1024 * 1024;

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
    async submitProduct() {
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

    const token = localStorage.getItem('access_token'); 

    try {
        const response = await fetch('http://127.0.0.1:8000/api/products', {
            method: 'POST',
            body: formData,
            headers: {
                'Authorization': `Bearer ${token}`, 
            },
        });

        if (response.ok) {
            this.showPopup = true;
        } else {
            const error = await response.json();
            alert(`Error: ${error.error || error.message}`);
        }
    } catch (error) {
        console.error('Error submitting product:', error);
        alert('An unexpected error occurred.');
    }
}

,
    closePopup() {
      this.showPopup = false;
      this.$router.push('/');
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
  font-family: Arial, sans-serif;
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

.label {
  font-weight: bold;
  margin-bottom: 0.25rem;
  color: #555;
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
  background-color: #4CAF50;
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
  grid-column: span 2;
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
  background-color: #45a049;
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
  background-color: #4CAF50;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.close-popup-btn:hover {
  background-color: #45a049;
}
</style>

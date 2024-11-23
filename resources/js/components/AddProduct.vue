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
          <select id="category" v-model="product.category" class="input-field" required>
            <option value="" disabled>Select a category</option>
            <option v-for="category in categories" :key="category.id" :value="category.name">
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
              @change="onFileChange"
              accept="image/*"
              required
              class="hidden-input"
              multiple
            />
            <button type="button" class="upload-btn">Choose Files</button>
            <div class="image-preview">
              <span v-if="product.image">Images Selected</span>
              <span v-else>No Files Chosen</span>
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
        image: null,
        category: '',
        additionalInfo: '',
      },
      categories: [
        { id: 1, name: 'Aluminum' },
        { id: 2, name: 'Copper' },
        { id: 3, name: 'Brass' },
        { id: 4, name: 'Steel' },
        { id: 5, name: 'Iron' },
        { id: 6, name: 'Lead' },
      ],
    };
  },
  methods: {
    onFileChange(event) {
      this.product.image = event.target.files;
    },
    cancel() {
      this.$router.push('/'); // Redirect to home or previous page
    },
    async submitProduct() {
      const formData = new FormData();
      formData.append('name', this.product.name);
      formData.append('description', this.product.description);
      formData.append('price', this.product.price);
      formData.append('category', this.product.category);
      for (let i = 0; i < this.product.image.length; i++) {
        formData.append('images[]', this.product.image[i]);
      }

      try {
        const response = await fetch('http://localhost:8000/api/products', {
          method: 'POST',
          body: formData,
        });

        if (response.ok) {
          alert('Product added successfully!');
          this.$router.push('/');
        } else {
          const error = await response.json();
          alert(`Error: ${error.message}`);
        }
      } catch (error) {
        console.error('Error submitting product:', error);
      }
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
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-btn:hover {
  background-color: #45a049;
}
</style>

<template>
  <div class="custom-request-form">
    <h1>Create Custom Request</h1>
    <form @submit.prevent="submitForm">
      <div v-if="errors.length" class="error-messages">
        <p v-for="(error, index) in errors" :key="index" class="error">{{ error }}</p>
      </div>

      <div class="form-grid">
        <!-- Description (Required) -->
        <div class="form-group">
          <label for="description">Description:<span class="required">*</span></label>
          <textarea id="description" v-model="form.description" required></textarea>
        </div>

        <!-- Preferred Materials -->
        <div class="form-group">
          <label for="materials">Preferred Materials:</label>
          <input id="materials" v-model="form.materials" type="text">
        </div>

        <!-- Dimensions -->
        <div class="form-group">
          <label for="dimensions">Dimensions:</label>
          <input id="dimensions" v-model="form.dimensions" type="text">
        </div>

        <!-- Style Preferences -->
        <div class="form-group">
          <label for="style_preferences">Style Preferences:</label>
          <input id="style_preferences" v-model="form.style_preferences" type="text">
        </div>

        <!-- Budget (Required) -->
        <div class="form-group">
          <label for="budget">Budget (In PKR):<span class="required">*</span></label>
          <input
            id="budget"
            v-model.number="form.budget"
            type="number"
            min="0"
            step="100"
            placeholder="Enter your budget"
          >
        </div>

        <!-- Deadline -->
        <div class="form-group">
          <label for="deadline">Deadline:</label>
          <input id="deadline" v-model="form.deadline" type="date">
        </div>

        <!-- Preferred Artist Expertise -->
        <div class="form-group">
          <label for="artist_expertise">Preferred Artist Expertise:</label>
          <input id="artist_expertise" v-model="form.artist_expertise" type="text">
        </div>

        <!-- Upload Images (Required) -->
        <div class="form-group">
          <label for="images">Upload Images:<span class="required">*</span></label>
          <input
            id="images"
            type="file"
            multiple
            @change="handleFiles"
            ref="fileInput"
            accept="image/*"
          />
          <div class="image-previews">
            <div v-for="(image, index) in imagePreviews" :key="index" class="image-preview">
              <img :src="image" alt="Image Preview" />
              <button type="button" @click="removeImage(index)">×</button>
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="submit-button">Submit Request</button>
    </form>

    <!-- SUCCESS MODAL (replacing alert) -->
    <div v-if="showSuccessModal" class="modal-overlay" @click.self="closeSuccessModal">
      <div class="modal-content">
        <h2>Notification</h2>
        <p>{{ successMessage }}</p>
        <div class="modal-buttons">
          <button class="ok-btn" @click="closeSuccessModal">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      form: {
        description: "",
        materials: "",
        dimensions: "",
        style_preferences: "",
        budget: null,
        deadline: "",
        artist_expertise: "",
        images: [],
      },
      errors: [], // To store validation errors
      imagePreviews: [], // To store image preview URLs

      // ========== NEW MODAL DATA PROPERTIES ==========
      showSuccessModal: false,
      successMessage: "",
    };
  },
  methods: {
    handleFiles(event) {
      const files = event.target.files;
      if (files.length > 5) {
        alert("You can only upload a maximum of 5 images.");
        return; // Exit if there are more than 5 files
      }

      if (files.length > 0) {
        this.form.images = Array.from(files);
        this.imagePreviews = [];

        Array.from(files).forEach((file) => {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.imagePreviews.push(e.target.result);
          };
          reader.readAsDataURL(file);
        });
      }
    },
    removeImage(index) {
      this.form.images.splice(index, 1);
      this.imagePreviews.splice(index, 1);

      // Update the file input value to reflect the removed files
      const dt = new DataTransfer();
      this.form.images.forEach((file) => {
        dt.items.add(file);
      });
      this.$refs.fileInput.files = dt.files;
    },
    async submitForm() {
      // Clear previous errors
      this.errors = [];

      // Validate required fields
      if (!this.form.description.trim()) {
        this.errors.push("Description is required.");
      }

      if (this.form.budget === null || this.form.budget === "") {
        this.errors.push("Budget is required.");
      } else if (this.form.budget < 0) {
        this.errors.push("Budget cannot be negative.");
      }

      // Require at least one image
      if (this.form.images.length === 0) {
        this.errors.push("At least one image is required.");
      }

      if (this.errors.length) {
        return; // Exit if there are validation errors
      }

      const formData = new FormData();
      for (const key in this.form) {
        if (key === "images") {
          this.form.images.forEach((file) => {
            formData.append("images[]", file); // This sends each image file in the array
          });
        } else {
          formData.append(key, this.form[key]);
        }
      }

      try {
        const response = await axios.post("/api/custom-requests", formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        // Original line:
        // alert(response.data.message); // Display success message
        // Replaced with a modal:
        this.successMessage = response.data.message;
        this.showSuccessModal = true;

        this.resetForm(); // Reset form on success
      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errors = Object.values(error.response.data.errors).flat();
        } else {
          console.error("Unexpected error:", error);
          alert("An unexpected error occurred.");
        }
      }
    },
    resetForm() {
      this.form = {
        description: "",
        materials: "",
        dimensions: "",
        style_preferences: "",
        budget: null,
        deadline: "",
        artist_expertise: "",
        images: [],
      };
      this.imagePreviews = [];
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },

    // ========== NEW MODAL CLOSE METHOD ==========
    closeSuccessModal() {
      this.showSuccessModal = false;
    },
  },
};
</script>

<style scoped>
/* Container Styles */
.custom-request-form {
  max-width: 900px;
  margin: 1rem auto;
  padding: 1.5rem;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Title Styles */
.custom-request-form h1 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  text-align: center;
  color: #3B1E54;
}

/* Grid Layout */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Form Group Styles */
.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 0.25rem;
  color: #3B1E54;
}

.required {
  color: #ff4d4f;
  margin-left: 5px;
}

.form-group input,
.form-group textarea {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.9rem;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

/* ERROR MESSAGE BOX */
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

/* IMAGE PREVIEWS SECTION */
.image-previews {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 15px;
}

.image-preview {
  position: relative;
  width: 90px;
  height: 90px;
  border: 1px solid #e4e4e4;
  border-radius: 10px;
  overflow: hidden;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-preview button {
  position: absolute;
  top: 4px;
  right: 4px;
  background-color: rgba(255, 255, 255, 0.8);
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  font-weight: bold;
  color: #ff4d4f;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-preview button:hover {
  background-color: rgba(255, 255, 255, 1);
}

/* Submit Button */
.submit-button {
  grid-column: span 2;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
  padding: 0.5rem 1rem;
}

.submit-button:hover {
  background-color: #EEEEEE;
}

/* ================== MODAL STYLES ================== */
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

.modal-buttons {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.ok-btn {
  padding: 0.6rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  border: none;
  color: #3B1E54;
  background-color: #D4BEE4;
}

.ok-btn:hover {
  opacity: 0.9;
}

/* Responsive Tweaks */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>

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
            min="0"  step="100"
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

        <!-- Upload Images -->
        <div class="form-group">
          <label for="images">Upload Images:</label>
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
}
,
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
    alert(response.data.message); // Display success message
    this.resetForm(); // Reset form on success
  } catch (error) {
    if (error.response && error.response.data.errors) {
      this.errors = Object.values(error.response.data.errors).flat();
    } else {
      console.error("Unexpected error:", error);
      alert("An unexpected error occurred.");
    }
  }
}
,
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
  },
};
</script>
<style scoped>
.custom-request-form {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  background-color: #fdfdfd;
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.custom-request-form h1 {
  text-align: center;
  color: #333;
  margin-bottom: 20px;
  font-size: 1.8em;
}

.error-messages {
  margin-bottom: 15px;
  color: #ff4d4f;
  background-color: #ffe6e6;
  padding: 10px;
  border-radius: 10px;
}

.error {
  margin: 5px 0;
  font-size: 0.9em;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 5px;
  font-weight: bold;
  color: #555;
  display: flex;
  align-items: center;
}

.required {
  color: #ff4d4f;
  margin-left: 5px;
}

.form-group input,
.form-group textarea {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 0.95em;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: #007bff;
  outline: none;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.image-previews {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.image-preview {
  position: relative;
  width: 80px;
  height: 80px;
  border: 1px solid #ddd;
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
  top: 2px;
  right: 2px;
  background-color: rgba(255, 255, 255, 0.7);
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
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

.submit-button {
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

.submit-button:hover {
  background-color: #218838;
}

/* Responsive Design */
@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .submit-button {
    grid-column: span 1;
  }
}
</style>

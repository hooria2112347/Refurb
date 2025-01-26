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
/* MAIN FORM WRAPPER */
.custom-request-form {
  max-width: 720px;
  margin: 40px auto;
  padding: 25px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

/* HEADER STYLING */
.custom-request-form h1 {
  text-align: center;
  color: #3B1E54;
  margin-bottom: 25px;
  font-size: 1.8em;
  font-weight: bold;
}

/* ERROR MESSAGE BOX */
.error-messages {
  margin-bottom: 20px;
  color: #b00020;
  background-color: #fdecec;
  padding: 15px;
  border-radius: 10px;
  font-size: 0.95em;
}

.error {
  margin: 5px 0;
}

/* FORM GRID LAYOUT */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

/* FORM GROUP STYLING */
.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 8px;
  font-weight: 600;
  color: #3B1E54;
}

.required {
  color: #ff4d4f;
  margin-left: 5px;
}

.form-group input,
.form-group textarea {
  padding: 12px 15px;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 0.95em;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: #5d9b8b;
  outline: none;
}

/* TEXTAREA STYLING */
.form-group textarea {
  resize: vertical;
  min-height: 100px;
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

/* SUBMIT BUTTON STYLING */
.submit-button {
  grid-column: span 2;
  padding: 14px 20px;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1em;
  font-weight: bold;
  transition: background-color 0.3s;
}

.submit-button:hover {
  background-color: #EEEEEE;
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .submit-button {
    grid-column: span 1;
  }
}
</style>

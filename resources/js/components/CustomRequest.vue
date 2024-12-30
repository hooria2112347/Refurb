<template>
    <div class="custom-request-form">
      <h1>Create Custom Request</h1>
      <form @submit.prevent="submitForm">
        <div v-if="errors.length" class="error-messages">
          <p v-for="(error, index) in errors" :key="index" class="error">{{ error }}</p>
        </div>
  
        <label for="description">Description:</label>
        <textarea id="description" v-model="form.description" required></textarea>
  
        <label for="materials">Preferred Materials:</label>
        <input id="materials" v-model="form.materials" type="text">
  
        <label for="dimensions">Dimensions:</label>
        <input id="dimensions" v-model="form.dimensions" type="text">
  
        <label for="style_preferences">Style Preferences:</label>
        <input id="style_preferences" v-model="form.style_preferences" type="text">
  
        <label for="budget">Budget:</label>
        <input id="budget" v-model="form.budget" type="number" step="0.01">
  
        <label for="deadline">Deadline:</label>
        <input id="deadline" v-model="form.deadline" type="date">
  
        <label for="artist_expertise">Preferred Artist Expertise:</label>
        <input id="artist_expertise" v-model="form.artist_expertise" type="text">
  
        <label for="images">Upload Images:</label>
        <input id="images" type="file" multiple @change="handleFiles" />
  
        <button type="submit">Submit Request</button>
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
      };
    },
    methods: {
      handleFiles(event) {
        this.form.images = event.target.files;
      },
      async submitForm() {
        // Clear previous errors
        this.errors = [];
  
        // Validate required fields
        if (!this.form.description) {
          this.errors.push("Description is required.");
        }
        if (!this.form.deadline) {
          this.errors.push("Deadline is required.");
        }
  
        if (this.errors.length) {
          return; // Exit if there are validation errors
        }
  
        const formData = new FormData();
        for (const key in this.form) {
          if (key === "images") {
            Array.from(this.form.images).forEach((file) => {
              formData.append("images[]", file);
            });
          } else {
            formData.append(key, this.form[key]);
          }
        }
  
        try {
          const response = await axios.post("/api/custom-requests", formData, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`,
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
        // this.$refs.fileInput.value = ""; // Clear file input
      },
    },
  };
  </script>
  
  <style scoped>
  .custom-request-form {
    max-width: 600px;
    margin: auto;
  }
  .custom-request-form label {
    display: block;
    margin: 10px 0 5px;
  }
  .custom-request-form input,
  .custom-request-form textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 15px;
  }
  .custom-request-form button {
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  .custom-request-form button:hover {
    background-color: #0056b3;
  }
  .error-messages {
    margin-bottom: 15px;
    color: red;
  }
  .error {
    margin: 0;
  }
  </style>
  
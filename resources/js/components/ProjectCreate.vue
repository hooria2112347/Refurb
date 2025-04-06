<template>
  <div class="project-create-form">
    <h1>Create a New Collaborative Project</h1>

    <!-- Server-side errors displayed if any -->
    <div v-if="serverErrors.length" class="error-messages">
      <p v-for="(error, index) in serverErrors" :key="index" class="error">{{ error }}</p>
    </div>

    <!-- Success Alert/Toast -->
    <div v-if="showSuccessAlert" class="success-alert">
      <div class="success-content">
        <span class="success-icon">✓</span>
        <p>Project successfully created!</p>
      </div>
    </div>

    <form @submit.prevent="createProject">
      <div class="form-group">
        <label for="title">
          Project Title<span class="required">*</span>
        </label>
        <input 
          id="title" 
          v-model="form.title" 
          :class="{ 'input-error': clientErrors.title }"
          placeholder="Enter a descriptive title..."
        />
        <span v-if="clientErrors.title" class="inline-error">
          {{ clientErrors.title }}
        </span>
      </div>

      <div class="form-group">
        <label for="description">
          Project Description<span class="required">*</span>
        </label>
        <textarea
          id="description"
          v-model="form.description"
          :class="{ 'input-error': clientErrors.description }"
          placeholder="Brief overview or objectives..."
        ></textarea>
        <span v-if="clientErrors.description" class="inline-error">
          {{ clientErrors.description }}
        </span>
      </div>

      <div class="form-group">
        <label for="required_roles">
          Required Roles<span class="required">*</span>
        </label>
        <input 
          id="required_roles" 
          v-model="form.required_roles"
          @input="filterNonAlphabetic('required_roles')"
          :class="{ 'input-error': clientErrors.required_roles }"
          placeholder="Example: Designer, Copywriter..."
        />
        <span v-if="clientErrors.required_roles" class="inline-error">
          {{ clientErrors.required_roles }}
        </span>
      </div>

      <div class="form-group">
        <label for="skills_required">
          Skills Required<span class="required">*</span>
        </label>
        <input 
          id="skills_required" 
          v-model="form.skills_required"
          @input="filterNonAlphabetic('skills_required')"
          :class="{ 'input-error': clientErrors.skills_required }"
          placeholder="e.g. UX Design, Photoshop..."
        />
        <span v-if="clientErrors.skills_required" class="inline-error">
          {{ clientErrors.skills_required }}
        </span>
      </div>

      <div class="form-group">
        <label for="deadline">
          Deadline<span class="required">*</span>
        </label>
        <input 
          id="deadline"
          type="date"
          v-model="form.deadline"
          :class="{ 'input-error': clientErrors.deadline }"
        />
        <span v-if="clientErrors.deadline" class="inline-error">
          {{ clientErrors.deadline }}
        </span>
      </div>

      <div class="form-group">
        <label for="budget">
          Budget<span class="required">*</span>
        </label>
        <input 
          id="budget"
          type="number"
          v-model="form.budget"
          :class="{ 'input-error': clientErrors.budget }"
          placeholder="e.g. 500"
        />
        <span v-if="clientErrors.budget" class="inline-error">
          {{ clientErrors.budget }}
        </span>
      </div>

      <button type="submit">Create Project</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ProjectCreate',
  data() {
    return {
      form: {
        title: '',
        description: '',
        required_roles: '',
        skills_required: '',
        deadline: '',
        budget: '',
      },
      clientErrors: {},
      serverErrors: [],
      showSuccessAlert: false,
    };
  },
  methods: {
    filterNonAlphabetic(field) {
      // Replace any non-alphabetic characters (except spaces, commas, and hyphens)
      this.form[field] = this.form[field].replace(/[^a-zA-Z\s,\-]/g, '');
    },
    validateForm() {
      this.clientErrors = {};

      if (!this.form.title.trim()) {
        this.clientErrors.title = 'Project title is required.';
      }
      
      if (!this.form.description.trim()) {
        this.clientErrors.description = 'Project description is required.';
      }
      
      if (!this.form.required_roles.trim()) {
        this.clientErrors.required_roles = 'Required roles field is required.';
      } else if (/\d/.test(this.form.required_roles)) {
        this.clientErrors.required_roles = 'Required roles should not contain numbers.';
      }
      
      if (!this.form.skills_required.trim()) {
        this.clientErrors.skills_required = 'Skills required field is required.';
      } else if (/\d/.test(this.form.skills_required)) {
        this.clientErrors.skills_required = 'Skills required should not contain numbers.';
      }
      
      if (!this.form.deadline) {
        this.clientErrors.deadline = 'Deadline is required.';
      }
      
      if (this.form.budget === '') {
        this.clientErrors.budget = 'Budget is required.';
      } else if (isNaN(this.form.budget) || /[a-zA-Z]/.test(this.form.budget)) {
        this.clientErrors.budget = 'Budget must be a numeric value.';
      }
    },
    async createProject() {
      // Clear out old errors
      this.serverErrors = [];
      this.clientErrors = {};

      // Client-side validation
      this.validateForm();
      if (Object.keys(this.clientErrors).length > 0) {
        return;
      }

      try {
        const res = await axios.post('/api/projects', this.form);
        
        // Show success alert
        this.showSuccessAlert = true;
        
        // Wait a moment before redirecting (give the alert time to be seen)
        setTimeout(() => {
          // On success, redirect to the new project's detail page
          this.$router.push(`/projects/${res.data.project.id}`);
        }, 1500); // Redirect after 1.5 seconds
        
      } catch (error) {
        console.error(error);
        // Server-side validation or error response
        if (error.response && error.response.data.errors) {
          this.serverErrors = Object.values(error.response.data.errors).flat();
        } else {
          this.serverErrors = [error.message || 'An error occurred'];
        }
      }
    }
  }
};
</script>

<style scoped>
/* WRAPPER */
.project-create-form {
  max-width: 600px;
  margin: 40px auto;
  padding: 25px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  position: relative; /* For absolute positioning of alert */
}

/* HEADING */
.project-create-form > h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3B1E54;
  margin-bottom: 25px;
  font-weight: 600;
}

/* SUCCESS ALERT */
.success-alert {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 0;
  z-index: 1000;
  animation: slideIn 0.3s ease-out;
}

.success-content {
  display: flex;
  align-items: center;
  background-color: #5d9b8b;
  color: white;
  padding: 16px 25px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.success-icon {
  font-size: 20px;
  margin-right: 12px;
  font-weight: bold;
}

.success-content p {
  margin: 0;
  font-size: 1rem;
  font-weight: 500;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
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

/* FORM GROUP */
.form-group {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
}

/* LABEL */
.form-group label {
  margin-bottom: 6px;
  font-size: 1rem;
  font-weight: 500;
  color: #3B1E54;
}

.required {
  color: #ff4d4f;
  margin-left: 5px;
}

/* INPUT AND TEXTAREA */
.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px;
  font-size: 0.95rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #fafafa;
  transition: border-color 0.3s ease, background-color 0.3s ease;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #aaa;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: #5d9b8b;
  outline: none;
  background-color: #fff;
}

.input-error {
  border-color: red !important;
}

/* INLINE ERROR */
.inline-error {
  color: red;
  font-size: 0.85rem;
  margin-top: 4px;
}

/* BUTTON */
button[type="submit"] {
  width: 100%;
  padding: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  background-color: #D4BEE4;
  color: #3B1E54;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: opacity 0.3s ease;
  margin-top: 10px;
}

button[type="submit"]:hover {
  opacity: 0.9;
}

/* RESPONSIVE TWEAKS */
@media (max-width: 480px) {
  .project-create-form {
    margin: 20px;
    padding: 15px;
  }

  .project-create-form > h1 {
    font-size: 1.4rem;
    margin-bottom: 20px;
  }
  
  .success-alert {
    right: 10px;
    left: 10px;
    width: auto;
  }
}
</style>
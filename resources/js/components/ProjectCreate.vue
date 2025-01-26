<template>
  <div class="project-create-form">
    <h1>Create a New Collaborative Project</h1>
    <form @submit.prevent="createProject">
      <div class="form-group">
        <label for="title">Project Title</label>
        <input 
          id="title" 
          v-model="form.title" 
          required 
          placeholder="Enter a descriptive title..."
        />
      </div>

      <div class="form-group">
        <label for="description">Project Description</label>
        <textarea
          id="description"
          v-model="form.description"
          placeholder="Brief overview or objectives..."
        ></textarea>
      </div>

      <div class="form-group">
        <label for="required_roles">Required Roles</label>
        <input 
          id="required_roles" 
          v-model="form.required_roles"
          required
          placeholder="Example: Designer, Copywriter..."
        />
      </div>

      <div class="form-group">
        <label for="skills_required">Skills Required</label>
        <input 
          id="skills_required" 
          v-model="form.skills_required" 
          required
          placeholder="e.g. UX Design, Photoshop..."
        />
      </div>

      <div class="form-group">
        <label for="deadline">Deadline</label>
        <input 
          id="deadline"
          type="date"
          v-model="form.deadline"
          required
        />
      </div>

      <div class="form-group">
        <label for="budget">Budget (Optional)</label>
        <input 
          id="budget"
          type="number"
          v-model="form.budget"
          placeholder="e.g. 500"
        />
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
        budget: null
      }
    };
  },
  methods: {
    async createProject() {
      try {
        const res = await axios.post('/api/projects', this.form);
        // Redirect to the new project's detail page
        this.$router.push(`/projects/${res.data.project.id}`);
      } catch (error) {
        console.error(error);
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
}

/* HEADING */
.project-create-form > h1 {
  text-align: center;
  font-size: 1.8rem;
  color: #3B1E54;
  margin-bottom: 25px;
  font-weight: 600;
}

/* FORM GROUP */
.form-group {
  margin-bottom: 20px;
}

/* LABEL */
.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 1rem;
  font-weight: 500;
  color: #3B1E54;
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

.form-group textarea {
  resize: vertical;
  min-height: 100px;
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
}
</style>

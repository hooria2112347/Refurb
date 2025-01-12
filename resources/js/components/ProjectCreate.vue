<template>
  <div class="project-create-form">
    <h1>Create Collaborative Project</h1>
    <form @submit.prevent="createProject">
      <div>
        <label for="title">Project Title</label>
        <input id="title" v-model="form.title" required />
      </div>

      <div>
        <label for="description">Description</label>
        <textarea id="description" v-model="form.description"></textarea>
      </div>

      <button type="submit">Post Project</button>
    </form>
  </div>
</template>

  
  <script>
  import axios from 'axios';
  
  export default {
    name: "ProjectCreate",
    data() {
      return {
        form: {
          title: '',
          description: ''
        }
      };
    },
    methods: {
      async createProject() {
        try {
          const res = await axios.post('/api/projects', this.form);
          // on success, redirect or show success message
          this.$router.push(`/projects/${res.data.project.id}`);
        } catch (error) {
          console.error(error);
        }
      }
    }
  };
  </script>
  
  <style scoped>
/* MAIN FORM WRAPPER */
.project-create-form {
  max-width: 720px;
  margin: 40px auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  font-family: 'Poppins', sans-serif;
}

/* FORM HEADER */
.project-create-form h1 {
  text-align: center;
  font-size: 2rem;
  color: #3C552D;
  margin-bottom: 30px;
  font-weight: bold;
}

/* FORM GROUP */
.project-create-form div {
  margin-bottom: 20px;
}

.project-create-form label {
  display: block;
  font-size: 1rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: bold;
}

/* INPUT AND TEXTAREA FIELDS */
.project-create-form input,
.project-create-form textarea {
  width: 100%;
  padding: 12px 15px;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: border-color 0.3s ease;
}

.project-create-form input:focus,
.project-create-form textarea:focus {
  border-color: #5d9b8b;
  outline: none;
  background-color: #ffffff;
}

.project-create-form textarea {
  resize: vertical;
  min-height: 120px;
}

/* SUBMIT BUTTON */
.project-create-form button {
  display: block;
  width: 100%;
  padding: 12px 20px;
  font-size: 1.2rem;
  font-weight: bold;
  background-color: #CA7373;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.project-create-form button:hover {
  background-color: #D7B26D;
}

/* RESPONSIVE DESIGN */
@media screen and (max-width: 768px) {
  .project-create-form {
    padding: 15px;
  }

  .project-create-form h1 {
    font-size: 1.8rem;
  }

  .project-create-form button {
    font-size: 1rem;
    padding: 10px 16px;
  }
}
</style>

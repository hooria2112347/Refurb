<template>
    <div>
      <h1>Create Collaborative Project</h1>
      <form @submit.prevent="createProject">
        <div>
          <label>Project Title</label>
          <input v-model="form.title" required />
        </div>
  
        <div>
          <label>Description</label>
          <textarea v-model="form.description"></textarea>
        </div>
  
        <!-- Add fields for roles, deadlines, images if needed -->
  
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
  
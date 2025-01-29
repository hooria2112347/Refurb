<template>
    <div>
      <h1>Collaborative Projects</h1>
      <div v-if="projects.length === 0">
        <p>No collaborative projects found.</p>
      </div>
      <div v-else>
        <div v-for="project in projects" :key="project.id">
          <h2>{{ project.title }}</h2>
          <p>{{ project.description }}</p>
          <router-link :to="`/projects/${project.id}`">View Details</router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: "ProjectList",
    data() {
      return {
        projects: [],
        searchTerm: ''
      };
    },
    async created() {
      await this.fetchProjects();
    },
    methods: {
      async fetchProjects() {
        try {
          // If you have an API endpoint for searching, pass query params
          const res = await axios.get('/api/projects', { params: { q: this.searchTerm }});
          this.projects = res.data;
        } catch (error) {
          console.error(error);
        }
      }
    }
  };
  </script>
  
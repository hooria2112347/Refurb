<template>
    <div>
      <h1>My Projects</h1>
  
      <div v-if="allProjects.length === 0">
        <p>You have no active or completed projects yet.</p>
      </div>
      
      <div v-else>
        <div 
          v-for="project in allProjects" 
          :key="project.id" 
          class="project-item"
        >
          <h2>{{ project.title }} <small>({{ project.status }})</small></h2>
          <p>{{ project.description }}</p>
          <router-link :to="`/projects/${project.id}`">View Project</router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: "MyProjects",
    data() {
      return {
        allProjects: []
      };
    },
    async created() {
      const session = localStorage.getItem('userSession');
      if (!session) {
        return this.$router.push('/login');
      }
      const userData = JSON.parse(session);
  
      try {
        // 1) Fetch projects this user OWNS
        const ownedRes = await axios.get('/api/my-owned-projects'); 
        const ownedProjects = ownedRes.data; // e.g. from your controller
  
        // 2) Fetch projects this user COLLABORATES on
        const collabRes = await axios.get('/api/my-collaborations');
        const collabProjects = collabRes.data;
  
        // Combine them or keep them separate
        this.allProjects = [...ownedProjects, ...collabProjects];
      } catch (error) {
        console.error(error);
      }
    }
  };
  </script>
  
  <style scoped>
  .project-item {
    border: 1px solid #ddd;
    margin-bottom: 1rem;
    padding: 1rem;
  }
  </style>
  
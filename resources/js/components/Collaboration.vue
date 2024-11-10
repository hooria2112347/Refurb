<template>
    <div class="collaboration">
      <h2>Collaborate on Projects</h2>
      <ul>
        <li v-for="project in projects" :key="project.id">
          <h3>{{ project.title }}</h3>
          <p>{{ project.description }}</p>
          <button @click="applyToProject(project.id)">Apply to Collaborate</button>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        projects: [
          { id: 1, title: "Project 1", description: "Description of Project 1" },
          // more projects...
        ]
      };
    },
    methods: {
      applyToProject(projectId) {
        // Apply to collaborate
        console.log("Applying to project", projectId);
      }
    }
  };
  </script>
  
  <style scoped>
  /* Collaboration page styles */
  </style>
  
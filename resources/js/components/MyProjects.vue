<template>
  <div class="project-list-container">
    <h1>Collaborative Projects</h1>
    <div v-if="projects.length === 0" class="no-projects">
      <p>No collaborative projects found.</p>
    </div>
    <div v-else class="projects-grid">
      <div 
        v-for="project in projects" 
        :key="project.id" 
        class="project-card"
      >
        <h2>{{ project.title }}</h2>
        <p>{{ project.description }}</p>
        <router-link 
          :to="`/projects/${project.id}`" 
          class="details-link"
        >
          View Details
        </router-link>
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

<style scoped>
/* Container for the list */
.project-list-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 16px;
}

/* Main title */
.project-list-container h1 {
  text-align: center;
  margin-bottom: 24px;
  color: #3C552D;
  font-size: 2rem;
}

/* No projects text */
.no-projects {
  text-align: center;
  font-style: italic;
  color: #666;
}

/* Grid layout for project cards */
.projects-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

/* Two columns on medium screens */
@media (min-width: 600px) {
  .projects-grid {
    grid-template-columns: 1fr 1fr;
  }
}

/* Individual project card */
.project-card {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: box-shadow 0.3s ease;
}

.project-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.project-card h2 {
  margin-bottom: 8px;
  font-size: 1.25rem;
  color: #333;
}

.project-card p {
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #555;
}

/* Link to project details */
.details-link {
  display: inline-block;
  padding: 8px 12px;
  background-color: #D4BEE4;
  color: #3B1E54;
  border-radius: 4px;
  text-decoration: none;
  font-size: 0.95rem;
  transition: opacity 0.3s ease;
}

.details-link:hover {
  opacity: 0.9;
}
</style>

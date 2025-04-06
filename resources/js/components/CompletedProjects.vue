<template>
  <div class="completed-projects-page">
    <div class="header-section">
      <h1>Completed Projects</h1>
      <p class="subtitle">Explore successful collaborations from our community of artists</p>
    </div>

    <div v-if="loading" class="loading-container">
      <p>Loading completed projects...</p>
    </div>

    <div v-else-if="completedProjects.length === 0" class="empty-state">
      <div class="empty-icon">🏆</div>
      <h2>No Completed Projects Yet</h2>
      <p>
        Check back soon as our community completes more amazing projects!
      </p>
      <router-link to="/projects" class="browse-button">
        Browse Active Projects
      </router-link>
    </div>

    <div v-else class="projects-grid">
      <div
        v-for="project in completedProjects"
        :key="project.id"
        class="project-item"
      >
        <div class="project-card">
          <div class="project-header">
            <h2>{{ project.title }}</h2>
            <span class="status-badge">Completed</span>
          </div>

          <p class="project-description">{{ project.description }}</p>

          <div class="project-meta">
            <div class="meta-item">
              <strong>Completed:</strong> {{ formatDate(project.updated_at) }}
            </div>
            <div class="meta-item">
              <strong>Contributors:</strong> {{ project.contributor_count || 'Multiple artists' }}
            </div>
          </div>

          <div class="project-footer">
            <router-link :to="`/projects/${project.id}`" class="view-button">
              View Details
            </router-link>
            <router-link 
              v-if="project.primary_artist_id"
              :to="`/profile/${project.primary_artist_id}`" 
              class="artist-button"
            >
              View Artist Profile
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-if="completedProjects.length > 0 && !loading" class="pagination">
      <button 
        :disabled="currentPage === 1" 
        @click="changePage(currentPage - 1)"
        class="pagination-button"
      >
        Previous
      </button>
      <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages" 
        @click="changePage(currentPage + 1)"
        class="pagination-button"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CompletedProjectsPage',
  data() {
    return {
      completedProjects: [],
      loading: true,
      currentPage: 1,
      totalPages: 1,
      perPage: 12
    };
  },
  created() {
    this.fetchCompletedProjects();
  },
  methods: {
    async fetchCompletedProjects() {
  try {
    console.log('Fetching completed projects, page:', this.currentPage);
    this.loading = true;
    
    const url = '/api/projects/completed';
    console.log('API URL:', url);
    
    const response = await axios.get(url, {
      params: {
        page: this.currentPage,
        per_page: this.perPage
      }
    });
    
    console.log('API Response:', response);
    this.completedProjects = response.data.data;
    
    // If pagination info is included in the response
    if (response.data.meta) {
      this.currentPage = response.data.meta.current_page;
      this.totalPages = response.data.meta.last_page;
      console.log('Pagination info:', this.currentPage, 'of', this.totalPages);
    }
  } catch (error) {
    console.error('Error fetching completed projects:', error);
    console.error('Error details:', error.response ? error.response.data : 'No response data');
  } finally {
    this.loading = false;
  }
},
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchCompletedProjects();
      }
    }
  }
};
</script>

<style scoped>
.completed-projects-page {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.header-section {
  text-align: center;
  margin-bottom: 40px;
}

.header-section h1 {
  font-size: 2.5rem;
  color: #3B1E54;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 1.1rem;
  color: #666;
}

/* Projects Grid */
.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
}

.project-item {
  position: relative;
}

.project-card {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 24px;
}

.project-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.project-header {
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.project-header h2 {
  font-size: 1.4rem;
  color: #3B1E54;
}

.status-badge {
  display: inline-block;
  background-color: #4caf50;
  color: white;
  padding: 4px 10px;
  border-radius: 16px;
  font-size: 0.85rem;
  font-weight: 600;
}

.project-description {
  font-size: 0.95rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 16px;
  flex-grow: 1;
}

.project-meta {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.meta-item {
  margin-bottom: 8px;
  font-size: 0.9rem;
  color: #555;
}

.project-footer {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-top: 20px;
}

.view-button,
.artist-button {
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.view-button {
  background-color: #8D6E97;
  color: white;
  border: none;
}

.view-button:hover {
  background-color: #6F5880;
}

.artist-button {
  background-color: #f0e6f5;
  color: #3B1E54;
  border: 1px solid #d7c1e0;
}

.artist-button:hover {
  background-color: #e2d3e9;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background-color: #f9f9f9;
  border-radius: 12px;
  max-width: 500px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 20px;
}

.empty-state h2 {
  font-size: 1.6rem;
  color: #3B1E54;
  margin-bottom: 16px;
}

.empty-state p {
  color: #666;
  margin-bottom: 24px;
}

.browse-button {
  display: inline-block;
  background-color: #8D6E97;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.browse-button:hover {
  background-color: #6F5880;
}

/* Loading State */
.loading-container {
  text-align: center;
  padding: 40px;
  color: #666;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 40px;
  gap: 16px;
}

.pagination-button {
  background-color: #e0e0e0;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.pagination-button:hover:not(:disabled) {
  background-color: #d0d0d0;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #666;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .projects-grid {
    grid-template-columns: 1fr;
  }
  
  .header-section h1 {
    font-size: 2rem;
  }
  
  .project-footer {
    grid-template-columns: 1fr;
  }
}
</style>
<template>
  <div class="completed-projects-page">
    <div class="header-section">
      <h1>Completed Projects</h1>
      <p class="subtitle">Explore successful collaborations from our community of artists</p>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="loader"></div>
      <p>Loading completed projects...</p>
    </div>

    <div v-else-if="completedProjects.length === 0" class="empty-state">
      <div class="empty-icon">🏆</div>
      <h2>No Completed Projects Yet</h2>
      <p>
        Check back soon as our community completes more amazing projects!
      </p>
    </div>

    <div v-else class="projects-grid">
      <div
        v-for="project in completedProjects"
        :key="project.id"
        class="project-item"
      >
        <div class="project-card" @click="navigateToProject(project.id)">
          <div class="project-header">
            <div class="title-section">
              <h2>{{ project.title }}</h2>
              <span class="status-badge">Completed</span>
            </div>
          </div>

          <p class="project-description">{{ project.description }}</p>

          <div class="project-meta">
            <div class="meta-item">
              <strong>Completed:</strong> {{ formatDate(project.updated_at) }}
            </div>
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
        this.loading = true;
        
        const url = '/api/projects/completed';
        
        const response = await axios.get(url, {
          params: {
            page: this.currentPage,
            per_page: this.perPage
          }
        });
        
        this.completedProjects = response.data.data;
        
        // If pagination info is included in the response
        if (response.data.meta) {
          this.currentPage = response.data.meta.current_page;
          this.totalPages = response.data.meta.last_page;
        }
      } catch (error) {
        console.error('Error fetching completed projects:', error);
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
    },
    navigateToProject(projectId) {
      this.$router.push(`/projects/${projectId}`);
    }
  }
};
</script>

<style scoped>
.completed-projects-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
  font-family: 'Inter', 'Segoe UI', sans-serif;
  color: #333;
}

.header-section {
  text-align: center;
  margin-bottom: 48px;
}

.header-section h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #3B1E54;
  margin-bottom: 12px;
}

.subtitle {
  font-size: 1.2rem;
  color: #666;
  max-width: 600px;
  margin: 0 auto;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.loader {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #6c63ff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Projects Grid */
.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 30px;
}

.project-item {
  height: 100%;
}

.project-card {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: transform 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 24px;
  cursor: pointer;
}

.project-card:hover {
  transform: translateY(-4px);
}

.project-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.title-section {
  flex: 1;
}

.project-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #3B1E54;
  margin-bottom: 12px;
  line-height: 1.3;
}

.status-badge {
  display: inline-block;
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 6px 14px;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 500;
}

.project-description {
  font-size: 1rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 16px;
  flex-grow: 1;
}

.project-meta {
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid #eee;
}

.meta-item {
  font-size: 0.9rem;
  color: #555;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px 20px;
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
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 12px 24px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.browse-button:hover {
  background-color: #EEEEEE;
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
  background-color: #f0f0f0;
  border: 1px solid #ddd;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.pagination-button:hover:not(:disabled) {
  background-color: #e0e0e0;
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
  
  .subtitle {
    font-size: 1rem;
  }
}
</style>
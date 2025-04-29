<template>
  <div class="projects-container">
    <div class="header-section">
      <h1 class="page-title">Collaborative Projects</h1>
      
      <!-- Advanced toolbar -->
      <div class="toolbar">
        <div class="search-bar">
          <input 
            v-model="searchTerm" 
            type="text" 
            placeholder="Search projects..." 
            class="search-input"
            @input="debounceSearch"
          />
          <button @click="fetchProjects" class="search-button">
            <span class="icon">🔍</span>
            <span class="btn-text">Search</span>
          </button>
        </div>
        
        <div class="actions-bar" v-if="projects.length > 0">
          <label class="select-all-container">
            <input 
              type="checkbox" 
              :checked="allSelected" 
              :indeterminate.prop="someSelected && !allSelected"
              @change="toggleSelectAll" 
            />
            <span class="checkbox-label">Select All</span>
          </label>
          
          <button 
            v-if="selectedProjects.length > 0" 
            @click="confirmBulkDelete" 
            class="bulk-delete-button"
          >
            <span class="icon">🗑️</span>
            <span class="btn-text">Delete Selected ({{ selectedProjects.length }})</span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- No projects message -->
    <div v-if="loading" class="loading-container">
      <div class="loader"></div>
      <p>Loading projects...</p>
    </div>
    
    <div v-else-if="projects.length === 0" class="empty-state">
      <div class="empty-icon">📂</div>
      <p>No collaborative projects found.</p>
      <router-link to="/projects/create" class="create-button">
        <span class="icon">➕</span>
        <span>Create New Project</span>
      </router-link>
    </div>
    
    <!-- Projects grid with selection -->
    <div v-else>
      <div class="projects-grid">
        <div 
          v-for="project in projects" 
          :key="project.id" 
          class="project-card"
          :class="{ 'selected': isSelected(project) }"
        >
          <div class="card-selection" v-if="isOwner(project)">
            <input 
              type="checkbox"
              :checked="isSelected(project)"
              @change="toggleSelect(project)"
            />
          </div>
          
          <div class="card-header">
            <h2>{{ project.title }}</h2>
            <div class="status-badge" :class="getStatusClass(project.status)">
              {{ project.status }}
            </div>
          </div>
          
          <p class="project-description">{{ truncateDescription(project.description) }}</p>
          
          <div class="project-meta">
            <div class="meta-item">
              <span class="meta-icon">📅</span>
              <span>{{ formatDate(project.deadline) }}</span>
            </div>
            <div class="meta-item" v-if="project.budget">
              <span class="meta-icon">💰</span>
              <span>PKR {{ project.budget }}</span>
            </div>
          </div>
          
          <div class="card-actions">
            <router-link :to="`/projects/${project.id}`" class="view-button">
              <span>View Details</span>
            </router-link>
            
            <!-- Delete button (only shown to project owner) -->
            <button 
              v-if="isOwner(project)" 
              @click.stop="confirmDelete(project)" 
              class="delete-button"
            >
              <span>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Single Delete Modal -->
    <div v-if="showDeleteModal" class="modal-backdrop">
      <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete <strong>{{ projectToDelete?.title }}</strong>?</p>
        <p class="warning-text">This action cannot be undone.</p>
        
        <div class="modal-actions">
          <button @click="deleteProject" class="confirm-delete">Delete Project</button>
          <button @click="showDeleteModal = false" class="cancel-button">Cancel</button>
        </div>
      </div>
    </div>
    
    <!-- Bulk Delete Modal -->
    <div v-if="showBulkDeleteModal" class="modal-backdrop">
      <div class="modal-content">
        <h3>Confirm Bulk Deletion</h3>
        <p>Are you sure you want to delete <strong>{{ selectedProjects.length }}</strong> selected projects?</p>
        <div class="selected-items">
          <div v-for="project in selectedProjects.slice(0, 3)" :key="project.id" class="selected-item">
            {{ project.title }}
          </div>
          <div v-if="selectedProjects.length > 3" class="selected-more">
            And {{ selectedProjects.length - 3 }} more...
          </div>
        </div>
        <p class="warning-text">This action cannot be undone.</p>
        
        <div class="modal-actions">
          <button @click="bulkDeleteProjects" class="confirm-delete">Delete Selected Projects</button>
          <button @click="showBulkDeleteModal = false" class="cancel-button">Cancel</button>
        </div>
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
      searchTerm: '',
      loading: true,
      showDeleteModal: false,
      showBulkDeleteModal: false,
      projectToDelete: null,
      searchTimeout: null,
      currentUser: null,
      selectedProjectIds: []
    };
  },
  computed: {
    selectedProjects() {
      return this.projects.filter(project => this.selectedProjectIds.includes(project.id));
    },
    allSelected() {
      const selectableProjects = this.projects.filter(project => this.isOwner(project));
      return selectableProjects.length > 0 && 
             selectableProjects.every(project => this.isSelected(project));
    },
    someSelected() {
      return this.selectedProjectIds.length > 0;
    }
  },
  async created() {
    await this.getCurrentUser();
    await this.fetchProjects();
  },
  methods: {
    async getCurrentUser() {
      try {
        const response = await axios.get('/api/user');
        this.currentUser = response.data;
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    },
    async fetchProjects() {
      this.loading = true;
      try {
        const res = await axios.get('/api/projects', { 
          params: { q: this.searchTerm }
        });
        this.projects = res.data;
        // Clear selections when projects change
        this.selectedProjectIds = [];
      } catch (error) {
        console.error('Error fetching projects:', error);
      } finally {
        this.loading = false;
      }
    },
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchProjects();
      }, 500);
    },
    truncateDescription(desc) {
      if (!desc) return '';
      return desc.length > 150 ? desc.substring(0, 147) + '...' : desc;
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    },
    isOwner(project) {
      return this.currentUser && project.owner_id === this.currentUser.id;
    },
    isSelected(project) {
      return this.selectedProjectIds.includes(project.id);
    },
    toggleSelect(project) {
      if (this.isSelected(project)) {
        this.selectedProjectIds = this.selectedProjectIds.filter(id => id !== project.id);
      } else {
        this.selectedProjectIds.push(project.id);
      }
    },
    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedProjectIds = [];
      } else {
        const ownedProjectIds = this.projects
          .filter(project => this.isOwner(project))
          .map(project => project.id);
        this.selectedProjectIds = ownedProjectIds;
      }
    },
    confirmDelete(project) {
      this.projectToDelete = project;
      this.showDeleteModal = true;
    },
    confirmBulkDelete() {
      if (this.selectedProjects.length > 0) {
        this.showBulkDeleteModal = true;
      }
    },
    async deleteProject() {
      try {
        await axios.delete(`/api/projects/${this.projectToDelete.id}`);
        this.projects = this.projects.filter(p => p.id !== this.projectToDelete.id);
        this.showDeleteModal = false;
        this.projectToDelete = null;
      } catch (error) {
        console.error('Error deleting project:', error);
      }
    },
    async bulkDeleteProjects() {
      try {
        // Create an array of promises for each delete request
        const deletePromises = this.selectedProjects.map(project => 
          axios.delete(`/api/projects/${project.id}`)
        );
        
        // Wait for all delete operations to complete
        await Promise.all(deletePromises);
        
        // Update the local projects list
        this.projects = this.projects.filter(p => !this.selectedProjectIds.includes(p.id));
        this.selectedProjectIds = [];
        this.showBulkDeleteModal = false;
      } catch (error) {
        console.error('Error performing bulk delete:', error);
      }
    },
    getStatusClass(status) {
      const statusMap = {
        'active': 'status-active',
        'completed': 'status-completed',
        'cancelled': 'status-cancelled'
      };
      return statusMap[status] || 'status-default';
    }
  }
};
</script>

<style scoped>
.projects-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #333;
}

.header-section {
  margin-bottom: 30px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #222;
  margin: 0 0 20px 0;
}

.toolbar {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.search-bar {
  display: flex;
  gap: 10px;
}

.search-input {
  padding: 12px 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  width: 300px;
  font-size: 14px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #4a6cfa;
  box-shadow: 0 2px 8px rgba(74, 108, 250, 0.2);
  outline: none;
}

.actions-bar {
  display: flex;
  align-items: center;
  gap: 15px;
}

.select-all-container {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.select-all-container input {
  margin-right: 8px;
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.checkbox-label {
  font-size: 14px;
  font-weight: 500;
}

.icon {
  margin-right: 6px;
}

.search-button, .bulk-delete-button, .view-button, .delete-button, .create-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.search-button {
  background-color: #D4BEE4;
  color: #3B1E54;
}

.search-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-1px);
}

.bulk-delete-button {
  background-color: #D4BEE4;
  color: #3B1E54;
}

.bulk-delete-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-1px);
}

.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
}

.project-card {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  padding: 24px;
  transition: all 0.3s;
  position: relative;
  border: 2px solid transparent;
}

.project-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.project-card.selected {
  border-color: #4a6cfa;
  background-color: #f7f9ff;
}

.card-selection {
  position: absolute;
  top: 16px;
  left: 16px;
}

.card-selection input {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 18px;
  padding-left: 30px;
}

.card-header h2 {
  font-size: 20px;
  margin: 0;
  color: #222;
  font-weight: 600;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-active {
  background-color: #e3f2fd;
  color: #1976d2;
}

.status-completed {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.status-cancelled {
  background-color: #ffebee;
  color: #c62828;
}

.status-default {
  background-color: #f5f5f5;
  color: #757575;
}

.project-description {
  color: #555;
  margin-bottom: 20px;
  line-height: 1.6;
  font-size: 15px;
  height: 72px;
  overflow: hidden;
}

.project-meta {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}

.meta-item {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #666;
}

.meta-icon {
  margin-right: 6px;
}

.card-actions {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.view-button {
  background-color: #D4BEE4;
  color: #3B1E54;
  flex-grow: 1;
  text-decoration: none;
}

.view-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
}

.delete-button {
  background-color: #D4BEE4;
  color: #3B1E54;
}

.delete-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
}

.create-button {
  background-color: #D4BEE4;
  color: #3B1E54;
  display: inline-flex;
  align-items: center;
  margin-top: 15px;
  text-decoration: none;
}

.create-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
}

.empty-state {
  text-align: center;
  padding: 60px 0;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 24px;
}

.empty-state p {
  font-size: 18px;
  color: #666;
  margin-bottom: 24px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 0;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.loader {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #4a6cfa;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Modal styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(3px);
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  padding: 30px;
  width: 450px;
  max-width: 90%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-content h3 {
  margin-top: 0;
  color: #222;
  font-size: 22px;
  font-weight: 600;
}

.warning-text {
  color: #ff4d4f;
  font-weight: 500;
  margin-bottom: 0;
}

.selected-items {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 12px;
  margin: 16px 0;
  max-height: 120px;
  overflow-y: auto;
}

.selected-item {
  padding: 6px 0;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

.selected-item:last-child {
  border-bottom: none;
}

.selected-more {
  font-size: 14px;
  padding-top: 6px;
  color: #666;
  font-style: italic;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.confirm-delete, .cancel-button {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.confirm-delete {
  background-color: #D4BEE4;
  color: #3B1E54;
}

.confirm-delete:hover {
  background-color: #EEEEEE;
}

.cancel-button {
  background-color: #EEEEEE;
  color: #3B1E54;
}

.cancel-button:hover {
  background-color: #D4BEE4;
}

/* Responsive styles */
@media (min-width: 768px) {
  .toolbar {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
  
  .search-bar {
    flex-grow: 0;
  }
}

@media (max-width: 767px) {
  .search-input {
    width: 100%;
  }
  
  .projects-grid {
    grid-template-columns: 1fr;
  }
  
  .btn-text {
    display: none;
  }
  
  .icon {
    margin-right: 0;
  }
  
  .search-button, .bulk-delete-button, .view-button, .delete-button {
    padding: 10px;
  }
  
  .card-actions {
    flex-direction: column;
  }
  
  .view-button {
    margin-bottom: 8px;
  }
}
</style>
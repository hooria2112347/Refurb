<template>
  <div class="portfolio-page">
    <div class="header-section">
      <h1>My Portfolio</h1>
      <p class="subtitle">Showcase your completed projects and collaborations</p>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="loader"></div>
      <p>Loading your portfolio...</p>
    </div>

    <div v-else-if="portfolioProjects.length === 0" class="empty-state">
      <div class="empty-icon">📂</div>
      <h2>Your Portfolio is Empty</h2>
      <p>
        Completed projects you collaborate on will automatically appear here.
        Start by joining or creating a project!
      </p>
      <router-link to="/projects" class="browse-button">
        Browse Projects
      </router-link>
    </div>

    <div v-else class="portfolio-grid">
      <div
        v-for="item in portfolioProjects"
        :key="item.id"
        class="portfolio-item"
      >
        <div class="portfolio-card" @click="navigateToProject(item.project_id)">
          <!-- Project Title and Basic Info with Action Icons -->
          <div class="project-header">
            <div class="title-section">
              <h2>{{ item.project.title }}</h2>
              <span class="role-badge">{{ item.role_in_project || 'Collaborator' }}</span>
            </div>
            <div class="action-icons">
              <button @click.stop="editContribution(item)" class="icon-btn edit-icon">
                <i class="fas fa-pencil-alt"></i>
              </button>
              <button @click.stop="showDeleteConfirmation(item)" class="icon-btn remove-icon">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>

          <!-- Project Description -->
          <p class="project-description">{{ item.project.description }}</p>

          <!-- Artist's Contribution (if specified) -->
          <div v-if="item.artist_contribution" class="contribution-section">
            <h3>My Contribution</h3>
            <p>{{ item.artist_contribution }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Contribution Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="cancelEdit">
      <div class="modal-content">
        <h2>Edit Project Contribution</h2>
        
        <div class="form-group">
          <label for="roleInput">Your Role</label>
          <input 
            id="roleInput" 
            v-model="editingItem.role_in_project" 
            placeholder="e.g. Designer, Developer, etc."
          />
        </div>
        
        <div class="form-group">
          <label for="contributionInput">Describe Your Contribution</label>
          <textarea 
            id="contributionInput" 
            v-model="editingItem.artist_contribution" 
            placeholder="Describe what you did on this project..."
            rows="4"
          ></textarea>
        </div>

        <div class="modal-buttons">
          <button @click="saveContribution" class="save-btn">Save Changes</button>
          <button @click="cancelEdit" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
      <div class="modal-content delete-modal">
        <h2>Remove from Portfolio</h2>
        <p>Are you sure you want to remove <strong>{{ itemToDelete?.project?.title }}</strong> from your portfolio?</p>
        <div class="modal-buttons">
          <button @click="confirmDelete" class="delete-btn">Remove Project</button>
          <button @click="cancelDelete" class="cancel-btn">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PortfolioPage',
  data() {
    return {
      portfolioProjects: [],
      loading: true,
      showEditModal: false,
      showDeleteModal: false,
      editingItem: null,
      itemToDelete: null
    };
  },
  async created() {
    await this.fetchPortfolio();
  },
  methods: {
    async fetchPortfolio() {
      try {
        this.loading = true;
        const response = await axios.get('/api/portfolio');
        this.portfolioProjects = response.data;
      } catch (error) {
        console.error('Error fetching portfolio:', error);
      } finally {
        this.loading = false;
      }
    },
    
    editContribution(item) {
      // Create a copy for editing to avoid direct mutation
      this.editingItem = { ...item };
      this.showEditModal = true;
    },
    
    cancelEdit() {
      this.showEditModal = false;
      this.editingItem = null;
    },
    
    async saveContribution() {
      if (!this.editingItem) return;
      
      try {
        await axios.put(`/api/portfolio/${this.editingItem.id}`, {
          artist_contribution: this.editingItem.artist_contribution,
          role_in_project: this.editingItem.role_in_project
        });
        
        // Update the item in the local state
        const index = this.portfolioProjects.findIndex(p => p.id === this.editingItem.id);
        if (index !== -1) {
          this.portfolioProjects[index].artist_contribution = this.editingItem.artist_contribution;
          this.portfolioProjects[index].role_in_project = this.editingItem.role_in_project;
        }
        
        this.showEditModal = false;
        this.editingItem = null;
      } catch (error) {
        console.error('Error saving contribution:', error);
        alert('Failed to save changes');
      }
    },
    
    showDeleteConfirmation(item) {
      this.itemToDelete = item;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.itemToDelete = null;
    },
    
    async confirmDelete() {
      if (!this.itemToDelete) return;
      
      try {
        await axios.delete(`/api/portfolio/${this.itemToDelete.id}`);
        
        // Remove from local state
        this.portfolioProjects = this.portfolioProjects.filter(p => p.id !== this.itemToDelete.id);
        this.showDeleteModal = false;
        this.itemToDelete = null;
      } catch (error) {
        console.error('Error removing from portfolio:', error);
        alert('Failed to remove project from portfolio');
      }
    },
    
    navigateToProject(projectId) {
      this.$router.push(`/projects/${projectId}`);
    }
  }
};
</script>

<style scoped>
.portfolio-page {
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

/* Portfolio Grid */
.portfolio-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 30px;
}

.portfolio-item {
  height: 100%;
}

.portfolio-card {
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

.portfolio-card:hover {
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
  color: #262626;
  margin-bottom: 12px;
  line-height: 1.3;
}

.role-badge {
  display: inline-block;
  background-color: #f0f0ff;
  color: #5050a0;
  padding: 6px 14px;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 500;
}

.action-icons {
  display: flex;
  gap: 10px;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-icon {
  color: #1a73e8;
}

.edit-icon:hover {
  background-color: #e8f0fe;
}

.remove-icon {
  color: #e53935;
}

.remove-icon:hover {
  background-color: #fee8e8;
}

.project-description {
  font-size: 1rem;
  color: #555;
  line-height: 1.6;
  margin-bottom: 20px;
  flex-grow: 1;
}

.contribution-section {
  margin-top: 16px;
  padding: 16px;
  background-color: #f9f9f9;
  border-radius: 8px;
  border-left: 3px solid #6c63ff;
}

.contribution-section h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #404040;
  margin-bottom: 8px;
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

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-content {
  background-color: #fff;
  padding: 28px;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.modal-content h2 {
  color: #262626;
  margin-bottom: 20px;
  font-size: 1.5rem;
  font-weight: 600;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #444;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.save-btn,
.cancel-btn,
.delete-btn {
  padding: 10px 16px;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
}

.save-btn {
  background-color: #6c63ff;
  color: white;
  border: none;
}

.delete-btn {
  background-color: #e53935;
  color: white;
  border: none;
}

.cancel-btn {
  background-color: #f5f5f5;
  color: #444;
  border: 1px solid #ddd;
}

.delete-modal p {
  margin-bottom: 20px;
  line-height: 1.5;
  color: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .portfolio-grid {
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
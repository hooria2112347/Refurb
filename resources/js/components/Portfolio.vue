<template>
    <div class="portfolio-page">
      <div class="header-section">
        <h1>My Portfolio</h1>
        <p class="subtitle">Showcase your completed projects and collaborations</p>
      </div>
  
      <div v-if="loading" class="loading-container">
        <p>Loading portfolio...</p>
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
          :class="{ 'featured': item.featured }"
        >
          <div class="portfolio-card">
            <!-- Featured Badge -->
            <div v-if="item.featured" class="featured-badge">⭐ Featured</div>
  
            <!-- Project Title and Basic Info -->
            <div class="project-header">
              <h2>{{ item.project.title }}</h2>
              <span class="role-badge">{{ item.role_in_project || 'Collaborator' }}</span>
            </div>
  
            <!-- Project Description -->
            <p class="project-description">{{ item.project.description }}</p>
  
            <!-- Artist's Contribution (if specified) -->
            <div v-if="item.artist_contribution" class="contribution-section">
              <h3>My Contribution</h3>
              <p>{{ item.artist_contribution }}</p>
            </div>
  
            <!-- Actions -->
            <div class="portfolio-actions">
              <button 
                @click="toggleFeatured(item)" 
                class="feature-btn"
                :class="{ 'featured-active': item.featured }"
              >
                {{ item.featured ? 'Unfeature' : 'Feature' }}
              </button>
              <button 
    @click="toggleVisibility(item)" 
    class="visibility-btn"
    :class="{ 'private': !item.public }"
  >
    {{ item.public ? 'Make Private' : 'Make Public' }}
  </button>
              <button @click="editContribution(item)" class="edit-btn">
                Edit
              </button>
              <button @click="removeFromPortfolio(item.id)" class="remove-btn">
                Remove
              </button>
              <router-link :to="`/projects/${item.project_id}`" class="view-btn">
                View Project
              </router-link>
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
        editingItem: null
      };
    },
    async created() {
      await this.fetchPortfolio();
    },
    methods: {
      async toggleVisibility(item) {
  try {
    await axios.put(`/api/portfolio/${item.id}`, {
      public: !item.public
    });
    
    // Update the local state
    item.public = !item.public;
  } catch (error) {
    console.error('Error updating visibility status:', error);
    alert('Failed to update visibility status');
  }
},
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
      
      async toggleFeatured(item) {
        try {
          await axios.put(`/api/portfolio/${item.id}`, {
            featured: !item.featured
          });
          
          // Update the local state
          item.featured = !item.featured;
        } catch (error) {
          console.error('Error updating featured status:', error);
          alert('Failed to update featured status');
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
      
      async removeFromPortfolio(id) {
        if (!confirm('Are you sure you want to remove this project from your portfolio?')) return;
        
        try {
          await axios.delete(`/api/portfolio/${id}`);
          
          // Remove from local state
          this.portfolioProjects = this.portfolioProjects.filter(p => p.id !== id);
        } catch (error) {
          console.error('Error removing from portfolio:', error);
          alert('Failed to remove project from portfolio');
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .portfolio-page {
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
  
  /* Portfolio Grid */
  .portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 24px;
  }
  
  .portfolio-item {
    position: relative;
  }
  
  .portfolio-card {
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
  
  .portfolio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  }
  
  /* Featured items */
  .featured .portfolio-card {
    border: 2px solid #8D6E97;
    box-shadow: 0 6px 16px rgba(141, 110, 151, 0.2);
  }
  
  .featured-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background-color: #8D6E97;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
  }
  
  .project-header {
    margin-bottom: 16px;
  }
  
  .project-header h2 {
    font-size: 1.4rem;
    color: #3B1E54;
    margin-bottom: 8px;
  }
  
  .role-badge {
    display: inline-block;
    background-color: #f3f3f3;
    padding: 4px 10px;
    border-radius: 16px;
    font-size: 0.85rem;
    color: #555;
  }
  
  .project-description {
    font-size: 0.95rem;
    color: #555;
    line-height: 1.6;
    margin-bottom: 16px;
    flex-grow: 1;
  }
  
  .contribution-section {
    margin-top: 16px;
    border-top: 1px solid #eee;
    padding-top: 16px;
  }
  
  .contribution-section h3 {
    font-size: 1.1rem;
    color: #3B1E54;
    margin-bottom: 8px;
  }
  
  /* Portfolio Actions */
  .portfolio-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 16px;
  }
  
  .portfolio-actions button,
  .portfolio-actions a {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  
  .feature-btn {
    background-color: #f0e6f5;
    color: #3B1E54;
    border: 1px solid #d7c1e0;
  }
  
  .feature-btn:hover {
    background-color: #e2d3e9;
  }
  
  .featured-active {
    background-color: #3B1E54;
    color: white;
  }
  
  .edit-btn {
    background-color: #e3f2fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
  }
  
  .edit-btn:hover {
    background-color: #bbdefb;
  }
  
  .remove-btn {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
  }
  
  .remove-btn:hover {
    background-color: #ffcdd2;
  }
  
  .view-btn {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .view-btn:hover {
    background-color: #c8e6c9;
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
    padding: 24px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  }
  
  .modal-content h2 {
    color: #3B1E54;
    margin-bottom: 20px;
  }
  
  .form-group {
    margin-bottom: 16px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
  }
  
  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
  }
  
  .form-group textarea {
    resize: vertical;
  }
  
  .modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
  }
  
  .save-btn,
  .cancel-btn {
    padding: 10px 16px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
  }
  
  .save-btn {
    background-color: #8D6E97;
    color: white;
    border: none;
  }
  
  .cancel-btn {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .portfolio-grid {
      grid-template-columns: 1fr;
    }
    
    .portfolio-actions {
      grid-template-columns: 1fr;
    }
  }
  .visibility-btn {
  background-color: #e0f2f1;
  color: #00796b;
  border: 1px solid #b2dfdb;
}

.visibility-btn:hover {
  background-color: #b2dfdb;
}

.visibility-btn.private {
  background-color: #ffe0b2;
  color: #e65100;
  border: 1px solid #ffcc80;
}

.visibility-btn.private:hover {
  background-color: #ffcc80;
}
  </style>
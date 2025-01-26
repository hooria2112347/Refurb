<template>
  <div class="home-page">
    <!-- Hero Section with Image -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Refurb</h1>
        <p class="hero-subtitle">
          Your go-to marketplace for buying <br />
          and refurbishing scrap items.
        </p>
        <button @click="exploreItems" class="hero-button">Explore Scrap Items</button>
      </div>
      <img src="images/main.jpg" alt="Hero image" class="hero-image" />
    </section>

    <!-- Scrap Products Section -->
    <section class="scrap-products">
      <h2 class="section-title">Scrap Products</h2>
      <div v-if="scrapProducts.length" class="scrap-items">
        <div
          v-for="product in scrapProducts"
          :key="product.id"
          class="scrap-item"
        >
          <div class="item-image-wrapper">
            <img :src="product.image" :alt="product.name" />
            <div class="overlay">
              <!-- Example: on click we can view details or route to a product page -->
              <button @click="viewScrapProduct(product.id)">View Details</button>
            </div>
          </div>
          <h3>{{ product.name }}</h3>
          <p>{{ product.description }}</p>
          <p>Price: {{ product.price }}</p>
        </div>
      </div>
      <p v-else>No scrap products found.</p>
    </section>

    <!-- Artist Projects Section -->
    <section class="artist-projects">
      <h2 class="section-title">Artist Projects</h2>
      <div v-if="artistProjects.length" class="projects-list">
        <div
          v-for="project in artistProjects"
          :key="project.id"
          class="project-item"
        >
          <h3>{{ project.title }}</h3>
          <p>{{ project.description }}</p>
          <!-- You can show more info like owner, status, etc. -->
        </div>
      </div>
      <p v-else>No artist projects found.</p>
    </section>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'HomePage',
  data() {
    return {
      scrapProducts: [],
      artistProjects: []
    };
  },
  methods: {
    exploreItems() {
      // Example: navigate to a specific route
      this.$router.push('/scrap-items');
    },
    viewScrapProduct(productId) {
      // For example, navigate to a product detail page
      this.$router.push(`/item/${productId}`);
    },
    // Fetch scrap products from your Laravel API
    fetchScrapProducts() {
      axios
        .get('/api/products')
        .then((response) => {
          this.scrapProducts = response.data;
        })
        .catch((error) => {
          console.error('Error fetching scrap products:', error);
        });
    },
    // Fetch artist projects from your Laravel API
    fetchArtistProjects() {
      axios
        .get('/api/projects')
        .then((response) => {
          this.artistProjects = response.data;
        })
        .catch((error) => {
          console.error('Error fetching artist projects:', error);
        });
    }
  },
  created() {
    // Automatically load data when component is created
    this.fetchScrapProducts();
    this.fetchArtistProjects();
  }
};
</script>

<style scoped>
.home-page {
  color: #3C552D;
  margin: 0 auto;
}

/* Hero Section */
.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2rem;
  background-color: #E7E7E7;
}

.hero-content {
  max-width: 50%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  margin-left: 2vw;
}

.hero-title {
  font-size: 3rem;
  font-weight: bold;
  color: #3C552D;
  margin-bottom: 0.5rem;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #3C552D;
  margin-bottom: 1.5rem;
}

.hero-button {
  background-color: #CA7373;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.hero-button:hover {
  background-color: #D7B26D;
}

.hero-image {
  height: 89vh;
  width: 50%;
  object-fit: cover;
  border-radius: 10px;
}

/* Scrap Products & Artist Projects Sections */
.scrap-products,
.artist-projects {
  padding: 2rem 0;
  text-align: center;
  background-color: #f4f4f4;
  border-radius: 10px;
  margin: 2rem 0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 2.5rem;
  color: #CA7373;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.scrap-items,
.projects-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  justify-items: center;
}

.scrap-item,
.project-item {
  background: #fff;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 12px;
  max-width: 280px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  height: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  position: relative;
  transition: transform 0.3s;
}

.scrap-item:hover,
.project-item:hover {
  transform: translateY(-5px);
}

/* Image Wrappers for Overlays */
.item-image-wrapper {
  position: relative;
  width: 100%;
}

.item-image-wrapper img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 5px;
  border: 2px solid #CA7373;
}

/* Overlay Styles */
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.item-image-wrapper:hover .overlay {
  opacity: 1;
}

.scrap-item h3,
.project-item h3 {
  margin: 0.5rem 0;
  font-size: 1.2rem;
  color: #CA7373;
}

.scrap-item p,
.project-item p {
  font-size: 0.9rem;
  margin: 0.5rem 0 1rem;
  color: #555;
}

.scrap-item button {
  padding: 0.5rem 1rem;
  background-color: #CA7373;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
}

.scrap-item button:hover {
  background-color: #D7B26D;
  transform: scale(1.05);
}
</style>

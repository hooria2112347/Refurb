<template>
  <div class="home-page">
    <!-- Hero Section with Image -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Refurb</h1>
        <p class="hero-subtitle">
          Your go-to marketplace for buying and refurbishing scrap items.
        </p>
        <button @click="exploreItems" class="hero-button">Explore Scrap Items</button>
      </div>
      <div class="hero-image-wrapper">
        <img src="images/main.jpg" alt="Refurb Marketplace" class="hero-image" />
      </div>
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
            <img :src="product.image" :alt="product.name" class="item-image" loading="lazy" />
            <div class="overlay">
              <button @click="viewScrapProduct(product.id)" class="overlay-button">View Details</button>
            </div>
          </div>
          <div class="item-content">
            <h3 class="item-title">{{ product.name }}</h3>
            <p class="item-description">{{ product.description }}</p>
            <p class="item-price">Price: {{ product.price }}</p>
          </div>
        </div>
      </div>
      <p v-else class="no-products">No scrap products found.</p>
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
          <div class="project-content">
            <h3 class="project-title">{{ project.title }}</h3>
            <p class="project-description">{{ project.description }}</p>
          </div>
        </div>
      </div>
      <p v-else class="no-projects">No artist projects found.</p>
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
      this.$router.push('/scrap-items');
    },
    viewScrapProduct(productId) {
      this.$router.push(`/item/${productId}`);
    },
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
    this.fetchScrapProducts();
    this.fetchArtistProjects();
  }
};
</script>

<style scoped>
.home-page {
  color: #3C552D;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Hero Section */
.hero {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  padding: 4rem 2rem;
  background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
  border-radius: 12px;
  margin-top: 2rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.hero-content {
  max-width: 50%;
}

.hero-title {
  font-size: 3rem;
  font-weight: 700;
  color: #3B1E54;
  margin-bottom: 1rem;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #3B1E54;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.hero-button {
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.hero-button:hover {
  background-color: #EEEEEE;
  transform: translateY(-2px);
}

.hero-image-wrapper {
  max-width: 45%;
  position: relative;
}

.hero-image {
  width: 100%;
  height: auto;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Scrap Products & Artist Projects Sections */
.scrap-products,
.artist-projects {
  padding: 3rem 0;
  text-align: center;
  background-color: #fff;
  border-radius: 12px;
  margin: 3rem 0;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.section-title {
  font-size: 2.5rem;
  color: #CA7373;
  margin-bottom: 2rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  position: relative;
  display: inline-block;
}

.section-title::after {
  content: '';
  width: 50px;
  height: 3px;
  background-color: #CA7373;
  position: absolute;
  left: 50%;
  bottom: -10px;
  transform: translateX(-50%);
  border-radius: 2px;
}

/* Scrap Products */
.scrap-items {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  justify-items: center;
}

.scrap-item {
  background: #fff;
  padding: 1.5rem;
  border: 1px solid #ddd;
  border-radius: 12px;
  max-width: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  transition: transform 0.3s, box-shadow 0.3s;
  position: relative;
}

.scrap-item:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.item-image-wrapper {
  position: relative;
  width: 100%;
  overflow: hidden;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.item-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.scrap-item:hover .item-image {
  transform: scale(1.05);
}

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(44, 44, 44, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.scrap-item:hover .overlay {
  opacity: 1;
}

.overlay-button {
  background-color: #fff;
  color: #3C552D;
  padding: 0.5rem 1.2rem;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.overlay-button:hover {
  background-color: #f0f0f0;
  transform: scale(1.05);
}

.item-content {
  flex-grow: 1;
}

.item-title {
  font-size: 1.25rem;
  color: #2E422A;
  margin: 0.5rem 0;
}

.item-description {
  font-size: 0.95rem;
  color: #555;
  margin-bottom: 1rem;
}

.item-price {
  font-size: 1rem;
  color: #CA7373;
  font-weight: 600;
}

/* Artist Projects */
.projects-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  justify-items: center;
}

.project-item {
  background: #fff;
  padding: 1.5rem;
  border: 1px solid #ddd;
  border-radius: 12px;
  max-width: 300px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.3s, box-shadow 0.3s;
}

.project-item:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.project-content {
  flex-grow: 1;
}

.project-title {
  font-size: 1.25rem;
  color: #2E422A;
  margin: 0.5rem 0;
}

.project-description {
  font-size: 0.95rem;
  color: #555;
  margin-bottom: 1rem;
}

/* No Products / No Projects Message */
.no-products,
.no-projects {
  font-size: 1rem;
  color: #777;
  margin-top: 1rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .hero {
    flex-direction: column;
    align-items: center;
  }

  .hero-content {
    max-width: 100%;
    text-align: center;
  }

  .hero-image-wrapper {
    max-width: 100%;
    margin-top: 2rem;
  }

  .hero-image {
    height: auto;
    width: 100%;
  }
}

@media (max-width: 768px) {
  .section-title {
    font-size: 2rem;
  }

  .scrap-item,
  .project-item {
    max-width: 90%;
  }

  .hero-title {
    font-size: 2.5rem;
  }

  .hero-subtitle {
    font-size: 1.1rem;
  }

  .hero-button {
    padding: 0.6rem 1.2rem;
    font-size: 0.95rem;
  }
}

@media (max-width: 480px) {
  .hero {
    padding: 2rem 1rem;
  }

  .section-title {
    font-size: 1.8rem;
  }

  .scrap-item,
  .project-item {
    max-width: 100%;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .hero-button {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }

  .item-description,
  .project-description {
    font-size: 0.85rem;
  }

  .item-price {
    font-size: 0.95rem;
  }
}
</style>

<template>
  <div class="home-page">
    <!-- Hero Section with Image -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Refurb</h1>
        <p class="hero-subtitle">
          Your go-to marketplace for buying and refurbishing scrap items.
        </p>
        <button @click="exploreItems" class="hero-button">Explore Items</button>
      </div>
      <div class="hero-image-wrapper">
        <img src="images/main.jpg" alt="Refurb Marketplace" class="hero-image" />
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'HomePage',
  methods: {
    exploreItems() {
      this.$router.push('/scrap-items');
    }
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

/* No Products / No Projects Message */
/* Removed as these sections are no longer present */

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
}
</style>

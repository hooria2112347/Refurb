<template>
  <div class="home-page">
    <!-- Hero Section with Image -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Refurb</h1>
        <p class="hero-subtitle">Your go-to marketplace for buying <br> and refurbishing scrap items.</p>
        <button @click="exploreItems" class="hero-button">Explore Scrap Items</button>
      </div>
      <img src="images/main.jpg" alt="Hero image" class="hero-image" />
    </section>

    <!-- Featured Scrap Items -->
    <section class="featured-scrap">
      <h2 class="section-title">Featured Scrap Items</h2>
      <div class="scrap-items">
        <div v-for="item in featuredItems" :key="item.id" class="scrap-item">
          <div class="item-image-wrapper">
            <img :src="item.image" :alt="item.name" />
            <div class="overlay">
              <button @click="viewItem(item.id)">View Details</button>
            </div>
          </div>
          <h3>{{ item.name }}</h3>
          <p>{{ item.description }}</p>
        </div>
      </div>
    </section>

    <!-- Artist Portfolios -->
    <section class="artist-portfolios">
      <h2 class="section-title">Meet Our Talented Artists</h2>
      <div class="portfolios">
        <div v-for="artist in artists" :key="artist.id" class="artist-portfolio">
          <div class="portfolio-image-wrapper">
            <img :src="artist.image" :alt="artist.name" />
            <div class="overlay">
              <button @click="viewPortfolio(artist.id)">View Portfolio</button>
            </div>
          </div>
          <h3>{{ artist.name }}</h3>
          <p>{{ artist.description }}</p>
        </div>
      </div>
    </section>

  </div>
</template>

<script>
export default {
  data() {
    return {
      featuredItems: [
        { id: 1, name: "Vintage Metal Chair", description: "A classic chair ready to be refurbished.", image: "/images/item1.jpeg" },
        { id: 2, name: "Wooden Table", description: "Solid wood table, perfect for upcycling.", image: "/images/item2.jpeg" },
        { id: 3, name: "Metal Cabinet", description: "Sturdy cabinet awaiting a new look.", image: "/images/item3.jpeg" }
      ],
      artists: [
        { id: 1, name: "Jane Doe", description: "Specializes in upcycled furniture.", image: "/images/people1.jpeg" },
        { id: 2, name: "John Smith", description: "Focused on sustainable art pieces.", image: "/images/people2.jpg" },
        { id: 3, name: "Emily Ray", description: "Creative with reclaimed wood.", image: "/images/people3.jpg" }
      ]
    };
  },
  methods: {
    exploreItems() {
      this.$router.push('/scrap-items');
    },
    viewItem(itemId) {
      this.$router.push(`/item/${itemId}`);
    },
    viewPortfolio(artistId) {
      this.$router.push(`/artist/${artistId}`);
    }
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
  flex-direction: column; /* Stack elements vertically */
  align-items: flex-start; /* Align to the start */
  justify-content: center; /* Center content vertically */
  margin-left: 2vw;
}

.hero-title {
  font-size: 3rem; /* Adjusted font size for hero title */
  font-weight: bold;
  color: #3C552D; /* Title color */
  margin-bottom: 0.5rem; /* Space below the title */
}

.hero-subtitle {
  font-size: 1.25rem; /* Subtitle size */
  color: #3C552D; /* Subtitle color */
  margin-bottom: 1.5rem; /* Space below the subtitle */
}

.hero-button {
  background-color: #CA7373;
  color: white;
  padding: 0.75rem 1.5rem; /* Increased padding for better button size */
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
  width: 50%; /* Adjusted width */
  object-fit: cover;
  border-radius: 10px;
}

/* Featured Scrap Items and Artist Portfolios */
.featured-scrap, .artist-portfolios {
  padding: 2rem 0;
  text-align: center;
  background-color: #f4f4f4; /* Light background for sections */
  border-radius: 10px;
  margin: 2rem 0; /* Spacing between sections */
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 2.5rem;
  color: #CA7373; /* Enhanced color for titles */
  margin-bottom: 1.5rem;
  text-transform: uppercase; /* Uppercase for section titles */
  letter-spacing: 1px; /* Spacing between letters */
}

.scrap-items, .portfolios {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid layout */
  gap: 1.5rem; /* Space between items */
  justify-items: center; /* Center items in each grid cell */
}

.scrap-item, .artist-portfolio {
  background: #fff;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 12px;
  max-width: 280px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  height: 100%; /* Full height for consistent sizing */
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  position: relative; /* Needed for overlay */
  transition: transform 0.3s; /* Smooth hover effects */
}

.scrap-item:hover, .artist-portfolio:hover {
  transform: translateY(-5px); /* Lift effect on hover */
}

/* Image Wrappers for Overlays */
.item-image-wrapper, .portfolio-image-wrapper {
  position: relative;
  width: 100%;
}

.item-image-wrapper img, .portfolio-image-wrapper img {
  width: 100%;
  height: 200px; /* Fixed height for consistency */
  object-fit: cover; /* Ensure the image fits within the box */
  border-radius: 5px;
  border: 2px solid #CA7373; /* Border around images */
}

/* Overlay Styles */
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5); /* Semi-transparent overlay */
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 0;
  transition: opacity 0.3s; /* Fade in/out effect */
}

.item-image-wrapper:hover .overlay,
.portfolio-image-wrapper:hover .overlay {
  opacity: 1; /* Show overlay on hover */
}

.scrap-item h3, .artist-portfolio h3 {
  margin: 0.5rem 0;
  font-size: 1.2rem;
  color: #CA7373; /* Change title color */
}

.scrap-item p, .artist-portfolio p {
  font-size: 0.9rem;
  margin: 0.5rem 0 1rem;
  color: #555; /* Darker text for descriptions */
}

.scrap-item button, .artist-portfolio button {
  padding: 0.5rem 1rem;
  background-color: #CA7373;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
}

.scrap-item button:hover, .artist-portfolio button:hover {
  background-color: #D7B26D;
  transform: scale(1.05); /* Slight scaling effect on hover */
}

</style>

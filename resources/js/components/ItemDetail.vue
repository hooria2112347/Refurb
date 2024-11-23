<template>
  <div class="item-detail" v-if="item">
    <div class="detail-container">
      <div class="image-section">
        <img :src="item.image" :alt="item.name" class="item-image" />
      </div>
      <div class="info-section">
        <h2 class="item-title">{{ item.name }}</h2>
        <div class="item-price">${{ item.price.toFixed(2) }}</div>
        
        <div class="rating">
          <span class="star-rating">★★★★☆</span>
          <span class="review-count">({{ item.reviewCount }} Reviews)</span>
        </div>

        <div class="color-selection">
          <h4>Color:</h4>
          <div class="color-options">
            <div class="color-option" 
                 v-for="color in item.colors" 
                 :key="color" 
                 :style="{ backgroundColor: color }"
                 @click="selectColor(color)">
            </div>
          </div>
        </div>

        <div class="size-selection">
          <h4>Size:</h4>
          <select v-model="selectedSize" class="size-select">
            <option v-for="size in item.sizes" :key="size">{{ size }}</option>
          </select>
        </div>

        <div class="button-container">
          <button @click="addToCart" class="add-to-cart-button">Add to Cart</button>
          <button @click="goBack" class="back-button">Back to Items</button>
        </div>

        <div class="delivery-info">
          Delivery in 4-6 days | Free from 100 EUR | Free returns & exchanges
        </div>

        <div class="tabs">
          <button @click="showOverview" :class="{ active: activeTab === 'overview' }">Overview</button>
          <button @click="showDetails" :class="{ active: activeTab === 'details' }">Details</button>
          <button @click="showCare" :class="{ active: activeTab === 'care' }">Care</button>
        </div>

        <div class="tab-content">
          <div v-if="activeTab === 'overview'" class="tab-description">{{ item.overview }}</div>
          <div v-if="activeTab === 'details'" class="tab-description">{{ item.details }}</div>
          <div v-if="activeTab === 'care'" class="tab-description">{{ item.care }}</div>
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    <p>Item not found.</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      items: [
        {
          id: '1',
          name: "Vintage Metal Chair",
          description: "A classic metal chair, perfect for refurbishing.",
          image: "/images/item1.jpeg",
          price: 59.99,
          reviewCount: 178,
          colors: ['#1D1D1D', '#A6A6A6', '#D0D0D0'], // Example color options
          sizes: ['S', 'M', 'L', 'XL'], // Example size options
          overview: "Debuted on the tennis courts of the 1920s, this chair fills the space between the shirt and the T-shirt.",
          details: "At its core is a custom developed soft and substantial organic cotton knit.",
          care: "Machine wash at 30°C, do not tumble dry."
        },
        {
          id: '2',
          name: "Wooden Table",
          description: "Solid wooden table, great for upcycling.",
          image: "/images/item2.jpeg",
          price: 89.99,
          reviewCount: 120,
          colors: ['#FFCCAA', '#AAFFCC', '#D0D0D0'], // Example color options
          sizes: ['M', 'L', 'XL'], // Example size options
          overview: "This table is made from reclaimed wood and has a rustic finish.",
          details: "Perfect for any kitchen or dining area, sturdy and elegant.",
          care: "Wipe clean with a damp cloth."
        },
        {
          id: '3',
          name: "Metal Cabinet",
          description: "Sturdy metal cabinet awaiting a new look.",
          image: "/images/item3.jpeg",
          price: 79.99,
          reviewCount: 45,
          colors: ['#D0D0D0', '#B0B0B0', '#7F7F7F'], // Example color options
          sizes: ['S', 'M', 'L'], // Example size options
          overview: "A functional piece for organizing your space, perfect for refurbishing.",
          details: "Made from high-quality metal, it is durable and stylish.",
          care: "Clean with a soft cloth."
        }
      ],
      item: null,
      selectedSize: 'M',
      selectedColor: null,
      activeTab: 'overview'
    };
  },
  methods: {
    addToCart() {
      console.log("Item added to cart:", this.item.id);
    },
    goBack() {
      this.$router.push('/scrap-items');
    },
    selectColor(color) {
      this.selectedColor = color; // Handle color selection
    },
    showOverview() {
      this.activeTab = 'overview';
    },
    showDetails() {
      this.activeTab = 'details';
    },
    showCare() {
      this.activeTab = 'care';
    }
  },
  mounted() {
    const itemId = this.$route.params.itemId;
    this.item = this.items.find(item => item.id === itemId);
  }
};


</script>

<style scoped>
.detail-container {
  display: flex;
  max-width: 1200px;
  margin: 2rem auto;
  background-color: #ffffff; /* White background for contrast */
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.image-section {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.item-image {
  width: 100%;
  max-width: 500px;
  border-radius: 10px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.info-section {
  flex: 1;
  padding: 2rem;
  text-align: left;
}

.item-title {
  font-size: 2rem;
  color: #CA7373; /* Title color */
  margin: 0;
}

.item-price {
  font-size: 1.5rem;
  color: #333;
  margin: 0.5rem 0 1.5rem;
}

.rating {
  margin: 0.5rem 0;
}

.star-rating {
  color: #FFD700; /* Gold color for stars */
}

.review-count {
  color: #555;
  margin-left: 5px;
}

.color-selection {
  margin: 1rem 0;
}

.color-options {
  display: flex;
  gap: 1rem;
}

.color-option {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid #ccc; /* Outline for color options */
}

.size-selection {
  margin: 1rem 0;
}

.size-select {
  padding: 0.5rem;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.button-container {
  margin: 2rem 0;
}

.add-to-cart-button {
  padding: 0.75rem 1.5rem;
  background-color: #CA7373; /* Primary color for add to cart */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.add-to-cart-button:hover {
  background-color: #D7B26D; /* Hover effect */
}

.back-button {
  padding: 0.75rem 1.5rem;
  background-color: #f0f0f0; /* Lighter background for back button */
  color: #333;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.back-button:hover {
  background-color: #e0e0e0; /* Hover effect */
}

.delivery-info {
  margin: 1rem 0;
  font-size: 0.9rem;
  color: #777;
}

.tabs {
  display: flex;
  justify-content: space-around;
  margin: 1rem 0;
}

.tabs button {
  padding: 0.75rem 1rem;
  border: none;
  background-color: transparent;
  font-size: 1rem;
  cursor: pointer;
  transition: border-bottom 0.3s;
}

.tabs button.active {
  border-bottom: 2px solid #CA7373;
  font-weight: bold;
}

.tab-content {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #555;
}

.tab-description {
  margin: 1rem 0;
}
</style>

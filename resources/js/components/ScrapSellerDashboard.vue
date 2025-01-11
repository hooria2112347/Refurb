<template>
    <div class="scrap-seller-dashboard">
      <!-- Side navigation for Scrap Seller -->
      <aside class="side-nav">
        <h3>Scrap Seller Menu</h3>
        <router-link to="/scrap-seller-dashboard">Seller Overview</router-link>
        <router-link to="/account">Account</router-link>
        <router-link to="/add-product">Add Product</router-link>
        <router-link to="/manage-products">Manage Products</router-link>
        <router-link to="/custom-request">Custom Request</router-link>
        <!-- Add more scrap-seller-specific links as needed -->
      </aside>
  
      <section class="dashboard-content">
        <h2>Welcome to the Scrap Seller Dashboard</h2>
        <p>Manage scrap product listings and handle custom requests.</p>
        <!-- Place your scrap-seller content here -->
      </section>
    </div>
  </template>
  
  <script>
  export default {
    name: "ScrapSellerDashboard",
    mounted() {
      // Optional: Confirm user is Scrap Seller
      const session = localStorage.getItem('userSession');
      if (session) {
        const userData = JSON.parse(session);
        if (userData.role !== 'scrapSeller') {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/login');
      }
    }
  };
  </script>
  
  <style scoped>
  .scrap-seller-dashboard {
    display: flex;
  }
  
  /* Side nav styling */
  .side-nav {
    width: 200px;
    border-right: 1px solid #ddd;
    padding: 1rem;
  }
  
  .side-nav h3 {
    margin-top: 0;
    color: #3C552D;
  }
  
  .side-nav a {
    display: block;
    margin-bottom: 0.5rem;
    color: #3C552D;
    text-decoration: none;
    font-weight: 500;
  }
  
  .side-nav a:hover {
    color: #0056b3;
  }
  
  .dashboard-content {
    flex: 1;
    padding: 1rem;
  }
  </style>
  
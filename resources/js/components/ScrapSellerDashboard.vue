<template>
    <div class="scrap-seller-dashboard">
      <!-- Side navigation for Scrap Seller -->
      <aside class="side-nav">
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
/* MAIN WRAPPER FOR SCRAP SELLER DASHBOARD */
.scrap-seller-dashboard {
  display: flex;
  background-color: #f7f9fc;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

/* SIDE NAVIGATION STYLING */
.side-nav {
  width: 220px;
  background-color: #ffffff;
  padding: 1rem;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
  border-right: 1px solid #e4e4e4;
  display: flex;
  flex-direction: column;
}

.side-nav h3 {
  font-size: 18px;
  color: #3C552D;
  margin-bottom: 1rem;
  font-weight: 600;
}

.side-nav a {
  display: block;
  margin-bottom: 0.8rem;
  padding: 10px;
  color: #3C552D;
  text-decoration: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.3s, color 0.3s;
}

.side-nav a:hover {
  background-color: #e4f2ea;
  color: #5d9b8b;
}

.side-nav a.active {
  background-color: #5d9b8b;
  color: #ffffff;
}

/* DASHBOARD CONTENT STYLING */
.dashboard-content {
  flex: 1;
  padding: 2rem;
  background-color: #f7f9fc;
}

.dashboard-content h2 {
  font-size: 28px;
  color: #3a3d40;
  margin-bottom: 1rem;
}

.dashboard-content p {
  font-size: 16px;
  color: #6b6f74;
  margin-bottom: 2rem;
}

/* RESPONSIVE DESIGN */
@media screen and (max-width: 768px) {
  .side-nav {
    width: 180px;
  }

  .dashboard-content {
    padding: 1rem;
  }
}

@media screen and (max-width: 480px) {
  .side-nav {
    display: none;
  }

  .dashboard-content {
    padding: 1rem;
  }
}
</style>

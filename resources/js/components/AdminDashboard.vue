<template>
    <div class="admin-dashboard">
      <!-- Side navigation for Admin -->
      <aside class="side-nav">
        <h3>Admin Menu</h3>
        <router-link to="/admin-dashboard">Admin Overview</router-link>
        <router-link to="/account">Account</router-link>
        <router-link to="/admin-feedback">Feedback Management</router-link>
        <router-link to="/admin-orders">Orders</router-link>
        <router-link to="/admin-products">Manage Products</router-link>
        <!-- Add more admin-specific links as needed -->
      </aside>
  
      <section class="dashboard-content">
        <h2>Welcome to the Admin Dashboard</h2>
        <p>Manage the entire e-commerce system here.</p>
        <!-- Place your admin content here -->
      </section>
    </div>
  </template>
  
  <script>
  export default {
    name: "AdminDashboard",
    mounted() {
      // Optional: Confirm user is Admin
      const session = localStorage.getItem('userSession');
      if (session) {
        const userData = JSON.parse(session);
        if (userData.role !== 'admin') {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/login');
      }
    }
  };
  </script>
  
  <style scoped>
  .admin-dashboard {
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
  
<template>
    <div class="artist-dashboard">
      <!-- Side navigation for Artist -->
      <aside class="side-nav">
        <h3>Artist Menu</h3>
        <router-link to="/artist-dashboard">Artist Overview</router-link>
        <router-link to="/account">Account</router-link>
        <router-link to="/my-projects">My Projects</router-link>
        <router-link to="/scrap-items">Browse Scrap</router-link>
        <router-link to="/artist-view-custom-requests">View All Custom Requests</router-link>
        <router-link to="/projects/create">Create New Project</router-link>
        <router-link to="/my-invitations">My Invitations</router-link>
        <router-link to="/my-sent-invitations">Invitations I Sent</router-link>
        <!-- Add more artist-specific links as needed -->
      </aside>
  
      <section class="dashboard-content">
        <h2>Welcome to the Artist Dashboard</h2>
        <p>Manage your portfolio, custom requests, and more.</p>
        <!-- Place your artist content here -->
      </section>
    </div>
  </template>
  
  <script>
  export default {
    name: "ArtistDashboard",
    mounted() {
      // Optional: Confirm user is Artist
      const session = localStorage.getItem('userSession');
      if (session) {
        const userData = JSON.parse(session);
        if (userData.role !== 'artist') {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/login');
      }
    }
  };
  </script>
  
  <style scoped>
  .artist-dashboard {
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
  
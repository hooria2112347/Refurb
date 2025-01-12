<template>
    <div class="artist-dashboard">
      <!-- Side navigation for Artist -->
      <aside class="side-nav">
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
/* MAIN WRAPPER FOR ARTIST DASHBOARD */
.artist-dashboard {
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

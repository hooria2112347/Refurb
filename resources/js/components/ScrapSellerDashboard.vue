<template>
  <div class="scrap-seller-dashboard">
    <!-- Side navigation for Scrap Seller -->
   <aside class="side-nav">
        <router-link :to="overviewRoute" exact-active-class="active">Overview</router-link> <router-link :to="'/user-profile/' + currentUserId">Account</router-link>
         <router-link to="/orders-received">View Orders Received</router-link>
      <router-link to="/order-history">Orders History</router-link>
        <!-- <router-link to="/account" exact-active-class="active">Account</router-link> -->
        <router-link to="/password-change" exact-active-class="active">Change Password</router-link>
           <!-- Selling Collapsible -->
     
      <div 
        class="collapsible" 
        :class="{ open: openSection === 'selling' }"
      >
        <div 
          class="collapsible-trigger" 
          @click="toggleAccordion('selling')"
        >
          Products
          <span class="arrow"></span>
        </div>
        <div class="collapsible-content">
          <router-link to="/add-product">Add Product</router-link>
          <router-link to="/manage-products">Manage Products</router-link>
        </div>
      </div>

      <!-- Custom Requests Collapsible -->
      <div 
        class="collapsible"
        :class="{ open: openSection === 'requests' }"
      >
        <div 
          class="collapsible-trigger" 
          @click="toggleAccordion('requests')"
        >
          Custom Requests
          <span class="arrow"></span>
        </div>
        <div class="collapsible-content">
          <router-link to="/custom-request">Send a Request</router-link>
          <router-link to="/view-custom-requests">View Requests</router-link>
        </div>
      </div>
    </aside>

    <section class="dashboard-content">
      <h2>Welcome to your dashboard</h2>
      <p>Manage your scrap product listings and handle custom requests.</p>
      <!-- Place your scrap-seller content here -->
    </section>
  </div>
</template>

<script>
export default {
  name: "ScrapSellerDashboard",
  data() {
    return {
      // Tracks which collapsible section is open. Possible values:
      // 'selling', 'requests', or null if none is open.
      openSection: null,
      currentUserId: null,
    };
  },
  mounted() {
    // Get user session and extract user ID
    const session = localStorage.getItem("userSession");
    if (session) {
      const userData = JSON.parse(session);
      this.currentUserId = userData.id || userData.user_id; // Handle different possible property names
      
      // Confirm user is Scrap Seller
      if (userData.role !== "scrapSeller") {
        this.$router.push("/");
      }
    } else {
      this.$router.push("/login");
    }
  },
  methods: {
    toggleAccordion(sectionName) {
      // If the requested section is already open, close it.
      // Otherwise, open the new section and close any previously opened one.
      this.openSection = this.openSection === sectionName ? null : sectionName;
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

.side-nav a {
  display: block;
  margin-bottom: 0.8rem;
  padding: 10px;
  color: #3B1E54;
  text-decoration: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.3s, color 0.3s;
}

.side-nav a:hover {
  background-color: #D4BEE4;
  color: #3B1E54;
}

.side-nav a.active {
  background-color: #9B7EBD;
  color: #3B1E54;
}

/* COLLAPSIBLE MENU SECTIONS */
.collapsible {
  margin-bottom: 0.8rem;
  border-bottom: 1px solid #e4e4e4;
}

.collapsible-trigger {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #3B1E54;
  padding: 10px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.3s, color 0.3s;
  border-radius: 8px;
}

.collapsible-trigger:hover {
  background-color: #D4BEE4;
  color: #3B1E54;
}

/* Arrow icon */
.arrow {
  border: solid #3C552D;
  border-width: 0 2px 2px 0;
  display: inline-block;
  padding: 4px;
  transform: rotate(45deg);
  margin-left: 8px;
  transition: transform 0.3s ease;
}

/* Collapsible Content */
.collapsible-content {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  margin-left: 1.5rem;
}

/* When "open" is toggled, rotate arrow and reveal content */
.collapsible.open .arrow {
  transform: rotate(-135deg);
}

.collapsible.open .collapsible-content {
  max-height: 300px; /* Adjust if needed */
  transition: max-height 0.3s ease;
}

/* Sub-links inside the collapsible content */
.collapsible-content a {
  font-size: 15px;
  font-weight: 400;
  border-radius: 6px;
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
    display: none; /* Or use a hamburger menu for mobile */
  }

  .dashboard-content {
    padding: 1rem;
  }
}
</style>

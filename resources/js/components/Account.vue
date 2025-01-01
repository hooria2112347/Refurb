<template>
  <div class="profile-container">
    <div class="sidebar">
      <ul class="menu">
        <li><router-link to="/account" active-class="active">Account</router-link></li>
        <li><router-link to="/password-change" active-class="active">Change Password</router-link></li>
        <li><router-link to="/billing-address" active-class="active">Billing Address</router-link></li>
        <li><router-link to="/shipping-address" active-class="active">Shipping Address</router-link></li>
        <li><router-link to="/my-orders" active-class="active">My Orders</router-link></li>
        <!-- Show the View Accepted Requests link if the user is an artist -->
        <li><router-link v-if="isArtist" to="/view-accepted-requests" active-class="active">View Accepted Requests</router-link></li>

        
      </ul>
    </div>
    <div class="main-content">
      <h1>Account Details</h1>
      <div class="form-container">
        <h2>My Information</h2>
        <div class="form-group">
          <label>Name:</label>
          <p>{{ user.name }}</p>
        </div>
        <div class="form-group">
          <label>Email:</label>
          <p>{{ user.email }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: {
        name: "",
        email: "",
      },
    };
  },
  computed: {
    isArtist() {
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          return userData.role === 'artist';
        } catch (e) {
          console.error("Error parsing session data:", e);
          return false;
        }
      }
      return false;
    },
  },
  methods: {
    fetchUserDetails() {
      // Retrieve session data from localStorage
      const session = localStorage.getItem("userSession");
      if (session) {
        try {
          const userData = JSON.parse(session);
          this.user.name = userData.name;
          this.user.email = userData.email;
        } catch (e) {
          console.error("Error parsing session data:", e);
          alert("Failed to load user details. Please log in again.");
          this.$router.push("/login"); // Redirect to login on error
        }
      } else {
        alert("No active session found. Please log in.");
        this.$router.push("/login");
      }
    },
  },
  mounted() {
    this.fetchUserDetails(); // Load user details on component mount
  },
};
</script>

  
  <style scoped>
  .profile-container {
  display: flex;
  background-color: #f9f9f9;
  font-family: Arial, sans-serif;
  align-items: flex-start; /* Ensures items align at the top */
}
  
  /* Sidebar */
  .sidebar {
  width: 25%;
  background-color: #ffffff;
  border-right: 1px solid #ddd;
  padding: 1.5rem;
  height: 88vh;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
}
  
  .menu {
    list-style: none;
    padding: 0;
  }
  
  .menu li {
    margin: 0.8rem 0;
  }
  
  .menu li a {
    text-decoration: none;
    color: #333;
    font-size: 1rem;
    display: block;
    padding: 0.5rem 0;
  }
  
  .menu li.active a {
    font-weight: bold;
    color: #CA7373;
  }
  
  /* Main Content */
  .main-content {
    flex: 1;
    padding: 2rem;
  }
  
  .main-content h1 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #333;
  }
  
  .form-container {
    background-color: #ffffff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .form-container h2 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #555;
  }
  
  .form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  
  button {
    display: inline-block;
    width: 100%;
    padding: 0.75rem;
    background-color: #CA7373;
    color: #fff;
    font-size: 1rem;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  button:hover {
    background-color: #D7B26D;
  }
  
  /* Success Message */
  .success-message {
    background-color: #4caf50;
    color: white;
    padding: 0.75rem;
    border-radius: 5px;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .success-message .close {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
  }
  </style>
  
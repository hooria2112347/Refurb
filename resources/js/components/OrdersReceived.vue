<template>
    <div class="user-account-dashboard">
      <!-- Side navigation -->
      <aside class="side-nav">
        <router-link :to="overviewRoute" exact-active-class="active">Overview</router-link>
         <router-link to="/orders-received">View Orders Received</router-link>
      <router-link to="/order-history">Orders History</router-link>
        <!-- <router-link to="/account" exact-active-class="active">Account</router-link> -->
        <router-link to="/password-change" exact-active-class="active">Change Password</router-link>
           <!-- Selling Collapsible -->
     
        <div class="collapsible-content">
          <router-link to="/add-product">Add Product</router-link>
          <router-link to="/manage-products">Manage Products</router-link>
        </div>
      
      </aside>
  
      <section class="dashboard-content">
        <h2>Orders Received</h2>
        
        <!-- Success Alert -->
        <div v-if="successMessage" class="success-alert mb-6">
          <div class="alert-content">
            <div class="alert-icon">✓</div>
            <div class="alert-message">{{ successMessage }}</div>
            <button @click="dismissAlert" class="dismiss-btn">×</button>
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="loading-state text-center my-12">
          <div class="spinner"></div>
          <p class="mt-4 text-gray-600">Loading your orders...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="error-state text-center my-12 p-6 bg-red-50 rounded-lg">
          <div class="error-icon mb-4">❌</div>
          <p class="error-message mb-4">{{ error }}</p>
          <button @click="fetchOrders" class="retry-btn">Try Again</button>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="!orders.length" class="empty-state text-center my-12 p-8 bg-white rounded-lg shadow-sm">
          <div class="empty-icon mb-4">📦</div>
          <h3 class="text-xl font-semibold mb-2">No orders received yet</h3>
          <p class="text-gray-600 mb-6">You haven't received any orders yet.</p>
        </div>
        
        <!-- Orders List -->
        <div v-else class="orders-container">
          <div v-for="order in orders" :key="order.order_id" :id="`order-${order.order_id}`" class="order-card mb-6">
            <div class="order-header">
              <div class="order-meta">
                <div class="order-id">
                  <span class="label">Order ID:</span>
                  <span class="value">#{{ order.order_id }}</span>
                </div>
                <div class="order-date">
                  <span class="label">Date:</span>
                  <span class="value">{{ formatDate(order.created_at) }}</span>
                </div>
              </div>
              <div class="order-status" :class="getStatusClass(order.status)">
                {{ capitalizeFirstLetter(order.status) }}
              </div>
            </div>
            
            <div class="order-body">
              <div class="order-items">
                <div v-for="item in order.items" :key="item.id" class="order-item">
                  <div class="item-image-container">
                    <img 
                      :src="item.image_path ? item.image_path : 'default-image.jpg'" 
                      :alt="item.name" 
                      class="item-image"
                    />
                  </div>
                  <div class="item-details">
                    <h4 class="item-name">{{ item.name }}</h4>
                    <div class="item-meta">
                      <span class="item-price"> PKR {{ item.price.toLocaleString() }}</span>
                      <span class="item-quantity">× {{ item.quantity }}</span>
                    </div>
                    <div class="item-status">
                      <label>Status:</label>
                      <select v-model="item.status" @change="updateItemStatus(order.order_id, item.id, item.status)">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                      </select>
                    </div>
                  </div>
                  <div class="item-subtotal">
                     PKR{{ (item.price * item.quantity).toLocaleString() }}
                  </div>
                </div>
              </div>
            </div>
            
            <div class="order-footer">
              <div class="order-totals">
                <div class="total-row">
                  <span>Total Items </span>
                  <span>{{ getTotalItems(order) }}</span>
                </div>
                <div class="total-row grand-total">
                  <span>Grand Total</span>
                  <span> PKR {{ order.total_amount.toLocaleString() }}</span>
                </div>
              </div>
              
              <div class="order-actions">
                <button @click="updateAllItemsStatus(order.order_id, 'processing')" class="process-btn">
                  Mark All as Processing
                </button>
                <button @click="updateAllItemsStatus(order.order_id, 'shipped')" class="ship-btn">
                  Mark All as Shipped
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </template>
  
  <script>
  export default {
    name: "OrdersReceived",
    data() {
      return {
        orders: [],
        loading: false,
        error: null,
        successMessage: null,
        highlightedOrderId: null,
        user: {
          role: ""
        }
      };
    },
    computed: {
      overviewRoute() {
        switch (this.user.role) {
          case "artist": return "/artist-dashboard";
          case "scrapSeller": return "/scrap-seller-dashboard";
          case "admin": return "/admin-dashboard";
          default: return "/";
        }
      }
    },
    methods: {
      
fetchUserDetails() {
  const token = localStorage.getItem("access_token");
  if (!token) {
    this.$router.push('/login');
    return;
  }
  
  fetch('http://127.0.0.1:8000/api/user', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to fetch user details');
      }
      return response.json();
    })
    .then(data => {
      this.user = data;
    })
    .catch(err => {
      console.error("Error fetching user details:", err);
    });
},
      async fetchOrders() {
  this.loading = true;
  this.error = null;
  
  const token = localStorage.getItem("access_token");
  if (!token) {
    this.error = "No authentication token found. Please login again.";
    this.loading = false;
    return;
  }
  
  try {
    console.log('Attempting to fetch orders with token', token.substring(0, 10) + '...');
    
    const response = await fetch('http://127.0.0.1:8000/api/orders/seller', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      console.error('Server response error:', response.status, response.statusText);
      
      // Try to get more detailed error if available
      let errorDetail = '';
      try {
        const errorData = await response.json();
        errorDetail = errorData.error || errorData.message || '';
        console.error('Error details:', errorData);
      } catch (e) {
        // Response might not contain JSON
      }
      
      throw new Error(`Failed to fetch orders (${response.status}): ${errorDetail}`);
    }
    
    const data = await response.json();
    console.log('Orders data received:', data);
    this.orders = data;
          
          if (this.highlightedOrderId) {
            this.$nextTick(() => {
              const element = document.getElementById(`order-${this.highlightedOrderId}`);
              if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
                element.classList.add('highlight-order');
                setTimeout(() => {
                  element.classList.remove('highlight-order');
                }, 3000);
              }
            });
          }
        } catch (err) {
          console.error("Error fetching orders:", err);
          this.error = "Could not load your orders. Please try again.";
        } finally {
          this.loading = false;
        }
      },
      
      formatDate(dateString) {
        const options = { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString(undefined, options);
      },
      
      capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      },
      
      getStatusClass(status) {
        const statusClasses = {
          'pending': 'status-pending',
          'processing': 'status-processing',
          'shipped': 'status-shipped',
          'delivered': 'status-delivered',
          'cancelled': 'status-cancelled'
        };
        return statusClasses[status] || 'status-default';
      },
      
      getTotalItems(order) {
        return order.items.reduce((total, item) => total + item.quantity, 0);
      },
      
      async updateItemStatus(orderId, itemId, status) {
        const token = localStorage.getItem("access_token");
        
        try {
          const response = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}/status`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              status: status,
              item_id: itemId
            })
          });
          
          if (!response.ok) {
            throw new Error('Failed to update status');
          }
          
          this.successMessage = "Order item status updated successfully";
        } catch (err) {
          console.error("Error updating status:", err);
          this.error = "Failed to update status. Please try again.";
        }
      },
      
      async updateAllItemsStatus(orderId, status) {
        const token = localStorage.getItem("access_token");
        
        try {
          const response = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}/status`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              status: status
            })
          });
          
          if (!response.ok) {
            throw new Error('Failed to update status');
          }
          
          // Update local state
          const order = this.orders.find(o => o.order_id === orderId);
          if (order) {
            order.items.forEach(item => {
              item.status = status;
            });
            order.status = status;
          }
          
          this.successMessage = `All items marked as ${status}`;
        } catch (err) {
          console.error("Error updating status:", err);
          this.error = "Failed to update status. Please try again.";
        }
      },
      
      dismissAlert() {
        this.successMessage = null;
      }
    },
    mounted() {
      this.fetchUserDetails();
      
      if (this.$route.params.message) {
        this.successMessage = this.$route.params.message;
      }
      
      if (this.$route.params.orderId) {
        this.highlightedOrderId = this.$route.params.orderId;
      }
      
      this.fetchOrders();
    }
  };
  </script>
  
  
  
  <style scoped>
  .user-account-dashboard {
    display: flex;
    background-color: #f7f9fc;
    min-height: 100vh;
  }
  
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
  
  .dashboard-content {
    flex: 1;
    padding: 2rem;
    background-color: #f7f9fc;
  }
  
  .dashboard-content h2 {
    font-size: 28px;
    color: #3B1E54;
    margin-bottom: 1.5rem;
  }
  
  .loading, .error, .no-orders {
    text-align: center;
    padding: 2rem;
    font-size: 1.2rem;
    color: #3B1E54;
  }
  
  .error {
    color: #e74c3c;
  }
  
  .order-card {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    padding: 1.5rem;
  }
  
  .order-header {
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
  }
  
  .order-header h3 {
    font-size: 1.5rem;
    color: #3B1E54;
    margin-bottom: 0.5rem;
  }
  
  .order-date, .order-status, .order-customer, .order-total {
    color: #666;
    margin: 0.3rem 0;
  }
  
  .order-details {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
  }
  
  .order-details h4 {
    font-size: 1.2rem;
    color: #3B1E54;
    margin-bottom: 0.5rem;
  }
  
  .order-items h4 {
    font-size: 1.2rem;
    color: #3B1E54;
    margin-bottom: 1rem;
  }
  
  .order-item {
    display: flex;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #f9f9f9;
    border-radius: 6px;
  }
  
  .item-image {
    width: 80px;
    height: 80px;
    margin-right: 1rem;
    flex-shrink: 0;
  }
  
  .item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
  }
  
  .no-image {
    width: 100%;
    height: 100%;
    background-color: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    border-radius: 4px;
  }
  
  .item-details {
    flex-grow: 1;
  }
  
  .item-name {
    font-weight: bold;
    color: #3B1E54;
    margin-bottom: 0.3rem;
  }
  
  .item-price, .item-subtotal {
    color: #666;
    margin-bottom: 0.3rem;
  }
  
  .item-status {
    margin-top: 0.5rem;
  }
  
  .item-status label {
    margin-right: 0.5rem;
    color: #3B1E54;
  }
  
  .item-status select {
    padding: 0.3rem;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: white;
  }
  
  .order-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
  }
  
  .order-actions button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s;
  }
  
  .btn-processing {
    background-color: #3498db;
    color: white;
  }
  
  .btn-processing:hover {
    background-color: #2980b9;
  }
  
  .btn-shipped {
    background-color: #f39c12;
    color: white;
  }
  
  .btn-shipped:hover {
    background-color: #d35400;
  }
  
  .btn-delivered {
    background-color: #2ecc71;
    color: white;
  }
  
  .btn-delivered:hover {
    background-color: #27ae60;
  }
  
  @media screen and (max-width: 768px) {
    .user-account-dashboard {
      flex-direction: column;
    }
    
    .side-nav {
      width: 100%;
      flex-direction: row;
      flex-wrap: wrap;
      justify-content: center;
      padding: 0.5rem;
    }
    
    .side-nav a {
      margin: 0.3rem;
      padding: 0.5rem;
    }
    
    .order-item {
      flex-direction: column;
    }
    
    .item-image {
      margin-right: 0;
      margin-bottom: 1rem;
    }
    
    .order-actions {
      flex-direction: column;
    }
  }
  </style>
<template>
  <div class="order-summary-stats">
    <div class="row">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Pending Orders</h5>
            <h2 class="card-value">{{ pendingCount }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Processing</h5>
            <h2 class="card-value">{{ processingCount }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Shipped</h5>
            <h2 class="card-value">{{ shippedCount }}</h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Delivered</h5>
            <h2 class="card-value">{{ deliveredCount }}</h2>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'OrderSummaryStats',
  
  data() {
    return {
      pendingCount: 0,
      processingCount: 0,
      shippedCount: 0,
      deliveredCount: 0
    }
  },
  
  created() {
    this.fetchOrderStats();
  },
  
  methods: {
    async fetchOrderStats() {
      try {
        const response = await axios.get('/api/seller/orders');
        if (response.data.success) {
          const orders = response.data.data;
          this.calculateStats(orders);
        }
      } catch (error) {
        console.error('Error fetching order stats:', error);
      }
    },
    
    calculateStats(orders) {
      // Reset counters
      this.pendingCount = 0;
      this.processingCount = 0;
      this.shippedCount = 0;
      this.deliveredCount = 0;
      
      // Count items by status
      orders.forEach(orderData => {
        orderData.items.forEach(item => {
          switch (item.status) {
            case 'pending':
              this.pendingCount++;
              break;
            case 'processing':
              this.processingCount++;
              break;
            case 'shipped':
              this.shippedCount++;
              break;
            case 'delivered':
              this.deliveredCount++;
              break;
          }
        });
      });
    }
  }
}
</script>

<style scoped>
.order-summary-stats {
  margin-bottom: 30px;
}

.card {
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-title {
  color: #666;
  font-size: 14px;
  margin-bottom: 5px;
}

.card-value {
  font-size: 32px;
  font-weight: bold;
  margin: 0;
}
</style>
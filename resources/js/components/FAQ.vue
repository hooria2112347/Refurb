<template>
  <div class="faq-page">
    <div class="container">
      <div class="faq-header">
        <h1>Frequently Asked Questions</h1>
        <p>Find answers to common questions about Refurb</p>
      </div>

      <div class="faq-list">
        <div 
          v-for="(faq, index) in faqs" 
          :key="index" 
          class="faq-item"
          :class="{ 'active': activeIndex === index }"
        >
          <div 
            class="faq-question" 
            @click="toggleFaq(index)"
          >
            <h3>{{ faq.question }}</h3>
            <span class="faq-icon">{{ activeIndex === index ? '−' : '+' }}</span>
          </div>
          
          <div 
            class="faq-answer"
            :style="{ maxHeight: activeIndex === index ? 'none' : '0' }"
          >
            <div class="faq-answer-content" v-html="faq.answer"></div>
          </div>
        </div>
      </div>

      <div class="contact-section">
        <h2>Still have questions?</h2>
        <p>Can't find what you're looking for? We're here to help!</p>
        <div class="contact-info">
          <p><strong>Email:</strong> <a href="mailto:refurb1111@gmail.com">refurb1111@gmail.com</a></p>
          <!-- <router-link to="/contact" class="contact-btn">Contact Us</router-link> -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FAQ',
  data() {
    return {
      activeIndex: null,
      faqs: [
        {
          question: "How do I place an order?",
          answer: "Simply browse our products, add your chosen item to the cart, and follow the checkout process."
        },
        {
          question: "How long does delivery take?",
          answer: "Delivery usually takes <strong>3-5 business days</strong>. You'll get a tracking number once your order is dispatched."
        },
        {
          question: "How can I contact customer support?",
          answer: "You can reach us at <a href='mailto:refurb1111@gmail.com'>refurb1111@gmail.com</a> or through our <strong>Contact Us</strong> page. We're happy to help!"
        },
        {
          question: "What is Refurb?",
          answer: "Refurb is a platform that transforms waste into wonderful creations. We connect artists who create beautiful, sustainable products from recycled materials with eco-conscious consumers."
        },
        
        {
          question: "Can I return or exchange items?",
          answer: "Due to the unique, handmade nature of our products, we have a limited return policy. Please contact our customer support team within 7 days of delivery for assistance with any issues."
        },
        {
          question: "How do I become an artist seller on Refurb?",
          answer: "We'd love to have you join our community! Simply sign up for an artist account and submit your application with examples of your work. Our team will review your application and get back to you within 5-7 business days."
        },
        {
          question: "Is my payment information secure?",
          answer: "Absolutely! We use secure payment processing and never store your payment information on our servers. All transactions are encrypted and protected."
        }
      ]
    }
  },
  methods: {
    toggleFaq(index) {
      this.activeIndex = this.activeIndex === index ? null : index;
    }
  }
}
</script>

<style scoped>
.faq-page {
  min-height: 100vh;
  background-color: #f9f9f9;
  padding: 2rem 0;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 2rem;
}

.faq-header {
  text-align: center;
  margin-bottom: 3rem;
}

.faq-header h1 {
  color: #3B1E54;
  font-size: 2.5rem;
  margin-bottom: 1rem;
  font-weight: bold;
}

.faq-header p {
  color: #666;
  font-size: 1.1rem;
}

.faq-list {
  margin-bottom: 3rem;
}

.faq-item {
  background-color: white;
  border-radius: 8px;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
}

.faq-item:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.faq-item.active {
  border-left: 4px solid #9B7EBD;
}

.faq-question {
  padding: 1.5rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background-color 0.3s ease;
}

.faq-question:hover {
  background-color: #f8f8f8;
}

.faq-question h3 {
  margin: 0;
  color: #3B1E54;
  font-size: 1.1rem;
  font-weight: 600;
}

.faq-icon {
  font-size: 1.5rem;
  color: #9B7EBD;
  font-weight: bold;
  transition: transform 0.3s ease;
}

.faq-item.active .faq-icon {
  transform: rotate(180deg);
}

.faq-answer {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
}

.faq-item.active .faq-answer {
  max-height: 200px;
}

.faq-answer-content {
  padding: 0 1.5rem 1.5rem;
  color: #555;
  line-height: 1.6;
}

.faq-answer-content a {
  color: #9B7EBD;
  text-decoration: none;
}

.faq-answer-content a:hover {
  text-decoration: underline;
}

.faq-answer-content strong {
  color: #3B1E54;
}

.contact-section {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.contact-section h2 {
  color: #3B1E54;
  margin-bottom: 1rem;
}

.contact-section p {
  color: #666;
  margin-bottom: 1.5rem;
}

.contact-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.contact-info p {
  margin: 0;
}

.contact-info a {
  color: #9B7EBD;
  text-decoration: none;
}

.contact-info a:hover {
  text-decoration: underline;
}

.contact-btn {
  background-color: #D4BEE4;
  color: #3B1E54;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.contact-btn:hover {
  background-color: #9B7EBD;
  color: white;
}

/* Responsive */
@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }

  .faq-header h1 {
    font-size: 2rem;
  }

  .faq-question {
    padding: 1rem;
  }

  .faq-question h3 {
    font-size: 1rem;
  }

  .faq-answer-content {
    padding: 0 1rem 1rem;
  }

  .contact-section {
    padding: 1.5rem;
  }

  .contact-info {
    flex-direction: column;
  }
}
</style>
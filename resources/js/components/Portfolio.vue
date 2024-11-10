<template>
    <div class="portfolio">
      <h2>My Portfolio</h2>
      <ul>
        <li v-for="project in portfolio" :key="project.id">
          <h3>{{ project.title }}</h3>
          <p>{{ project.description }}</p>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        portfolio: [
          { id: 1, title: "Upcycled Chair", description: "Refurbished an old chair." },
          // additional portfolio projects...
        ]
      };
    }
  };
  </script>
  
  <style scoped>
  /* Portfolio page styles */
  </style>
  
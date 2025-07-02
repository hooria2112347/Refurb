<template>
  <div class="about-page">
    <div class="container grid-layout">
      <div class="about-header">
        <h1>About Refurb</h1>
        <p>Transforming waste into wonder through the power of creative collaboration</p>
      </div>

      <!-- Mission -->
      <div class="card">
        <h2>Our Mission</h2>
        <p>
          At <span class="highlight">Refurb</span>, we believe that every piece of scrap has the potential to become something beautiful.
          Our mission is to <span class="highlight">bridge the gap between waste and artistry</span>, creating a sustainable ecosystem
          where discarded materials find new life through the hands of talented artists and craftspeople.
        </p>
       
      </div>

      <!-- How It Works -->
      <div class="card">
        <h2>How It Works</h2>
        <p><strong>Scrap Sellers</strong> can easily list their materials, from metal scraps and wood remnants to fabric offcuts and electronic components. Our platform makes it simple to connect with artists who are actively seeking these specific materials for their projects.</p>
        <p><strong>Artists</strong> can browse, search, and discover unique materials that spark their creativity. Whether you're a furniture maker, jewelry designer, sculptor, or DIY enthusiast, you'll find the perfect materials to bring your vision to life.</p>
      </div>

      <!-- Values -->
      <div class="card values">
        <h2>Our Values</h2>
        <div class="grid-values">
          <div class="value">
            <div class="icon">🌱</div>
            <h3>Sustainability</h3>
            <p>Reducing waste by giving materials a second chance at life</p>
          </div>
          <div class="value">
            <div class="icon">🎨</div>
            <h3>Creativity</h3>
            <p>Empowering artists to transform the ordinary into extraordinary</p>
          </div>
          <div class="value">
            <div class="icon">🤝</div>
            <h3>Community</h3>
            <p>Building connections between sellers, artists, and eco-conscious consumers</p>
          </div>
          <div class="value">
            <div class="icon">💡</div>
            <h3>Innovation</h3>
            <p>Pioneering new ways to think about waste and creative potential</p>
          </div>
        </div>
      </div>

      <!-- Why Choose Us -->
      <div class="card">
        <h2>Why Choose Refurb?</h2>
        <p><strong>For Sellers:</strong> Turn your waste into income while contributing to environmental sustainability. Our platform helps you reach artists who specifically need your materials, ensuring better prices and faster sales.</p>
        <p><strong>For Artists:</strong> Access unique, affordable materials that you can't find anywhere else. Connect with reliable suppliers and discover new materials that inspire your next masterpiece.</p>
        <p><strong>For Everyone:</strong> Be part of a movement that's reshaping how we think about waste, creativity, and community. Every transaction on our platform is a step toward a more sustainable future.</p>
      </div>

      <!-- Story -->
      <div class="card">
        <h2>Our Story</h2>
        <p>Refurb was born from a simple observation: too much valuable material was ending up in landfills while artists struggled to find affordable, unique supplies for their projects. We realized that by connecting these two groups, we could solve both problems while creating something beautiful in the process.</p>
    
      </div>

      <!-- CTA -->
      <div class="cta">
        <h2>Ready to Join Our Community?</h2>
        <p>Whether you have materials to sell or projects to create, Refurb is here to help you make it happen.</p>
       
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AboutRefurb",
};
</script>
<style scoped>
.about-page {
  background-color: #fdfdfd;
  padding: 2rem 1rem;
  color: #333;
  font-family: 'Segoe UI', sans-serif;
}

.container.grid-layout {
  display: grid;
  gap: 2rem;
  max-width: 1000px;
  margin: 0 auto;
  grid-template-columns: 1fr;
}

.about-header {
  text-align: center;
}

.about-header h1 {
  font-size: 2.5rem;
  color: #3b1e54;
}

.about-header p {
  color: #666;
  font-size: 1.1rem;
}

.card {
  background-color: #ffffff;
  border: 1px solid #e3d9f3;
  border-left: 5px solid #9b7ebd;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card h2 {
  color: #3b1e54;
  margin-bottom: 1rem;
}

.card p {
  margin-bottom: 0.8rem;
  line-height: 1.6;
}

.highlight {
  color: #9b7ebd;
  font-weight: 600;
}

.values .grid-values {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.value {
  background-color: #f6f2fa;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
}

.value .icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.value h3 {
  color: #3b1e54;
  font-size: 1.1rem;
  margin-bottom: 0.3rem;
}

.cta {
  background-color: #fefefe;
  text-align: center;
  padding: 2rem 1.5rem;
  border: 1px solid #e0d1ef;
  border-radius: 10px;
}

.cta h2 {
  color: #3b1e54;
  margin-bottom: 1rem;
}

.cta p {
  color: #666;
  margin-bottom: 2rem;
}

.cta-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.btn.primary {
  background-color: #9b7ebd;
  color: white;
}

.btn.primary:hover {
  background-color: #3b1e54;
}

.btn.secondary {
  background-color: #d4bee4;
  color: #3b1e54;
}

.btn.secondary:hover {
  background-color: #9b7ebd;
  color: white;
}

@media (max-width: 600px) {
  .cta-buttons {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    text-align: center;
  }
}
</style>

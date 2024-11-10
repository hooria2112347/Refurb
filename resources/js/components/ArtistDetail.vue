<template>
  <div class="artist-detail" v-if="artist">
    <div class="profile-container">
      <div class="profile-info">
        <img :src="artist.image" :alt="artist.name" class="artist-image" />
        <h2 class="artist-name">{{ artist.name }}</h2>
        <p class="artist-location">Location: {{ artist.location }}</p>
        <h3>About</h3>
        <p class="artist-description">{{ artist.description }}</p>
        <h3>Statement</h3>
        <p class="artist-statement">{{ artist.statement }}</p>
      </div>
      <div class="projects-section">
        <h3>Projects</h3>
        <ul class="project-list">
          <li v-for="project in artist.projects" :key="project.id" class="project-item">
            {{ project.name }}
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div v-else>
    <p>Artist not found.</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      artists: [
        {
          id: '1',
          name: "Jane Doe",
          location: "Orleans, Ontario",
          description: "Experienced in upcycled furniture.",
          image: "/images/people1.jpeg",
          projects: [
            { id: 1, name: "Refurbished Chair" },
            { id: 2, name: "Upcycled Table" }
          ],
          statement: "Art is an expression of creativity and a way to communicate one's inner thoughts and feelings."
        },
        {
          id: '2',
          name: "John Smith",
          location: "Toronto, Ontario",
          description: "Focused on sustainable art pieces.",
          image: "/images/people2.jpg",
          projects: [
            { id: 1, name: "Recycled Metal Art" },
            { id: 2, name: "Eco-Friendly Decor" }
          ],
          statement: "I believe in creating art that not only looks good but also contributes to a better environment."
        },
        {
          id: '3',
          name: "Emily Ray",
          location: "Ottawa, Ontario",
          description: "Creative with reclaimed wood.",
          image: "/images/people3.jpg",
          projects: [
            { id: 1, name: "Wooden Sculpture" },
            { id: 2, name: "Upcycled Shelves" }
          ],
          statement: "My work focuses on sustainability and the beauty of nature."
        }
      ],
      artist: null // This will hold the selected artist's details
    };
  },
  mounted() {
    // Find the artist based on the route parameter
    const artistId = this.$route.params.artistId;
    this.artist = this.artists.find(artist => artist.id === artistId);
  }
};
</script>

<style scoped>
.artist-detail {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 2rem;
  background-color: #ffffff; /* White background for contrast */
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.profile-container {
  display: flex;
  align-items: flex-start; /* Align items to the top */
}

.profile-info {
  flex: 1; /* Take up space on the left */
  padding-right: 2rem; /* Space between profile and projects */
  text-align: left;
}

.artist-image {
  width: 150px; /* Fixed width for profile picture */
  height: 150px; /* Fixed height for profile picture */
  border-radius: 50%; /* Circular profile picture */
  object-fit: cover; /* Ensure the image fits */
  margin-bottom: 1rem; /* Space below the image */
  border: 2px solid #CA7373; /* Border around the image */
}

.artist-name {
  font-size: 2rem;
  color: #CA7373; /* Title color */
  margin: 0.5rem 0;
}

.artist-location {
  font-size: 1rem;
  color: #777; /* Lighter color for location */
  margin-bottom: 1rem;
}

.artist-description,
.artist-statement {
  font-size: 1rem;
  color: #555;
  margin-bottom: 1rem;
}

.projects-section {
  flex: 2; /* Take up more space on the right */
}

.projects-section h3 {
  font-size: 1.5rem;
  color: #333; /* Darker color for subtitles */
  margin: 1rem 0;
}

.project-list {
  list-style-type: none;
  padding: 0;
}

.project-item {
  font-size: 1rem;
  background-color: #f9f9f9; /* Light background for project items */
  padding: 0.75rem;
  margin: 0.5rem 0;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.project-item:hover {
  background-color: #e0e0e0; /* Change background on hover */
  cursor: pointer;
}
</style>

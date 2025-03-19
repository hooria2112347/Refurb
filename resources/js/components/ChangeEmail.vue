<template>
    <div class="change-email-container">
      <h2>Change Email</h2>
  
      <!-- STEP NAVIGATION -->
      <div class="steps">
        <button 
          :class="{ active: currentStep === 1 }" 
          @click="currentStep = 1"
        >
          Step 1: Send Code
        </button>
        <button 
          :class="{ active: currentStep === 2 }" 
          @click="currentStep = 2"
          :disabled="!codeSent"
        >
          Step 2: Verify Code
        </button>
        <button 
          :class="{ active: currentStep === 3 }" 
          @click="currentStep = 3"
          :disabled="!codeVerified"
        >
          Step 3: Confirm Email Change
        </button>
      </div>
  
      <!-- SUCCESS MESSAGE -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
        <button @click="successMessage = ''" class="close">&times;</button>
      </div>
  
      <!-- ERROR MESSAGE -->
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
        <button @click="errorMessage = ''" class="close">&times;</button>
      </div>
  
      <!-- STEP 1: SEND CODE FORM -->
      <form v-if="currentStep === 1" @submit.prevent="sendChangeCode">
        <div class="form-group">
          <label for="currentEmail">Current Email</label>
          <input
            id="currentEmail"
            type="email"
            v-model="form.currentEmail"
            placeholder="Enter your current email"
            required
          />
        </div>
  
        <div class="form-group">
          <label for="newEmail">New Email</label>
          <input
            id="newEmail"
            type="email"
            v-model="form.newEmail"
            placeholder="Enter your new email"
            required
          />
        </div>
  
        <button type="submit">Send Verification Code</button>
      </form>
  
      <!-- STEP 2: VERIFY CODE FORM -->
      <form v-else-if="currentStep === 2" @submit.prevent="verifyChangeCode">
        <div class="form-group">
          <label for="verificationCode">Verification Code</label>
          <input
            id="verificationCode"
            type="text"
            v-model="form.code"
            placeholder="Enter the code sent to your new email"
            required
          />
        </div>
  
        <button type="submit">Verify Code</button>
      </form>
  
      <!-- STEP 3: CONFIRM EMAIL CHANGE -->
      <form v-else @submit.prevent="changeEmail">
        <p>Click below to confirm your email change.</p>
        <button type="submit">Change Email</button>
      </form>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    name: "ChangeEmail",
    data() {
      return {
        currentStep: 1,        // Manages which step of the process we're on
        codeSent: false,       // Flag to indicate if code was sent successfully
        codeVerified: false,   // Flag to indicate if code was verified successfully
  
        form: {
          currentEmail: "",
          newEmail: "",
          code: "",            // The verification code
        },
  
        successMessage: "",
        errorMessage: "",
      };
    },
    methods: {
      // STEP 1: Send code to new email
      sendChangeCode() {
        this.resetMessages();
        axios
          .post("/api/send-change-code", {
            current_email: this.form.currentEmail,
            new_email: this.form.newEmail,
          })
          .then((response) => {
            this.successMessage = response.data.message || "Code sent successfully";
            this.codeSent = true;
            this.currentStep = 2;
          })
          .catch((error) => {
            this.errorMessage = error.response?.data?.message || "Failed to send code";
          });
      },
  
      // STEP 2: Verify the code
      verifyChangeCode() {
        this.resetMessages();
        axios
          .post("/api/verify-change-code", {
            new_email: this.form.newEmail,
            code: this.form.code,
          })
          .then((response) => {
            this.successMessage = response.data.message || "Code verified successfully";
            this.codeVerified = true;
            this.currentStep = 3;
          })
          .catch((error) => {
            this.errorMessage = error.response?.data?.message || "Failed to verify code";
          });
      },
  
      // STEP 3: Actually change the email
      changeEmail() {
        this.resetMessages();
        axios
          .post("/api/change-email", {
            current_email: this.form.currentEmail,
            new_email: this.form.newEmail,
            code: this.form.code,
          })
          .then((response) => {
            this.successMessage = response.data.message || "Email changed successfully";
            this.resetForm();
          })
          .catch((error) => {
            this.errorMessage = error.response?.data?.message || "Failed to change email";
          });
      },
  
      resetForm() {
        this.form.currentEmail = "";
        this.form.newEmail = "";
        this.form.code = "";
        this.codeSent = false;
        this.codeVerified = false;
        this.currentStep = 1;
      },
  
      resetMessages() {
        this.successMessage = "";
        this.errorMessage = "";
      },
    },
  };
  </script>
  
  <style scoped>
  .change-email-container {
    max-width: 500px;
    margin: 0 auto;
    background-color: #ffffff;
    padding: 1.5rem;
    border-radius: 8px;
  }
  
  h2 {
    text-align: center;
    margin-bottom: 1rem;
  }
  
  .steps {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
  }
  
  .steps button {
    margin: 0 0.5rem;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border: 1px solid #ccc;
    background-color: #eee;
    transition: background-color 0.3s;
  }
  
  .steps button.active {
    background-color: #d4bee4;
    border-color: #b99ed2;
  }
  
  .steps button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
  }
  
  .form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  
  button[type="submit"] {
    padding: 0.75rem 1rem;
    background-color: #d4bee4;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
  }
  
  button[type="submit"]:hover {
    background-color: #b99ed2;
  }
  
  .success-message {
    background-color: #27ae60;
    color: #fff;
    padding: 0.75rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .error-message {
    background-color: #e74c3c;
    color: #fff;
    padding: 0.75rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #fff;
  }
  </style>
  
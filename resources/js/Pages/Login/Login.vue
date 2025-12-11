<script>
import axios from "axios";

export default {
  name: "Login",

  data() {
    return {
      email: "",
      password: "",
      showPassword: false,
      errors: [],
      loading: false,
    };
  },

  methods: {
    async submitLogin() {
      this.errors = [];
      this.loading = true;

      try {
        const res = await axios.post("/logincheck", {
          email: this.email,
          temppassword: this.password,
        });

        if (res.data.success) {
          window.location.href = "/dashboard";
        }
      }
      catch (err) {
        const r = err.response;

        // Laravel validation errors
        if (r?.status === 422 && r.data?.errors) {
          this.errors = Object.values(r.data.errors).flat();
        }
        // Server errors
        else if (r?.status >= 500) {
          this.errors = ["Server error. Please try again later."];
        }
        // Network issues
        else if (err.message === "Network Error") {
          this.errors = ["Network error. Check your connection."];
        }
        // Fallback
        else {
          this.errors = ["Something went wrong. Try again."];
        }
      }

      this.loading = false;
    }
  }
};
</script>

<template>
  <div class="container-fluid layout">
    <div class="row auth-container">
      <div class="col left">
        <img src="../../../../public/images/lost-and-found-illustration.png" alt="reload">
      </div>

      <div class="col right">
        <img src="../../../../public/images/dnscfindr-logo-light.png" alt="reload">

        <!-- LOGIN FORM -->

        <div class="auth-form" id="user-login-form">
          <form @submit.prevent="submitLogin">

            <div v-if="errors.length" class="errorMsgContainer">
              <small v-for="(err, index) in errors" :key="index" class="errorMsg">
                {{ err }}
              </small>
            </div>

            <label for="email" class="input-label">Your Email</label>
            <input type="email" id="email" placeholder="example@dnsc.edu.ph" name="email" v-model="email">

            <label for="password" class="input-label">Password</label>
            <input :type="showPassword ? 'text' : 'password'" id="password" placeholder="password123" name="password"
              v-model="password">

            <div class="login-essential">
              <div>
                <input type="checkbox" id="user-show-password" v-model="showPassword">
                <label for="user-show-password">Show Password</label>
              </div>
              <div>
                <a data-bs-toggle="modal" data-bs-target="#lost-password-modal">Forgot Password?</a>
              </div>
            </div>

            <button type="submit" class="btn login-button" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              <span v-if="loading">Logging in...</span>
              <span v-else>Login</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <div id="links">
      <div>
        <p>DNSC FINDR - LOST AND FOUND SYSTEM</p>
      </div>
      <div>
        <a href="/about">ABOUT</a>
        <a href="/how-it-works">HOW IT WORKS?</a>
      </div>
    </div>

    <!-- LOST PASSWORD MODAL -->
     
    <div class="modal fade primary-modal" id="lost-password-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Forgot Your Password?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body text-center" style="color: #333;">

            <strong>How to reset your password:</strong>

            <p>You can visit the OSDS office to reset your password.</p>
            <br>
            <p>
              OSDS will reset your forgotten password and issue a new temporary password. You will be required to change
              this password upon logging in.
            </p>

          </div>

          <div class="modal-footer">
            <div class="action-buttons">
              <button type="button" class="btn btn-sm primary-btn" data-bs-dismiss="modal">Close</button>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.layout {
  font-family: 'Montserrat', sans-serif;
  color: #333;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: #f2f3f7 !important;
  background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url("../../../../public/images/dnsc-background.jpg");
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  background-attachment: fixed;
}

.layout #links {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.layout #links div:nth-child(1) {
  margin-top: 10px;
}

.layout #links div:nth-child(1) p {
  font-size: 14px;
  color: #9c9c9c;
  font-weight: 700;
  margin: 0;
}

.layout #links div:nth-child(2) {
  display: flex;
  flex-direction: row;
  gap: 15px;
}

.layout #links div:nth-child(2) a {
  font-size: 14px;
  text-decoration: none;
  color: #9c9c9c;
  margin: 0;
  cursor: pointer;
}

.layout #links div:nth-child(2) a:hover {
  text-decoration: underline;
}

/* LOGIN */

.auth-container {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  min-width: 50%;
  min-height: 60%;
  border-radius: 18px;
  background-color: #f2f3f7;
}

.auth-container .left {
  background-color: #f2f3f7;
  min-width: 50%;
  min-height: 100%;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
}

@media (max-width: 768px) {
  .auth-container .left {
    display: none;
  }
}

@media (min-width: 768px) and (max-width: 1024px) {
  .auth-container .left {
    display: none;
  }
}

.auth-container .left img {
  width: 200px;
}

.auth-container .right {
  background-color: #282829;
  min-width: 50%;
  min-height: 100%;
  border-radius: 16px;
  padding: 15px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

@media (max-width: 768px) {
  .auth-container .right {
    width: 360px;
  }
}

.auth-container .right img {
  width: 235px;
}

.auth-container .right .auth-form {
  margin-top: 18px;
}

.auth-container .right .auth-form form {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
}

/* Error Container */

.errorMsgContainer {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
  background: #ffe6e6;
  border: 1px solid #ffb3b3;
  border-left: 4px solid #ff4d4d;
  padding: 10px 14px;
  border-radius: 12px;
  margin-bottom: 10px;
  animation: fadeIn 0.3s ease-in-out;
}

.errorMsg {
  display: flex;
  align-items: center;
  color: #d60000;
  font-weight: 400;
}

.errorMsg::before {
  content: "⚠️";
  margin-right: 6px;
  font-size: 0.85rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-container .right .auth-form form .input-label {
  color: #f2f3f7;
  font-weight: 500;
  margin-bottom: -8px;
}

.auth-container .right .auth-form form input {
  font-weight: 400;
  color: #333;
  border-radius: 12px;
  padding: 8px;
  border: transparent 3px solid;
  outline: none;
}

.auth-container .right .auth-form form>input:active,
.auth-container .right .auth-form form>input:focus {
  border: #48c441 3px solid;
}

.auth-container .right .auth-form form .login-essential {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.auth-container .right .auth-form form .login-essential div:nth-child(1) {
  display: flex;
  align-items: center;
  gap: 8px;
}

.auth-container .right .auth-form form .login-essential div:nth-child(1)>label {
  color: #ececec;
  font-size: 0.9rem;
}

.auth-container .right .auth-form form .login-essential div:nth-child(2)>a {
  color: rgb(255, 85, 85);
  font-size: 0.9rem;
  text-decoration: none;
  cursor: pointer;
}

.auth-container .right .auth-form form .login-essential div:nth-child(2)>a:hover {
  color: rgb(255, 33, 33);
}

.auth-container .right .auth-form form .login-button {
  background-color: #48c441;
  border-radius: 12px;
  transition: 0.2s ease;
  text-decoration: none;
  color: #ececec;
  font-weight: 600;
}

.auth-container .right .auth-form form .login-button:hover {
  transform: scale(1.005);
  background-color: #3ca237;
}
</style>

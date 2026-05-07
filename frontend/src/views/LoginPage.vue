<template>
  <div class="login-wrapper">
    <div class="container-fluid px-0">
      <div class="row g-0 min-vh-100">
        <!-- Left Side - Brand Section -->
        <div class="col-lg-6 d-none d-lg-flex brand-section">
          <div class="brand-content">
            <div class="text-center text-white">
              <i class="bi bi-box-seam display-1 mb-4"></i>
              <h1 class="display-4 fw-bold mb-3">Welcome Back!</h1>
              <p class="lead mb-4">Manage your tasks efficiently</p>
              <div class="stats mt-5">
                <div class="row g-4">
                  <div class="col-6">
                    <div class="stat-item">
                      <h2 class="fw-bold mb-0">0</h2>
                      <p>Active Users</p>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="stat-item">
                      <h2 class="fw-bold mb-0">0</h2>
                      <p>Tasks Completed</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center form-section">
          <div class="form-container p-4 p-xl-5">
            <div class="text-center mb-4">
              <div class="logo-wrapper mb-3">
                <i class="bi bi-check2-circle display-1 text-primary"></i>
              </div>
              <h2 class="fw-bold text-gradient">Sign In</h2>
              <p class="text-muted">Access your account to manage tasks</p>
            </div>

            <form @submit.prevent="handleLogin">
              <!-- Email -->
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-envelope me-1"></i> Email Address
                </label>
                <input 
                  type="email" 
                  class="form-control form-control-lg" 
                  :class="{ 'is-invalid': errors.email }"
                  v-model="form.email" 
                  placeholder="john@example.com"
                >
                <div class="invalid-feedback" v-if="errors.email">
                  {{ errors.email }}
                </div>
              </div>

              <!-- Password -->
              <div class="mb-4">
                <label class="form-label fw-semibold">
                  <i class="bi bi-lock me-1"></i> Password
                </label>
                <div class="input-group">
                  <input 
                    :type="showPassword ? 'text' : 'password'" 
                    class="form-control form-control-lg" 
                    :class="{ 'is-invalid': errors.password }"
                    v-model="form.password" 
                    placeholder="••••••••"
                  >
                  <button 
                    class="btn btn-outline-secondary" 
                    type="button"
                    @click="showPassword = !showPassword"
                  >
                    <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                </div>
                <div class="invalid-feedback" v-if="errors.password">
                  {{ errors.password }}
                </div>
              </div>

              <!-- Remember Me & Forgot Password -->
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="remember" v-model="remember">
                  <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#" class="text-decoration-none small" @click.prevent="forgotPassword">
                  Forgot password?
                </a>
              </div>

              <!-- Error Alert -->
              <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ errorMessage }}
                <button type="button" class="btn-close" @click="errorMessage = ''"></button>
              </div>

              <!-- Submit Button -->
              <button 
                type="submit" 
                class="btn btn-primary btn-lg w-100 mb-3"
                :disabled="loading"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-box-arrow-in-right me-2"></i>
                {{ loading ? 'Signing In...' : 'Sign In' }}
              </button>

              <!-- Register Link -->
              <div class="text-center">
                <p class="mb-0">
                  Don't have an account? 
                  <router-link to="/register" class="text-primary fw-bold text-decoration-none">
                    Create Account
                  </router-link>
                </p>
              </div>
            </form>

            <!-- Social Login (Optional) -->
            <div class="mt-4">
              <div class="position-relative text-center mb-3">
                <hr>
                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">
                  Or continue with
                </span>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary w-100" @click="socialLogin('google')">
                  <i class="bi bi-google"></i> Google
                </button>
                <button class="btn btn-outline-secondary w-100" @click="socialLogin('github')">
                  <i class="bi bi-github"></i> GitHub
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'LoginPage',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errors: {},
      errorMessage: '',
      loading: false,
      showPassword: false,
      remember: false
    }
  },
  methods: {
    validateForm() {
      this.errors = {}
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(this.form.email)) {
        this.errors.email = 'Please enter a valid email address'
      }
      
      if (this.form.password.length < 1) {
        this.errors.password = 'Password is required'
      }
      
      return Object.keys(this.errors).length === 0
    },
    async handleLogin() {
      if (!this.validateForm()) {
        return
      }
      
      this.loading = true
      this.errorMessage = ''
      
      try {
        const response = await axios.post('/login', this.form)
        
        const token = response.data.token
        localStorage.setItem('token', token)
        
        if (this.remember) {
          localStorage.setItem('remember_token', token)
        }
        
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        this.$router.push('/')
      } catch (error) {
        if (error.response) {
          if (error.response.status === 401) {
            this.errorMessage = 'Invalid email or password'
          } else if (error.response.data.errors) {
            this.errors = error.response.data.errors
          } else {
            this.errorMessage = error.response.data.message || 'Login failed'
          }
        } else {
          this.errorMessage = 'Cannot connect to server. Please try again.'
        }
      } finally {
        this.loading = false
      }
    },
    forgotPassword() {
      alert('Please contact support to reset your password')
    },
    socialLogin(provider) {
      alert(`${provider} login coming soon!`)
    }
  }
}
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  background: #f8f9fa;
}

.brand-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

.brand-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-20px, -20px) scale(1.05); }
}

.brand-content {
  position: relative;
  z-index: 1;
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}

.stat-item {
  background: rgba(255,255,255,0.1);
  border-radius: 15px;
  padding: 1rem;
  backdrop-filter: blur(10px);
  transition: transform 0.3s ease;
}

.stat-item:hover {
  transform: translateY(-5px);
}

.form-section {
  background: white;
}

.form-container {
  max-width: 480px;
  width: 100%;
  margin: 0 auto;
}

.logo-wrapper {
  animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.text-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.form-control {
  border: 2px solid #e9ecef;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.1);
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.form-check-input:checked {
  background-color: #667eea;
  border-color: #667eea;
}

@media (max-width: 991px) {
  .form-container {
    padding: 2rem !important;
  }
}
</style>
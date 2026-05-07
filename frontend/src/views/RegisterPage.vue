<template>
  <div class="register-wrapper">
    <div class="container-fluid px-0">
      <div class="row g-0 min-vh-100">
        <!-- Left Side - Brand Section -->
        <div class="col-lg-6 d-none d-lg-flex brand-section">
          <div class="brand-content">
            <div class="text-center text-white">
              <i class="bi bi-check-circle-fill display-1 mb-4"></i>
              <h1 class="display-4 fw-bold mb-3">Task Manager</h1>
              <p class="lead mb-4">Organize your tasks efficiently</p>
              <div class="features mt-5">
                <div class="feature-item mb-3">
                  <i class="bi bi-kanban me-2"></i> Project Management
                </div>
                <div class="feature-item mb-3">
                  <i class="bi bi-list-check me-2"></i> Task Tracking
                </div>
                <div class="feature-item mb-3">
                  <i class="bi bi-people me-2"></i> Team Collaboration
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Registration Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center form-section">
          <div class="form-container p-4 p-xl-5">
            <div class="text-center mb-4">
              <h2 class="fw-bold text-gradient">Create Account</h2>
              <p class="text-muted">Get started with your free account</p>
            </div>

            <form @submit.prevent="handleRegister">
              <!-- Full Name -->
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-person-circle me-1"></i> Full Name
                </label>
                <input 
                  type="text" 
                  class="form-control form-control-lg" 
                  :class="{ 'is-invalid': errors.name }"
                  v-model="form.name" 
                  placeholder="John Doe"
                >
                <div class="invalid-feedback" v-if="errors.name">
                  {{ errors.name }}
                </div>
              </div>

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
              <div class="mb-3">
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
                <small class="text-muted">Minimum 6 characters</small>
              </div>

              <!-- Confirm Password -->
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <i class="bi bi-shield-check me-1"></i> Confirm Password
                </label>
                <input 
                  :type="showConfirmPassword ? 'text' : 'password'" 
                  class="form-control form-control-lg" 
                  :class="{ 'is-invalid': errors.password_confirmation }"
                  v-model="form.password_confirmation" 
                  placeholder="••••••••"
                >
                <div class="invalid-feedback" v-if="errors.password_confirmation">
                  {{ errors.password_confirmation }}
                </div>
              </div>

              <!-- Role Selection - NEW -->
              <div class="mb-4">
                <label class="form-label fw-semibold">
                  <i class="bi bi-person-badge me-1"></i> Account Type
                </label>
                <div class="d-flex gap-3">
                  <div class="form-check flex-grow-1">
                    <input 
                      class="form-check-input" 
                      type="radio" 
                      name="role" 
                      value="user" 
                      v-model="form.role"
                      id="roleUser"
                    >
                    <label class="form-check-label" for="roleUser">
                      <i class="bi bi-person text-info"></i> Regular User
                      <small class="d-block text-muted">Can view and complete assigned tasks</small>
                    </label>
                  </div>
                  <div class="form-check flex-grow-1">
                    <input 
                      class="form-check-input" 
                      type="radio" 
                      name="role" 
                      value="admin" 
                      v-model="form.role"
                      id="roleAdmin"
                    >
                    <label class="form-check-label" for="roleAdmin">
                      <i class="bi bi-shield-lock-fill text-danger"></i> Admin
                      <small class="d-block text-muted">Full control over projects, tasks, and users</small>
                    </label>
                  </div>
                </div>
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
                <i v-else class="bi bi-person-plus me-2"></i>
                {{ loading ? 'Creating Account...' : 'Sign Up' }}
              </button>

              <!-- Login Link -->
              <div class="text-center">
                <p class="mb-0">
                  Already have an account? 
                  <router-link to="/login" class="text-primary fw-bold text-decoration-none">
                    Sign In
                  </router-link>
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'RegisterPage',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'user'  // Default role is user
      },
      errors: {},
      errorMessage: '',
      loading: false,
      showPassword: false,
      showConfirmPassword: false
    }
  },
  methods: {
    validateForm() {
      this.errors = {}
      
      if (this.form.name.length < 2) {
        this.errors.name = 'Name must be at least 2 characters'
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(this.form.email)) {
        this.errors.email = 'Please enter a valid email address'
      }
      
      if (this.form.password.length < 6) {
        this.errors.password = 'Password must be at least 6 characters'
      }
      
      if (this.form.password !== this.form.password_confirmation) {
        this.errors.password_confirmation = 'Passwords do not match'
      }
      
      return Object.keys(this.errors).length === 0
    },
    async handleRegister() {
      if (!this.validateForm()) {
        return
      }
      
      this.loading = true
      this.errorMessage = ''
      
      try {
        const response = await axios.post('/register', this.form)
        
        // Store token and user info
        localStorage.setItem('token', response.data.token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
        
        // Redirect to dashboard
        this.$router.push('/')
        
      } catch (error) {
        if (error.response) {
          if (error.response.data.errors) {
            this.errors = error.response.data.errors
          } else {
            this.errorMessage = error.response.data.message || 'Registration failed'
          }
        } else {
          this.errorMessage = 'Cannot connect to server. Please try again.'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.register-wrapper {
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
  animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.8; }
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

.feature-item {
  font-size: 1.1rem;
  opacity: 0.95;
  transition: transform 0.3s ease;
}

.feature-item:hover {
  transform: translateX(10px);
}

.form-section {
  background: white;
}

.form-container {
  max-width: 500px;
  width: 100%;
  margin: 0 auto;
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
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
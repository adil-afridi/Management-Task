<template>
  <div id="app">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" v-if="isLoggedIn">
      <div class="container-fluid px-4">
        <router-link class="navbar-brand" to="/">
          <i class="bi bi-check2-square me-2"></i>
          TaskFlow
        </router-link>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="/">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/projects">
                <i class="bi bi-kanban me-1"></i> Projects
              </router-link>
            </li>
            <li v-if="userInfo.role === 'admin'" class="nav-item">
              <router-link class="nav-link" to="/team-members">
                <i class="bi bi-people me-1"></i> Team
              </router-link>
            </li>
          </ul>
          
          <!-- User Info and Logout Button - Simple Version -->
          <div class="d-flex align-items-center gap-3">
          
            <button class="btn btn-danger btn-sm" @click="logout">
              <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <router-view />
    
    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">My Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img :src="avatarUrl" class="rounded-circle mb-3" width="100" height="100">
            <h4>{{ userInfo.name }}</h4>
            <p class="text-muted">{{ userInfo.email }}</p>
            <hr>
            <div class="text-start">
              <p><strong>Role:</strong> 
                <span class="badge" :class="userInfo.role === 'admin' ? 'bg-danger' : 'bg-info'">
                  {{ userInfo.role === 'admin' ? 'Administrator' : 'Team Member' }}
                </span>
              </p>
              <p><strong>Member Since:</strong> {{ formatDate(userInfo.created_at) }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
              <i class="bi bi-check me-1"></i> OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { Modal } from 'bootstrap'

export default {
  name: 'App',
  data() {
    return {
      userName: '',
      userInfo: {
        name: '',
        email: '',
        role: '',
        created_at: ''
      },
      avatarUrl: ''
    }
  },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('token')
    }
  },
  async mounted() {
    if (this.isLoggedIn) {
      await this.loadUser()
      this.setupAvatarClick()
    }
  },
  methods: {
    async loadUser() {
      try {
        const response = await axios.get('/me')
        this.userInfo = response.data
        this.userName = response.data.name
        this.avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(response.data.name)}&background=667eea&color=fff&bold=true&size=128`
      } catch (error) {
        console.error('Error loading user:', error)
      }
    },
    setupAvatarClick() {
      // Make avatar/name clickable to show profile
      const userElement = document.querySelector('.text-white')
      if (userElement) {
        userElement.style.cursor = 'pointer'
        userElement.addEventListener('click', () => {
          const modalElement = document.getElementById('profileModal')
          const modal = new Modal(modalElement)
          modal.show()
        })
      }
    },
    async logout() {
      try {
        await axios.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      }
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
      this.$router.push('/login')
    },
    formatDate(date) {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}

#app {
  min-height: 100vh;
}

.navbar {
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.navbar-brand {
  font-weight: bold;
  font-size: 1.5rem;
}

.nav-link {
  color: rgba(255,255,255,0.8) !important;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: white !important;
}

.nav-link.router-link-active {
  color: white !important;
  font-weight: 500;
}

.btn-danger {
  background: #dc3545;
  border: none;
  transition: all 0.3s ease;
}

.btn-danger:hover {
  background: #c82333;
  transform: translateY(-2px);
}

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #667eea;
  border-radius: 10px;
}
</style>
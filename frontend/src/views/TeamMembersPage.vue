<template>
  <div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="display-5 fw-bold mb-2">Team Members</h1>
        <p class="text-muted">Manage all users and their roles</p>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Member Since</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>
                  <div class="d-flex align-items-center">
                    <img :src="'https://ui-avatars.com/api/?name='+user.name+'&background=667eea&color=fff&size=32'"
                         class="rounded-circle me-2" width="32" height="32">
                    <strong>{{ user.name }}</strong>
                  </div>
                </td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="user.role === 'admin' ? 'bg-danger' : 'bg-info'">
                    {{ user.role === 'admin' ? 'Administrator' : 'Team Member' }}
                  </span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <button 
                    v-if="user.role !== 'admin'" 
                    class="btn btn-sm btn-warning me-1"
                    @click="makeAdmin(user.id, user.name)"
                  >
                    <i class="bi bi-shield-plus"></i> Make Admin
                  </button>
                  <button 
                    v-if="user.role === 'admin' && user.id !== currentUserId"
                    class="btn btn-sm btn-info me-1"
                    @click="removeAdmin(user.id, user.name)"
                  >
                    <i class="bi bi-shield-minus"></i> Remove Admin
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TeamMembersPage',
  data() {
    return {
      users: [],
      currentUserId: null
    }
  },
  async mounted() {
    await this.loadCurrentUser()
    await this.loadUsers()
  },
  methods: {
    async loadCurrentUser() {
      const response = await axios.get('/me')
      this.currentUserId = response.data.id
    },
    async loadUsers() {
      try {
        const response = await axios.get('/users')
        this.users = response.data
      } catch (error) {
        console.error('Error loading users:', error)
        alert('Only admin can view team members')
      }
    },
    async makeAdmin(userId, userName) {
      if (confirm(`Make ${userName} an admin? They will have full control over the system.`)) {
        try {
          await axios.put(`/users/${userId}/role`, { role: 'admin' })
          await this.loadUsers()
          alert(`${userName} is now an admin!`)
        } catch (error) {
          alert('Error updating role: ' + (error.response?.data?.message || 'Unknown error'))
        }
      }
    },
    async removeAdmin(userId, userName) {
      if (confirm(`Remove ${userName}'s admin privileges? They will become a regular user.`)) {
        try {
          await axios.put(`/users/${userId}/role`, { role: 'user' })
          await this.loadUsers()
          alert(`${userName} is no longer an admin.`)
        } catch (error) {
          alert('Error updating role: ' + (error.response?.data?.message || 'Unknown error'))
        }
      }
    },
    formatDate(date) {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString()
    }
  }
}
</script>

<style scoped>
.table th, .table td {
  vertical-align: middle;
}

.btn-warning, .btn-info {
  color: white;
}
</style>
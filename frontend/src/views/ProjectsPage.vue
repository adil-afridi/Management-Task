<template>
  <div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="display-5 fw-bold mb-2">Projects</h1>
        <p class="text-muted">Manage your projects and team collaboration</p>
      </div>
      <!-- Only show create button for ADMIN -->
      <button v-if="userRole === 'admin'" class="btn btn-primary btn-lg" @click="openCreateModal">
        <i class="bi bi-plus-circle me-2"></i>New Project
      </button>
    </div>

    <!-- Projects Grid -->
    <div class="row g-4">
      <div class="col-md-6 col-xl-4" v-for="project in projects" :key="project.id">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="project-icon bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-folder-fill text-primary fs-4"></i>
              </div>
              <div class="dropdown" v-if="userRole === 'admin' || project.created_by === currentUserId">
                <button class="btn btn-link text-dark" data-bs-toggle="dropdown">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" @click="editProject(project)"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                  <li><a class="dropdown-item" @click="manageTeam(project)"><i class="bi bi-people me-2"></i>Manage Team</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" @click="deleteProject(project.id)">
                    <i class="bi bi-trash me-2"></i>Delete
                  </a></li>
                </ul>
              </div>
            </div>
            
            <h5 class="card-title">{{ project.title }}</h5>
            <p class="card-text text-muted">{{ truncate(project.description, 100) }}</p>
            
            <div class="mb-3">
              <div class="small text-muted">
                <i class="bi bi-calendar me-1"></i> Due: {{ project.due_date }}
              </div>
              <div class="small text-muted">
                <i class="bi bi-person me-1"></i> Created by: {{ project.creator?.name }}
              </div>
            </div>
            
            <div class="d-flex gap-2">
              <router-link :to="'/projects/' + project.id" class="btn btn-primary flex-grow-1">
                <i class="bi bi-eye me-1"></i> View Tasks
              </router-link>
            </div>
            
            <!-- Team Members -->
            <div class="mt-3 pt-2 border-top">
              <div class="d-flex align-items-center">
                <i class="bi bi-people-fill small text-muted me-1"></i>
                <small class="text-muted">Team: </small>
                <div class="ms-2 d-flex gap-1">
                  <img v-for="member in project.users?.slice(0, 3)" :key="member.id" 
                       :src="'https://ui-avatars.com/api/?name='+member.name+'&background=667eea&color=fff&size=24'"
                       class="rounded-circle" width="24" height="24" :title="member.name">
                  <span v-if="project.users?.length > 3" class="small text-muted ms-1">
                    +{{ project.users.length - 3 }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="projects.length === 0" class="text-center py-5">
      <i class="bi bi-folder2-open display-1 text-muted"></i>
      <h3 class="mt-3">No Projects Yet</h3>
      <p class="text-muted" v-if="userRole === 'admin'">Create your first project to get started</p>
      <p class="text-muted" v-else>You haven't been added to any projects yet</p>
      <button v-if="userRole === 'admin'" class="btn btn-primary btn-lg" @click="openCreateModal">
        <i class="bi bi-plus-circle me-2"></i>Create Project
      </button>
    </div>

    <!-- Create/Edit Project Modal with Team Member Selection -->
    <div class="modal fade" id="projectModal" tabindex="-1" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit Project' : 'Create New Project' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveProject">
              <div class="mb-3">
                <label class="form-label">Project Title</label>
                <input type="text" class="form-control" v-model="form.title" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" v-model="form.description" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" v-model="form.due_date" required>
              </div>
              
              <!-- Team Member Selection - Only for new projects -->
              <div v-if="!isEditing" class="mb-3">
                <label class="form-label">Add Team Members</label>
                <div class="border rounded p-3">
                  <div class="mb-2">
                    <select class="form-select" v-model="selectedUserId">
                      <option value="">Select a user to add...</option>
                      <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                        {{ user.name }} ({{ user.email }}) - {{ user.role === 'admin' ? 'Admin' : 'User' }}
                      </option>
                    </select>
                  </div>
                  <button type="button" class="btn btn-sm btn-primary" @click="addUserToTeam" :disabled="!selectedUserId">
                    <i class="bi bi-person-plus"></i> Add User
                  </button>
                  
                  <div class="mt-3">
                    <label class="form-label">Selected Team Members:</label>
                    <div class="d-flex flex-wrap gap-2">
                      <div v-for="user in selectedTeamMembers" :key="user.id" class="badge bg-info p-2">
                        {{ user.name }}
                        <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 8px;" @click="removeUserFromTeam(user.id)"></button>
                      </div>
                      <div v-if="selectedTeamMembers.length === 0" class="text-muted small">
                        No team members selected yet
                      </div>
                    </div>
                  </div>
                </div>
                <small class="text-muted">Select users to add to this project. They will be able to see and work on tasks.</small>
              </div>
              
              <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ isEditing ? 'Update' : 'Create' }} Project</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Management Modal - Add/Remove Members -->
    <div class="modal fade" id="teamModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Manage Team Members - {{ currentProject?.title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Add Team Members</label>
              <div class="input-group mb-2">
                <select class="form-select" v-model="selectedUserId">
                  <option value="">Select a user...</option>
                  <option v-for="user in availableUsersForProject" :key="user.id" :value="user.id">
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <button class="btn btn-primary" @click="addTeamMember" :disabled="!selectedUserId">
                  Add Member
                </button>
              </div>
              <small class="text-muted">Select users to add them to this project</small>
            </div>
            
            <h6 class="mt-4">Current Team Members ({{ currentProject?.users?.length || 0 }})</h6>
            <div class="list-group">
              <div v-for="member in currentProject?.users" :key="member.id" class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <img :src="'https://ui-avatars.com/api/?name='+member.name+'&background=667eea&color=fff&size=32'"
                       class="rounded-circle me-2" width="32" height="32">
                  <strong>{{ member.name }}</strong>
                  <small class="text-muted d-block">{{ member.email }}</small>
                </div>
                <button class="btn btn-sm btn-danger" @click="removeTeamMember(member.id)">
                  Remove
                </button>
              </div>
              <div v-if="!currentProject?.users?.length" class="text-center py-3 text-muted">
                No team members yet. Add members above.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
  name: 'ProjectsPage',
  data() {
    return {
      projects: [],
      userRole: '',
      currentUserId: null,
      currentProject: null,
      selectedUserId: '',
      availableUsers: [],
      availableUsersForProject: [],
      selectedTeamMembers: [],
      form: {
        id: null,
        title: '',
        description: '',
        due_date: ''
      },
      isEditing: false
    }
  },
  async mounted() {
    await this.loadCurrentUser()
    await this.loadProjects()
    await this.loadAllUsers()
  },
  methods: {
    async loadCurrentUser() {
      try {
        const response = await axios.get('/me')
        this.userRole = response.data.role
        this.currentUserId = response.data.id
      } catch (error) {
        console.error('Error loading user:', error)
      }
    },
    
    async loadProjects() {
      try {
        const response = await axios.get('/projects')
        this.projects = response.data
      } catch (error) {
        console.error('Error loading projects:', error)
      }
    },
    
    async loadAllUsers() {
      try {
        const response = await axios.get('/users')
        this.availableUsers = response.data.filter(u => u.id !== this.currentUserId)
      } catch (error) {
        console.error('Error loading users:', error)
      }
    },
    
    openCreateModal() {
      if (this.userRole !== 'admin') {
        alert('Only administrators can create projects')
        return
      }
      this.isEditing = false
      this.selectedTeamMembers = []
      this.form = { id: null, title: '', description: '', due_date: '' }
      const modalElement = document.getElementById('projectModal')
      const modal = new Modal(modalElement)
      modal.show()
    },
    
    addUserToTeam() {
      if (!this.selectedUserId) return
      const user = this.availableUsers.find(u => u.id === parseInt(this.selectedUserId))
      if (user && !this.selectedTeamMembers.find(m => m.id === user.id)) {
        this.selectedTeamMembers.push(user)
      }
      this.selectedUserId = ''
    },
    
    removeUserFromTeam(userId) {
      this.selectedTeamMembers = this.selectedTeamMembers.filter(u => u.id !== userId)
    },
    
    editProject(project) {
      if (this.userRole !== 'admin' && project.created_by !== this.currentUserId) {
        alert('You can only edit your own projects')
        return
      }
      this.isEditing = true
      this.form = { ...project }
      const modalElement = document.getElementById('projectModal')
      const modal = new Modal(modalElement)
      modal.show()
    },
    
    async saveProject() {
      try {
        let response
        if (this.isEditing) {
          response = await axios.put(`/projects/${this.form.id}`, {
            title: this.form.title,
            description: this.form.description,
            due_date: this.form.due_date
          })
        } else {
          // Create project with team members
          response = await axios.post('/projects', {
            title: this.form.title,
            description: this.form.description,
            due_date: this.form.due_date
          })
          
          const newProject = response.data
          
          // Add selected team members to the project
          for (const user of this.selectedTeamMembers) {
            await axios.post(`/projects/${newProject.id}/add-member`, {
              user_id: user.id
            })
          }
        }
        
        // Close modal
        const modalElement = document.getElementById('projectModal')
        const modal = Modal.getInstance(modalElement)
        modal.hide()
        
        // Reload projects
        await this.loadProjects()
        
        alert(this.isEditing ? 'Project updated successfully!' : `Project created successfully with ${this.selectedTeamMembers.length} team members!`)
        
        // Reset form
        this.form = { id: null, title: '', description: '', due_date: '' }
        this.selectedTeamMembers = []
        this.isEditing = false
        
      } catch (error) {
        console.error('Error saving project:', error)
        if (error.response) {
          if (error.response.status === 403) {
            alert('Only administrators can create projects')
          } else {
            alert('Error: ' + (error.response.data.message || 'Failed to save project'))
          }
        } else {
          alert('Network error. Please check if backend is running.')
        }
      }
    },
    
    async deleteProject(id) {
      if (confirm('Delete this project? All tasks will be deleted.')) {
        try {
          await axios.delete(`/projects/${id}`)
          await this.loadProjects()
          alert('Project deleted successfully!')
        } catch (error) {
          console.error('Error deleting project:', error)
          alert('Error deleting project')
        }
      }
    },
    
    async manageTeam(project) {
      this.currentProject = project
      const currentMemberIds = this.currentProject?.users?.map(u => u.id) || []
      this.availableUsersForProject = this.availableUsers.filter(u => !currentMemberIds.includes(u.id))
      const modalElement = document.getElementById('teamModal')
      const modal = new Modal(modalElement)
      modal.show()
    },
    
    async addTeamMember() {
      if (!this.selectedUserId) return
      
      try {
        await axios.post(`/projects/${this.currentProject.id}/add-member`, {
          user_id: this.selectedUserId
        })
        
        // Refresh project data
        const response = await axios.get(`/projects/${this.currentProject.id}`)
        this.currentProject = response.data
        
        // Update the projects list
        const index = this.projects.findIndex(p => p.id === this.currentProject.id)
        if (index !== -1) {
          this.projects[index] = this.currentProject
        }
        
        // Refresh available users
        const currentMemberIds = this.currentProject?.users?.map(u => u.id) || []
        this.availableUsersForProject = this.availableUsers.filter(u => !currentMemberIds.includes(u.id))
        this.selectedUserId = ''
        
        alert('Team member added successfully!')
      } catch (error) {
        console.error('Error adding team member:', error)
        alert('Error adding team member')
      }
    },
    
    async removeTeamMember(userId) {
      if (confirm('Remove this team member from the project?')) {
        // Note: You'll need to implement a remove member endpoint
        alert('Remove member feature - would remove user from project')
      }
    },
    
    truncate(text, length) {
      if (text.length <= length) return text
      return text.substring(0, length) + '...'
    }
  }
}
</script>

<style scoped>
.project-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.badge {
  font-size: 0.8rem;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}
</style>
<template>
  <div class="container-fluid px-4 py-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-0">{{ project.title }}</h3>
            <p class="text-muted mb-0 mt-1">{{ project.description }}</p>
          </div>
          <div class="text-end">
            <span class="badge bg-primary">Due: {{ project.due_date }}</span>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Tasks Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="mb-0">
            <i class="bi bi-list-check me-2"></i>My Tasks
          </h5>
          <!-- Only show Add Task button for ADMIN -->
          <button v-if="userRole === 'admin'" class="btn btn-primary" @click="openTaskForm">
            <i class="bi bi-plus-circle me-1"></i> Add Task
          </button>
        </div>
        
        <!-- Add Task Form - Only for Admin -->
        <div v-if="showTaskForm && userRole === 'admin'" class="card mb-4 border-primary">
          <div class="card-body">
            <h6 class="mb-3">Create New Task</h6>
            <form @submit.prevent="addTask">
              <div class="mb-3">
                <label class="form-label">Task Title</label>
                <input type="text" class="form-control" v-model="newTask.title" required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" v-model="newTask.description" rows="2" required></textarea>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Due Date</label>
                  <input type="date" class="form-control" v-model="newTask.due_date" required>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="form-label">Assign To</label>
                  <select class="form-select" v-model="newTask.assigned_to" required>
                    <option value="">Select a team member...</option>
                    <option v-for="user in allUsers" :value="user.id" :key="user.id">
                      {{ user.name }} ({{ user.email }})
                    </option>
                  </select>
                </div>
              </div>
              
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-save me-1"></i> Save Task
                </button>
                <button type="button" class="btn btn-secondary" @click="closeTaskForm">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card mb-4 border-0 bg-light">
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-6">
                <label class="form-label">Filter by Status</label>
                <select class="form-select" v-model="filters.status" @change="applyFilters">
                  <option value="">All Tasks</option>
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
              <div class="col-md-6">
                <button class="btn btn-primary w-100" @click="clearFilters">
                  <i class="bi bi-eraser me-1"></i> Clear Filters
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Tasks List -->
        <div v-if="filteredTasks.length > 0">
          <div v-for="task in filteredTasks" :key="task.id" class="card mb-3 border-0 shadow-sm task-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <h5 class="mb-0">{{ task.title }}</h5>
                    <span class="badge" :class="getStatusBadge(task.status)">
                      {{ getStatusText(task.status) }}
                    </span>
                  </div>
                  <p class="text-muted mb-2">{{ task.description }}</p>
                  <div class="small text-muted">
                    <i class="bi bi-calendar me-1"></i> Due: {{ task.due_date }} |
                    <i class="bi bi-person me-1"></i> Assigned by: {{ task.creator?.name || 'Admin' }}
                  </div>
                </div>
                <div class="ms-3">
                  <!-- Status Update Buttons for assigned user -->
                  <div v-if="task.assigned_to === currentUserId" class="btn-group-vertical">
                    <button v-if="task.status !== 'pending'" 
                            class="btn btn-sm btn-outline-warning mb-1" 
                            @click="updateTaskStatus(task.id, 'pending')">
                      <i class="bi bi-clock"></i> Set Pending
                    </button>
                    <button v-if="task.status !== 'in_progress'" 
                            class="btn btn-sm btn-outline-info mb-1" 
                            @click="updateTaskStatus(task.id, 'in_progress')">
                      <i class="bi bi-play-circle"></i> Start Progress
                    </button>
                    <button v-if="task.status !== 'completed'" 
                            class="btn btn-sm btn-outline-success" 
                            @click="updateTaskStatus(task.id, 'completed')">
                      <i class="bi bi-check2-circle"></i> Complete
                    </button>
                  </div>
                  
                  <!-- Show status for completed tasks -->
                  <span v-if="task.status === 'completed'" class="text-success">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div class="small">Completed</div>
                  </span>
                  
                  <!-- Show message if not assigned to current user -->
                  <div v-if="task.assigned_to !== currentUserId && userRole !== 'admin'" class="text-muted small">
                    <i class="bi bi-lock"></i> Not assigned to you
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-else class="text-center py-5 text-muted">
          <i class="bi bi-inbox display-1"></i>
          <p class="mt-2">No tasks found.</p>
          <p v-if="userRole === 'admin'">Click "Add Task" to create one.</p>
          <p v-else>No tasks assigned to you yet. Contact your admin.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ProjectDetailsPage',
  data() {
    return {
      project: {},
      tasks: [],
      originalTasks: [],
      allUsers: [],
      userRole: '',
      currentUserId: null,
      showTaskForm: false,
      filters: {
        status: ''
      },
      newTask: {
        title: '',
        description: '',
        due_date: '',
        project_id: this.$route.params.id,
        assigned_to: ''
      }
    }
  },
  computed: {
    filteredTasks() {
      let filtered = [...this.originalTasks]
      
      if (this.filters.status) {
        filtered = filtered.filter(t => t.status === this.filters.status)
      }
      
      return filtered
    }
  },
  async mounted() {
    await this.loadUserRole()
    await this.loadProject()
    await this.loadAllUsers()
    await this.loadTasks()
  },
  methods: {
    async loadUserRole() {
      try {
        const response = await axios.get('/me')
        this.userRole = response.data.role
        this.currentUserId = response.data.id
        console.log('User role:', this.userRole, 'User ID:', this.currentUserId)
      } catch (error) {
        console.error('Error loading user role:', error)
      }
    },
    
    async loadProject() {
      try {
        const response = await axios.get(`/projects/${this.$route.params.id}`)
        this.project = response.data
      } catch (error) {
        console.error('Error loading project:', error)
        alert('Error loading project details')
      }
    },
    
    async loadAllUsers() {
      try {
        if (this.userRole === 'admin') {
          const response = await axios.get('/users')
          this.allUsers = response.data
        }
      } catch (error) {
        console.error('Error loading users:', error)
      }
    },
    
    async loadTasks() {
      try {
        const response = await axios.get(`/tasks?project_id=${this.$route.params.id}`)
        this.originalTasks = response.data
        console.log('Tasks loaded:', this.originalTasks.length)
        
        // For debugging - log tasks and their assignments
        this.originalTasks.forEach(task => {
          console.log(`Task: ${task.title}, Assigned to: ${task.assigned_to}, Current user: ${this.currentUserId}`)
        })
      } catch (error) {
        console.error('Error loading tasks:', error)
      }
    },
    
    openTaskForm() {
      if (this.userRole !== 'admin') {
        alert('Only administrators can create tasks')
        return
      }
      this.showTaskForm = true
    },
    
    closeTaskForm() {
      this.showTaskForm = false
      this.newTask = {
        title: '',
        description: '',
        due_date: '',
        project_id: this.$route.params.id,
        assigned_to: ''
      }
    },
    
    async addTask() {
      if (this.userRole !== 'admin') {
        alert('Only administrators can create tasks')
        return
      }
      
      if (!this.newTask.assigned_to) {
        alert('Please select a team member to assign this task')
        return
      }
      
      try {
        const taskData = {
          title: this.newTask.title,
          description: this.newTask.description,
          due_date: this.newTask.due_date,
          project_id: parseInt(this.$route.params.id),
          assigned_to: parseInt(this.newTask.assigned_to)
        }
        
        await axios.post('/tasks', taskData)
        
        this.closeTaskForm()
        await this.loadTasks()
        
        alert('Task created successfully!')
      } catch (error) {
        console.error('Error creating task:', error)
        if (error.response?.status === 403) {
          alert('Only administrators can create tasks')
        } else if (error.response) {
          alert('Error creating task: ' + (error.response.data.message || 'Unknown error'))
        } else {
          alert('Error creating task. Please try again.')
        }
      }
    },
    
    async updateTaskStatus(id, status) {
      try {
        await axios.put(`/tasks/${id}`, { status })
        await this.loadTasks()
        
        let message = ''
        if (status === 'pending') message = 'Task marked as Pending'
        if (status === 'in_progress') message = 'Task started! Good luck!'
        if (status === 'completed') message = 'Task completed! Great job!'
        
        alert(message)
      } catch (error) {
        console.error('Error updating task:', error)
        if (error.response?.status === 403) {
          alert('You can only update tasks assigned to you')
        } else {
          alert('Error updating task status')
        }
      }
    },
    
    applyFilters() {
      // Filtering is handled by computed property
    },
    
    clearFilters() {
      this.filters = {
        status: ''
      }
    },
    
    getStatusBadge(status) {
      const badges = {
        'pending': 'bg-warning text-dark',
        'in_progress': 'bg-info text-white',
        'completed': 'bg-success text-white'
      }
      return badges[status] || 'bg-secondary'
    },
    
    getStatusText(status) {
      const texts = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed'
      }
      return texts[status] || status
    }
  }
}
</script>

<style scoped>
.card {
  border-radius: 15px;
}

.card-header {
  border-radius: 15px 15px 0 0;
}

.badge {
  padding: 5px 10px;
  font-weight: 500;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.task-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.task-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.btn-outline-warning:hover, .btn-outline-info:hover, .btn-outline-success:hover {
  transform: translateY(-1px);
}

.form-select:focus, .form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.bg-light {
  background-color: #f8f9fa !important;
}

.btn-group-vertical {
  gap: 5px;
}

.btn-group-vertical .btn {
  white-space: nowrap;
}
</style>
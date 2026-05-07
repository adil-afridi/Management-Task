<template>
  <div class="dashboard-container">
    <div class="container-fluid px-4">
      <!-- Welcome Section -->
      <div class="welcome-card mb-4">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="display-5 fw-bold mb-2">Welcome back, {{ userName }}! 👋</h1>
            <p class="lead mb-0">Here's what's happening with your tasks today.</p>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="date-badge">
              <i class="bi bi-calendar3 me-2"></i>
              {{ currentDate }}
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
          <div class="stat-card projects-card">
            <div class="stat-icon">
              <i class="bi bi-kanban"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ projectCount }}</h3>
              <p class="stat-label">Projects I'm In</p>
            </div>
            <router-link to="/projects" class="stat-link">
              View All <i class="bi bi-arrow-right"></i>
            </router-link>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="stat-card tasks-card">
            <div class="stat-icon">
              <i class="bi bi-list-check"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ myTasksCount }}</h3>
              <p class="stat-label">Tasks Assigned to Me</p>
            </div>
            <button @click="loadMyTasks" class="stat-link">
              Refresh <i class="bi bi-arrow-repeat"></i>
            </button>
          </div>
        </div>

        <div class="col-md-6 col-xl-3">
          <div class="stat-card completed-card">
            <div class="stat-icon">
              <i class="bi bi-check2-circle"></i>
            </div>
            <div class="stat-info">
              <h3 class="stat-number">{{ completedTasks }}</h3>
              <p class="stat-label">Completed Tasks</p>
            </div>
            <div class="stat-progress">
              <div class="progress">
                <div class="progress-bar" :style="{ width: completionRate + '%' }"></div>
              </div>
              <small>{{ completionRate }}% Complete</small>
            </div>
          </div>
        </div>
      </div>

      <!-- My Tasks Section - Shows actual tasks with project names -->
      <div class="row">
        <div class="col-12">
          <div class="card tasks-list-card">
            <div class="card-header bg-white">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                  <i class="bi bi-list-task me-2 text-primary"></i>My Assigned Tasks
                </h5>
                <div class="d-flex gap-2">
                  <select class="form-select form-select-sm" v-model="taskFilter" style="width: 150px" @change="filterTasks">
                    <option value="all">All Tasks</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                  </select>
                  <button class="btn btn-sm btn-outline-primary" @click="loadMyTasks">
                    <i class="bi bi-arrow-repeat"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              <div v-if="filteredMyTasks.length > 0" class="list-group list-group-flush">
                <div v-for="task in filteredMyTasks" :key="task.id" class="list-group-item task-item">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <h6 class="mb-0">{{ task.title }}</h6>
                        <span class="badge" :class="getStatusBadge(task.status)">
                          {{ getStatusText(task.status) }}
                        </span>
                      </div>
                      <p class="text-muted small mb-2">{{ task.description }}</p>
                      <div class="small text-muted">
                        <i class="bi bi-folder me-1"></i> Project: 
                        <strong>{{ task.project?.title || 'Unknown Project' }}</strong>
                        <span class="mx-2">|</span>
                        <i class="bi bi-calendar me-1"></i> Due: {{ task.due_date }}
                        <span class="mx-2">|</span>
                        <i class="bi bi-person me-1"></i> Assigned by: {{ task.creator?.name || 'Admin' }}
                      </div>
                    </div>
                    <div class="ms-3">
                      <!-- Task Action Buttons -->
                      <div class="btn-group-vertical">
                        <button v-if="task.status !== 'in_progress' && task.status !== 'completed'" 
                                class="btn btn-sm btn-outline-info mb-1" 
                                @click="updateTaskStatus(task.id, 'in_progress')">
                          <i class="bi bi-play-circle"></i> Start
                        </button>
                        <button v-if="task.status === 'in_progress'" 
                                class="btn btn-sm btn-outline-warning mb-1" 
                                @click="updateTaskStatus(task.id, 'pending')">
                          <i class="bi bi-clock"></i> Pause
                        </button>
                        <button v-if="task.status !== 'completed'" 
                                class="btn btn-sm btn-outline-success" 
                                @click="updateTaskStatus(task.id, 'completed')">
                          <i class="bi bi-check2-circle"></i> Complete
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="mt-3 text-muted">No tasks assigned to you yet.</p>
                <p class="small text-muted">Ask your admin to assign tasks to you.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity - Projects I'm In -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card recent-projects-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0 fw-bold">
                <i class="bi bi-people me-2 text-primary"></i>Projects I'm In
              </h5>
              <router-link to="/projects" class="btn btn-sm btn-outline-primary">View All Projects</router-link>
            </div>
            <div class="card-body p-0">
              <div class="list-group list-group-flush">
                <div v-for="project in myProjects" :key="project.id" class="list-group-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-folder-fill text-primary me-2"></i>
                      <strong>{{ project.title }}</strong>
                      <p class="small text-muted mb-0 mt-1">{{ project.description.substring(0, 80) }}...</p>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-info mb-1">{{ getProjectTaskCount(project.id) }} tasks</span>
                      <router-link :to="'/projects/' + project.id" class="btn btn-sm btn-primary ms-2">
                        View Tasks <i class="bi bi-arrow-right"></i>
                      </router-link>
                    </div>
                  </div>
                </div>
                <div v-if="myProjects.length === 0" class="text-center py-4 text-muted">
                  You are not a member of any projects yet.
                </div>
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
  name: 'DashboardPage',
  data() {
    return {
      userName: '',
      userEmail: '',
      userRole: '',
      projectCount: 0,
      myProjects: [],
      myTasks: [],
      taskFilter: 'all',
      currentDate: new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      })
    }
  },
  computed: {
    myTasksCount() {
      return this.myTasks.length
    },
    completedTasks() {
      return this.myTasks.filter(t => t.status === 'completed').length
    },
    completionRate() {
      if (this.myTasks.length === 0) return 0
      return Math.round((this.completedTasks / this.myTasks.length) * 100)
    },
    filteredMyTasks() {
      if (this.taskFilter === 'all') return this.myTasks
      return this.myTasks.filter(t => t.status === this.taskFilter)
    }
  },
  mounted() {
    this.loadUserData()
    this.loadMyProjects()
    this.loadMyTasks()
  },
  methods: {
    async loadUserData() {
      try {
        const response = await axios.get('/me')
        this.userName = response.data.name
        this.userEmail = response.data.email
        this.userRole = response.data.role
      } catch (error) {
        console.error('Error loading user:', error)
      }
    },
    
    async loadMyProjects() {
      try {
        const response = await axios.get('/projects')
        this.myProjects = response.data
        this.projectCount = response.data.length
      } catch (error) {
        console.error('Error loading projects:', error)
      }
    },
    
    async loadMyTasks() {
      try {
        // For regular users, this only returns tasks assigned to them
        const response = await axios.get('/tasks')
        this.myTasks = response.data
        console.log('My tasks:', this.myTasks)
      } catch (error) {
        console.error('Error loading tasks:', error)
      }
    },
    
    async updateTaskStatus(id, status) {
      try {
        await axios.put(`/tasks/${id}`, { status })
        await this.loadMyTasks()
        
        let message = ''
        if (status === 'in_progress') message = 'Task started! Good luck! 🚀'
        if (status === 'pending') message = 'Task paused ⏸️'
        if (status === 'completed') message = 'Task completed! Great job! 🎉'
        
        alert(message)
      } catch (error) {
        console.error('Error updating task:', error)
        alert('Error updating task status')
      }
    },
    
    getProjectTaskCount(projectId) {
      return this.myTasks.filter(t => t.project_id === projectId).length
    },
    
    async refreshData() {
      await this.loadMyProjects()
      await this.loadMyTasks()
      alert('Dashboard refreshed!')
    },
    
    filterTasks() {
      // Handled by computed property
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
.dashboard-container {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  padding: 2rem 0;
}

.welcome-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 2rem;
  color: white;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.date-badge {
  background: rgba(255,255,255,0.2);
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  backdrop-filter: blur(10px);
}

.stat-card {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-icon {
  position: absolute;
  right: 1rem;
  top: 1rem;
  font-size: 2.5rem;
  opacity: 0.2;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 0.25rem;
  color: #2c3e50;
}

.stat-label {
  color: #6c757d;
  margin-bottom: 1rem;
}

.stat-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.875rem;
  background: none;
  border: none;
  padding: 0;
}

.stat-link:hover {
  color: #764ba2;
}

.stat-progress {
  margin-top: 1rem;
}

.progress {
  height: 5px;
  border-radius: 10px;
  background: #e9ecef;
}

.progress-bar {
  background: linear-gradient(90deg, #28a745, #20c997);
  border-radius: 10px;
}

.projects-card .stat-icon { color: #667eea; }
.tasks-card .stat-icon { color: #f59e0b; }
.completed-card .stat-icon { color: #10b981; }
.user-card .stat-icon { color: #ef4444; }

.tasks-list-card, .recent-projects-card {
  border: none;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.card-header {
  border-bottom: 2px solid #f0f0f0;
  padding: 1rem 1.5rem;
}

.task-item {
  transition: background 0.3s ease;
  cursor: pointer;
  border-left: 3px solid transparent;
}

.task-item:hover {
  background: #f8f9fa;
  border-left-color: #667eea;
}

.btn-group-vertical {
  gap: 5px;
}

.btn-group-vertical .btn {
  white-space: nowrap;
  font-size: 0.75rem;
  padding: 4px 10px;
}

.btn-outline-info:hover, .btn-outline-warning:hover, .btn-outline-success:hover {
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .welcome-card {
    padding: 1.5rem;
  }
  
  .stat-number {
    font-size: 1.75rem;
  }
  
  .task-item .d-flex {
    flex-direction: column;
  }
  
  .btn-group-vertical {
    margin-top: 10px;
    flex-direction: row;
  }
}
</style>
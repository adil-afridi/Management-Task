import { createRouter, createWebHistory } from "vue-router";
import LoginPage from "../views/LoginPage.vue";
import RegisterPage from "../views/RegisterPage.vue";
import DashboardPage from "../views/DashboardPage.vue";
import ProjectsPage from "../views/ProjectsPage.vue";
import TeamMembersPage from "../views/TeamMembersPage.vue";
import ProjectDetailsPage from "../views/ProjectDetailsPage.vue";

const routes = [
  {
    path: "/login",
    name: "login",
    component: LoginPage,
  },

  {
    path: "/register",
    name: "register",
    component: RegisterPage,
  },
  {
    path: "/team-members",
    name: "team-members",
    component: TeamMembersPage,
    meta: { requiresAdmin: true },
  },

  {
    path: "/",
    name: "dashboard",
    component: DashboardPage,
  },
  {
    path: "/projects",
    name: "projects",
    component: ProjectsPage,
  },
  {
    path: "/projects/:id",
    name: "project-details",
    component: ProjectDetailsPage,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard - protect routes
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");
  const publicPages = ["/login", "/register"];
  const authRequired = !publicPages.includes(to.path);

  if (authRequired && !token) {
    next("/login");
  } else {
    next();
  }
});

export default router;

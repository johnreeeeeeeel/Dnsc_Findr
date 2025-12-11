import { createApp } from "vue";
import { Icon } from "@iconify/vue";
import axios from 'axios';

window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

// Admin components
import AdminAppLayout from "./Layouts/AdminAppLayout.vue";  
import AdminManageInstituteProgram from "./Pages/Admin/ManageInstituteProgram.vue";
import AdminDashboard from "./Pages/Admin/Dashboard.vue";
import AdminFeedbacks from "./Pages/Admin/Feedbacks.vue";
import AdminManageUsers from "./Pages/Admin/ManageUsers.vue";
import AdminManagePosts from "./Pages/Admin/ManagePosts.vue";
import AdminPosts from "./Pages/Admin/Posts.vue";
import AdminProfile from "./Pages/Admin/Profile.vue";
import AdminSettings from "./Pages/Admin/Settings.vue";

// User components
import UserAppLayout from "./Layouts/UserAppLayout.vue";
import UserGiveFeedback from "./Pages/User/GiveFeedback.vue";
import UserHome from "./Pages/User/Home.vue";
import UserMyPosts from "./Pages/User/MyPosts.vue";
import UserProfile from "./Pages/User/Profile.vue"
import UserSettings from "./Pages/User/Settings.vue"

// Login
import LoginPage from "./Pages/Login/Login.vue";
import AboutPage from "./Pages/Login/About.vue";
import HowItWorksPage from "./Pages/Login/HowItWorks.vue";

// Component
const components = {
    AdminAppLayout,
    AdminManageInstituteProgram,
    AdminDashboard,
    AdminFeedbacks,
    AdminManageUsers,
    AdminManagePosts,
    AdminPosts,
    AdminProfile,
    AdminSettings,

    UserAppLayout,
    UserGiveFeedback,
    UserHome,
    UserMyPosts,
    UserProfile,
    UserSettings,

    LoginPage,
    AboutPage,
    HowItWorksPage,
};

// Mount Vue apps
function mountApp(elId) {
    const el = document.getElementById(elId);
    if (!el) return;

    const componentName = el.dataset.component;
    const component = components[componentName];

    if (!component) {
        console.error(`Component "${componentName}" not found.`);
        return;
    }

    const app = createApp(component);
    app.component("Icon", Icon);
    app.mount(el);
}

// Mount all <div id="___-app">
mountApp("admin-app");
mountApp("user-app");
mountApp("login-app");

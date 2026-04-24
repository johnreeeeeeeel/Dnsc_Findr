<template>
  <AdminAppLayout title="Dashboard">
    <div class="container-fluid layout">

      <!-- Loading -->
      <div v-if="loading" class="loading">
        <div class="spinner-border" role="status"></div>
        <div class="loading-text">Loading statistics...</div>
      </div>

      <!-- Main Stats -->
      <div v-else class="stats-container">

        <!-- User Stats -->
        <section>

          <h4 class="section-header">

            <div class="section-title">
              <Icon class="icon" icon="mingcute:user-3-fill" />
              <p>Users Statistics</p>
            </div>

            <div class="section-tools">
              <!-- See All -->
              <a href="/manage-users" class="see-all">
                <p>See All</p>
                <Icon class="icon" icon="mingcute:right-line" />
              </a>
            </div>

          </h4>

          <div class="row m-0">
            <div class="col">
              <Icon class="icon" icon="mingcute:user-1-line" />
              <div class="stat-details">
                <div class="stat-label">Total Users</div>
                <div class="stat-number">{{ stats.totalUsers }}</div>
              </div>
            </div>
            <div class="col">
              <Icon class="icon" icon="mingcute:user-1-line" />
              <div class="stat-details">
                <div class="stat-label">Students</div>
                <div class="stat-number">{{ stats.students }}</div>
              </div>
            </div>
            <div class="col">
              <Icon class="icon" icon="mingcute:user-1-line" />
              <div class="stat-details">
                <div class="stat-label">Instructors</div>
                <div class="stat-number">{{ stats.instructors }}</div>
              </div>
            </div>
            <div class="col">
              <Icon class="icon" icon="mingcute:user-1-line" />
              <div class="stat-details">
                <div class="stat-label">Staff</div>
                <div class="stat-number">{{ stats.staff }}</div>
              </div>
            </div>
          </div>
        </section>

        <!-- Post Stats -->
        <section>

          <h4 class="section-header">

            <div class="section-title">
              <Icon class="icon" icon="mingcute:album-2-fill" />
              <p>Posts Statistics</p>
            </div>

            <div class="section-tools">
              <!-- See All -->
              <a href="/manage-posts" class="see-all">
                <p>See All</p>
                <Icon class="icon" icon="mingcute:right-line" />
              </a>
            </div>

          </h4>

          <div class="row m-0">
            <div class="col">
              <Icon class="icon" icon="mingcute:album-2-line" />
              <div class="stat-details">
                <div class="stat-label">Total Posts</div>
                <div class="stat-number">{{ stats.totalPosts }}</div>
              </div>
            </div>
            <div class="col">
              <Icon class="icon" icon="mingcute:time-duration-line" />
              <div class="stat-details">
                <div class="stat-label">Pending</div>
                <div class="stat-number">{{ stats.pendingPosts }}</div>
              </div>
            </div>
            <div class="col">
              <Icon class="icon" icon="mingcute:check-2-line" />
              <div class="stat-details">
                <div class="stat-label">Approved</div>
                <div class="stat-number">{{ stats.approvedPosts }}</div>
              </div>
            </div>
            <div class="col">
              <Icon class="icon" icon="mingcute:calendar-line" />
              <div class="stat-details">
                <div class="stat-label">Posted Today</div>
                <div class="stat-number">{{ stats.postsToday }}</div>
              </div>
            </div>
          </div>
        </section>

        <!-- Recent Users -->
        <section class="recent-users">

          <h4 class="section-header">

            <div class="section-title">
              <Icon class="icon" icon="mingcute:user-3-fill" />
              <p>Recent Users</p>
            </div>

            <div class="section-tools">
              <!-- See All -->
              <a href="/manage-users" class="see-all">
                <p>See All</p>
                <Icon class="icon" icon="mingcute:right-line" />
              </a>
            </div>

          </h4>

          <div class="card">
            <div class="card-body">

              <div v-if="recentUsers.length === 0" class="empty-state-sm mb-5">
                <Icon class="icon" icon="mingcute:user-1-line" />
                <p>No user yet</p>
              </div>

              <table v-else class="table table-borderless users-table">
                <tbody>
                  <tr v-for="user in recentUsers" :key="user.user_id">
                    <td class="small text-break" data-label="Name">
                      <strong>
                        {{ user.firstname }}
                        {{ user.middlename ? user.middlename + ' ' : '' }}{{ user.lastname }}
                      </strong>
                    </td>
                    <td class="small text-break" data-label="Email">{{ user.email }}</td>
                    <td class="small" data-label="Type">
                      <span class="badge user-type" :class="{
                        student: user.usertype === 'Student',
                        instructor: user.usertype === 'Instructor',
                        staff: user.usertype === 'Staff'
                      }">
                        {{ user.usertype }}
                      </span>
                    </td>
                    <td class="small text-muted" data-label="Added">
                      {{ formatDate(user.created_at) }}
                    </td>
                  </tr>
                </tbody>
              </table>

            </div>
          </div>
        </section>

      </div>
    </div>
  </AdminAppLayout>
</template>

<script>
import AdminAppLayout from '../../Layouts/AdminAppLayout.vue';
import axios from 'axios';

export default {
  components: { AdminAppLayout },

  data() {
    return {
      loading: true,
      stats: {},
      recentUsers: []
    };
  },

  mounted() {
    this.fetchStats();
  },

  methods: {
    async fetchStats() {
      try {
        const [statsRes, usersRes] = await Promise.all([
          axios.get('/api/admin/stats'),
          axios.get('/api/admin/recent-users')
        ]);

        this.stats = statsRes.data;
        this.recentUsers = usersRes.data;
      } catch (err) {
        console.error(err);
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    }
  }
};
</script>

<style scoped>
.layout {
  font-family: 'Montserrat', sans-serif;
  background-color: #f2f3f7 !important;
  display: flex;
  flex-direction: column;
  gap: 24px;
  color: #333;
}

/* STATS CONTAINER */

.stats-container {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.stats-container>section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stats-container>section h4 {
  color: #333;
}

.row {
  background-color: #ffffff;
  border-radius: 16px;
  padding: 32px 24px;
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.col {
  flex: 1 1 220px;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 24px;
}

.col .icon {
  background-color: #D8FDE9;
  color: #3ca237;
  border-radius: 50%;
  padding: 24px;
  font-size: 100px;
}

.col .stat-details {
  display: flex;
  flex-direction: column;
}

.col .stat-details .stat-label {
  font-size: 14px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-weight: 500;
  margin-bottom: 12px;
}

.col .stat-details .stat-number {
  font-size: 48px;
  font-weight: 700;
  margin: 0;
  line-height: 1.1;
  color: #555;
}

/* USER LISTS */

.section-header {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

.section-title {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  color: #444 !important;
  font-weight: 500;
}

.section-tools {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  font-size: 16px;
}

.see-all {
  display: flex;
  align-items: center;
  text-decoration: none;
  gap: 4px;
  color: #444;
}

.see-all:hover {
  color: #3ca237;
}

.see-all p {
  font-weight: 600;
}

.see-all .icon {
  font-size: 22px;
  cursor: pointer;
}

.recent-users .card {
  background-color: #ffffff;
  border-radius: 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.users-table {
  margin: 0;
}

.users-table thead,
.users-table th {
  display: none;
}

.users-table tbody {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.users-table tbody tr {
  background: transparent;
  border: 1px solid #3ca237;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  padding: 16px;
  transition: all 0.2s ease;
}

.users-table tbody td {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 6px 0;
  border: none;
  gap: 12px;
}

.users-table tbody td::before {
  content: attr(data-label);
  font-weight: 600;
  color: #2d3748;
  min-width: 100px;
  flex-shrink: 0;
}

.users-table tbody td.text-break {
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}
</style>

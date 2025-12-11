<template>
  <AdminAppLayout title="Feedbacks & Ratings">
    <div class="container-fluid layout">

      <!-- Header -->
      <div class="header">
        <h4 class="title">
          <Icon class="icon" icon="mingcute:star-fill" />
          Feedbacks & Ratings
        </h4>

        <div class="tools">

          <!-- Search -->
          <div class="search">
            <Icon icon="mingcute:search-line" class="icon" />
            <input type="text" v-model="searchQuery" class="form-control" placeholder="Search feedback..." />
          </div>

          <!-- Filters -->
          <div class="filter">
            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <Icon icon="mingcute:filter-fill" /> Filters
              <span v-if="activeFilterCount > 0" class="filter-badge badge">
                {{ activeFilterCount }}
              </span>
            </button>

            <div class="dropdown-menu p-3" style="width: 360px;">
              <div class="row g-3">

                <!-- Service Availed (NEW) -->
                <div class="col-12">
                  <label class="form-label small text-muted">Service Availed</label>
                  <select v-model="filterServiceAvailed" class="form-select form-select-sm">
                    <option value="">All Services</option>
                    <option value="Reported Found Item">Reported Found Item</option>
                    <option value="Claim Lost">Claimed Lost Item</option>
                  </select>
                </div>

                <!-- Is Anonymous -->
                <div class="col-6">
                  <label class="form-label small text-muted">Is Anonymous</label>
                  <select v-model="filterAnonymous" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="anonymous">Yes</option>
                    <option value="named">No</option>
                  </select>
                </div>

                <!-- Time Range -->
                <div class="col-6">
                  <label class="form-label small text-muted">Time Range</label>
                  <select v-model="filterTime" class="form-select form-select-sm">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                  </select>
                </div>

                <!-- Service Rating -->
                <div class="col-6">
                  <label class="form-label small text-muted">Service Rating</label>
                  <select v-model="filterServiceRating" class="form-select form-select-sm">
                    <option value="">Any Rating</option>
                    <option value="5">5 Stars Only</option>
                    <option value="4">4 Stars Only</option>
                    <option value="3">3 Stars Only</option>
                    <option value="2">2 Stars Only</option>
                    <option value="1">1 Star Only</option>
                  </select>
                </div>

                <!-- System Rating -->
                <div class="col-6">
                  <label class="form-label small text-muted">System Rating</label>
                  <select v-model="filterSystemRating" class="form-select form-select-sm">
                    <option value="">Any Rating</option>
                    <option value="5">5 Stars Only</option>
                    <option value="4">4 Stars Only</option>
                    <option value="3">3 Stars Only</option>
                    <option value="2">2 Stars Only</option>
                    <option value="1">1 Star Only</option>
                  </select>
                </div>

                <div class="col-12 text-end mt-2">
                  <button @click="resetFilters" class="btn btn-link reset-filter text-danger">
                    <Icon icon="mingcute:refresh-2-line" /> Clear all filters
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading">
        <div class="spinner-border" role="status"></div>
        <div class="loading-text">Loading feedbacks...</div>
      </div>

      <!-- Empty -->
      <template v-else-if="feedbacks.length === 0">
        <div class="empty-state-md">
          <Icon class="icon" icon="mingcute:empty-box-fill" />
          <p>No feedbacks & ratings yet</p>
          <p>Try reloading</p>
          <button @click="fetchFeedbacks" class="btn btn-link reset-filter">
            <Icon icon="mingcute:refresh-2-line" /> Reload
          </button>
        </div>
      </template>

      <!-- Filter Empty -->
      <template v-else-if="filteredFeedbacks.length === 0">
        <div class="empty-state-md">
          <Icon class="icon" icon="mingcute:search-3-line" />
          <p>No feedback matches your filters</p>
          <button @click="resetFilters" class="btn btn-link reset-filter">
            <Icon icon="mingcute:refresh-2-line" /> Clear all filters
          </button>
        </div>
      </template>

      <!-- Feedback List -->
      <div v-else class="row feedback-list">
        <div class="col-12" v-for="fb in paginatedFeedbacks" :key="fb.feedback_id">
          <div class="feedback-card">
            <!-- Feedback Header -->
            <div class="feedback-header">
              <div>
                <h5 class="feedback-name">
                  {{ fb.is_anonymous ? 'Anonymous' : `${fb.user.firstname} ${fb.user.lastname}` }}
                  <code v-if="!fb.is_anonymous && fb.user?.user_id" class="user-id">({{ fb.user.user_id }})</code>
                </h5>
                <span class="badge feedback-tag">
                  {{ fb.service_used === 'Claim Lost' ? 'Claimed Lost Item' : 'Reported Found Item' }}
                </span>
              </div>
              <div class="right-side">
                <code class="feedback-date">{{ formatDateTime(fb.submitted_at) }}</code>
                <div class="menu-wrapper" @click.stop>
                  <button class="menu-btn" @click="toggleMenu(fb.feedback_id)">⋮</button>
                  <div v-if="openMenuId === fb.feedback_id" class="menu-dropdown">
                    <button @click="deleteFeedback(fb.feedback_id)">Delete</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Feedback Body -->
            <div class="row feedback-body">
              <!-- Service -->
              <div class="col feedback-ratings">
                <div class="rating-item">
                  <label>Service Rating</label>
                  <div class="rating-stars">
                    <span v-for="n in 5" :key="'svc' + n"
                      :class="['star', n <= fb.rating_service ? 'active' : '']">★</span>
                    <span class="rating-score">({{ fb.rating_service }}/5)</span>
                  </div>
                </div>
                <div class="message-block">
                  <label>Message About Service</label>
                  <p>{{ fb.message_service }}</p>
                </div>
              </div>

              <!-- System -->
              <div class="col feedback-ratings">
                <div class="rating-item">
                  <label>System Rating</label>
                  <div class="rating-stars">
                    <span v-for="n in 5" :key="'sys' + n"
                      :class="['star', n <= fb.rating_system ? 'active' : '']">★</span>
                    <span class="rating-score">({{ fb.rating_system }}/5)</span>
                  </div>
                </div>
                <div class="message-block">
                  <label>Message About System</label>
                  <p>{{ fb.message_system }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Pagination -->
      <div class="pagination-wrapper">

        <ul class="pagination justify-content-center align-items-center">
          <!-- Previous -->
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">
              Previous
            </a>
          </li>

          <!-- Current Page Number -->
          <li class="page-item active">
            <a class="page-link" href="javascript:void(0)">
              {{ currentPage }} - {{ totalPages }}
            </a>
          </li>

          <!-- Next -->
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" @click="currentPage < totalPages && currentPage++" href="javascript:void(0)">
              Next
            </a>
          </li>
        </ul>

        <!-- Show info -->
        <div class="pagination-info text-center mt-2">
          Showing <strong>{{ (currentPage - 1) * perPage + 1 }}</strong>–
          <strong>{{ Math.min(currentPage * perPage, filteredFeedbacks.length) }}</strong>
          of <strong>{{ filteredFeedbacks.length }}</strong>
          feedback{{ filteredFeedbacks.length !== 1 ? 's' : '' }}
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div class="modal fade danger-modal" id="deleteFeedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Delete</h5>
            </div>
            <div class="modal-body">
              <p class="normal-text">Are you sure you want to delete this feedback?</p>
            </div>
            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm primary-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm secondary-btn" @click="confirmDelete">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Success Delete Toast -->
      <div class="success-toast">
        <div id="successDeleteToast" class="toast">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:delete-3-fill" />
              Feedback deleted successfully!
            </div>
            <button type="button" class="close-btn" data-bs-dismiss="toast">
              <Icon class="icon" icon="mingcute:close-line" />
            </button>
          </div>
        </div>
      </div>

    </div>
  </AdminAppLayout>
</template>

<script>
import AdminAppLayout from '../../Layouts/AdminAppLayout.vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';

export default {
  components: { AdminAppLayout, Icon },

  data() {
    return {
      feedbacks: [],
      loading: true,

      // Search & Filters
      searchQuery: '',
      filterServiceAvailed: '',
      filterAnonymous: '',
      filterServiceRating: '',
      filterSystemRating: '',
      filterTime: 'all',

      // UI
      openMenuId: null,
      feedbackToDelete: null,
      deleteModalConfirmation: null,
      successDeleteToastInstance: null,

      // Pagination
      currentPage: 1,
      perPage: 10
    };
  },

  computed: {
    // Active Filer Count
    activeFilterCount() {
      let count = 0;
      if (this.filterAnonymous) count++;
      if (this.filterServiceAvailed) count++;
      if (this.filterServiceRating) count++;
      if (this.filterSystemRating) count++;
      if (this.filterTime !== 'all') count++;
      return count;
    },

    // Search
    searchedFeedbacks() {
      if (!this.searchQuery.trim()) return this.feedbacks;

      const q = this.searchQuery.toLowerCase();
      return this.feedbacks.filter(fb => {
        const name = fb.is_anonymous
          ? 'anonymous'
          : `${fb.user?.firstname || ''} ${fb.user?.lastname || ''} ${fb.user?.user_id || ''}`.toLowerCase();

        const messages = `${fb.message_service || ''} ${fb.message_system || ''}`.toLowerCase();

        return name.includes(q) || messages.includes(q);
      });
    },

    // Filter
    filteredFeedbacks() {
      let result = this.searchedFeedbacks;

      // Service
      if (this.filterServiceAvailed) {
        result = result.filter(fb => fb.service_used === this.filterServiceAvailed);
      }

      // Anonymous / Named
      if (this.filterAnonymous === 'anonymous') {
        result = result.filter(fb => fb.is_anonymous);
      } else if (this.filterAnonymous === 'named') {
        result = result.filter(fb => !fb.is_anonymous);
      }

      // Service Rating
      if (this.filterServiceRating) {
        const rating = parseInt(this.filterServiceRating);
        result = result.filter(fb => fb.rating_service === rating);
      }

      // System Rating
      if (this.filterSystemRating) {
        const rating = parseInt(this.filterSystemRating);
        result = result.filter(fb => fb.rating_system === rating);
      }

      // Time Range
      if (this.filterTime !== 'all') {
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        const cutoffs = {
          today: now,
          '7days': new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000),
          '30days': new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
        };

        const cutoffDate = cutoffs[this.filterTime];
        result = result.filter(fb => {
          const submittedDate = new Date(fb.submitted_at);
          submittedDate.setHours(0, 0, 0, 0);
          return submittedDate >= cutoffDate;
        });
      }

      return result;
    },

    // PAGINATION
    totalPages() {
      return Math.ceil(this.filteredFeedbacks.length / this.perPage);
    },

    paginatedFeedbacks() {
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredFeedbacks.slice(start, start + this.perPage);
    },

  },

  methods: {

    async fetchFeedbacks() {
      this.loading = true;
      try {
        const { data } = await axios.get('/api/admin/feedbacks');
        this.feedbacks = data;
      } catch (err) {
        console.error(err);
        this.$swal && this.$swal('Error', 'Failed to load feedbacks', 'error');
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.searchQuery = '';
      this.filterServiceAvailed = '';
      this.filterAnonymous = '';
      this.filterServiceRating = '';
      this.filterSystemRating = '';
      this.filterTime = 'all';
      this.currentPage = 1;
    },

    toggleMenu(id) {
      this.openMenuId = this.openMenuId === id ? null : id;
    },

    deleteFeedback(id) {
      this.feedbackToDelete = id;
      this.deleteModalConfirmation?.show();
    },

    async confirmDelete() {
      if (!this.feedbackToDelete) return;

      try {
        await axios.delete(`/api/admin/feedbacks/${this.feedbackToDelete}`);

        this.feedbacks = this.feedbacks.filter(f => f.feedback_id !== this.feedbackToDelete);
        this.feedbackToDelete = null;
        this.deleteModalConfirmation?.hide();
        this.showDeleteToast();

        // Go back if current page becomes empty
        if (this.paginatedFeedbacks.length === 0 && this.currentPage > 1) {
          this.currentPage--;
        }
      } catch (err) {
        console.error(err);
        alert('Failed to delete feedback');
      }
    },

    showDeleteToast() {
      const el = document.getElementById('successDeleteToast');
      if (!el) return;
      this.successDeleteToastInstance = new bootstrap.Toast(el, { delay: 3000 });
      this.successDeleteToastInstance.show();
    },

    formatDateTime(date) {
      return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
      });
    }
  },

  mounted() {
    this.fetchFeedbacks();

    // Close dropdown menu when clicking outside
    document.addEventListener('click', () => {
      this.openMenuId = null;
    });

    // Initialize delete modal
    const modalEl = document.getElementById('deleteFeedbackModal');
    if (modalEl) {
      this.deleteModalConfirmation = new bootstrap.Modal(modalEl);
    }
  },

  watch: {
    // Reset page when filters change
    filteredFeedbacks() {
      this.currentPage = 1;
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

.layout .header {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.layout .header .title {
  color: #333;
}

.layout .header .tools {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
}

.layout .header .search {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 12px;
  border: 2px solid #3ca237;
}

.layout .header .search input {
  margin: 0;
  padding: 0;
  outline: none;
  border: none;
  box-shadow: none;
  background-color: transparent;
}

.layout .header .search input:active,
.layout .header .search input:focus {
  outline: none;
  border: none;
  box-shadow: none;
}

.layout .header .filter .filter-badge {
  background-color: #48c441;
}

.layout .header .filter .dropdown-toggle,
.layout .header .filter .dropdown-toggle:hover,
.layout .header .filter .dropdown-toggle:active,
.layout .header .filter .dropdown-toggle:focus {
  background-color: transparent;
  color: #333;
  padding: 0;
  margin: 0;
  outline: none;
  border: none;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 12px;
  border: 2px solid #3ca237;
  font-weight: 400;
}

.layout .header .filter .dropdown-menu {
  padding: 8px;
  border-radius: 12px;
  width: 380px;
}

.layout .header .filter .dropdown-menu .row {
  display: flex;
}

.layout .header .filter .dropdown-menu .row .col {
  display: flex;
}

.layout .header .filter .dropdown-menu select {
  border: solid 1px #3ca237 !important;
  border-radius: 8px;
  outline: none;
  box-shadow: none;
  cursor: pointer;
  margin-bottom: 6px;
}

.layout .header .filter .reset-filter {
  color: #3ca237;
  font-weight: 500;
  text-decoration: none;
}

.layout .header .filter .reset-filter:hover {
  color: #3ca237;
  text-decoration: underline;
}

.feedback-card {
  background: #fff;
  border-radius: 16px;
  padding: 26px;
  margin-bottom: 20px;
  border: 1px solid #e0e4e8;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
  transition: 0.2s ease;
}

.feedback-card:hover {
  transform: translateY(-0.5px);
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.feedback-name {
  font-size: 19px;
  font-weight: 700;
  margin-bottom: 4px;
}

.user-id {
  font-size: 13px;
  color: #707070;
}

.feedback-tag {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 10px;
  background-color: #48c441;
  color: #fff;
  font-size: 12.5px;
  margin-top: 4px;
}

.feedback-date {
  font-size: 13px;
  color: #7a7a7a;
  background: #f1f1f1;
  padding: 4px 8px;
  border-radius: 8px;
}

.right-side {
  display: flex;
  align-items: center;
  gap: 10px;
}

.menu-wrapper {
  position: relative;
}

.menu-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.menu-dropdown {
  position: absolute;
  right: 0;
  top: 22px;
  background: #fff;
  border: 1px solid #ddd;
  padding: 4px;
  border-radius: 12px;
  min-width: 110px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 10;
}

.menu-dropdown button {
  width: 100%;
  background: none;
  border: none;
  padding: 6px 8px;
  text-align: left;
  cursor: pointer;
  border-radius: 8px;
}

.menu-dropdown button:hover {
  background: #f5f5f5;
}

/* Feedback Body */

.feedback-body {
  margin-top: 22px;
  display: flex;
  gap: 20px;
}

.feedback-ratings {
  background: #fafafa;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid #ececec;
  box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.rating-item label {
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 14px;
  color: #555;
}

.rating-stars {
  display: flex;
  align-items: center;
  gap: 2px;
}

.star {
  font-size: 20px;
  color: #cfcfcf;
  transition: 0.2s;
}

.star.active {
  color: #f4c430;
}

.rating-score {
  margin-left: 6px;
  font-size: 14px;
  color: #666;
}

.message-block label {
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 14px;
  color: #555;
}

.message-block p {
  color: #555;
  margin: 0;
  font-size: 15px;
  line-height: 1.55;
  border-radius: 8px;

  white-space: pre-wrap;
  word-wrap: break-word;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.feedback-empty {
  text-align: center;
  padding: 50px;
  color: #9d9d9d;
  font-size: 17px;
}

/* PAGINATION */

.pagination-wrapper {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.pagination {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 4px;
}

.page-item {
  cursor: pointer;
}

.page-link {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  padding: 0 12px;
  font-size: 14px;
  color: #333;
  background-color: #D8FDE9;
  border: 1px solid #48c441;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.2s ease;
  outline: none;
  box-shadow: none;
}

.page-item .page-link:hover:not(.disabled) {
  background-color: #3ca237;
  color: #ffff;
  font-weight: 600;
}

.page-item.disabled .page-link {
  color: #aaa !important;
  background: #f5f5f5;
}

.page-item.active .page-link {
  background: #3ca237;
  color: white;
  border-color: #3ca237;
  font-weight: 600;
}

.page-item:first-child .page-link,
.page-item:last-child .page-link {
  width: 94px;
  font-weight: 500;
}

.pagination-info {
  font-size: 14px;
  color: #333;
}

.pagination-info strong {
  color: #3ca237;
  font-weight: 600;
}
</style>

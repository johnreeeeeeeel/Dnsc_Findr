<template>
  <UserAppLayout title="Home">

    <div class="container-fluid layout">

      <!-- Header -->
      <div class="header">

        <h4 class="title">
          <Icon class="icon" icon="mingcute:album-2-fill" />
          All Found Items
        </h4>

        <div class="tools">

          <!-- Search -->
          <div class="search">
            <Icon icon="mingcute:search-line" class="icon" />
            <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
          </div>

          <!-- Filters -->
          <div class="filter">
            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <Icon icon="mingcute:filter-fill" /> Filters
              <span v-if="activeFilterCount > 0" class="filter-badge badge">
                {{ activeFilterCount }}
              </span>
            </button>

            <div class="dropdown-menu">
              <div class="row g-2">

                <div class="col-6">
                  <label class="form-label small text-muted">Posted By</label>
                  <select v-model="filterUserType" class="form-select form-select-sm">
                    <option value="">All Posters</option>
                    <option value="Admin">Admin</option>
                    <option value="User">Regular User</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label small text-muted">Status</label>
                  <select v-model="filterStatus" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="Not Yet Claimed">Not Yet Claimed</option>
                    <option value="Claimed">Claimed</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label small text-muted">Category</label>
                  <select v-model="filterCategory" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label small text-muted">Time Range</label>
                  <select v-model="filterTime" class="form-select form-select-sm">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                  </select>
                </div>

                <div class="col-12 text-end mt-2">
                  <button @click="resetFilters" class="btn btn-link reset-filter">
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
        <div class="loading-text">Loading posts...</div>
      </div>

      <!-- Empty -->
      <div v-else-if="posts.length === 0" class="empty-state-md">
        <Icon class="icon" icon="mingcute:empty-box-fill" />
        <p>No found items posted yet</p>
      </div>

      <!-- POST LISTS -->
      <div v-else class="container posts-container">
        <div class="post-lists">

          <div v-for="post in paginatedPosts" :key="post.item_id" class="card post-card">

            <!-- Card Header -->
            <div class="card-header posted-by">
              <div class="avatar">
                <Icon class="icon" icon="mingcute:user-4-fill" />
              </div>

              <div class="posted-by-info">
                <p class="name">{{ fullName(post.postedBy) }}</p>
                <div class="other-info">
                  <span class="badge user-type" :class="post.postedBy?.usertype?.toLowerCase() || 'user'">
                    {{ post.postedBy?.usertype || 'User' }}
                  </span>
                  <span class="date-time">
                    <Icon icon="mingcute:time-line" class="icon" />
                    {{ formatDate_1(post.date_found) }} at {{ formatTime_1(post.time_found) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body">
              <div class="card-image">
                <img :src="post.photo_url" alt="Found item" @click="openImageModal(post.photo_url)"
                  style="cursor:pointer;" />
                <span :class="['badge item-status-badge',
                  post.status === 'Not Yet Claimed' ? 'not-claimed' : 'claimed']">
                  {{ post.status }}
                </span>
              </div>
              <h5 class="card-title">{{ post.item_name }}</h5>
            </div>

            <!-- Card Footer - User Claim Button -->
            <div class="card-footer">
              <div class="action-buttons">
                <button v-if="post.status === 'Not Yet Claimed'" class="btn primary-btn w-100"
                  @click="openClaimModal(post)" :disabled="!canClaim(post)" :class="{ 'disabled-btn': !canClaim(post) }"
                  :title="!canClaim(post)
                    ? (isOwnPost(post) ? 'You posted this item' : 'You already claimed this item')
                    : 'Click to claim this item'">
                  <span v-if="isOwnPost(post)">You Posted This</span>
                  <span v-else-if="alreadyClaimed(post)">Already Claimed by You</span>
                  <span v-else>Claim This Item</span>
                </button>

                <button v-else class="btn btn-success btn-sm w-100" disabled>
                  Item has been Claimed
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">

          <!-- Pagination Controls - show only when more than 1 page -->
          <ul class="pagination">
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
          <div class="pagination-info">
            <span v-if="filteredPosts.length === 0 && !loading" class="empty-state-md">
              <Icon class="icon" icon="mingcute:empty-box-fill" />
              <p>No found items found</p>

              <p>Try adjusting your search or filters</p>

              <button @click="resetFilters" class="btn btn-link reset-filter">
                <Icon icon="mingcute:refresh-2-line" /> Clear all filters
              </button>

            </span>
            <span v-else>
              Showing <strong>{{ (currentPage - 1) * perPage + 1 }}</strong>–
              <strong>{{ Math.min(currentPage * perPage, filteredPosts.length) }}</strong>
              of <strong>{{ filteredPosts.length }}</strong> post{{ filteredPosts.length !== 1 ? 's' : '' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Preview Image -->
      <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center">
              <img :src="previewImage" class="img-fluid rounded" alt="Enlarged Image">
            </div>
          </div>
        </div>
      </div>

      <!-- CLAIM ITEM MODAL -->

      <div class="modal fade primary-modal" id="claimModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-md">
          <div class="modal-content">

            <form @submit.prevent="submitClaim">

              <!-- Header -->
              <div class="modal-header">
                <h5 class="modal-title">Claim this Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                <div class="field">
                  <label>Description / Evidence <span class="text-danger">*</span></label>
                  <textarea v-model="claimForm.description" rows="4"
                    placeholder="Tell us how you know this is your item..." class="input"></textarea>
                  <p v-if="errors.description" class="error-text">
                    {{ errors.description[0] }}
                  </p>
                </div>

                <div class="field">
                  <label>Where did you lose it?</label>
                  <input v-model="claimForm.location_lost" type="text" class="input" placeholder="e.g. Library">
                  <p v-if="errors.location_lost" class="error-text">
                    {{ errors.location_lost[0] }}
                  </p>
                </div>

                <div class="field">
                  <label>Date Lost <span class="text-danger">*</span></label>
                  <input v-model="claimForm.date_lost" type="date" class="input" />
                  <p v-if="errors.date_lost" class="error-text">
                    {{ errors.date_lost[0] }}
                  </p>
                </div>

                <div class="field">
                  <label>Time Lost</label>
                  <input v-model="claimForm.time_lost" type="time" class="input" />


                  <p v-if="errors.time_lost" class="error-text">
                    {{ errors.time_lost[0] }}
                  </p>
                </div>

              </div>

              <div class="modal-footer">
                <div class="action-buttons">
                  <button class="btn btn-sm secondary-btn" data-bs-dismiss="modal" :disabled="processing">
                    Cancel
                  </button>

                  <button type="submit" class="btn btn-sm primary-btn" :disabled="processing">

                    <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    {{ processing ? 'Submitting...' : 'Submit Claim' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Success Submit Toast -->
      <div class="success-toast">
        <div id="successSubmitToast" class="toast">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:delete-3-fill" />
              Claim submitted successfully!
            </div>
            <button type="button" class="close-btn" data-bs-dismiss="toast">
              <Icon class="icon" icon="mingcute:close-line" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </UserAppLayout>
</template>

<script>
import UserAppLayout from '../../Layouts/UserAppLayout.vue';
import { Icon } from '@iconify/vue';

export default {
  components: { UserAppLayout, Icon },

  data() {
    return {

      posts: [],
      loading: true,
      previewImage: null,

      // Filters
      searchQuery: '',
      filterUserType: '',
      filterStatus: '',
      filterCategory: '',
      filterTime: 'all',

      // Pagination
      currentPage: 1,
      perPage: 10,

      claimForm: {
        description: '',
        location_lost: '',
        date_lost: '',
        time_lost: ''
      },

      claimingItemId: null,
      userClaimedItemIds: [],
      currentUserId: null,

      processing: false,
      errors: {}
    };
  },

  computed: {
    // Fixed categories 
    categories() {
      return [
        'ID / Student Card',
        'Electronics',
        'Documents',
        'Accessories',
        'Clothing',
        'Bags / Wallets',
        'Others'
      ];
    },

    activeFilterCount() {
      let count = 0;
      if (this.filterUserType) count++;
      if (this.filterStatus) count++;
      if (this.filterCategory) count++;
      if (this.filterTime !== 'all') count++;
      return count;
    },

    searchedPosts() {
      if (!this.searchQuery.trim()) return this.posts;

      const q = this.searchQuery.toLowerCase();

      return this.posts.filter(post => {
        const itemName = post.item_name?.toLowerCase() || '';
        const description = post.description?.toLowerCase() || '';
        const location = post.location_found?.toLowerCase() || '';
        const posterName = post.postedBy ? this.fullName(post.postedBy).toLowerCase() : '';

        return (
          itemName.includes(q) ||
          description.includes(q) ||
          location.includes(q) ||
          posterName.includes(q)
        );
      });
    },

    filteredPosts() {
      let result = this.searchedPosts;

      // Filter: Posted By
      if (this.filterUserType) {
        result = result.filter(post => {
          const isAdmin = post.postedBy?.usertype === 'Admin';
          return (isAdmin ? 'Admin' : 'User') === this.filterUserType;
        });
      }

      // Filter: Status
      if (this.filterStatus) {
        result = result.filter(post => post.status === this.filterStatus);
      }

      // Filter: Category
      if (this.filterCategory) {
        result = result.filter(post => post.item_category === this.filterCategory);
      }

      // Filter: Time Range
      if (this.filterTime !== 'all') {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const cutoff = {
          today: today,
          '7days': new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000),
          '30days': new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000)
        };

        result = result.filter(post => {
          const postDate = new Date(post.date_found);
          return postDate >= cutoff[this.filterTime];
        });
      }

      return result;
    },

    totalPages() {
      return Math.ceil(this.filteredPosts.length / this.perPage);
    },

    paginatedPosts() {
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredPosts.slice(start, start + this.perPage);
    },

  },

  async mounted() {
    await this.fetchCurrentUserId();
    await this.fetchUserClaims();
    await this.fetchApprovedPosts();
  },

  methods: {

    // FETCH ALL APPROVED POSTS
    async fetchApprovedPosts() {
      this.loading = true;
      try {
        const res = await axios.get('/found-items/all-post-approve');
        this.posts = res.data;
      } catch (err) {
        console.error(err);
        alert('Failed to load posts');
      } finally {
        this.loading = false;
      }
    },

    // RESET FILTERS
    resetFilters() {
      this.searchQuery = '';
      this.filterUserType = '';
      this.filterStatus = '';
      this.filterCategory = '';
      this.filterTime = 'all';
      this.currentPage = 1;
    },

    // FETCH CURRENT USER ID
    async fetchCurrentUserId() {
      try {
        const res = await axios.get('/api/user-info');
        this.currentUserId = res.data.user_id;
      } catch (err) {
        console.error('Failed to get user info', err);
      }
    },

    // FETCH USER CLAIMS
    async fetchUserClaims() {
      try {
        const res = await axios.get('/api/user-claims');
        this.userClaimedItemIds = res.data;
      } catch (err) {
        console.error('Failed to fetch user claims', err);
      }
    },

    // CHECK IF POST BELONGS TO CURRENT USER
    isOwnPost(post) {
      return post.postedBy?.user_id === this.currentUserId;
    },

    // CHECK IF POST IS ALREADY CLAIMED
    alreadyClaimed(post) {
      return this.userClaimedItemIds.includes(post.item_id);
    },

    // DETERMINE IF USER CAN CLAIM THIS POST
    canClaim(post) {
      return post.status === 'Not Yet Claimed' && !this.isOwnPost(post) && !this.alreadyClaimed(post);
    },

    // OPEN CLAIM MODAL
    openClaimModal(post) {
      if (!this.canClaim(post)) return;

      this.claimingItemId = post.item_id;

      // Reset form and clear previous errors
      this.claimForm = {
        description: '',
        location_lost: '',
        date_lost: '',
        time_lost: ''
      };
      this.submitError = null;
      this.errors = {}; // ← Clear validation errors

      const modalEl = document.getElementById('claimModal');
      new bootstrap.Modal(modalEl).show();
    },

    // SUBMIT CLAIM
    async submitClaim() {
      if (!this.claimingItemId) return;

      this.processing = true;
      this.errors = {};

      try {
        await axios.post(`/claims/claim-item/${this.claimingItemId}`, this.claimForm);

        // Show success toast
        this.showSuccessToast('successSubmitToast');

        this.userClaimedItemIds.push(this.claimingItemId);
        bootstrap.Modal.getInstance(document.getElementById('claimModal'))?.hide();
        this.claimingItemId = null;

      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || {};
        } else {
          this.errors = {
            description: [error.response?.data?.message || 'Something went wrong.']
          };

          if (error.response?.status === 403) {
            this.userClaimedItemIds.push(this.claimingItemId);
          }
        }
      } finally {
        this.processing = false;
      }
    },

    // HELPERS

    showSuccessToast(id) {
      const toastEl = document.getElementById(id);

      // Dispose any existing instance on this element
      const oldToast = bootstrap.Toast.getInstance(toastEl);
      if (oldToast) oldToast.dispose();

      // Hide any other visible success-toast
      const visibleToasts = document.querySelectorAll('.success-toast .toast.show');
      visibleToasts.forEach(t => {
        const instance = bootstrap.Toast.getInstance(t);
        if (instance) instance.hide();
      });

      // Create new toast instance and show it
      const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 3000
      });
      toast.show();
    },

    // FORMAT FULL NAME
    fullName(user) {
      if (!user) return "Unknown";
      if (user.usertype === 'Admin') return user.username || "Admin";
      const names = [user.firstname, user.middlename, user.lastname].filter(n => n && n.trim());
      return names.length ? names.join(" ") : (user.username || "User");
    },

    // 4:23 PM
    formatTime_1(time24) {
      if (!time24) return '—';

      const [hours, minutes] = time24.split(':');
      let hour = parseInt(hours, 10);
      const minute = minutes.padStart(2, '0');
      if (isNaN(hour)) return '—';

      const period = hour >= 12 ? 'PM' : 'AM';
      hour = hour % 12 || 12;

      return `${hour}:${minute} ${period}`;
    },

    // Dec 11, 2025
    formatDate_1(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    },

    // OPEN IMAGE PREVIEW MODAL
    openImageModal(url) {
      this.previewImage = url;
      const modalEl = document.getElementById('imageModal');
      new bootstrap.Modal(modalEl).show();
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

/* POST LISTS */

.posts-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.post-lists {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 16px;
  max-width: 650px;
  margin: 0 auto;
}

.posts-container .post-card {
  width: 100%;
  border-radius: 12px;
  border: none;
  background-color: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s, box-shadow 0.2s;
  overflow: hidden;
}

.posts-container .post-card .card-header {
  outline: none;
  border: none;
  background-color: #ffffff;
  padding: 12px 16px;
}

.user-type {
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  color: #fff;
}

.user-type.admin {
  background-color: #0d6efd;
}

.user-type.instructor {
  background-color: #198754;
}

.user-type.staff {
  background-color: #fd7e14;
}

.user-type.student {
  background-color: #6f42c1;
}

.user-type.user {
  background-color: #6c757d;
}

.posts-container .post-card .card-body {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 12px 16px;
  outline: none;
  border: none;
  background-color: #ffffff;
}

.posts-container .post-card .card-body .card-image {
  position: relative;
}

.posts-container .post-card .card-body .card-image img {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 12px;
}

.posts-container .post-card .card-body .card-title {
  font-weight: 600;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* Item Statuts Badges */
.item-status-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  color: #fff;
}

.item-status-badge.not-claimed {
  background-color: #0dff00;
  color: #333;
}

.item-status-badge.claimed {
  background-color: #48c441;
  color: #f2f3f7;
}

/* Footer */
.posts-container .post-card .card-footer {
  padding: 12px 16px;
  outline: none;
  border: none;
  background-color: #ffffff;
  font-size: 14px;
}

.posts-container .post-card .card-footer .action-buttons {
  display: flex;
  flex-direction: row;
  gap: 8px;
}

.posts-container .post-card .card-footer .action-buttons .btn {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
  background-color: #48c441;
  border: none;
  color: white;
  transition: all 0.2s ease;
  width: 100%;
}

.posts-container .post-card .card-footer .action-buttons .btn:hover {
  background-color: #3ca237;
  transform: scale(1.005);
}

/* POSTED BY & CLAIM BY */

.posted-by,
.claim-by {
  display: flex;
  align-items: center;
  gap: 12px;
  outline: none;
  border: none;
  background-color: transparent;
}

.posted-by .avatar,
.claim-by .avatar {
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.posted-by .avatar .icon,
.claim-by .avatar .icon {
  width: 48px;
  height: 48px;
  color: #555;
}

.posted-by .posted-by-info,
.claim-by .claim-by-info {
  display: flex;
  flex-direction: column;
}

.posted-by .posted-by-info .name,
.claim-by .claim-by-info .name {
  font-weight: 500;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.posted-by .posted-by-info .other-info,
.claim-by .claim-by-info .other-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}

/* CLAIM ITEM MODAL */

#claimModal .modal-content {
  border: none !important;
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
  border-radius: 12px !important;
}

#claimModal .modal-header {
  border: none !important;
  border-radius: 12px !important;
}

#claimModal .modal-header .modal-title {
  font-weight: 600;
  letter-spacing: 0.5px;
  color: #48c441;
}

#claimModal .modal-body {
  border: none !important;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

#claimModal .modal-body .field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

#claimModal .modal-body .field label {
  font-weight: 500;
  color: #333;
}

#claimModal .modal-body .field .error-text {
  font-size: 14px;
  color: #d60000;
  display: flex;
  align-items: center;
  gap: 4px;
}

#claimModal .modal-body .field .error-text::before {
  content: "⚠️";
}

#claimModal .modal-body .field input,
#claimModal .modal-body .field textarea {
  width: 100%;
  padding: 10px 12px 10px 12px;
  border-radius: 12px;
  border: 1px solid #48c441;
  box-sizing: border-box;
  resize: none;
}

#claimModal .modal-body .field input:focus,
#claimModal .modal-body .field textarea:focus {
  outline: none;
  border: #48c441 solid 1px;
  box-shadow: 0 0 4px 1px #48c441;
}

#claimModal .modal-footer {
  border: none !important;
  border-radius: 12px !important;
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

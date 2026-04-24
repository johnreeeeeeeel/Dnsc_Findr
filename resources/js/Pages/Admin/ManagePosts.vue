<template>
  <AdminAppLayout title="Manage Posts" @post-created="fetchAllPostsStatus">
    <div class="container-fluid layout">

      <!-- Header -->
      <div class="header">
        <h4 class="title">
          <Icon class="icon" icon="mingcute:album-2-fill" />
          Manage Found Items Post
        </h4>

        <div class="tools">

          <!-- Tabs -->
          <ul class="nav nav-tabs" id="statusTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pendingPosts">
                Pending <span class="badge count-badge">{{ pending.length }}</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approvedPosts">
                Approved <span class="badge count-badge">{{ approved.length }}</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rejectedPosts">
                Rejected <span class="badge count-badge">{{ rejected.length }}</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#archivedPosts">
                Archived <span class="badge count-badge">{{ archived.length }}</span>
              </button>
            </li>
          </ul>

          <!-- Search -->
          <div class="search">
            <Icon icon="mingcute:search-line" class="icon" />
            <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
          </div>
        </div>
      </div>

      <div class="tab-content">

        <!-- PENDING TAB -->
        <div class="tab-pane fade show active" id="pendingPosts">

          <!-- Loading -->
          <div v-if="loading" class="loading">
            <div class="spinner-border" role="status"></div>
            <div class="loading-text">Loading posts...</div>
          </div>

          <!-- Empty -->
          <div v-else-if="currentPosts.length === 0" class="empty-state-md">
            <Icon class="icon" icon="mingcute:empty-box-fill" />
            <p>No found items posted yet</p>

            <p>Try reloading or clear search</p>

            <button @click="fetchAllPostsStatus" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else class="content">
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">

                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item photo" />

                  <div class="card-body">
                    <span class="badge pending">Pending</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted by {{ formatFullName(post.postedBy) }}</small>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="action-buttons" @click.stop>
                      <button @click.stop="openApproveModal(post)" class="btn btn-sm">Approve</button>
                      <button @click.stop="openRejectModal(post)" class="btn btn-sm">Reject</button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">Delete</button>
                    </div>

                  </div>

                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="currentPosts.length > 0" class="pagination-wrapper">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>

                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} - {{ totalPages }}</a>
                </li>

                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" @click="currentPage < totalPages && currentPage++"
                    href="javascript:void(0)">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- APPROVED TAB -->
        <div class="tab-pane fade" id="approvedPosts">

          <!-- Loading -->
          <div v-if="loading" class="loading">
            <div class="spinner-border" role="status"></div>
            <div class="loading-text">Loading posts...</div>
          </div>

          <!-- Empty -->
          <div v-else-if="currentPosts.length === 0" class="empty-state-md">
            <Icon class="icon" icon="mingcute:empty-box-fill" />
            <p>No found items posted yet</p>

            <p>Try reloading or clear search</p>

            <button @click="fetchAllPostsStatus" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else class="content">
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">
                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item photo" />

                  <div class="card-body">
                    <span class="badge approved">Approved</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted by {{ formatFullName(post.postedBy) }}</small>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>
                    <small>Approved on {{ formatDate(post.updated_at || post.created_at) }}</small>

                    <!-- Action Buttons-->
                    <div class="action-buttons" @click.stop>
                      <button @click.stop="openRejectModal(post)" class="btn btn-sm">Reject</button>
                      <button @click.stop="openArchiveModal(post)" class="btn btn-sm">Archive</button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">Delete</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="currentPosts.length > 0" class="pagination-wrapper">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>

                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} - {{ totalPages }}</a>
                </li>

                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" @click="currentPage < totalPages && currentPage++"
                    href="javascript:void(0)">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- REJECTED TAB -->
        <div class="tab-pane fade" id="rejectedPosts">

          <!-- Loading -->
          <div v-if="loading" class="loading">
            <div class="spinner-border" role="status"></div>
            <div class="loading-text">Loading posts...</div>
          </div>

          <!-- Empty -->
          <div v-else-if="currentPosts.length === 0" class="empty-state-md">
            <Icon class="icon" icon="mingcute:empty-box-fill" />
            <p>No found items posted yet</p>

            <p>Try reloading or clear search</p>

            <button @click="fetchAllPostsStatus" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else class="content">
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">
                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item photo" />

                  <div class="card-body">
                    <span class="badge rejected">Rejected</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted by {{ formatFullName(post.postedBy) }}</small>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>
                    <small>Rejected on {{ formatDate(post.updated_at || post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="action-buttons" @click.stop>
                      <button @click.stop="openApproveModal(post)" class="btn btn-sm">Approve</button>
                      <button @click.stop="openArchiveModal(post)" class="btn btn-sm">Archive</button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">Delete</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="currentPosts.length > 0" class="pagination-wrapper">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>

                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} - {{ totalPages }}</a>
                </li>

                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" @click="currentPage < totalPages && currentPage++"
                    href="javascript:void(0)">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- ARCHIVED TAB -->
        <div class="tab-pane fade" id="archivedPosts">

          <!-- Loading -->
          <div v-if="loading" class="loading">
            <div class="spinner-border" role="status"></div>
            <div class="loading-text">Loading posts...</div>
          </div>

          <!-- Empty -->
          <div v-else-if="currentPosts.length === 0" class="empty-state-md">
            <Icon class="icon" icon="mingcute:empty-box-fill" />
            <p>No found items posted yet</p>

            <p>Try reloading or clear search</p>

            <button @click="fetchAllPostsStatus" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else class="content">
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">

                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Archived post" />

                  <div class="card-body">
                    <span class="badge archived">Archived</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted by {{ formatFullName(post.postedBy) }}</small>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>
                    <small>Archived on {{ formatDate(post.updated_at || post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="action-buttons" @click.stop>
                      <button @click.stop="openUnarchiveModal(post)" class="btn btn-sm">Unarchive</button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">Delete</button>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="currentPosts.length > 0" class="pagination-wrapper">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>

                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} - {{ totalPages }}</a>
                </li>

                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" @click="currentPage < totalPages && currentPage++"
                    href="javascript:void(0)">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>

      </div>

      <!-- POST DETAIL MODAL -->

      <div class="modal fade" id="detailModal" tabindex="-1" data-bs-backdrop="static">

        <div class="modal-dialog modal-dialog-centered modal-md">

          <div class="modal-content" v-if="selectedPost">

            <div class="modal-header">
              <h5 class="modal-title">
                <code>#{{ selectedPost.item_id }}</code> - {{ selectedPost.item_name }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="row">

                <div class="col-12">
                  <img :src="selectedPost.photo_url" class="img-fluid-top" />
                </div>

                <div class="col-12">
                  <div>
                    <span class="badge" :class="{
                      pending: selectedPost.post_status === 'pending',
                      approved: selectedPost.post_status === 'approved',
                      rejected: selectedPost.post_status === 'rejected',
                      archived: selectedPost.post_status === 'archived'
                    }">
                      {{ selectedPost.post_status === 'archived'
                        ? 'Archived'
                        : selectedPost.post_status.charAt(0).toUpperCase() + selectedPost.post_status.slice(1)
                      }}
                    </span>
                  </div>
                  <p>
                    <span>Description:</span>
                    {{ selectedPost.description || 'No description' }}
                  </p>

                  <p>
                    <span>Category:</span>
                    {{ selectedPost.item_category }}
                  </p>

                  <p>
                    <span>Location Found:</span>
                    {{ selectedPost.location_found }}
                  </p>

                  <p>
                    <span>Date & Time Found:</span>
                    {{ formatDate(selectedPost.date_found) }} at {{ formatTime(selectedPost.time_found) }}
                  </p>

                  <p>
                    <span>Status:</span>
                    {{ selectedPost.status }}
                  </p>

                  <p>
                    <span>Posted By:</span>
                    {{ formatFullName(selectedPost.postedBy) }}
                  </p>

                  <p>
                    <span>Posted On:</span>
                    {{ formatDateTime(selectedPost.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- APPROVE CONFIRMATION MODAL -->

      <div class="modal fade primary-modal" id="approveModal" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered">

          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon icon="mingcute:check-circle-fill" class="me-2" />
                Approve Post
              </h5>
            </div>

            <div class="modal-body">
              <p class="normal-text">Are you sure you want to <strong>approve</strong> this post?</p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-sm primary-btn" @click="performApprove" :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                  {{ processing ? 'Approving...' : 'Yes, Approve' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- APPROVE SUCCESS TOAST -->

      <div class="success-toast">
        <div id="approvePostSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:check-circle-fill" />
              Post approved successfully!
            </div>
            <button type="button" class="close-btn" data-bs-dismiss="toast">
              <Icon class="icon" icon="mingcute:close-line" />
            </button>
          </div>
        </div>
      </div>

      <!-- REJECT CONFIRMATION MODAL -->

      <div class="modal fade danger-modal" id="rejectModal" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered">

          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon icon="mingcute:close-circle-fill" class="me-2" />
                Reject Post
              </h5>
            </div>

            <div class="modal-body">
              <p class="normal-text">Are you sure you want to <strong>reject</strong> this post?</p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-sm primary-btn" @click="performReject" :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                  {{ processing ? 'Rejecting...' : 'Yes, Reject' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- REJECT SUCCESS TOAST -->

      <div class="success-toast">
        <div id="rejectPostSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:close-circle-fill" />
              Post rejected successfully!
            </div>
            <button type="button" class="close-btn" data-bs-dismiss="toast">
              <Icon class="icon" icon="mingcute:close-line" />
            </button>
          </div>
        </div>
      </div>

      <!-- ARCHIVE CONFIRMATION MODAL -->

      <div class="modal fade danger-modal" id="archiveModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon icon="mingcute:archive-fill" class="me-2" />
                Archive Post
              </h5>
            </div>

            <div class="modal-body">
              <p class="normal-text">
                Are you sure you want to <strong>archive</strong> this post?<br>
              </p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm primary-btn" data-bs-dismiss="modal">
                  No
                </button>
                <button type="button" class="btn btn-sm secondary-btn" @click="performArchive" :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                  {{ processing ? 'Archiving...' : 'Yes, Archive' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ARCHIVE SUCCESS TOAST -->

      <div class="success-toast">
        <div id="archivePostSuccess" class="toast" role="alert">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:archive-fill" />
              Post archived successfully!
            </div>
            <button type="button" class="close-btn" data-bs-dismiss="toast">
              <Icon class="icon" icon="mingcute:close-line" />
            </button>
          </div>
        </div>
      </div>

      <!-- UNARCHIVE CONFIRMATION MODAL -->

      <div class="modal fade primary-modal" id="unarchiveModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon icon="mingcute:unarchive-fill" class="me-2" />
                Unarchive Post
              </h5>
            </div>

            <div class="modal-body">
              <p class="normal-text">
                Are you sure you want to <strong>Unarchive</strong> this post?<br>
              </p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm primary-btn" @click="performUnarchive" :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                  {{ processing ? 'Unarchiving...' : 'Yes, Unarchive' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- UNARCHIVE SUCCESS TOAST -->

      <div class="success-toast">
        <div id="unarchiveSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:unarchive-fill" />
              Post unarchived successfully!
            </div>
            <button type="button" class="close-btn" data-bs-dismiss="toast">
              <Icon class="icon" icon="mingcute:close-line" />
            </button>
          </div>
        </div>
      </div>

      <!-- DELETE CONFIRMATION MODAL -->

      <div class="modal fade danger-modal" id="permanentDeleteModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">

          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon icon="mingcute:delete-2-fill" class="me-2" />
                Delete Permanently
              </h5>
            </div>

            <div class="modal-body">
              <p class="text-danger">
                Are you sure you want delete the post permamently?
              </p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm primary-btn" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-sm secondary-btn" @click="performPermanentDelete"
                  :disabled="processing">
                  <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                  {{ processing ? 'Deleting...' : 'Delete Permanently' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- DELETE SUCCESS TOAST -->

      <div class="success-toast">
        <div id="permanentDeleteSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:delete-2-fill" />
              Post deleted permanently!
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
import AdminAppLayout from "../../Layouts/AdminAppLayout.vue";
import { Icon } from '@iconify/vue';

export default {
  components: { AdminAppLayout, Icon },

  data() {
    return {
      allPosts: [],
      loading: true,
      selectedPost: null,

      processing: false,

      searchQuery: '',
      itemsPerPage: 9,

      // One page per tab
      page: { pending: 1, approved: 1, rejected: 1, archived: 1 },

      // Active tab
      activeTab: 'pending'
    };
  },

  computed: {
    // Current page for active tab
    currentPage: {
      get() { return this.page[this.activeTab]; },
      set(val) { this.page[this.activeTab] = val; }
    },

    // Search + filter
    filteredPosts() {
      if (!this.searchQuery.trim()) return this.allPosts;
      const q = this.searchQuery.toLowerCase();
      return this.allPosts.filter(p => {
        const fullName = this.formatFullName(p.postedBy)?.toLowerCase() || '';
        return (
          p.item_name?.toLowerCase().includes(q) ||
          p.item_category?.toLowerCase().includes(q) ||
          p.location_found?.toLowerCase().includes(q) ||
          p.item_id?.toString().includes(q) ||
          fullName.includes(q)
        );
      });
    },

    // Posts per status
    pending() { return this.filteredPosts.filter(p => p.post_status === 'pending'); },
    approved() { return this.filteredPosts.filter(p => p.post_status === 'approved'); },
    rejected() { return this.filteredPosts.filter(p => p.post_status === 'rejected'); },
    archived() { return this.filteredPosts.filter(p => p.post_status === 'archived'); },

    // Current tab posts
    currentPosts() {
      switch (this.activeTab) {
        case 'pending': return this.pending;
        case 'approved': return this.approved;
        case 'rejected': return this.rejected;
        case 'archived': return this.archived;
        default: return [];
      }
    },

    // Pagination
    totalPages() {
      return Math.max(1, Math.ceil(this.currentPosts.length / this.itemsPerPage));
    },

    paginatedPosts() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.currentPosts.slice(start, start + this.itemsPerPage);
    }
  },

  watch: {
    searchQuery() { this.currentPage = 1; },
    activeTab() { this.currentPage = 1; }
  },

  mounted() {
    this.fetchAllPostsStatus();

    // Let Bootstrap tell us which tab is active
    const tabEl = document.querySelector('#statusTab');
    tabEl.addEventListener('shown.bs.tab', (e) => {
      const target = e.target.getAttribute('data-bs-target'); // e.g. "#approvedPosts"
      const tabName = target.substring(1).replace('Posts', '').toLowerCase(); // "approved"
      this.activeTab = tabName === 'myarchived' ? 'archived' : tabName;
      this.resetCurrentPage();
    });
  },

  methods: {
    // Fetch All Posts
    async fetchAllPostsStatus() {
      this.loading = true;
      try {
        const { data } = await axios.get("/found-items/all-post-status");
        this.allPosts = data;
      } catch (err) {
        alert("Failed to load posts");
      } finally {
        this.loading = false;
      }
    },

    // Clear Search
    async clearSearch() {
      this.loading = true;

      try {
        this.searchQuery = '';
        this.currentPage = 1;
      } catch (err) {
        alert("Failed to clear search");
      } finally {
        this.loading = false;
      }
    },

    // Reset all pages after any action
    resetAllPages() {
      this.page = { pending: 1, approved: 1, rejected: 1, archived: 1 };
    },

    // Open Details Modal
    openDetailModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal('#detailModal').show();
    },

    // Open Approve Modal
    openApproveModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal('#approveModal').show();
    },

    // Perform Approve
    async performApprove() {
      this.processing = true;

      try {
        await axios.post(`/found-items/${this.selectedPost.item_id}/approve`);
        bootstrap.Modal.getInstance('#approveModal')?.hide();

        this.showSuccessToast('approvePostSuccess');
        await this.fetchAllPostsStatus();
        this.resetAllPages();

      } catch {
        alert("Failed to approve");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    // Open Reject Modal
    openRejectModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal('#rejectModal').show();
    },

    // Perform Reject
    async performReject() {
      this.processing = true;

      try {
        await axios.post(`/found-items/${this.selectedPost.item_id}/reject`);
        bootstrap.Modal.getInstance('#rejectModal')?.hide();

        this.showSuccessToast('rejectPostSuccess');
        await this.fetchAllPostsStatus();
        this.resetAllPages();

      } catch {
        alert("Failed to reject");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    // Open Arhive Modal
    openArchiveModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal(document.getElementById('archiveModal')).show();
    },

    // Perform Archive
    async performArchive() {
      this.processing = true;

      try {
        await axios.delete(`/found-items/${this.selectedPost.item_id}/archive`);
        bootstrap.Modal.getInstance('#archiveModal')?.hide();

        this.showSuccessToast('archivePostSuccess');
        await this.fetchAllPostsStatus();
        this.resetAllPages();

      } catch {
        alert("Failed to archive");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    // Open Unarhive Modal
    openUnarchiveModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal(document.getElementById('unarchiveModal')).show();
    },

    // Perform Unarchive
    async performUnarchive() {
      this.processing = true;

      try {
        await axios.post(`/found-items/${this.selectedPost.item_id}/unarchive`);
        bootstrap.Modal.getInstance('#unarchiveModal')?.hide();

        this.showSuccessToast('unarchiveSuccess');
        await this.fetchAllPostsStatus();
        this.resetAllPages();

      } catch {
        alert("Failed to unarchive");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    // Open Delete Modal
    openPermanentDeleteModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal(document.getElementById('permanentDeleteModal')).show();
    },

    // Perform Delete Modal
    async performPermanentDelete() {
      this.processing = true;

      try {
        await axios.delete(`/found-items/${this.selectedPost.item_id}/delete`);
        bootstrap.Modal.getInstance('#permanentDeleteModal')?.hide();

        this.showSuccessToast('permanentDeleteSuccess');
        await this.fetchAllPostsStatus();
        this.resetAllPages();

      } catch {
        alert("Failed to delete");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    // HELPERS

    showSuccessToast(id) {
      const toast = new bootstrap.Toast(document.getElementById(id), { delay: 3500 });
      toast.show();
    },

    formatFullName(user) {
      if (!user) return "Unknown";
      const names = [user.firstname, user.middlename, user.lastname].filter(Boolean);
      return names.length ? names.join(" ") : user.username || "User";
    },

    formatDate(date) {
      return date
        ? new Date(date).toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric'
        })
        : '—';
    },

    formatTime(time) {
      if (!time) return '—';
      const [h, m] = time.split(':').map(Number);
      const period = h >= 12 ? 'PM' : 'AM';
      const hour = ((h + 11) % 12 + 1);
      return `${hour}:${String(m).padStart(2, '0')} ${period}`;
    },

    formatDateTime(date) {
      return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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

.layout .header {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.layout .header .title {
  color: #333;
}

.layout .header .tools {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
}

.layout .header .search {
  flex: 1;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  width: 380px;
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

.layout .header #statusTab {
  border-bottom: none !important;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 4px;
}

.layout .header #statusTab .nav-link .count-badge {
  background-color: #444;
}

.layout .header #statusTab .nav-link {
  border-bottom: none !important;
  border-radius: 12px !important;
  font-weight: 500;
  color: #333;
  background-color: #e3e3e3;
  flex: 1 0 1000px;
}

.layout .header #statusTab .nav-link.active {
  border-bottom: none !important;
  background-color: #48c441;
  font-weight: 500;
  color: #ffffff;
}

.layout .header #statusTab .nav-link:hover {
  background-color: #48c441;
  color: #ffffff;
}

/* Pending Posts & Approve Posts & Rejected Posts & Archived Posts */

#pendingPosts .content,
#approvedPosts .content,
#rejectedPosts .content,
#archivedPosts .content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

#pendingPosts .card,
#approvedPosts .card,
#rejectedPosts .card,
#archivedPosts .card {
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  color: #333;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

#pendingPosts .card:hover,
#approvedPosts .card:hover,
#rejectedPosts .card:hover,
#archivedPosts .card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: scale(1.005);
}

#pendingPosts .card .card-img-top,
#approvedPosts .card .card-img-top,
#rejectedPosts .card .card-img-top,
#archivedPosts .card .card-img-top {
  border-radius: 12px;
  height: 200px;
  object-fit: cover;
  width: 100%;
}

#pendingPosts .card .card-body,
#approvedPosts .card .card-body,
#rejectedPosts .card .card-body,
#archivedPosts .card .card-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 12px;
}

#pendingPosts .card .card-body .badge.pending,
#approvedPosts .card .card-body .badge.pending,
#rejectedPosts .card .card-body .badge.pending,
#archivedPosts .card .card-body .badge.pending {
  width: 100%;
  background-color: #FFC107;
  color: #333;
}

#pendingPosts .card .card-body .badge.approved,
#approvedPosts .card .card-body .badge.approved,
#rejectedPosts .card .card-body .badge.approved,
#archivedPosts .card .card-body .badge.approved {
  width: 100%;
  background-color: #28A745;
  color: #fff;
}

#pendingPosts .card .card-body .badge.rejected,
#approvedPosts .card .card-body .badge.rejected,
#rejectedPosts .card .card-body .badge.rejected,
#archivedPosts .card .card-body .badge.rejected {
  width: 100%;
  background-color: #DC3545;
  color: #fff;
}

#pendingPosts .card .card-body .badge.archived,
#approvedPosts .card .card-body .badge.archived,
#rejectedPosts .card .card-body .badge.archived,
#archivedPosts .card .card-body .badge.archived {
  width: 100%;
  background-color: #C0C0C0;
  color: #333;
}

#pendingPosts .card .card-body h6,
#approvedPosts .card .card-body h6,
#rejectedPosts .card .card-body h6,
#archivedPosts .card .card-body h6 {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: block;
  color: #3ca237;
  font-weight: 600;
}

#pendingPosts .card .card-body small,
#approvedPosts .card .card-body small,
#rejectedPosts .card .card-body small,
#archivedPosts .card .card-body small {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: block;
  color: #444;
  font-weight: 500;
}

#pendingPosts .card .card-body .action-buttons,
#approvedPosts .card .card-body .action-buttons,
#rejectedPosts .card .card-body .action-buttons,
#archivedPosts .card .card-body .action-buttons {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
}

#pendingPosts .card .card-body .action-buttons .btn,
#approvedPosts .card .card-body .action-buttons .btn,
#rejectedPosts .card .card-body .action-buttons .btn,
#archivedPosts .card .card-body .action-buttons .btn {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
  width: 100%;
  flex: 1;
  background-color: #bfffdd;
}

#pendingPosts .card .card-body .action-buttons .btn:hover,
#approvedPosts .card .card-body .action-buttons .btn:hover,
#rejectedPosts .card .card-body .action-buttons .btn:hover,
#archivedPosts .card .card-body .action-buttons .btn:hover {
  background-color: #3ca237;
  color: #f2f3f7;
}

/* Post Detail Modal */

#detailModal .modal-content {
  background-color: #ffffff;
  color: #333;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

#detailModal .modal-header .modal-title {
  color: #333;
}

#detailModal .modal-body {
  display: flex;
  flex-direction: column;
  padding: 12px;
}

#detailModal .modal-body .row {
  display: flex;
  gap: 8px;
}

#detailModal .modal-body .row .col-12:nth-child(1) img {
  width: 100%;
  border-radius: 12px;
}

#detailModal .modal-body .badge.pending {
  width: 100%;
  background-color: #FFC107;
  color: #333;
}

#detailModal .modal-body .badge.approved {
  width: 100%;
  background-color: #28A745;
}

#detailModal .modal-body .badge.rejected {
  width: 100%;
  background-color: #DC3545;
}

#detailModal .modal-body .row .col-12:nth-child(2) {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

#detailModal .modal-body .row .col-12:nth-child(2) p {
  display: flex;
  justify-content: space-between;
  gap: 30px;
  margin: 4px 0;
  font-size: 15px;
}

#detailModal .modal-body .row .col-12:nth-child(2) p span {
  font-weight: 500;
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

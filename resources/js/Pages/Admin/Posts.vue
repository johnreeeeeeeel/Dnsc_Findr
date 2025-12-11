<template>
  <AdminAppLayout title="Posts" @post-created="fetchApprovedPosts">

    <div class="container-fluid layout">

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

        <p>Try reloading</p>

        <button @click="fetchApprovedPosts" class="btn btn-link reset-filter">
          <Icon icon="mingcute:refresh-2-line" /> Reload
        </button>
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
                <p class="name">{{ formatFullName(post.postedBy) }}</p>
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
                <span
                  :class="['badge item-status-badge', post.status === 'Not Yet Claimed' ? 'not-claimed' : 'claimed']">
                  {{ post.status }}
                </span>
              </div>
              <h5 class="card-title">{{ post.item_name }}</h5>
            </div>

            <!-- Card Footer -->
            <div class="card-footer">
              <div class="action-buttons">
                <button type="button" class="btn" @click="openPostDetailModal(post)">
                  <Icon icon="mingcute:information-fill" class="icon" />
                  Details
                </button>
                <button type="button" class="btn" @click="openEditPostModal(post)">
                  <Icon icon="mingcute:edit-3-fill" class="icon" />
                  Edit
                </button>
                <button type="button" class="btn" @click.stop="openArchiveModal(post)">
                  <Icon icon="mingcute:archive-fill" class="icon" />
                  Archive
                </button>
                <button type="button" class="btn" @click.stop="openPermanentDeleteModal(post)">
                  <Icon icon="mingcute:delete-3-fill" class="icon" />
                  Delete
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

      <!-- PREVIEW IMAGE MODAL -->

      <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center">
              <img :src="previewImage" class="img-fluid rounded" alt="Enlarged Image">
            </div>
          </div>
        </div>
      </div>

      <!-- DETAIL MODAL -->

      <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
          <div class="modal-content" v-if="selectedPost">

            <div class="modal-header">
              <h5 class="title">{{ selectedPost.item_name }} (#{{ selectedPost.item_id }})</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">

                  <div class="posted-by">
                    <div class="avatar">
                      <Icon class="icon" icon="mingcute:user-4-fill" />
                    </div>

                    <div class="posted-by-info">
                      <p class="name">{{ formatFullName(selectedPost.postedBy) }}</p>
                      <div class="other-info">
                        <span class="badge user-type" :class="selectedPost.postedBy?.usertype?.toLowerCase() || 'user'">
                          {{ selectedPost.postedBy?.usertype || 'User' }}
                        </span>
                        <span class="date-time">
                          <Icon icon="mingcute:time-line" class="icon" />
                          {{ formatDate_1(selectedPost.created_at) }} at {{ formatTime_1(selectedPost.created_at) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="item-image">
                    <img :src="selectedPost.photo_url" class="img" @click="openImageModal(selectedPost.photo_url)">
                    <span class="badge item-status-badge"
                      :class="selectedPost.status === 'Not Yet Claimed' ? 'not-claimed' : 'claimed'">
                      {{ selectedPost.status }}
                    </span>
                  </div>

                  <div class="item-info">

                    <div>
                      <label>Category:</label>
                      <p>{{ selectedPost.item_category }}</p>
                    </div>

                    <div>
                      <label>Description:</label>
                      <p>{{ selectedPost.description }}</p>
                    </div>

                    <div>
                      <label>Location Found:</label>
                      <p>{{ selectedPost.location_found }}</p>
                    </div>

                    <div>
                      <label>Found on:</label>
                      <p>{{ formatDate_1(selectedPost.date_found) }} at {{ formatTime_1(selectedPost.time_found) }}</p>
                    </div>
                  </div>

                </div>

                <div class="col-md-6">

                  <!-- CLAIMS SECTION -->

                  <h5 class="title">
                    <Icon icon="mingcute:file-check-line" class="icon" />
                    Claims for this Item ({{ claims.length }})
                  </h5>

                  <div v-if="claims.length === 0" class="no-claims-yet">
                    <Icon class="icon" icon="mingcute:inbox-line" />
                    <p>No one has claimed this item yet.</p>
                  </div>

                  <div v-for="c in claims" :key="c.claim_reference_number" class="claimers">

                    <!-- Claimer Profile -->
                    <div class="claimer-profile">
                      <div class="claim-by">
                        <div class="avatar">
                          <Icon class="icon" icon="mingcute:user-4-fill" />
                        </div>

                        <div class="claim-by-info">
                          <p class="name">{{ formatFullName(c.user) }}</p>
                          <div class="other-info">
                            <span class="badge user-type"
                              :class="selectedPost.postedBy?.usertype?.toLowerCase() || 'user'">
                              {{ c.user.usertype }}
                            </span>
                            <code class="date-time">
                              ID: {{ c.user.user_id }}
                            </code>
                          </div>
                        </div>
                      </div>

                      <span class="badge claim-status" :class="{
                        'pending': c.claim_status === 'pending',
                        'accepted': c.claim_status === 'accepted',
                        'rejected': c.claim_status === 'rejected'
                      }">
                        {{ c.claim_status.charAt(0).toUpperCase() + c.claim_status.slice(1) }}
                      </span>
                    </div>

                    <!-- Other Claim Details -->
                    <div class="row other-details">
                      <div class="col-md-6">
                        <small>Claim Reference:</small>
                        <code>
                          {{ c.claim_reference_number }}
                        </code>
                      </div>
                      <div class="col-md-6">
                        <small>Claim Details Reference:</small>
                        <code>
                          {{ c.claim_details_reference_number }}
                        </code>
                      </div>
                      <div class="col-12">
                        <small>Submitted: </small>
                        <code>{{ formatDate_1(c.request_date) }} at {{ formatTime_1(c.request_date) }}</code>
                      </div>
                    </div>

                    <hr>

                    <div class="claim-evidence">
                      <h5 class="title">Claim Evidence Provided</h5>

                      <p>
                        <label>Description:</label>
                        {{ c.details?.description || '—' }}
                      </p>

                      <p>
                        <label>Location Lost:</label>
                        {{ c.details?.location_lost }}
                      </p>

                      <p>
                        <label>Lost On:</label>
                        {{ formatDate_1(c.details?.date_lost) }} at {{ formatTime_1(c.details?.time_lost) }}
                      </p>

                    </div>

                    <div class="action-buttons">
                      <template v-if="c.claim_status === 'pending'">
                        <button class="btn btn-sm primary-btn" @click="acceptClaim(c.claim_reference_number)"
                          :disabled="processingClaimId === c.claim_reference_number">
                          <span v-if="processingClaimId === c.claim_reference_number"
                            class="spinner-border spinner-border-sm me-2"></span>
                          <Icon v-else icon="mingcute:check-fill" class="me-1" />
                          {{ processingClaimId === c.claim_reference_number ? 'Accepting...' : 'Accept Claim' }}
                        </button>

                        <button class="btn btn-sm danger-btn" @click="rejectClaim(c.claim_reference_number)"
                          :disabled="processingClaimId === c.claim_reference_number">
                          <span v-if="processingClaimId === c.claim_reference_number"
                            class="spinner-border spinner-border-sm me-2 text-light"></span>
                          <Icon v-else icon="mingcute:close-fill" class="me-1" />
                          {{ processingClaimId === c.claim_reference_number ? 'Rejecting...' : 'Reject Claim' }}
                        </button>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- EDIT POST MODAL -->

      <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">

            <div class="modal-header">
              <h5>
                <Icon class="icon me-2" icon="mingcute:edit-3-line" />
                Edit Found Item
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" v-if="editForm">


              <form @submit.prevent="performEditPost">

                <div class="image-card">
                  <!-- Loading State -->
                  <div v-if="isUploadingImage" class="placeholder-loading">
                    <div class="spinner-border spinner-border-sm"></div>
                    <p>Uploading image... Please wait.</p>
                  </div>

                  <!-- Default State -->
                  <div v-if="!previewImage" class="placeholder-text">
                    Select an Image
                  </div>

                  <!-- Preview Image -->
                  <img v-if="previewImage" :src="previewImage" class="image-preview" />
                </div>

                <span v-if="inputErrors.photo" class="input-error">{{ inputErrors.photo[0] }}</span>

                <label class="btn select-btn primary-btn mt-2">
                  Select Image
                  <input type="file" accept="image/*" @change="onFileChange" hidden>
                </label>

                <!-- Item Name -->
                <div class="mt-3">
                  <label class="form-label">
                    Item Name <span class="text-danger">*</span>
                    <span v-if="inputErrors.item_name" class="input-error">{{ inputErrors.item_name[0] }}</span>
                  </label>
                  <input type="text" v-model="editForm.item_name" class="form-control" />
                </div>

                <!-- Description -->
                <div class="mt-3">
                  <label class="form-label">
                    Description <span class="text-danger">*</span>
                    <span v-if="inputErrors.description" class="input-error">{{ inputErrors.description[0] }}</span>
                  </label>
                  <textarea v-model="editForm.description" class="form-control" rows="5"
                    placeholder="Color, brand, size, special marks..."></textarea>
                </div>

                <!-- Category -->
                <label class="form-label">
                  Category <span class="text-danger">*</span>
                  <span v-if="inputErrors.item_category" class="input-error">{{ inputErrors.item_category[0]
                  }}</span>
                </label>

                <div class="dropdown">
                  <button class="btn dropdown-toggle w-100 text-start" data-bs-toggle="dropdown">
                    {{ editForm.item_category || 'Select Category' }}
                  </button>
                  <ul class="dropdown-menu w-100">
                    <li><a class="dropdown-item" href="#"
                        @click.prevent="editForm.item_category = 'ID / Student Card'">ID
                        / Student Card</a></li>
                    <li><a class="dropdown-item" href="#"
                        @click.prevent="editForm.item_category = 'Electronics'">Electronics</a></li>
                    <li><a class="dropdown-item" href="#"
                        @click.prevent="editForm.item_category = 'Documents'">Documents</a>
                    </li>
                    <li><a class="dropdown-item" href="#"
                        @click.prevent="editForm.item_category = 'Accessories'">Accessories</a></li>
                    <li><a class="dropdown-item" href="#"
                        @click.prevent="editForm.item_category = 'Clothing'">Clothing</a>
                    </li>
                    <li><a class="dropdown-item" href="#"
                        @click.prevent="editForm.item_category = 'Bags / Wallets'">Bags
                        / Wallets</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="editForm.item_category = 'Others'">Others</a>
                    </li>
                  </ul>
                </div>

                <!-- Location Found -->
                <div class="mt-3">
                  <label class="form-label">
                    Location Found <span class="text-danger">*</span>
                    <span v-if="inputErrors.location_found" class="input-error">{{ inputErrors.location_found[0]
                    }}</span>
                  </label>
                  <input type="text" v-model="editForm.location_found" class="form-control" />
                </div>

                <!-- Date Found -->
                <div class="mt-3">
                  <label class="form-label">
                    Date Found <span class="text-danger">*</span>
                    <span v-if="inputErrors.date_found" class="input-error">{{ inputErrors.date_found[0] }}</span>
                  </label>
                  <input type="date" v-model="editForm.date_found" class="form-control" />
                </div>

                <!-- Time Found -->
                <div class="mt-3">
                  <label class="form-label">
                    Time Found <span class="text-danger">*</span>
                    <span v-if="inputErrors.time_found" class="input-error">{{ inputErrors.time_found[0] }}</span>
                  </label>
                  <input type="time" v-model="editForm.time_found" class="form-control" />
                </div>

                <!-- Status -->
                <div class="mt-3">
                  <label class="form-label">Status</label>
                  <div class="dropdown">
                    <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown">
                      {{ editForm.status || 'Select Status' }}
                    </button>
                    <ul class="dropdown-menu w-100">
                      <li><a class="dropdown-item" href="#" @click.prevent="editForm.status = 'Not Yet Claimed'">Not
                          Yet
                          Claimed</a></li>
                      <li><a class="dropdown-item" href="#" @click.prevent="editForm.status = 'Claimed'">Claimed</a>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mt-4">
                  <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-sm primary-btn" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                    {{ saving ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>

              </form>
            </div>

          </div>
        </div>
      </div>

      <!-- EDIT SUCCESS TOAST -->

      <div class="success-toast">
        <div id="editPostSuccess" class="toast" role="alert">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:edit-3-fill" />
              Post edited successfully!
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

      <!-- PERMANENT DELETE CONFIRMATION MODAL -->

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
                  Delete Permamently
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
import AdminAppLayout from '../../Layouts/AdminAppLayout.vue';
import { Icon } from '@iconify/vue';

export default {
  components: { AdminAppLayout, Icon },

  data() {
    return {
      posts: [],
      loading: true,
      selectedPost: null,
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

      editForm: null,
      newPhoto: null,
      inputErrors: {},

      saving: false,
      deleting: false,
      processing: false,

      claims: [],
      processingClaimId: null,
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
        // Search in item name, description, location
        const inItemName = post.item_name.toLowerCase().includes(q);
        const inDescription = post.description?.toLowerCase().includes(q) ?? false;
        const inLocation = post.location_found.toLowerCase().includes(q);

        // Search in posted person's name
        const posterName = this.formatFullName(post.postedBy)?.toLowerCase() || '';
        const inPosterName = posterName.includes(q);

        // Return true if matches anywhere
        return inItemName || inDescription || inLocation || inPosterName;
      });
    },

    filteredPosts() {
      let result = this.searchedPosts;

      if (this.filterUserType) {
        result = result.filter(post => {
          const isAdmin = post.postedBy?.usertype === 'Admin';
          return (isAdmin ? 'Admin' : 'User') === this.filterUserType;
        });
      }

      if (this.filterStatus) {
        result = result.filter(post => post.status === this.filterStatus);
      }

      if (this.filterCategory) {
        result = result.filter(post => post.item_category === this.filterCategory);
      }

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

    pageNumbers() {
      if (this.totalPages <= 3) {
        return Array.from({ length: this.totalPages }, (_, i) => i + 1);
      }
      return [1, 2, 3, this.totalPages];
    }
  },

  mounted() {
    this.fetchApprovedPosts();
  },

  methods: {

    // FETCH ALL APPROVE ITEMS
    async fetchApprovedPosts() {
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

    // FETCH CLAIMS 
    async fetchClaims(itemId) {
      try {
        const res = await axios.get(`/claims/${itemId}`);
        this.claims = res.data;
      } catch (err) {
        console.error('Failed to load claims:', err);
        this.claims = [];
      }
    },

    // IMAGE PREVIEW
    openImageModal(url) {
      this.previewImage = url;
      new bootstrap.Modal('#imageModal').show();
    },

    // OPEN DETAILS MODAL
    openPostDetailModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal('#detailModal').show();
      this.fetchClaims(post.item_id);
    },

    // EDIT POST
    openEditPostModal(post) {
      this.selectedPost = post;
      this.editForm = { ...post };
      this.newPhoto = null;
      this.previewImage = post.photo_url || null;
      new bootstrap.Modal('#editPostModal').show();
    },

    onFileChange(e) {
      const file = e.target.files[0] || null;
      this.newPhoto = file;

      if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
          this.previewImage = event.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.previewImage = this.editForm.photo_url || null;
      }
    },

    async performEditPost() {
      if (!this.editForm) return;

      this.saving = true;

      const formData = new FormData();
      formData.append('item_name', this.editForm.item_name);
      formData.append('item_category', this.editForm.item_category);
      formData.append('description', this.editForm.description || '');
      formData.append('location_found', this.editForm.location_found);
      formData.append('date_found', this.editForm.date_found);
      formData.append('time_found', this.editForm.time_found);
      formData.append('status', this.editForm.status);
      if (this.newPhoto) formData.append('photo', this.newPhoto);

      try {
        await axios.post(`/found-items/${this.editForm.item_id}/edit`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        await this.fetchApprovedPosts();
        bootstrap.Modal.getInstance('#editPostModal').hide();
        this.showSuccessToast('editPostSuccess');

      } catch (error) {
        if (error.response?.status === 422) {
          this.inputErrors = error.response.data.errors;
        }
      } finally {
        this.saving = false;
      }
    },

    // ARCHIVE POSTS
    openArchiveModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal('#archiveModal').show();
    },

    async performArchive() {
      this.processing = true;
      try {
        await axios.delete(`/found-items/${this.selectedPost.item_id}/archive`);
        bootstrap.Modal.getInstance('#archiveModal').hide();
        this.showSuccessToast('archivePostSuccess');
        await this.fetchApprovedPosts();
        this.selectedPost = null;
      } catch {
        alert("Failed to archive post");
      } finally {
        this.processing = false;
      }
    },

    // DELETE POSTS
    openPermanentDeleteModal(post) {
      this.selectedPost = post;
      new bootstrap.Modal('#permanentDeleteModal').show();
    },

    async performPermanentDelete() {
      this.processing = true;
      try {
        await axios.delete(`/found-items/${this.selectedPost.item_id}/delete`);
        bootstrap.Modal.getInstance('#permanentDeleteModal').hide();
        this.showSuccessToast('permanentDeleteSuccess');
        await this.fetchApprovedPosts();
        this.selectedPost = null;
      } catch {
        alert("Failed to delete post");
      } finally {
        this.processing = false;
      }
    },

    // SHOW SUCCESS TOAST
    showSuccessToast(id) {
      const toast = document.getElementById(id);
      if (toast) new bootstrap.Toast(toast, { delay: 3000 }).show();
    },

    // FORMAT FULLNAME
    formatFullName(user) {
      if (!user) return 'Unknown User';

      if (user.usertype === 'Admin') return user.username || 'Admin User';

      const names = [user.firstname, user.middlename, user.lastname]
        .filter(name => name && name.trim() !== '');

      return names.length > 0 ? names.join(' ') : (user.username || 'User');
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

    // ACCEPT CLAIM
    async acceptClaim(claimId) {
      this.processingClaimId = claimId;
      try {
        await axios.post(`/claims/${claimId}/accept`);
        this.fetchClaims(this.selectedPost.item_id);
      } catch (err) {
        console.error(err);
      } finally {
        this.processingClaimId = null;
      }
    },

    // REJECT CLAIM
    async rejectClaim(claimId) {
      this.processingClaimId = claimId;
      try {
        await axios.post(`/claims/${claimId}/reject`);
        this.fetchClaims(this.selectedPost.item_id);
      } catch (err) {
        console.error(err);
      } finally {
        this.processingClaimId = null;
      }
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
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  flex-wrap: wrap;
  gap: 8px;
}

.posts-container .post-card .card-footer .action-buttons .btn {
  padding: 6px 12px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
  border: 2px solid #48c441;
  background-color: transparent;
  color: #48c441;
  transition: all 0.2s ease;
  width: 100%;
  flex: 1 0 120px;
}

.posts-container .post-card .card-footer .action-buttons .btn:hover {
  border: 2px solid #48c441;
  background-color: #48c441;
  color: #f2f3f7;
}

/* DETAILS MODAL */

#detailModal .modal-header {
  border: none;
  background-color: #ffffff;
}

#detailModal .modal-header .title {
  color: #3ca237;
  font-weight: 600;
}

#detailModal .modal-body {
  border: none;
  background-color: #ffffff;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-image {
  position: relative;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-image img {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 12px;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-info {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 4px;
  margin-bottom: 24px;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-info .item-category {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 14px;
  color: #555;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-info .item-category .icon {
  font-size: 18px;
  color: #555;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-info>div {
  background: #f7f7f9;
  padding: 10px 12px;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-info>div label {
  font-weight: 600;
  color: #666;
  margin: 0;
}

#detailModal .modal-body .row .col-md-6:nth-child(1) .item-info>div p {
  margin: 0;
  color: #333;
  line-height: 1.4;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .no-claims-yet {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .no-claims-yet .icon {
  font-size: 84px;
  color: #666;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .claimers {
  border: 2px solid #48c441;
  border-radius: 12px;
  padding: 8px;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .claimers .claimer-profile {
  display: flex;
  justify-content: space-between;
  align-items: start;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details {
  padding: 8px;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details .col-md-6,
#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details .col-12 {
  margin: 8px 0 8px 0;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details .col-md-6 small,
#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details .col-12 {
  margin-bottom: -8px;
  font-weight: 500;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details .col-md-6 code,
#detailModal .modal-body .row .col-md-6:nth-child(2) .other-details .col-12 code {
  background-color: #f2f3f7;
  padding: 0 4px 0 4px;
  border-radius: 4px;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .claimers .claim-evidence {
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .claimers .claim-evidence .title {
  font-weight: 600;
  margin-bottom: 8px !important;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .claimers .claim-evidence label {
  font-weight: 500;
}

#detailModal .modal-body .row .col-md-6:nth-child(2) .claimers .action-buttons {
  padding: 8px;
  display: flex;
  justify-content: end;
  gap: 8px;
}


/* ALL ACTION BUTTONS */

.btn {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
}

.primary-btn {
  background-color: #48c441;
  border: none;
  color: white;
  transition: all 0.2s ease;
}

.primary-btn:hover,
.primary-btn:active {
  background-color: #3ca237;
  transform: scale(1.005);
}

.danger-btn {
  border: 2px solid #d60000;
  background-color: transparent;
  color: #d60000;
  transition: all 0.2s ease;
}

.danger-btn:hover,
.danger-btn:active {
  background-color: #a30000;
  border: 2px solid #a30000;
  color: #f2f2f2;
  transform: scale(1.005);
}

/* CLAIM STATUS BADGE */

.claim-status {
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  color: #333;
  padding: 6px 10px;
}

.claim-status.pending {
  background-color: #0dff00;
}

.claim-status.accepted {
  background-color: #198754;
}

.claim-status.rejected {
  background-color: #fd7e14;
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

/* EDIT POST MODAL */

#editPostModal .modal-content {
  border: none !important;
  height: 100%;
  display: flex;
  flex-direction: column;
}

#editPostModal .modal-header {
  border: none !important;
}

#editPostModal .modal-header h5 {
  color: #48c441;
  font-weight: 600;
}

#editPostModal .modal-body {
  border: none !important;
  flex: 1;
  overflow: hidden;
}

#editPostModal .modal-body form {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-bottom: 18px;
  flex: 1;
  overflow-y: auto;
  max-height: 100%;
  box-sizing: border-box;
}

/* Image card */
#editPostModal .modal-body form .image-card {
  width: 100%;
  min-height: 470px;
  background: #f3f3f3;
  border: 2px dashed #48c441;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  transition: 0.2s ease;
}

#editPostModal .modal-body form .image-card .placeholder-text {
  font-size: 15px;
  color: #888;
  padding: 10px;
  text-align: center;
}

#editPostModal .modal-body form .image-card .image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px;
}

/* Buttons and inputs */
#editPostModal .modal-body form .select-btn {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  background-color: #48c441;
  border: none;
  color: white;
  transition: all 0.2s ease;
}

#editPostModal .modal-body form .select-btn:hover {
  background-color: #3ca237;
  transform: scale(1.005);
}

#editPostModal .modal-body form label {
  font-weight: 500;
  color: #333;
}

#editPostModal .modal-body form label .input-error {
  font-weight: 400;
  color: #d60000;
  margin-left: 8px;
  font-size: 14px;
}

#editPostModal .modal-body form label .input-error::before {
  content: "⚠️ ";
}

#editPostModal .modal-body form .input-error {
  font-weight: 400;
  color: #d60000;
  font-size: 14px;
}

#editPostModal .modal-body form .input-error::before {
  content: "⚠️ ";
}

#editPostModal .modal-body form input,
#editPostModal .modal-body form textarea {
  width: 100%;
  padding: 10px 12px 10px 12px;
  border-radius: 12px;
  border: 1px solid #48c441;
  box-sizing: border-box;
}

#editPostModal .modal-body form input:focus,
#editPostModal .modal-body form textarea:focus {
  outline: none;
  border: #48c441 solid 1px;
  box-shadow: 0 0 4px 1px #48c441;
}

/* Dropdown wrapper */
#editPostModal .dropdown {
  width: 100%;
}

/* Dropdown button */
#editPostModal .dropdown .dropdown-toggle {
  width: 100%;
  padding: 10px 40px 10px 12px;
  border-radius: 12px;
  border: 1px solid #48c441;
  background-color: #fff;
  color: #000;
  text-align: left;
  position: relative;
}

/* Arrow positioning */
#editPostModal .dropdown .dropdown-toggle::after {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
}

/* Hover / focus */
#editPostModal .dropdown .dropdown-toggle:hover,
#editPostModal .dropdown .dropdown-toggle:focus {
  outline: none;
  border-color: #48c441;
  box-shadow: 0 0 4px 1px #48c441;
}

/* Dropdown menu */
#editPostModal .dropdown .dropdown-menu {
  width: 100%;
  border: 1px solid #48c441;
  border-radius: 12px;
  padding: 0;
  margin-top: 5px;
  overflow: hidden;
}

/* Items */
#editPostModal .dropdown .dropdown-item {
  padding: 10px 14px;
}

#editPostModal .dropdown .dropdown-item:hover {
  background-color: #48c441;
  color: #fff;
}


#editPostModal .modal-body form .action-buttons {
  display: flex;
  justify-content: end;
  gap: 8px;
}

#editPostModal .modal-body form .action-buttons .btn {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
}

#editPostModal .modal-body form .action-buttons .primary-btn {
  background-color: #48c441;
  border: none;
  color: white;
}

#editPostModal .modal-body form .action-buttons .primary-btn:hover,
#editPostModal .modal-body form .action-buttons .primary-btn:active {
  background-color: #3ca237;
  transform: scale(1.005);
}

#editPostModal .modal-body form .action-buttons .secondary-btn {
  border: 2px solid #48c441;
  background-color: transparent;
  color: #48c441;
}

#editPostModal .modal-body form .action-buttons .secondary-btn:hover,
#editPostModal .modal-body form .action-buttons .secondary-btn:active {
  background-color: #3ca237;
  border: 2px solid #3ca237;
  color: #f2f2f2;
  transform: scale(1.005);
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
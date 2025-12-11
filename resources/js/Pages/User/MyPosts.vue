<template>
  <UserAppLayout title="My Posts" @post-created="fetchAllUserPosts">
    <div class="container-fluid layout">

      <!-- Header -->
      <div class="header">
        <h4 class="title">
          <Icon class="icon" icon="mingcute:album-2-fill" />
          My Posts
        </h4>

        <div class="tools">
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
                My Archived <span class="badge count-badge">{{ archived.length }}</span>
              </button>
            </li>
          </ul>

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

            <button @click="fetchAllUserPosts" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <!-- Posts -->
          <div v-else>
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">
                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item" />

                  <div class="card-body">
                    <span class="badge pending">
                      Pending
                    </span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="action-buttons" @click.stop>
                      <button @click.stop="openEditModal(post)" class="btn btn-sm">Edit</button>
                      <button @click.stop="openArchiveModal(post)" class="btn btn-sm">Archive</button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">Delete</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="currentPosts.length > 0" class="pagination-wrapper mt-4">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>
                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} of {{ totalPages }}</a>
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

            <button @click="fetchAllUserPosts" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else>
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">
                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item" />

                  <div class="card-body">
                    <span class="badge approved">Approved</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>
                    <small>Approved on {{ formatDate(post.updated_at || post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="action-buttons" @click.stop>
                      <button disabled class="btn btn-sm">Edit</button>
                      <button disabled class="btn btn-sm">Archive</button>
                      <button disabled class="btn btn-sm">Delete</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="currentPosts.length > 0" class="pagination-wrapper mt-4">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>
                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} of {{ totalPages }}</a>
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

            <button @click="fetchAllUserPosts" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else>
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">
                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item" />

                  <div class="card-body">
                    <span class="badge rejected">Rejected</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>
                    <small>Rejected on {{ formatDate(post.updated_at || post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="action-buttons" @click.stop>
                      <button disabled class="btn btn-sm">Edit</button>
                      <button disabled class="btn btn-sm">Archive</button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">Delete</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="currentPosts.length > 0" class="pagination-wrapper mt-4">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>
                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} of {{ totalPages }}</a>
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

            <button @click="fetchAllUserPosts" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Reload
            </button>

            <button @click="clearSearch" class="btn btn-link reset-filter">
              <Icon icon="mingcute:refresh-2-line" /> Clear Search
            </button>
          </div>

          <div v-else>
            <div class="row g-3">
              <div v-for="post in paginatedPosts" :key="post.item_id" class="col-md-6 col-lg-4">
                <div class="card" @click="openDetailModal(post)">

                  <img :src="post.photo_url" class="card-img-top" alt="Item" />

                  <div class="card-body">
                    <span class="badge archived">Archived</span>
                    <h6 class="card-title">
                      <code>#{{ post.item_id }}</code> - {{ post.item_name }}
                    </h6>
                    <small>Posted on {{ formatDate(post.created_at) }}</small>
                    <small>Archived on {{ formatDate(post.updated_at || post.created_at) }}</small>

                    <!-- Action Buttons -->
                    <div class="mt-auto pt-3 action-buttons" @click.stop>
                      <button @click.stop="openUnarchiveModal(post)" class="btn btn-sm">
                        Unarchive
                      </button>
                      <button @click.stop="openPermanentDeleteModal(post)" class="btn btn-sm">
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="currentPosts.length > 0" class="pagination-wrapper mt-4">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" @click="currentPage > 1 && currentPage--" href="javascript:void(0)">Previous</a>
                </li>
                <li class="page-item active">
                  <a class="page-link" href="javascript:void(0)">{{ currentPage }} of {{ totalPages }}</a>
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

                <!-- Action Buttons -->
                <div class="action-buttons mt-4">
                  <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn primary-btn" :disabled="saving">
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
  </UserAppLayout>
</template>

<script>
import UserAppLayout from "../../Layouts/UserAppLayout.vue";
import { Icon } from '@iconify/vue';

export default {
  components: { UserAppLayout, Icon },

  data() {
    return {
      allPosts: [],
      loading: true,

      selectedPost: null,
      editForm: null,
      previewImage: null,
      selectedFile: null,

      saving: false,
      processing: false,
      inputErrors: {},

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
      set(val) { this.page[this.activeTab] = Math.max(1, val); }
    },

    // Search + filter
    filteredPosts() {
      if (!this.searchQuery.trim()) return this.allPosts;
      const q = this.searchQuery.toLowerCase();
      return this.allPosts.filter(p =>
        p.item_name?.toLowerCase().includes(q) ||
        p.item_category?.toLowerCase().includes(q) ||
        p.location_found?.toLowerCase().includes(q) ||
        String(p.item_id).includes(q)
      );
    },

    // Posts per status
    pending() { return this.filteredPosts.filter(p => p.post_status === 'pending'); },
    approved() { return this.filteredPosts.filter(p => p.post_status === 'approved'); },
    rejected() { return this.filteredPosts.filter(p => p.post_status === 'rejected'); },
    archived() { return this.filteredPosts.filter(p => p.post_status === 'archived'); },

    // Current tab posts
    currentPosts() {
      const map = {
        pending: this.pending,
        approved: this.approved,
        rejected: this.rejected,
        archived: this.archived
      };
      return map[this.activeTab] || [];
    },

    // Pagination
    totalPages() {
      return Math.ceil(this.currentPosts.length / this.itemsPerPage) || 1;
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
    this.fetchAllUserPosts();

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
    // Fetch All User Posts
    async fetchAllUserPosts() {
      this.loading = true;
      try {
        const { data } = await axios.get('/found-items/user-post');
        this.allPosts = data;
      } catch {
        alert('Failed to load posts');
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

    // Open Edit Modal
    openEditModal(post) {

      if (post.post_status !== 'pending') {
        return alert('You can only edit posts that are still pending.');
      }

      this.editForm = { ...post };
      this.previewImage = post.photo_url;
      this.selectedFile = null;
      this.inputErrors = {};

      new bootstrap.Modal('#editPostModal').show();
    },

    // File preview for edit
    onFileChange(e) {

      const file = e.target.files[0];
      if (!file) return;

      this.selectedFile = file;

      const reader = new FileReader();
      reader.onload = ev => this.previewImage = ev.target.result;

      reader.readAsDataURL(file);
    },

    // Edit post
    async performEditPost() {

      this.saving = true;
      this.inputErrors = {};

      try {

        const formData = new FormData();
        const fields = [
          'item_name', 'item_category', 'description',
          'location_found', 'date_found', 'time_found'
        ];

        fields.forEach(key => formData.append(key, this.editForm[key] || ''));

        if (this.selectedFile) {
          formData.append('photo', this.selectedFile);
        }

        await axios.post(
          `/found-items/${this.editForm.item_id}/edit`,
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        await this.fetchAllUserPosts();
        this.resetAllPages();

        bootstrap.Modal.getInstance('#editPostModal')?.hide();
        this.showSuccessToast('editPostSuccess');

      } catch (err) {

        if (err.response?.status === 422) {
          this.inputErrors = err.response.data.errors || {};
        } else {
          alert(err.response?.data?.message || 'Failed to update post');
        }

      } finally {
        this.saving = false;
        this.editForm = this.previewImage = this.selectedFile = null;
      }
    },

    // Open Archive Modal
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

        await this.fetchAllUserPosts();
        this.resetAllPages();

      } catch {
        alert("Failed to archive");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    // Open Unarchive Modal
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

        await this.fetchAllUserPosts();
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

    // Perform Delete
    async performPermanentDelete() {

      this.processing = true;

      try {
        await axios.delete(`/found-items/${this.selectedPost.item_id}/delete`);

        bootstrap.Modal.getInstance('#permanentDeleteModal')?.hide();
        this.showSuccessToast('permanentDeleteSuccess');

        await this.fetchAllUserPosts();
        this.resetAllPages();

      } catch {
        alert("Failed to delete");

      } finally {
        this.processing = false;
        this.selectedPost = null;
      }
    },

    //HELPERS

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

#pendingPosts .card .card-body .action-buttons .btn:disabled,
#approvedPosts .card .card-body .action-buttons .btn:disabled,
#rejectedPosts .card .card-body .action-buttons .btn:disabled,
#archivedPosts .card .card-body .action-buttons .btn:disabled {
  border: none;
  outline: none;
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
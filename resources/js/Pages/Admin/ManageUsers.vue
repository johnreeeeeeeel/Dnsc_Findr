<script>
import axios from 'axios';
import AdminAppLayout from '../../Layouts/AdminAppLayout.vue';

export default {
  name: "AdminManageUsers",
  components: {
    AdminAppLayout
  },

  data() {
    return {
      loading: true,

      users: [],
      allUsers: [],

      // Pagination
      currentPage: 1,
      perPage: 10,

      // Search & Filters
      searchQuery: "",
      filterUserType: "",
      filterSex: "",
      filterInstitute: "",
      filterProgram: "",
      filterEmailDomain: "",

      viewUser: {},

      // ADD USER EMAIL
      emailUsername: "",
      emailDomain: "dnsc.edu.ph",

      // EDIT USER EMAIL
      editEmailUsername: "",
      editEmailDomain: "dnsc.edu.ph",

      institutes: [],
      programs: {},

      /* ADD USER FORM */
      form: {
        lastname: "",
        firstname: "",
        middlename: "",
        sex: "",
        dob: "",
        usertype: "",
        institute: "",
        program: "",
        username: "",
        email: ""
      },

      /* EDIT USER FORM */
      editForm: {
        user_id: "",
        lastname: "",
        firstname: "",
        middlename: "",
        sex: "",
        dob: "",
        usertype: "",
        institute: "",
        program: "",
        username: "",
        email: ""
      },

      // Changed from array to object for Laravel-style validation
      errors: {},

      // For success modal
      userId: "",
      lastname: "",
      firstname: "",
      middlename: "",
      temppassword: "",

      // Processing state (for submit button spinner)
      processing: false,

      /* RESET PASSWORD */
      resetTarget: null,
      tempPassword: "",
      confirmResetModal: null,
      tempPasswordModal: null,

      /* DELETE USER */
      deleteTarget: null,
      deleteModal: null,
    };
  },

  computed: {
    // Filtered & Searched Users
    filteredUsers() {
      let filtered = this.allUsers;

      // Search by name or username
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(user =>
          `${user.firstname} ${user.middlename} ${user.lastname}`.toLowerCase().includes(query) ||
          user.username.toLowerCase().includes(query)
        );
      }

      // Filter by User Type
      if (this.filterUserType) {
        filtered = filtered.filter(u => u.usertype === this.filterUserType);
      }

      // Filter by Sex
      if (this.filterSex) {
        filtered = filtered.filter(u => u.sex === this.filterSex);
      }

      // Filter by Institute
      if (this.filterInstitute) {
        filtered = filtered.filter(u => u.institute === this.filterInstitute);
      }

      // Filter by Program
      if (this.filterProgram) {
        filtered = filtered.filter(u => u.program === this.filterProgram);
      }

      // Filter by Email Domain
      if (this.filterEmailDomain) {
        filtered = filtered.filter(u => u.email.endsWith(`@${this.filterEmailDomain}`));
      }

      return filtered;
    },

    // Paginated Users
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      return this.filteredUsers.slice(start, end);
    },

    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.perPage);
    },

    hasResults() {
      return this.filteredUsers.length > 0;
    }
  },

  watch: {
    // Reset page when filters change
    searchQuery() { this.currentPage = 1; },
    filterUserType() { this.currentPage = 1; },
    filterSex() { this.currentPage = 1; },
    filterInstitute() { this.currentPage = 1; this.filterProgram = ""; }, // Reset program when institute changes
    filterProgram() { this.currentPage = 1; },
    filterEmailDomain() { this.currentPage = 1; },
  },

  methods: {

    async loadInstitutes() {
      const res = await axios.get('/api/institutes');
      this.institutes = res.data.map(i => i.institute_code);

      // Preload programs
      for (let inst of res.data) {
        const pres = await axios.get(`/api/programs/${inst.institute_code}`);
        this.programs[inst.institute_code] = pres.data.map(p => p.program_code);
      }
    },

    // LOAD USERS
    async loadUsers() {
      try {
        const response = await axios.get("/users");
        this.allUsers = response.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        this.users = [...this.allUsers]; // for backward compatibility if needed
      } catch (error) {
        console.error("Failed to load users", error);
      }
    },

    // LOAD ALL DATA
    async loadAllData() {
      this.loading = true;
      try {
        await Promise.all([
          this.loadUsers(),
          this.loadInstitutes()
        ]);
      } finally {
        this.loading = false;
      }
    },

    // RESET FILTERS
    resetFilters() {
      this.searchQuery = "";
      this.filterUserType = "";
      this.filterSex = "";
      this.filterInstitute = "";
      this.filterProgram = "";
      this.filterEmailDomain = "";
      this.currentPage = 1;
    },

    // PAGINATION
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    // OPEN VIEW USER PROFILE MODAL
    openViewUserProfileModal(user) {
      this.viewUser = user;
      bootstrap.Modal.getOrCreateInstance('#viewUserProfileModal').show();
    },

    // ADD USER & EDIT USER

    // Check if field is editable based on user type (ADD)
    isFieldEditable(field) {
      switch (this.form.usertype) {
        case 'Admin':
          return ['username', 'email'].includes(field);
        case 'Instructor':
          return !['institute', 'program'].includes(field);
        case 'Staff':
          return !['institute', 'program'].includes(field);
        case 'Student':
          return true;
        default:
          return false;
      }
    },

    // Check if field is editable based on user type (EDIT)
    isEditFieldEditable(field) {
      switch (this.editForm.usertype) {
        case 'Admin':
          return ['username', 'email'].includes(field);
        case 'Instructor':
          return !['institute', 'program'].includes(field);
        case 'Staff':
          return !['institute', 'program'].includes(field);
        case 'Student':
          return true;
        default:
          return false;
      }
    },

    // Auto generate username if not Admin (ADD)
    shouldAutogenerateUsername() {
      if (!this.form.usertype) return false;
      return ['Instructor', 'Staff', 'Student'].includes(this.form.usertype);
    },

    // Auto generate username if not Admin (EDIT)
    shouldAutogenerateEditUsername() {
      return ['Instructor', 'Staff', 'Student'].includes(this.editForm.usertype);
    },

    // Auto generateg username (ADD)
    autoGenerateUsername() {
      if (!this.shouldAutogenerateUsername()) return;

      if (!this.form.firstname || !this.form.lastname) {
        this.form.username = "";
        return;
      }

      let base = `${this.form.firstname}${this.form.lastname}`.replace(/\s+/g, '');
      let username = base;
      let counter = 1;
      const existing = this.users.map(u => u.username);

      while (existing.includes(username)) {
        username = `${base}${counter}`;
        counter++;
      }

      this.form.username = username;
    },

    // Auto generateg username (EDIT)
    autoGenerateEditUsername() {
      if (!this.shouldAutogenerateEditUsername()) return;

      if (!this.editForm.firstname || !this.editForm.lastname) {
        this.editForm.username = "";
        return;
      }

      let base = `${this.editForm.firstname}${this.editForm.lastname}`.replace(/\s+/g, '');
      let username = base;
      let counter = 1;
      const existing = this.users.map(u => u.username).filter(u => u !== this.editForm.username);

      while (existing.includes(username)) {
        username = `${base}${counter}`;
        counter++;
      }

      this.editForm.username = username;
    },

    // Trigger auto generate username
    onNameChange() {
      this.autoGenerateUsername();
    },

    // Check if student
    isStudent(usertype) {
      return usertype === "Student";
    },

    // Get program based on instiitute
    availablePrograms(institute) {
      return this.programs[institute] || [];
    },

    // OPEN ADD USER MODAL
    openAddUserModal() {
      this.resetForm();
    },

    // SUBMIT ADD USER FORM
    async submitForm() {
      this.errors = {};
      this.processing = true;

      // Build full email
      this.form.email = this.emailUsername && this.emailDomain
        ? `${this.emailUsername}@${this.emailDomain}`
        : '';

      try {
        const response = await axios.post("/addusercheck", this.form);

        // Success
        this.lastname = response.data.lastname;
        this.firstname = response.data.firstname;
        this.middlename = response.data.middlename;
        this.userId = response.data.user_id;
        this.temppassword = response.data.temp_password;

        bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
        new bootstrap.Modal(document.getElementById('userSuccessModal')).show();

        this.loadUsers();
        this.resetForm();

      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        }
      } finally {
        this.processing = false;
      }
    },

    // OPEN EDIT USER MODAL
    openEditModal(user) {
      this.editForm = { ...user };

      // Split email into username + domain
      if (user.email) {
        const [username, domain = 'dnsc.edu.ph'] = user.email.split('@');
        this.editEmailUsername = username || '';
        this.editEmailDomain = domain;
      } else {
        this.editEmailUsername = "";
        this.editEmailDomain = "dnsc.edu.ph";
      }

      this.errors = {}; // Clear previous errors
      this.processing = false;

      const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
      this.editModal = modal;
      modal.show();
    },

    // SUBMIT EDIT USER FORM (now consistent with Add User)
    async submitEditForm() {
      this.errors = {};
      this.processing = true;

      // Combine email
      this.editForm.email = this.editEmailUsername && this.editEmailDomain
        ? `${this.editEmailUsername}@${this.editEmailDomain}`
        : '';

      try {
        await axios.put(`/users/${this.editForm.user_id}`, this.editForm);

        this.editModal.hide();
        this.loadUsers();

        // Optional: show success toast here if you have one
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors; // Laravel-style object
        } else {
          console.error("Update failed", error);
        }
      } finally {
        this.processing = false;
      }
    },

    // OPEN RESET PASSWORD CONFIRMATION
    openResetModal(user) {
      this.resetTarget = user;
      const modal = new bootstrap.Modal(document.getElementById('confirmResetModal'));
      this.confirmResetModal = modal;
      modal.show();
    },

    // RESET PASSWORD
    async resetPassword() {
      try {
        const res = await axios.post(`/users/${this.resetTarget.user_id}/reset-password`);
        this.tempPassword = res.data.temp_password;

        this.confirmResetModal.hide();

        const modal = new bootstrap.Modal(document.getElementById('tempPasswordModal'));
        this.tempPasswordModal = modal;
        modal.show();

        this.loadUsers();
      } catch (e) {
        console.error("Failed to reset password", e);
      }
    },

    // DELETE CONFIRMATION
    openDeleteModal(user) {
      this.deleteTarget = user;
      const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
      this.deleteModal = modal;
      modal.show();
    },

    // DELETE USER
    async deleteUser() {
      try {
        await axios.delete(`/users/${this.deleteTarget.user_id}`);

        this.deleteModal.hide();
        this.deleteTarget = null;

        this.loadUsers();

        // SHOW SUCCESS TOAST
        this.showDeleteToast();

      } catch (error) {
        console.error("Failed to delete user", error);
      }
    },

    // DELETE TOAST
    showDeleteToast() {
      const toastEl = document.getElementById("successDeleteToast");
      const toast = new bootstrap.Toast(toastEl);

      // Hide if already showing
      toastEl.classList.remove('show');
      toast.show();
    },

    // RESET FORM
    resetForm() {
      this.form = {
        lastname: "",
        firstname: "",
        middlename: "",
        sex: "",
        dob: "",
        usertype: "",
        institute: "",
        program: "",
        email: "",
        username: "",
        temppassword: "",
      };
      this.emailUsername = "";
      this.emailDomain = "dnsc.edu.ph";
      this.errors = [];
    },

    // HELPERS

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    },
  },

  mounted() {
    this.loadAllData();

    const modalEl = document.getElementById('addUserModal');
    modalEl.addEventListener('shown.bs.modal', () => {
      this.form = { lastname: "", firstname: "", middlename: "", sex: "", dob: "", usertype: "", institute: "", program: "", username: "", email: "" };
      this.emailUsername = "";
      this.emailDomain = "dnsc.edu.ph";
      this.errors = [];
    });
  },
};
</script>

<template>
  <AdminAppLayout title="Manage Users">

    <div class="container-fluid layout">

      <div class="header">

        <h4 class="title">
          <Icon class="icon" icon="mingcute:user-3-fill" />
          Users List
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
              <span
                v-if="Object.values({ filterUserType, filterSex, filterInstitute, filterProgram, filterEmailDomain }).some(v => v)"
                class="filter-badge badge">
                {{ [filterUserType, filterSex, filterInstitute, filterProgram, filterEmailDomain].filter(Boolean).length
                }}
              </span>
            </button>
            <div class="dropdown-menu">
              <div class="row">
                <div class="col-6">
                  <label class="form-label small text-muted">User Type</label>
                  <select v-model="filterUserType" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option>Admin</option>
                    <option>Instructor</option>
                    <option>Staff</option>
                    <option>Student</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label small text-muted">Sex</label>
                  <select v-model="filterSex" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option>Male</option>
                    <option>Female</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label small text-muted">Institute</label>
                  <select v-model="filterInstitute" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option v-for="inst in institutes" :key="inst" :value="inst">{{ inst }}</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label small text-muted">Program</label>
                  <select v-model="filterProgram" class="form-select form-select-sm" :disabled="!filterInstitute">
                    <option value="">All</option>
                    <option v-for="prog in availablePrograms(filterInstitute)" :key="prog" :value="prog">{{ prog }}
                    </option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label small text-muted">Email Domain</label>
                  <select v-model="filterEmailDomain" class="form-select form-select-sm">
                    <option value="">All Domains</option>
                    <option value="dnsc.edu.ph">@dnsc.edu.ph</option>
                    <option value="dnscedu.onmicrosoft.com">@dnscedu.onmicrosoft.com</option>
                  </select>
                </div>
                <div class="col-12 text-end">
                  <button @click="resetFilters" class="btn btn-link reset-filter">
                    <Icon icon="mingcute:refresh-2-line" /> Clear all filters
                  </button>
                </div>
              </div>
            </div>
          </div>

          <button type="button" class="btn add-user-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <Icon icon="mingcute:user-add-2-fill" />
          </button>
        </div>
      </div>

      <!-- USERS SECTION -->

      <!-- Loading -->
      <div v-if="loading" class="loading">
        <div class="spinner-border" role="status"></div>
        <div class="loading-text">Loading statistics...</div>
      </div>

      <!-- No Users Found -->
      <div v-else-if="filteredUsers.length === 0" class="empty-state-md">
        <Icon class="icon" icon="mingcute:search-3-line" />
        <p>No feedback matches your filters</p>
        <button @click="resetFilters" class="btn btn-link reset-filter">
          <Icon icon="mingcute:refresh-2-line" /> Clear all filters
        </button>
      </div>

      <!-- Users Lists -->
      <div v-else>
        <table class="table table-borderless users-table">
          <tbody>
            <tr v-for="user in paginatedUsers" :key="user.user_id">

              <td class="small text-break" data-label="User ID">{{ user.user_id }}</td>
              <td class="small text-break" data-label="Username">{{ user.username }}</td>
              <td class="small text-break" data-label="Full Name">{{ user.lastname }} {{ user.firstname }} {{
                user.middlename }}</td>
              <td class="small text-break" data-label="User Type">{{ user.usertype }}</td>
              <td class="small text-break" data-label="Email">{{ user.email }}</td>

              <td class="action-btns small" data-label="Action">
                <div class="buttons">
                  <button type="button" class="btn btn-sm rounded-pill btn-outline-info"
                    @click="openViewUserProfileModal(user)">
                    <Icon class="icon" icon="mingcute:user-visible-fill" /> View
                  </button>
                  <button type="button" class="btn btn-sm rounded-pill btn-outline-warning"
                    @click="openEditModal(user)">
                    <Icon class="icon" icon="mingcute:user-edit-fill" /> Edit
                  </button>
                  <button type="button" class="btn btn-sm rounded-pill btn-outline-secondary"
                    @click="openResetModal(user)">
                    <Icon class="icon" icon="mingcute:safe-lock-fill" /> Reset
                  </button>
                  <button type="button" class="btn btn-sm rounded-pill btn-outline-danger"
                    @click="openDeleteModal(user)">
                    <Icon class="icon" icon="mingcute:delete-3-fill" /> Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->

      <ul class="pagination">
        <!-- Previous -->
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="currentPage > 1 && currentPage--"
            :tabindex="currentPage === 1 ? -1 : null" :aria-disabled="currentPage === 1">
            Previous
          </a>
        </li>

        <!-- Page Numbers (1, 2, 3, ...) -->
        <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
          <a class="page-link" href="#" @click.prevent="currentPage = page">
            {{ page }}
            <span v-if="currentPage === page" class="visually-hidden">(current)</span>
          </a>
        </li>

        <!-- Next -->
        <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
          <a class="page-link" href="#" @click.prevent="currentPage < totalPages && currentPage++"
            :tabindex="currentPage >= totalPages ? -1 : null" :aria-disabled="currentPage >= totalPages">
            Next
          </a>
        </li>
      </ul>

      <!-- ADD USER MODAL -->

      <div class="modal fade primary-modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">

            <form @submit.prevent="submitForm">

              <!-- Header -->
              <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                  <Icon class="icon me-1" icon="mingcute:user-add-2-fill" />
                  Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">User Type <span class="text-danger">*</span></label>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown">
                        {{ form.usertype || 'Select User Type' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="#" @click.prevent="form.usertype = 'Admin'">Admin</a></li>
                        <li><a class="dropdown-item" href="#"
                            @click.prevent="form.usertype = 'Instructor'">Instructor</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="form.usertype = 'Staff'">Staff</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="form.usertype = 'Student'">Student</a>
                        </li>
                      </ul>
                    </div>
                    <p v-if="errors.usertype" class="error-text">
                      {{ errors.usertype[0] }}
                    </p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" v-model="form.username" class="form-control" placeholder="Username"
                      :readonly="!form.usertype || shouldAutogenerateUsername()" />
                    <p v-if="errors.username" class="error-text">
                      {{ errors.username[0] }}
                    </p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" v-model="form.lastname" class="form-control" placeholder="Last Name"
                      :disabled="!isFieldEditable('lastname')" @input="autoGenerateUsername()" />
                    <p v-if="errors.lastname" class="error-text">{{ errors.lastname[0] }}</p>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" v-model="form.firstname" class="form-control" placeholder="First Name"
                      :disabled="!isFieldEditable('firstname')" @input="autoGenerateUsername()" />
                    <p v-if="errors.firstname" class="error-text">{{ errors.firstname[0] }}</p>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" v-model="form.middlename" class="form-control" placeholder="Middle Name"
                      :disabled="!isFieldEditable('middlename')" />
                    <p v-if="errors.middlename" class="error-text">{{ errors.middlename[0] }}</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Sex</label>
                    <div class="dropdown" :class="{ 'disabled-dropdown': !isFieldEditable('sex') }">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isFieldEditable('sex')">
                        {{ form.sex || 'Select Sex' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="#" @click.prevent="form.sex = 'Male'">Male</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="form.sex = 'Female'">Female</a></li>
                      </ul>
                    </div>
                    <p v-if="errors.sex" class="error-text">{{ errors.sex[0] }}</p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" v-model="form.dob" class="form-control" :disabled="!isFieldEditable('dob')" />
                    <p v-if="errors.dob" class="error-text">{{ errors.dob[0] }}</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Institute</label>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isFieldEditable('institute')">
                        {{ form.institute || 'Select Institute' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li v-for="inst in institutes" :key="inst">
                          <a class="dropdown-item" href="#" @click.prevent="form.institute = inst; form.program = ''">
                            {{ inst }}
                          </a>
                        </li>
                      </ul>
                    </div>
                    <p v-if="errors.institute" class="error-text">{{ errors.institute[0] }}</p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Program</label>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isFieldEditable('program') || !form.institute">
                        {{ form.program || (form.institute ? 'Select Program' : 'Select Institute First') }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li v-for="prog in availablePrograms(form.institute)" :key="prog">
                          <a class="dropdown-item" href="#" @click.prevent="form.program = prog">{{ prog }}</a>
                        </li>
                      </ul>
                    </div>
                    <p v-if="errors.program" class="error-text">{{ errors.program[0] }}</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email Username</label>
                    <input type="text" v-model="emailUsername" class="form-control" placeholder="Email Username"
                      :disabled="!isFieldEditable('email')" />
                    <p v-if="errors.email" class="error-text">{{ errors.email[0] }}</p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email Domain</label>
                    <div class="dropdown" :class="{ 'disabled-dropdown': !isFieldEditable('email') }">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isFieldEditable('email')">
                        {{ emailDomain || 'Select Domain' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="#"
                            @click.prevent="emailDomain = 'dnsc.edu.ph'">@dnsc.edu.ph</a></li>
                        <li><a class="dropdown-item" href="#"
                            @click.prevent="emailDomain = 'dnscedu.onmicrosoft.com'">@dnscedu.onmicrosoft.com</a></li>
                      </ul>
                    </div>

                  </div>
                </div>

              </div>

              <!-- Footer -->
              <div class="modal-footer">
                <div class="action-buttons">
                  <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal" :disabled="processing">
                    Close
                  </button>
                  <button type="submit" class="btn btn-sm primary-btn" :disabled="processing">
                    <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    {{ processing ? 'Adding...' : 'Add User' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ADD USER SUCCESS MODAL -->

      <div class="modal fade" id="userSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">User Created Successfully</h5>
            </div>

            <div class="modal-body">
              <p><span>User ID: </span> <code>{{ userId }}</code></p>
              <p><span>Full Name: </span>
                <code>{{ lastname }} {{ firstname }} {{ middlename }}</code>
              </p>
              <p><span>Temporary Password: </span> <code>{{ temppassword }}</code></p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn" id="closeBtn" data-bs-dismiss="modal">OK</button>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- VIEW USER PROFILE MODAL -->

      <div class="modal fade" id="viewUserProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-body">
              <div class="profile-card">

                <div class="icon-container">
                  <Icon class="icon" icon="mingcute:user-4-fill" />
                  <span class="user-type-badge badge" :class="viewUser.usertype?.toLowerCase() || 'user'">
                    {{ (viewUser.usertype || 'user').charAt(0).toUpperCase() }}
                  </span>
                </div>

                <!-- Full Name -->
                <h5 class="fullname" v-if="viewUser.usertype !== 'Admin'">
                  {{ [viewUser.lastname, viewUser.firstname, viewUser.middlename].filter(Boolean).join(' ') || 'N/A' }}
                </h5>

                <!-- Username / User ID -->
                <code class="username-id">@{{ viewUser.username || 'N/A' }} | {{ viewUser.user_id || 'N/A' }}</code>

                <hr>

                <!-- Email -->
                <p class="email">{{ viewUser.email || 'N/A' }}</p>

                <!-- Non-admin fields -->
                <template v-if="viewUser.usertype !== 'Admin'">
                  <p class="sex" v-if="viewUser.sex"><span>Sex:</span> {{ viewUser.sex }}</p>
                  <p class="dob" v-if="viewUser.dob"><span>Date of Birth:</span> {{ formatDate(viewUser.dob) }}</p>
                </template>

                <!-- Student-specific -->
                <template v-if="viewUser.usertype === 'Student'">
                  <p class="institute" v-if="viewUser.institute"><span>Institute:</span> {{ viewUser.institute }}</p>
                  <p class="program" v-if="viewUser.program"><span>Program:</span> {{ viewUser.program }}</p>
                </template>

                <!-- Instructor / Staff-specific -->
                <template v-if="viewUser.usertype === 'Instructor' || viewUser.usertype === 'Staff'">
                  <p class="institute" v-if="viewUser.institute"><span>Institute:</span> {{ viewUser.institute }}</p>
                  <p class="program" v-if="viewUser.program"><span>Program:</span> {{ viewUser.program }}</p>
                </template>

                <button type="button" class="btn btn-sm" id="messageBtn">
                  <Icon class="icon" icon="mingcute:chat-1-fill" />
                  <p>Message</p>
                </button>

              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- EDIT USER MODAL -->

      <div class="modal fade primary-modal" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">

            <form @submit.prevent="submitEditForm">

              <!-- Header -->
              <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                  <Icon class="icon me-1" icon="mingcute:edit-3-fill" />
                  Edit User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">User Type</label>
                    <div class="dropdown">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        disabled>
                        {{ editForm.usertype || 'Select User Type' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="#" @click.prevent>Admin</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent>Instructor</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent>Staff</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent>Student</a></li>
                      </ul>
                    </div>
                    <p v-if="errors.usertype" class="error-text">
                      {{ errors.usertype[0] }}
                    </p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" v-model="editForm.username" class="form-control" placeholder="Username"
                      :readonly="!editForm.usertype || shouldAutogenerateEditUsername()" />
                    <p v-if="errors.username" class="error-text">
                      {{ errors.username[0] }}
                    </p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" v-model="editForm.lastname" class="form-control" placeholder="Last Name"
                      :disabled="!isEditFieldEditable('lastname')" @input="autoGenerateEditUsername()" />
                    <p v-if="errors.lastname" class="error-text">{{ errors.lastname[0] }}</p>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" v-model="editForm.firstname" class="form-control" placeholder="First Name"
                      :disabled="!isEditFieldEditable('firstname')" @input="autoGenerateEditUsername()" />
                    <p v-if="errors.firstname" class="error-text">{{ errors.firstname[0] }}</p>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" v-model="editForm.middlename" class="form-control" placeholder="Middle Name"
                      :disabled="!isEditFieldEditable('middlename')" />
                    <p v-if="errors.middlename" class="error-text">{{ errors.middlename[0] }}</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Sex</label>
                    <div class="dropdown" :class="{ 'disabled-dropdown': !isEditFieldEditable('sex') }">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isEditFieldEditable('sex')">
                        {{ editForm.sex || 'Select Sex' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="#" @click.prevent="editForm.sex = 'Male'">Male</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="editForm.sex = 'Female'">Female</a></li>
                      </ul>
                    </div>
                    <p v-if="errors.sex" class="error-text">{{ errors.sex[0] }}</p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" v-model="editForm.dob" class="form-control"
                      :disabled="!isEditFieldEditable('dob')" />
                    <p v-if="errors.dob" class="error-text">{{ errors.dob[0] }}</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Institute</label>
                    <div class="dropdown" :class="{ 'disabled-dropdown': !isEditFieldEditable('institute') }">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isEditFieldEditable('institute')">
                        {{ editForm.institute || 'Select Institute' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li v-for="inst in institutes" :key="inst">
                          <a class="dropdown-item" href="#"
                            @click.prevent="editForm.institute = inst; editForm.program = ''">
                            {{ inst }}
                          </a>
                        </li>
                      </ul>
                    </div>
                    <p v-if="errors.institute" class="error-text">{{ errors.institute[0] }}</p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Program</label>
                    <div class="dropdown"
                      :class="{ 'disabled-dropdown': !isEditFieldEditable('program') || !editForm.institute }">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isEditFieldEditable('program') || !editForm.institute">
                        {{ editForm.program || (editForm.institute ? 'Select Program' : 'Select Institute First') }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li v-for="prog in availablePrograms(editForm.institute)" :key="prog">
                          <a class="dropdown-item" href="#" @click.prevent="editForm.program = prog">{{ prog }}</a>
                        </li>
                      </ul>
                    </div>
                    <p v-if="errors.program" class="error-text">{{ errors.program[0] }}</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email Username</label>
                    <input type="text" v-model="editEmailUsername" class="form-control" placeholder="Email Username"
                      :disabled="!isEditFieldEditable('email')" />
                    <p v-if="errors.email" class="error-text">{{ errors.email[0] }}</p>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email Domain</label>
                    <div class="dropdown" :class="{ 'disabled-dropdown': !isEditFieldEditable('email') }">
                      <button class="btn dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown"
                        :disabled="!isEditFieldEditable('email')">
                        {{ editEmailDomain || 'Select Domain' }}
                      </button>
                      <ul class="dropdown-menu w-100">
                        <li><a class="dropdown-item" href="#"
                            @click.prevent="editEmailDomain = 'dnsc.edu.ph'">@dnsc.edu.ph</a></li>
                        <li><a class="dropdown-item" href="#"
                            @click.prevent="editEmailDomain = 'dnscedu.onmicrosoft.com'">@dnscedu.onmicrosoft.com</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Footer -->
              <div class="modal-footer">
                <div class="action-buttons">
                  <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal" :disabled="processing">
                    Cancel
                  </button>
                  <button type="submit" class="btn btn-sm primary-btn" :disabled="processing">
                    <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    {{ processing ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- RESET PASSWORD CONFIRMATION MODAL -->

      <div class="modal fade danger-modal" id="confirmResetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon class="icon me-1" icon="mingcute:key-2-fill" />
                Confirm Password Reset
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <p class="normal-text">Are you sure you want to reset the password for this user? </p>
              <p class="danger-text">
                <Icon class="icon" icon="mingcute:user-2-fill" />
                {{ resetTarget?.lastname }} {{ resetTarget?.firstname }} {{ resetTarget?.middlename }}
              </p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm primary-btn" id="cancelResetBtn" data-bs-dismiss="modal">
                  No
                </button>

                <button type="button" class="btn btn-sm secondary-btn" id="confirmResetBtn" @click="resetPassword">
                  Yes, Reset
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RESET PASSWORD MODAL -->

      <div class="modal fade" id="tempPasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">New Temporary Password</h5>
            </div>

            <div class="modal-body">
              <p>Your new temporary password:</p>
              <h4><code>{{ tempPassword }}</code></h4>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn" data-bs-dismiss="modal">OK</button>
            </div>
          </div>
        </div>
      </div>

      <!-- DELETE USER CONFIRMATION MODAL -->

      <div class="modal fade danger-modal" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">
                <Icon class="icon me-1" icon="mingcute:delete-3-fill" />
                Confirm Delete
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <p class="normal-text">Are you sure you want to delete this user?</p>
              <p class="danger-text">
                <Icon class="icon" icon="mingcute:user-2-fill" />
                {{ deleteTarget?.lastname }} {{ deleteTarget?.firstname }} {{ deleteTarget?.middlename
                }}
              </p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button type="button" class="btn btn-sm primary-btn" id="cancelBtn" data-bs-dismiss="modal">
                  No
                </button>

                <button type="button" class="btn btn-sm secondary-btn" id="yesBtn" @click="deleteUser">
                  Yes, Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- DELETE SUCCESS TOAST -->

      <div class="success-toast">
        <div id="successDeleteToast" class="toast">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:delete-3-fill" />
              User deleted successfully!
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

.layout .header .add-user-btn {
  color: #333;
  font-weight: 500;
  border-radius: 12px;
  background-color: #48c441;
}

.layout .header .add-user-btn:hover,
.layout .header .add-user-btn:active,
.layout .header .add-user-btn:focus {
  color: #fff;
  font-weight: 500;
  background-color: #3ca237;
}

/* RESULT INFO */

.layout .result-info small {
  color: #3ca237;
}

/* PAGINATION */

.layout .pagination {
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

.layout .pagination .page-item .page-link {
  color: #333;
  border: 1px solid #ddd;
  border-radius: 6px !important;
  font-weight: 500;
  transition: all 0.2s ease;
}

.layout .pagination .page-item.active .page-link {
  background-color: #48c441;
  color: white;
  border-color: #48c441;
}

.layout .pagination .page-item.disabled .page-link {
  color: #999;
  background-color: #f8f9fa;
  border-color: #ddd;
  opacity: 0.6;
  cursor: not-allowed;
}

.layout .pagination .page-link:hover:not(.disabled) {
  background-color: #ffff;
  border-color: #48c441;
  color: #48c441;
}

/* USERS TABLE */

.users-table {
  margin: 0;
}

.users-table thead,
.users-table th,
.users-table tbody,
.users-table tr,
.users-table td {
  border: none;
}

.users-table thead {
  display: none;
}

.users-table thead th {
  color: #444;
}

.users-table tbody {
  display: flex;
  justify-content: center;
  flex-direction: column;
  gap: 8px;
}

.users-table tbody tr.no-user-found-container td.no-user-found-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.users-table tbody tr.no-user-found-container td.no-user-found-content .icon {
  color: #999;
}

.users-table tbody tr.no-user-found-container td.no-user-found-content h5 {
  color: #333;
}

.users-table tbody tr.no-user-found-container td.no-user-found-content p {
  color: #8b8b8b;
}

.reset-filter {
  color: #3ca237;
  text-decoration: none;
}

.reset-filter:hover,
.reset-filter:active,
.reset-filter:focus {
  color: #48c441;
}

.users-table tbody tr {
  border: 1px solid #dee2e6;
  background: #fff;
  box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.1);
  padding: 12px;
  border-radius: 12px;
  transition: transform 0.2s;
}

.users-table tbody tr:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.users-table tbody td {
  display: flex;
  justify-content: space-between;
  align-items: center;
  text-align: justify;
}

.users-table tbody td::before {
  content: attr(data-label);
  margin-right: 46px;
  font-weight: 600;
  color: #333;
}

.users-table tbody td.text-break {
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

.users-table tbody tr td.action-btns .buttons {
  display: flex;
  justify-content: right;
  flex-wrap: wrap;
  gap: 8px;
}

.users-table tbody tr td.action-btns .buttons .btn {
  color: #333;
  font-weight: 500;
  border: 2px solid #48c441;
}

.users-table tbody tr td.action-btns .buttons .btn:hover,
.users-table tbody tr td.action-btns .buttons .btn:active,
.users-table tbody tr td.action-btns .buttons .btn:focus {
  color: #fff;
  font-weight: 500;
  border: 2px solid #48c441;
  background-color: #48c441;
}

/* USER LIST PAGINATION */

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 12px;
}

.pagination .page-item .page-link {
  outline: none;
  border: none;
  box-shadow: none;
}

/* ADD USER MODAL IN THE app.css*/

#addUserModal .modal-body .error-text {
  font-size: 14px;
  color: #d60000;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 4px !important;
}

#addUserModal .modal-body .error-text::before {
  content: "⚠️";
}

/* ADD USER SUCCESS MODAL */

#userSuccessModal .modal-content {
  border: none;
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
  border-radius: 12px;
}

#userSuccessModal .modal-header {
  background: rgba(255, 255, 255, 0.85);
  border: none;
  border-radius: 12px;
}

#userSuccessModal .modal-header .modal-title {
  font-weight: 600;
  letter-spacing: 0.5px;
  color: #48c441;
}

#userSuccessModal .modal-body {
  border: none;
}

#userSuccessModal .modal-body p span {
  color: #333;
  font-weight: 600;
}

#userSuccessModal .modal-footer {
  border: none;
}

#userSuccessModal .modal-footer .action-buttons {
  display: flex;
  justify-content: end;
  gap: 8px;
}

#userSuccessModal .modal-footer .action-buttons #closeBtn {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
}

#userSuccessModal .modal-footer .action-buttons #closeBtn {
  background-color: #48c441;
  border: none;
  color: white;
  transition: all 0.2s ease;
}

#userSuccessModal .modal-footer .action-buttons #closeBtn:hover,
#userSuccessModal .modal-footer .action-buttons #closeBtn:active {
  background-color: #3ca237;
  transform: scale(1.005);
}

/* EDIT USER MODAL IN THE app.css*/

#editUserModal .modal-body .error-text {
  font-size: 14px;
  color: #d60000;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 4px !important;
}

#editUserModal .modal-body .error-text::before {
  content: "⚠️";
}

/* RESET PASSWORD CONFIRMATION MODAL IN THE app.css */

/* RESET PASSWORD CONFIRMATION MODAL SUCCESS */

#tempPasswordModal .modal-content {
  border: none;
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
  border-radius: 12px;
}

#tempPasswordModal .modal-header {
  background: rgba(255, 255, 255, 0.85);
  border: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

#tempPasswordModal .modal-header .modal-title {
  font-weight: 600;
  letter-spacing: 0.5px;
  color: #48c441;
}

#tempPasswordModal .modal-body {
  border: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

#tempPasswordModal .modal-body p {
  font-weight: 500;
  margin-bottom: 12px;
  color: #333;
}

#tempPasswordModal .modal-body h4 {
  font-weight: 600;
  color: #d60000;
}

#tempPasswordModal .modal-footer {
  border: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

#tempPasswordModal .modal-footer button {
  padding: 9px 18px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  background-color: #48c441;
  border: none;
  color: white;
  transition: all 0.22s ease;
}

#tempPasswordModal .modal-footer button:hover,
#tempPasswordModal .modal-footer button:active {
  background-color: #3ca237;
  transform: scale(1.005);
}

/* DELETE USER CONFIRMATION MODAL IN THE app.css */

/* DELETE SUCCESS TOAST IN THE app.css */



/* VIEW USER PROFILE */

#viewUserProfileModal .modal-dialog {
  background-color: transparent !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

#viewUserProfileModal .modal-content {
  background-color: transparent !important;
  border: none;
  box-shadow: none;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-card {
  width: 400px;
  padding: 30px;
  border-radius: 15px;
  background-color: #ffffff;
  border: 1px solid #48c441;
  animation: neonGlow 1.5s ease-in-out infinite alternate;
  display: flex;
  flex-direction: column;
  gap: 8px;
  color: #333;
}

@keyframes neonGlow {
  from {
    box-shadow: 0 0 3px #48c441;
  }

  to {
    box-shadow: 0 0 10px #48c441;
  }
}

.profile-card .icon-container {
  position: relative;
  display: inline-block;
  margin: 0 auto;
}

.profile-card .icon-container .icon {
  font-size: 138px;
  color: #555;
}

.user-type-badge {
  position: absolute;
  bottom: 12px;
  right: 12px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  font-size: 18px;
  font-weight: bold;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 4px solid white;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
  z-index: 10;
}

.user-type-badge.admin {
  background-color: #0d6efd;
}

.user-type-badge.instructor {
  background-color: #198754;
}

.user-type-badge.staff {
  background-color: #fd7e14;
}

.user-type-badge.student {
  background-color: #6f42c1;
}

.user-type-badge.user {
  background-color: #6c757d;
}

.profile-card .fullname {
  text-align: center;
  font-weight: 600;
}

.profile-card .username-id {
  text-align: center;
  font-weight: 400;
}

.profile-card hr {
  color: #666;
}

.profile-card .email {
  font-size: 14px;
  text-align: center;
  color: #3ca237;
  margin-bottom: 12px !important;
}

.profile-card .sex,
.profile-card .dob,
.profile-card .institute,
.profile-card .program {
  font-size: 14px;
}

.profile-card .sex span,
.profile-card .dob span,
.profile-card .institute span,
.profile-card .program span {
  font-weight: 600;
}

.profile-card #messageBtn {
  padding: 9px 18px;
  margin-top: 12px;
  border-radius: 12px;
  font-weight: 600;
  letter-spacing: 0.2px;
  transition: all 0.22s ease;
  background-color: #48c441;
  border: none;
  color: white;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.profile-card #messageBtn:hover,
.profile-card #messageBtn:active {
  background-color: #3ca237;
}

.profile-card #messageBtn .icon {
  font-size: 18px;
}
</style>
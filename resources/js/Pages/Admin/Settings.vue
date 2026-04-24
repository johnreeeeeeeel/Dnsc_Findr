<template>
  <AdminAppLayout title="Settings">
    <div class="container-fluid layout">

      <div class="settings-nav p-0">
        <!-- Manage Institutes Button -->
        <div class="manage-institute-program">
          <Icon class="icon" icon="mingcute:edit-3-fill" />
          <a class="btn p-0" href="/admin/manage-institute-program.php">
            Manage Institutes & Programs
          </a>
        </div>

        <!-- Change Password -->
        <div class="change-password">
          <Icon class="icon" icon="mingcute:safe-lock-fill" />
          <a class="btn p-0" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
            Change Password
          </a>
        </div>
      </div>

      <!-- CHANGE PASSWORD MODAL -->
      <div class="modal fade primary-modal" id="changePasswordModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form @submit.prevent="changePassword">

              <!-- Header -->
              <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <!-- Body -->
              <div class="modal-body">

                <div class="field">
                  <label for="current_password">
                    Temporary Password <span class="text-danger">*</span>
                  </label>
                  <div class="password-wrapper">
                    <input :type="showCurrent ? 'text' : 'password'" v-model="form.current_password"
                      id="current_password" class="input" />
                    <img class="toggle-password-icon"
                      src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg" alt="Show password"
                      @click="showCurrent = !showCurrent" />
                  </div>
                  <p v-if="errors.current_password" class="error-text">{{ errors.current_password[0] }}</p>
                </div>

                <div class="field">
                  <label for="new_password">New Password <span class="text-danger">*</span></label>
                  <div class="password-wrapper">
                    <input :type="showNew ? 'text' : 'password'" v-model="form.new_password" id="new_password"
                      class="input" />
                    <img class="toggle-password-icon"
                      src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg" alt="Show password"
                      @click="showNew = !showNew" />
                  </div>
                  <p v-if="errors.new_password" class="error-text">{{ errors.new_password[0] }}</p>
                </div>

                <div class="field">
                  <label for="new_password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                  <div class="password-wrapper">
                    <input :type="showConfirm ? 'text' : 'password'" v-model="form.new_password_confirmation"
                      id="new_password_confirmation" class="input" />
                    <img class="toggle-password-icon"
                      src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/icons/eye.svg" alt="Show password"
                      @click="showConfirm = !showConfirm" />
                  </div>
                  <p v-if="errors.new_password_confirmation" class="error-text">
                    {{ errors.new_password_confirmation[0] }}
                  </p>
                </div>

              </div>

              <!-- Footer -->
              <div class="modal-footer">
                <div class="action-buttons">
                  <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal"
                    :disabled="formLoading">
                    Cancel
                  </button>
                  <button type="submit" class="btn btn-sm primary-btn" :disabled="formLoading">
                    <span v-if="formLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    {{ formLoading ? 'Changing...' : 'Change Password' }}
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>

      <!-- Success Changed Password -->
      <div class="success-toast">
        <div id="changedPasswordSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-content">
            <div class="toast-body">
              <Icon class="icon" icon="mingcute:check-circle-fill" />
              Changed Password Successfully!
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
import AdminAppLayout from '../../Layouts/AdminAppLayout.vue'
import axios from 'axios'

export default {
  components: { AdminAppLayout },

  data() {
    return {
      formLoading: false,
      form: {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      },
      showCurrent: false,
      showNew: false,
      showConfirm: false,
      errors: {}
    }
  },

  methods: {
    async changePassword() {
      this.formLoading = true
      this.errors = {}

      try {
        const response = await axios.post('/change-password', this.form, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })

        // Auto-close modal after success
        const modalEl = document.getElementById('changePasswordModal')
        bootstrap.Modal.getInstance(modalEl)?.hide()

        // Show Success Toast
        this.showSuccessToast('changedPasswordSuccess');

        // Clear form
        this.form.current_password = ''
        this.form.new_password = ''
        this.form.new_password_confirmation = ''

      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors || {}
        } else {
          this.errors = { current_password: [err.response?.data?.message || 'Something went wrong.'] }
        }
      } finally {
        this.formLoading = false
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
  }
}
</script>

<style scoped>
@import '../../../css/change-password-modal.css';

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

.settings-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.settings-nav .btn {
  outline: none;
  border: none;
  color: #444;
  font-weight: 500;
}

.settings-nav .btn:hover {
  color: #48c441;
}

.settings-nav .manage-institute-program,
.settings-nav .change-password {
  display: flex;
  align-items: center;
  gap: 8px;
}

.settings-nav .manage-institute-program .icon,
.settings-nav .change-password .icon {
  width: 20px;
  height: 20px;
}
</style>
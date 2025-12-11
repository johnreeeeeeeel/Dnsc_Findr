<template>
  <UserAppLayout title="Give Feedback">
    <div class="container-fluid layout">
      <div class="feedback-card">

        <div class="feedback-header">
          <h4>We Value Your Feedback</h4>
        </div>

        <div class="feedback-body">

          <!-- Service Availed -->
          <div class="form-group">
            <label class="label-title">Which service did you avail?</label>

            <div class="radio-group">
              <label class="radio-option" :class="{ selected: form.service_used === 'Claim Lost' }">
                <input type="radio" value="Claim Lost" v-model="form.service_used" />
                <span>Claim Lost Item</span>
              </label>

              <label class="radio-option" :class="{ selected: form.service_used === 'Report Found' }">
                <input type="radio" value="Report Found" v-model="form.service_used" />
                <span>Report Found Item</span>
              </label>
            </div>
          </div>

          <!-- Rate the Service -->
          <div class="form-group">
            <label class="label-title">Rate the Service</label>

            <div class="star-rating">
              <span v-for="n in 5" :key="'service-' + n" class="star" :class="{ active: n <= form.rating_service }"
                @click="form.rating_service = n">
                ★
              </span>
            </div>
          </div>

          <!-- Message Service -->
          <div class="form-group">
            <label class="label-title">Feedback about the Service</label>
            <textarea class="textarea-input" rows="5" v-model="form.message_service"
              placeholder="Your feedback on the service..."></textarea>
          </div>

          <!-- Rate the System -->
          <div class="form-group">
            <label class="label-title">Rate the System (Website)</label>

            <div class="star-rating">
              <span v-for="n in 5" :key="'system-' + n" class="star" :class="{ active: n <= form.rating_system }"
                @click="form.rating_system = n">
                ★
              </span>
            </div>
          </div>

          <!-- Message System -->
          <div class="form-group">
            <label class="label-title">Feedback about the System</label>
            <textarea class="textarea-input" rows="5" v-model="form.message_system"
              placeholder="Your feedback on the system..."></textarea>
          </div>

          <!-- Anonymous -->
          <label class="checkbox-row">
            <input type="checkbox" v-model="form.is_anonymous" />
            <span>Submit as Anonymous</span>
          </label>

          <!-- Submit -->
          <button class="submit-btn" @click="submitFeedback" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            <Icon class="icon" icon="mingcute:send-fill" />
            {{ loading ? "Submitting..." : "Submit Feedback" }}
          </button>

          <p class="error" v-if="error">⚠️ {{ error }}</p>

        </div>
      </div>

      <!-- Success Modal -->
      <div class="modal fade success-modal" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <div class="modal-header text-center">
              <h5 class="modal-title">Feedback Submitted</h5>
            </div>

            <div class="modal-body">
              <p>Thank you for your feedback!</p>
            </div>

            <div class="modal-footer">
              <div class="action-buttons">
                <button class="btn btn-sm primary-btn" data-bs-dismiss="modal">Close</button>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </UserAppLayout>
</template>

<script>
import UserAppLayout from '../../Layouts/UserAppLayout.vue';
import axios from 'axios';

export default {
  components: { UserAppLayout },
  data() {
    return {
      form: {
        service_used: '',
        rating_system: 0,
        rating_service: 0,
        message: '',
        is_anonymous: false,
      },
      loading: false,
      success: '',
      error: ''
    };
  },
  methods: {
    async submitFeedback() {
      if (this.form.rating_system === 0 || this.form.rating_service === 0) {
        this.error = 'Please give both ratings';
        return;
      }

      this.loading = true;
      this.error = '';

      try {
        await axios.post('/api/user/feedback', this.form);

        // Reset form
        Object.assign(this.form, {
          service_used: '',
          rating_system: 0,
          rating_service: 0,
          message_service: '',
          message_system: '',
          is_anonymous: false,
        });

        // Open success modal
        const modal = new bootstrap.Modal(document.getElementById("successModal"));
        modal.show();

      } catch (err) {
        this.error = err.response?.data?.message || 'Something went wrong';
      } finally {
        this.loading = false;
      }
    }
  }
}
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

.feedback-card {
  width: 100%;
  max-width: 550px;
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  margin: auto;
}

.feedback-header {
  background-color: #48c441;
  padding: 16px;
  text-align: center;
}

.feedback-header h4 {
  color: #ffffff;
  font-size: 20px;
  margin: 0;
}

.feedback-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.label-title {
  font-size: 14px;
  font-weight: 600;
  color: #555;
}

/* Service Availed */
.radio-group {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 12px;
}

.radio-option {
  flex: 1 1 48%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 16px;
  border: 1px solid #48c441;
  border-radius: 12px;
  cursor: pointer;
  gap: 6px;
  user-select: none;
  transition: background 0.2s ease, color 0.2s ease, border 0.2s ease, box-shadow 0.2s ease;
  font-size: 14px;
  text-align: center;
  position: relative;
}

.radio-option input[type="radio"] {
  display: none;
}

.radio-option:hover {
  background-color: #D8FDE9;
}

.radio-option.selected {
  background-color: #48c441;
  color: #ffffff;
  border-color: #48c441;
}

/* Star rating */
.star-rating {
  display: flex;
  gap: 6px;
  font-size: 38px;
  cursor: pointer;
}

.star-rating .star {
  color: #ccc;
  transition: transform 0.2s ease, color 0.2s ease;
}

.star-rating .star.active {
  color: #48c441;
}

/* Textarea inputs */
textarea {
  width: 100%;
  border: 1px solid #48c441;
  border-radius: 12px;
  padding: 10px;
  font-size: 14px;
  resize: none;
  outline: none;
}

textarea:focus,
textarea:active {
  border-color: #48c441;
  outline: none;
  box-shadow: 0 0 4px 1px rgba(72, 196, 65, 0.4);
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

/* Checkbox */
.checkbox-row {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

/* Submit button */
.submit-btn {
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
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.submit-btn:hover {
  background-color: #3ca237;
  transform: scale(1.005);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.submit-btn:hover:not(:disabled) {
  background-color: #3ca237;
}

/* General Error */

.feedback-body .error {
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 14px;
  background-color: #fdecef; 
  color: #a42834; 
  border-left: 4px solid #e05260; 
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease-in-out;
}

</style>
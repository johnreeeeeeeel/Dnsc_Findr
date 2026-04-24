<script>
import { Icon } from '@iconify/vue';

export default {
    name: 'AdminAppLayout',
    props: {
        title: {
            type: String,
            default: 'DNSC Findr'
        }
    },

    data() {
        return {
            // CREATE POST
            form: {
                item_name: '',
                item_category: '',
                description: '',
                location_found: '',
                date_found: '',
                time_found: '',
                photo: null
            },

            previewImage: null,
            isUploadingImage: false,

            submitting: false,
            rescheduleSubmitting: false,
            inputErrors: {},

            // MESSAGE
            conversations: [],
            selectedChat: null,
            chatMessages: [],
            newMessage: '',
            sending: false,
            unreadMessages: 0,
            showUserList: false,
            loadingUsers: false,
            users: { data: [], current_page: 1, last_page: 1 },
            userSearch: '',

            currentUserId: null,

            messagesPolling: null,
            chatPolling: null,

            // NOTIFICATION
            notifications: [],
            unreadNotification: 0,
            selectedNotification: null,
            notificationPolling: null,

            // APPOINTMENT
            appointments: [],
            pendingAppointments: 0,
            selectedApptForApprove: null,
            selectedApptForReject: null,
            selectedApptForReschedule: null,
            selectedApptToDelete: null,
            approveMessage: '',
            rejectMessage: '',
            rescheduleForm: {
                reschedule_date: '',
                reschedule_time: '',
                response_message: ''
            },
        }
    },

    computed: {
        // ACTIVE PAGE
        currentPath() {
            return window.location.pathname;
        }
    },

    mounted() {
        // MESSAGE
        this.fetchConversations();
        this.startMessagesPolling();

        // NOTIFICATION
        this.fetchNotifications();
        this.startNotificationPolling();

        // APPOINTMENT
        this.fetchAppointments();
        this.startAppointmentPolling();

        // Reset form when modal is closed
        const modalEl = document.getElementById('createPostModal');
        modalEl.addEventListener('hidden.bs.modal', () => {
            this.resetForm();
            this.previewImage = null;
            this.inputErrors = {};
        });

        // Tooltips 
        this.$nextTick(() => {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
    },

    beforeDestroy() {
        if (this.messagesPolling) clearInterval(this.messagesPolling);
        if (this.chatPolling) clearInterval(this.chatPolling);
        if (this.notificationPolling) clearInterval(this.notificationPolling);
        if (this.appointmentPolling) clearInterval(this.appointmentPolling);
    },

    methods: {
        // ACTIVE PAGE
        isActive(path) {
            return this.currentPath === path ? 'active' : '';
        },

        // UPLOADING IMAGE
        onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.isUploadingImage = true;
            this.form.photo = file;

            const reader = new FileReader();

            reader.onload = (event) => {
                this.previewImage = event.target.result;
                this.isUploadingImage = false;
            };

            reader.readAsDataURL(file);
        },

        // CREATE AND SUBMIT POST
        submitPost() {
            this.submitting = true;
            this.inputErrors = {};

            const formData = new FormData();
            formData.append('item_name', this.form.item_name);
            formData.append('item_category', this.form.item_category || '');
            formData.append('description', this.form.description || '');
            formData.append('location_found', this.form.location_found);
            formData.append('date_found', this.form.date_found);
            formData.append('time_found', this.form.time_found);
            formData.append('photo', this.form.photo);

            axios.post('/found-items', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json',
                }
            })
                .then(response => {
                    // Close modal and reset
                    const modalEl = document.getElementById('createPostModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    this.resetForm();
                    this.previewImage = null;
                    this.inputErrors = {};

                    const successModalEl = document.getElementById('successPostModal');
                    const successModal = new bootstrap.Modal(successModalEl);
                    successModal.show();

                    this.$emit('post-created');
                })
                .catch(error => {
                    if (error.response?.status === 422) {
                        // Laravel validation errors
                        this.inputErrors = error.response.data.errors;
                    }
                })
                .finally(() => {
                    this.submitting = false;
                });
        },

        resetForm() {
            this.form = {
                item_name: '', item_category: '', description: '',
                location_found: '', date_found: '', time_found: '', photo: null
            };
        },

        // MESSAGE

        async fetchConversations() {
            try {
                const res = await axios.get('/api/messages/conversations');
                this.conversations = res.data;
                this.unreadMessages = this.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
            } catch (err) {
                console.error('Failed to load conversations:', err);
            }
        },

        async fetchUsers(page = 1) {
            try {
                const res = await axios.get('/api/messages/users', {
                    params: { page, search: this.userSearch }
                });
                this.users = res.data;
            } catch (err) {
                console.error('Failed to load users:', err);
            }
        },

        toggleUserList() {
            if (this.showUserList) {
                // Going back to chats
                this.showUserList = false;
            } else {
                // Opening "New Message"
                this.showUserList = true;
                this.userSearch = '';
                this.loadingUsers = true;
                this.users.data = [];

                this.fetchUsers(1).finally(() => {
                    this.loadingUsers = false;
                });
            }
        },

        startChat(user) {
            this.selectedChat = user;
            this.showUserList = false;
            this.openChat(user);
        },

        async openChat(user) {
            this.selectedChat = user;

            // Clear old messages
            this.chatMessages = [];

            try {
                const res = await axios.get(`/api/messages/chat/${user.user_id}`);
                this.chatMessages = res.data;

                // Auto-scroll
                this.$nextTick(() => {
                    const container = this.$refs.chatContainer;
                    if (container) container.scrollTop = container.scrollHeight;
                });

                this.startChatPolling();
            } catch (err) {
                console.error('Failed to load chat:', err);
            }
        },

        closeChat() {
            this.selectedChat = null;
            this.showUserList = false;
            if (this.chatPolling) {
                clearInterval(this.chatPolling);
                this.chatPolling = null;
            }
        },

        startChatPolling() {
            if (this.chatPolling) clearInterval(this.chatPolling);

            this.chatPolling = setInterval(async () => {
                if (!this.selectedChat) return;

                try {
                    const res = await axios.get(`/api/messages/chat/${this.selectedChat.user_id}`);
                    const newMessages = res.data;

                    if (JSON.stringify(this.chatMessages) !== JSON.stringify(newMessages)) {
                        this.chatMessages = newMessages;

                        this.$nextTick(() => {
                            const container = this.$refs.chatContainer;
                            if (!container) return;

                            const isNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;
                            if (isNearBottom) {
                                container.scrollTop = container.scrollHeight;
                            }
                        });
                    }
                } catch (err) {
                    console.error('Chat polling failed:', err);
                }
            }, 3000);
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending) return;
            this.sending = true;

            try {
                await axios.post('/api/messages/send', {
                    receiver_id: this.selectedChat.user_id,
                    message: this.newMessage.trim()
                });

                this.newMessage = '';
                await this.openChat(this.selectedChat);     // Refresh chat
                await this.fetchConversations();             // Refresh sidebar
            } catch (err) {
                this.showAlert('Failed to send message', 'alert-danger');
            } finally {
                this.sending = false;
            }
        },

        startMessagesPolling() {
            let previousConversations = [];

            this.messagesPolling = setInterval(async () => {
                try {
                    const res = await axios.get('/api/messages/conversations');
                    const newConversations = res.data;

                    this.conversations = newConversations;

                    // Update unread badge
                    this.unreadMessages = newConversations.reduce(
                        (sum, c) => sum + (c.unread_count || 0),
                        0
                    );

                    // Save for comparison next round
                    previousConversations = JSON.parse(JSON.stringify(newConversations));
                } catch (err) {
                    console.error('Failed to poll conversations:', err);
                }
            }, 5000);
        },

        formatMessageDateTime(date) {
            if (!date) return '';
            const d = new Date(date);
            const now = new Date();
            const isToday = d.toDateString() === now.toDateString();
            return isToday
                ? d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                : d.toLocaleDateString([], { month: 'short', day: 'numeric' });
        },

        // NOTIFICATION

        async fetchNotifications() {
            try {
                const res = await axios.get('/api/notifications');
                this.notifications = res.data.map(n => ({
                    id: n.id,
                    title: n.title,
                    message: n.message,
                    is_read: n.is_read,
                    created_at: n.created_at
                }));
                this.unreadNotification = this.notifications.filter(n => !n.is_read).length;
            } catch (err) {
                console.error('Failed to fetch notifications:', err);
            }
        },

        startNotificationPolling() {
            setInterval(async () => {
                try {
                    const res = await axios.get('/api/notifications');
                    const newNotifications = res.data.map(n => ({
                        id: n.id,
                        title: n.title,
                        message: n.message,
                        is_read: n.is_read,
                        created_at: n.created_at
                    }));

                    // Identify new notifications
                    const existingIds = this.notifications.map(n => n.id);

                    newNotifications.forEach(n => {
                        if (!existingIds.includes(n.id)) {
                            this.notifications.unshift(n);
                        }
                    });

                    // Update unread badge
                    this.unreadNotification = newNotifications.filter(n => !n.is_read).length;

                    this.notifications = newNotifications;
                } catch (err) {
                    console.error('Failed to fetch notifications:', err);
                }
            }, 5000);
        },

        async openNotification(notif) {
            this.selectedNotification = notif;

            if (!notif.is_read) {
                try {
                    await axios.post(`/notifications/${notif.id}/mark-read`);
                    notif.is_read = true;
                    this.unreadNotification = this.notifications.filter(n => !n.is_read).length;
                } catch (err) {
                    console.error('Failed to mark notification as read:', err);
                }
            }
        },

        async markAllRead() {
            try {
                await axios.post('/notifications/mark-all-read');
                this.notifications = this.notifications.map(n => ({ ...n, is_read: true }));
                this.unreadNotification = 0;
            } catch (err) {
                console.error(err);
                alert('Failed to mark notifications as read');
            }
        },

        formatNotificationDateTime(datetime) {
            if (!datetime) return '—';
            const d = new Date(datetime);
            if (isNaN(d)) return '—';
            const datePart = this.formatDate_2(d);
            const timePart = this.formatTime_1(d.getHours() + ':' + String(d.getMinutes()).padStart(2, '0'));
            return `${datePart} at ${timePart}`;
        },

        formatNotificationMessage(message) {
            if (!message) return '';

            return message
                .replace(/\n\n/g, '</p><p class="mb-3">')  // Double newline = new paragraph with bottom margin
                .replace(/\n/g, '<br>')                     // Single newline = line break
                .replace(/<p/g, '<p class="mb-3"')          // Ensure all paragraphs have spacing
                .replace(/^/, '<p class="mb-3">')           // Wrap starting text in <p>
                .replace(/$/, '</p>');                      // Close the last paragraph
        },

        showDeleteNotificationModal(notif) {
            this.notificationToDelete = notif;
            const modalEl = document.getElementById('deleteNotificationModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        },

        async confirmDeleteNotification() {
            try {
                await axios.delete(`/notifications/${this.notificationToDelete.id}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                // Remove the notification from the list
                this.notifications = this.notifications.filter(n => n.id !== this.notificationToDelete.id);
                this.unreadNotification = this.notifications.filter(n => !n.is_read).length;

                // Show success toast
                this.showSuccessToast('deletedSuccess');

                // If the deleted notification is currently opened in expanded view, close it
                if (this.selectedNotification && this.selectedNotification.id === this.notificationToDelete.id) {
                    this.selectedNotification = null;
                }
            } catch (err) {
                console.error('Failed to delete notification:', err);
            } finally {
                this.notificationToDelete = null;
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteNotificationModal'));
                if (modal) modal.hide();
            }
        },

        showDeleteAllNotificationsModal() {
            const modalEl = document.getElementById('deleteAllNotificationsModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        },

        async confirmDeleteAllNotifications() {
            try {
                await axios.delete('/notifications/delete-all', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                this.notifications = [];
                this.unreadNotification = 0;

                // Show success toast
                this.showSuccessToast('deletedAllSuccess');

            } catch (err) {
                console.error('Failed to delete all notifications:', err);
            } finally {
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAllNotificationsModal'));
                if (modal) modal.hide();
            }
        },

        // APPOINTMENTS

        async fetchAppointments() {
            try {
                const res = await axios.get('/api/admin/appointments'); // CORRECT
                this.appointments = res.data;
                this.pendingAppointments = res.data.filter(a => a.status === 'Pending').length;
            } catch (err) {
                console.error('Failed to load admin appointments:', err);
                // Optional: show toast if admin
                if (err.response?.status === 403) {
                    console.warn('You are not authorized to view admin appointments');
                }
            }
        },

        startAppointmentPolling() {
            if (this.appointmentPolling) clearInterval(this.appointmentPolling);
            this.appointmentPolling = setInterval(() => {
                this.fetchAppointments();
            }, 3000);
        },

        async approveAppointment(ref) {
            if (!confirm('Approve this appointment?')) return;

            try {
                await axios.post(`/api/admin/appointments/${ref}/approve`);
                this.fetchAppointments();
            } catch (err) {
                console.error(err);
                alert('Failed to approve appointment');
            }
        },

        openApproveModal(appt) {
            this.selectedApptForApprove = appt;
            this.approveMessage = 'Your appointment has been approved by OSDS.'; // default

            const modalEl = document.getElementById('approveApptModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        },

        async confirmApproveAppointment() {
            let message = this.approveMessage.trim();
            if (!message) {
                message = 'Your appointment has been approved by OSDS.';
            }

            try {
                await axios.post(
                    `/api/admin/appointments/${this.selectedApptForApprove.appointment_reference_number}/approve`,
                    { response_message: message }
                );

                this.fetchAppointments();

                // Show success toast
                this.showSuccessToast('approvedApptSuccess');

                const modalEl = document.getElementById('approveApptModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                this.approveMessage = 'Your appointment has been approved by OSDS.';
            } catch (err) {
                alert('Failed to approve appointment');
            }
        },

        openRejectModal(appt) {
            this.selectedApptForReject = appt;
            this.rejectMessage = 'Your appointment has been rejected.';

            const modalEl = document.getElementById('rejectApptModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        },

        async rejectAppointment() {
            let message = this.rejectMessage.trim();

            // Use default if admin left it empty or just whitespace
            if (!message) {
                message = 'Your appointment has been rejected.';
            }

            try {
                await axios.post(
                    `/api/admin/appointments/${this.selectedApptForReject.appointment_reference_number}/reject`,
                    { response_message: message }
                );

                this.fetchAppointments();

                // Show success toast
                this.showSuccessToast('rejectedApptSuccess');

                const modalEl = document.getElementById('rejectApptModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Optional: reset for next use
                this.rejectMessage = 'Your appointment has been rejected.';
            } catch (err) {
                console.error(err);
                alert('Failed to reject appointment');
            }
        },

        openRescheduleModal(appt) {
            this.selectedApptForReschedule = appt;
            this.rescheduleForm = {
                reschedule_date: '',
                reschedule_time: '',
                response_message: 'Your appointment has been rescheduled to a new date and time.'
            };

            const modalEl = document.getElementById('rescheduleApptModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        },

        async rescheduleAppointment() {
            this.rescheduleSubmitting = true;
            this.inputErrors = {}; // Clear old errors

            try {
                await axios.post(
                    `/api/admin/appointments/${this.selectedApptForReschedule.appointment_reference_number}/reschedule`,
                    this.rescheduleForm
                );

                this.fetchAppointments();
                this.showSuccessToast('rescheduleApptSuccess');

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('rescheduleApptModal'));
                modal.hide();

            } catch (err) {
                if (err.response?.status === 422) {
                    this.inputErrors = err.response.data.errors || {};
                } else {
                    alert(err.response?.data?.message || 'Failed to reschedule appointment');
                }
            } finally {
                this.rescheduleSubmitting = false;
            }
        },

        openDeleteAppointmentModal(appt) {
            this.selectedApptToDelete = appt;
            const modal = new bootstrap.Modal(document.getElementById('deleteApptModal'));
            modal.show();
        },

        async confirmDeleteAppointment() {
            if (!this.selectedApptToDelete) return;

            try {
                await axios.delete(`/api/admin/appointments/${this.selectedApptToDelete.appointment_reference_number}`);

                this.fetchAppointments();

                // Show success toast
                this.showSuccessToast('deletedApptSuccess');

                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteApptModal'));
                modal.hide();

                this.selectedApptToDelete = null;
            } catch (err) {
                alert('Failed to delete appointment');
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

        // DATE & TIME FORMATTING HELPER

        // 11:30 AM
        formatTime_1(time24) {
            if (!time24) return '—';

            const [hours, minutes] = time24.split(':');
            let hour = parseInt(hours, 10);
            const minute = minutes.padStart(2, '0');

            if (isNaN(hour)) return '—';

            const period = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            if (hour === 0) hour = 12;

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

        // 12/02/2025
        formatDate_2(date) {
            if (!date) return '—';

            const d = new Date(date);
            if (isNaN(d)) return '—';

            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const year = d.getFullYear();

            return `${month}/${day}/${year}`;
        },

        // LOGOUT

        confirmLogout() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutConfirmModal'));
            modal.hide();
            this.performLogout();
        },

        performLogout() {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!tokenMeta) {
                alert('CSRF token not found. Cannot logout.');
                return;
            }
            const token = tokenMeta.content;

            fetch('/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else if (response.ok) {
                        window.location.href = '/login';
                    } else {
                        return response.json().then(data => {
                            alert(data.message || 'Logout failed.');
                        });
                    }
                })
                .catch(() => {
                    alert('Network error. Please try again.');
                });
        }
    }
}
</script>

<template>
    <div class="layout">

        <!-- HEADER -->

        <header id="header" class="header">

            <div class="header-left">
                <button id="hamburger-btn" class="hamburger-btn" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#mobileSidebar">
                    <Icon class="icon" icon="mingcute:menu-line" />
                </button>

                <h1 class="title">{{ title }}</h1>
            </div>

            <div class="header-right">

                <!-- Messages button -->
                <button id="messagesBell" type="button" class="btn position-relative" data-bs-toggle="offcanvas"
                    data-bs-target="#messages">

                    <Icon class="icon" icon="mingcute:message-2-line" />

                    <span v-if="unreadMessages > 0"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ unreadMessages > 99 ? '99+' : unreadMessages }}
                    </span>
                </button>

                <!-- Notifications Button -->
                <button id="notificationBell" type="button" class="btn position-relative" data-bs-toggle="offcanvas"
                    data-bs-target="#notifications" aria-controls="notifications">
                    <Icon class="icon" icon="mingcute:notification-line" />
                    <span v-if="unreadNotification > 0"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ unreadNotification }}
                    </span>
                </button>

                <!-- Appointment Button -->
                <button type="button" class="btn position-relative" data-bs-toggle="offcanvas"
                    data-bs-target="#appointments">
                    <Icon class="icon" icon="mingcute:calendar-line" />
                    <span v-if="pendingAppointments > 0"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ pendingAppointments }}
                    </span>
                </button>

                <!-- Profile Dropdown -->
                <div class="dropdown profile-dropdown">
                    <button type="button" class="btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <Icon class="icon" icon="mingcute:user-2-fill" />
                    </button>

                    <ul class="dropdown-menu profile-dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="/admin/profile.php">
                                <Icon class="icon" icon="mingcute:user-1-line" />
                                <p>View Profile</p>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/admin/settings.php">
                                <Icon class="icon" icon="mingcute:settings-1-line" />
                                <p>Settings</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Desktop Sidebar -->

        <aside id="desktopSidebar" class="desktopSidebar">
            <ul class="nav">
                <img class="logo" src="../../../public/images/dnscfindr-logo-light.png" alt="logo">

                <li class="nav-item">
                    <a class="nav-link" :class="isActive('/dashboard')" href="/dashboard">
                        <Icon class="icon" icon="mingcute:grid-2-fill" />
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" :class="isActive('/posts')" href="/posts">
                        <Icon class="icon" icon="mingcute:album-2-fill" />
                        View Posts
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" :class="isActive('/manage-posts')" href="/manage-posts">
                        <Icon class="icon" icon="mingcute:album-2-fill" />
                        Manage Posts
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" :class="isActive('/feedbacks')" href="/feedbacks">
                        <Icon class="icon" icon="mingcute:star-fill" />
                        View Feedbacks & Ratings
                    </a>
                </li>
            </ul>

            <ul class="nav">
                <li class="nav-item">
                    <a href="#" class="nav-link create-post" data-bs-toggle="modal" data-bs-target="#createPostModal">
                        <Icon class="icon" icon="material-symbols:add-2" />
                        Create Post
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" :class="isActive('/manage-users')" href="/manage-users">
                        <Icon class="icon" icon="mingcute:user-2-fill" />
                        Manage Users
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link logout" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                        <Icon class="icon" icon="material-symbols:logout" />
                        Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Mobile Sidebar -->

        <div id="mobileSidebar" class="offcanvas offcanvas-start">
            <div class="mobileSidebar offcanvas-body">
                <ul class="nav">
                    <img class="logo" src="../../../public/images/dnscfindr-logo-light.png" alt="logo">

                    <li class="nav-item">
                        <a class="nav-link" :class="isActive('/dashboard')" href="/dashboard">
                            <Icon class="icon" icon="mingcute:grid-2-fill" />
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" :class="isActive('/posts')" href="/posts">
                            <Icon class="icon" icon="mingcute:album-2-fill" />
                            View Posts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" :class="isActive('/manage-posts')" href="/manage-posts">
                            <Icon class="icon" icon="mingcute:album-2-fill" />
                            Manage Posts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" :class="isActive('/feedbacks')" href="/feedbacks">
                            <Icon class="icon" icon="mingcute:star-fill" />
                            View Feedbacks & Ratings
                        </a>
                    </li>
                </ul>

                <ul class="nav">
                    <li class="nav-item">
                        <a href="#" class="nav-link create-post" data-bs-toggle="modal"
                            data-bs-target="#createPostModal">
                            <Icon class="icon" icon="material-symbols:add-2" />
                            Create Post
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" :class="isActive('/manage-users')" href="/manage-users">
                            <Icon class="icon" icon="mingcute:user-2-fill" />
                            Manage Users
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link logout" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                            <Icon class="icon" icon="material-symbols:logout" />
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Message Offcanvas -->

        <div class="offcanvas offcanvas-end" id="messages" tabindex="-1">

            <!-- Header -->
            <div class="offcanvas-header">
                <h4>
                    <Icon class="avatar" icon="mingcute:chat-3-fill" />
                    Messages
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <!-- Body -->
            <div class="offcanvas-body p-0">

                <div v-if="!selectedChat" class="header-actions">
                    <button @click="toggleUserList">
                        <i class="bi bi-pencil-square"></i>
                        {{ showUserList ? 'Back to Chats' : 'New Message' }}
                    </button>

                    <input v-if="showUserList" v-model="userSearch" @input="fetchUsers(1)" type="text"
                        placeholder="Search users..." class="mt-2">
                </div>

                <div v-if="showUserList" class="user-list">

                    <!-- Loading Spinner -->
                    <div v-if="loadingUsers" class="small-loading-container">
                        <div class="spinner"></div>
                        <p class="loading-text">Loading users...</p>
                    </div>

                    <!-- No results -->
                    <div v-else-if="!loadingUsers && users.data.length === 0" class="empty-state text-center py-5">
                        <p>No users found</p>
                    </div>

                    <!-- Users List -->
                    <div v-else v-for="user in users.data" :key="user.user_id" @click="startChat(user)"
                        class="user-item">
                        <Icon class="avatar" icon="mingcute:user-4-fill" />
                        <div class="user-info">
                            <strong>{{ user.username }}</strong>
                            <small>{{ user.usertype }}</small>
                        </div>
                    </div>

                </div>

                <div v-else-if="!selectedChat" class="conversations">
                    <div v-if="conversations.length === 0" class="empty-state-sm">
                        <Icon class="icon" icon="mingcute:empty-box-fill" />
                        <p>No conversations yet</p>
                    </div>

                    <!-- Conversations list -->
                    <div v-for="conv in conversations" :key="conv.partner.user_id" @click="openChat(conv.partner, conv)"
                        class="conversation-item">
                        <div class="conversation-avatar">
                            <Icon class="avatar" icon="mingcute:user-4-fill" />
                        </div>

                        <div class="conversation-info">
                            <strong>{{ conv.display_name }}</strong>

                            <!-- bold the last message when unread -->
                            <p v-if="conv.unread_count > 0">
                                <strong>{{ conv.last_message || 'Start chatting...' }}</strong>
                            </p>
                            <p v-else>
                                {{ conv.last_message || 'Start chatting...' }}
                            </p>
                        </div>

                        <small class="conversation-time">{{ formatMessageDateTime(conv.last_time) }}</small>
                    </div>

                </div>

                <div v-else class="active-chat">

                    <div class="chat-header">
                        <Icon class="icon" icon="mingcute:arrow-left-line" @click="closeChat" />
                        <Icon class="avatar" icon="mingcute:user-4-fill" />
                        <div class="chat-info">
                            <strong>{{ selectedChat.username }}</strong>
                            <small>{{ selectedChat.usertype }}</small>
                        </div>
                    </div>

                    <div ref="chatContainer" class="messages-area">
                        <div v-for="msg in chatMessages" :key="msg.id"
                            :class="msg.is_sender ? 'message-sent' : 'message-received'">
                            <div class="bubble">{{ msg.message }}</div>
                            <small class="message-time">{{ formatMessageDateTime(msg.created_at) }}</small>
                        </div>

                        <div v-if="chatMessages.length === 0" class="empty-state-sm">
                            <Icon class="icon" icon="mingcute:chat-3-fill" />
                            <p>Start the conversation!</p>
                        </div>
                    </div>

                    <form @submit.prevent="sendMessage" class="send-message-form">
                        <textarea v-model="newMessage" @keydown.enter.exact.prevent="sendMessage"
                            @keydown.enter.shift.exact="newMessage += '\n'" rows="1" placeholder="Type a message..."
                            required>
                        </textarea>
                        <button type="submit" :disabled="sending || !newMessage.trim()">
                            <Icon class="icon" icon="mingcute:send-fill" />
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Offcanvas -->

        <div class="offcanvas offcanvas-end" id="notifications" @show.bs.offcanvas="fetchNotifications">

            <!-- Header -->
            <div class="offcanvas-header">
                <h4>
                    <Icon class="avatar" icon="mingcute:notification-fill" />
                    Notifications
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <!-- Body -->
            <div class="offcanvas-body">
                <div class="tools" v-if="!selectedNotification">
                    <h6>
                        Unread Notifications ({{ unreadNotification }}/{{ notifications.length }})
                    </h6>

                    <div class="action-buttons">
                        <!-- Delete All -->
                        <button type="button" class="btn btn-sm" data-bs-toggle="tooltip"
                            title="Delete All Notifications" @click="showDeleteAllNotificationsModal">
                            <Icon class="icon" icon="mingcute:delete-3-fill" />
                        </button>

                        <!-- Mark All as Read -->
                        <button type="button" class="btn btn-sm" data-bs-toggle="tooltip" title="Mark All as Read"
                            @click="markAllRead">
                            <Icon class="icon" icon="mingcute:eye-2-fill" />
                        </button>
                    </div>
                </div>

                <!-- Notification list -->
                <div v-if="!selectedNotification">

                    <!-- Empty -->
                    <div v-if="notifications.length === 0" class="empty-state-sm">
                        <Icon class="icon" icon="mingcute:empty-box-fill" />
                        <p>No notifications yet</p>
                    </div>

                    <div v-else class="notification-list">
                        <div v-for="notif in notifications" :key="notif.id" class="notification-item"
                            :class="{ 'not-read': !notif.is_read, 'read': notif.is_read }"
                            @click="openNotification(notif)">

                            <!-- Title -->
                            <div class="notification-title">
                                <h6 class="title">
                                    {{ notif.title || 'Notification' }}
                                </h6>
                            </div>

                            <!-- Message preview -->
                            <small class="preview" v-html="formatNotificationMessage(notif.message)"></small>

                            <!-- Other Info -->
                            <div class="other-info">
                                <small>{{ formatNotificationDateTime(notif.created_at) }}</small>
                                <button class="btn btn-sm" data-bs-toggle="tooltip" title="Delete Notification"
                                    @click.stop.prevent="showDeleteNotificationModal(notif)">
                                    <Icon class="icon" icon="mingcute:delete-3-fill" />
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Expanded notification view -->
                <div v-else class="expanded-view">
                    <div class="tools">
                        <!-- Back -->
                        <button class="btn btn-sm" @click="selectedNotification = null">
                            <Icon class="icon" icon="mingcute:left-fill" />
                            Back
                        </button>

                        <!-- Delete -->
                        <button class="btn btn-sm" @click="showDeleteNotificationModal(selectedNotification)">
                            <Icon class="icon" icon="mingcute:delete-3-fill" />
                            Delete
                        </button>
                    </div>

                    <div class="content">
                        <h6 class="title">{{ selectedNotification.title }}</h6>

                        <small class="mb-3" v-html="formatNotificationMessage(selectedNotification.message)"></small>
                        <small class="text-muted">
                            {{ formatNotificationDateTime(selectedNotification.created_at) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Offcanvas -->

        <div class="offcanvas offcanvas-end" id="appointments" tabindex="-1">

            <!-- Header -->
            <div class="offcanvas-header">
                <h4>
                    <Icon class="avatar" icon="mingcute:calendar-fill" />
                    Appointments
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <!-- Body -->
            <div class="offcanvas-body">
                <div class="appointment-list">

                    <!-- Empty -->
                    <div v-if="appointments.length === 0" class="empty-state-sm">
                        <Icon class="icon" icon="mingcute:empty-box-fill" />
                        <p>No appointments yet</p>
                    </div>

                    <!-- Appointment Items -->
                    <div v-for="appt in appointments" :key="appt.appointment_reference_number" class="appointment-item">

                        <!-- Item Header -->
                        <div class="item-header">
                            <P>{{ appt.appointment_reference_number }}</P>
                            <div>
                                <span class="badge" :class="{
                                    'pending': appt.status === 'Pending',
                                    'approved': appt.status === 'Approved',
                                    'rejected': appt.status === 'Rejected',
                                    'rescheduled': appt.status === 'Rescheduled'
                                }">
                                    {{ appt.status }}
                                </span>
                                <small class="text-muted">{{ formatDate_2(appt.sent_at) }}</small>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="item-details">
                            <small>
                                <span>User Name: </span>
                                {{ appt.user.firstname }} {{ appt.user.middlename }} {{ appt.user.lastname }}
                            </small>
                            <small>
                                <span>User ID: </span>
                                {{ appt.user.user_id }}
                            </small>
                            <small>
                                <span>Purpose: </span>
                                {{ appt.purpose }}
                            </small>
                            <small>
                                <span>Schedule Date: </span>
                                {{ formatDate_1(appt.schedule_date) }} at {{ formatTime_1(appt.schedule_time) }}
                            </small>
                            <small v-if="appt.status === 'Rescheduled'">
                                <span>Reschedule Date: </span>
                                {{ formatDate_1(appt.reschedule_date) }} at {{ formatTime_1(appt.reschedule_time) }}
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="appt.status === 'Pending'" class="action-buttons">
                            <button @click="openApproveModal(appt)" class="btn btn-sm approve-btn">
                                Approve
                            </button>

                            <button @click="openRescheduleModal(appt)" class="btn btn-sm reschedule-btn">
                                Reschedule
                            </button>
                            <button @click="openRejectModal(appt)" class="btn btn-sm reject-btn">
                                Reject
                            </button>
                        </div>

                        <!-- Response Message -->
                        <div v-if="appt.response_message" class="response-message">
                            <small>
                                <span>Response:</span> {{ appt.response_message }}
                            </small>
                        </div>

                        <!-- Delete Button (!Pending) -->
                        <div>
                            <button v-if="appt.status !== 'Pending'" @click="openDeleteAppointmentModal(appt)"
                                class="btn btn-sm delete-appointment-btn">
                                <Icon class="icon" icon="mingcute:delete-3-fill" />
                                Delete Appointment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->

        <div class="modal fade primary-modal" id="approveApptModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">


                    <div class="modal-header">
                        <h5 class="modal-title">Approve Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div>
                            <label>Approval Message</label>
                            <textarea v-model="approveMessage" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button @click="confirmApproveAppointment" class="btn btn-sm primary-btn">
                                Approve Appointment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Approved App -->
        <div class="success-toast">
            <div id="approvedApptSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-content">
                    <div class="toast-body">
                        <Icon class="icon" icon="mingcute:check-circle-fill" />
                        Appointment Approved Successfully!
                    </div>
                    <button type="button" class="close-btn" data-bs-dismiss="toast">
                        <Icon class="icon" icon="mingcute:close-line" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div class="modal fade primary-modal" id="rescheduleApptModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">Reschedule Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">

                        <!-- New Date -->
                        <div class="mt-3">
                            <label class="form-label">New Date <span class="text-danger">*</span></label>
                            <input type="date" v-model="rescheduleForm.reschedule_date" class="form-control"
                                :min="new Date().toISOString().split('T')[0]" />
                            <p v-if="inputErrors.reschedule_date" class="error-text mt-1">
                                {{ inputErrors.reschedule_date[0] }}
                            </p>
                        </div>

                        <!-- New Time -->
                        <div class="mt-3">
                            <label class="form-label">New Time <span class="text-danger">*</span></label>
                            <input type="time" v-model="rescheduleForm.reschedule_time" class="form-control" />
                            <p v-if="inputErrors.reschedule_time" class="error-text mt-1">
                                {{ inputErrors.reschedule_time[0] }}
                            </p>
                        </div>

                        <!-- Message to Student -->
                        <div class="mt-3">
                            <label class="form-label">Message to Student <span class="text-danger">*</span></label>
                            <textarea v-model="rescheduleForm.response_message" class="form-control" rows="4"
                                placeholder="Let the student know why you're rescheduling..."></textarea>
                            <p v-if="inputErrors.response_message" class="error-text mt-1">
                                {{ inputErrors.response_message[0] }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="action-buttons mt-4">
                            <button @click="rescheduleAppointment"
                                class="btn primary-btn d-flex align-items-center justify-content-center gap-2"
                                :disabled="rescheduleSubmitting">
                                <span v-if="rescheduleSubmitting" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>

                                <span v-if="rescheduleSubmitting">
                                    Saving...
                                </span>
                                <span v-else>
                                    Save New Schedule
                                </span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Success Reschedule App -->
        <div class="success-toast">
            <div id="rescheduleApptSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-content">
                    <div class="toast-body">
                        <Icon class="icon" icon="mingcute:check-circle-fill" />
                        Appointment Rescheduled Successfully!
                    </div>
                    <button type="button" class="close-btn" data-bs-dismiss="toast">
                        <Icon class="icon" icon="mingcute:close-line" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->

        <div class="modal fade primary-modal" id="rejectApptModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label>Rejection Reason</label>
                            <textarea v-model="rejectMessage" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm secondary-btn"
                                data-bs-dismiss="modal">Cancel</button>
                            <button @click="rejectAppointment" class="btn btn-sm primary-btn">Reject
                                Appointment</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Rejected App -->
        <div class="success-toast">
            <div id="rejectedApptSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-content">
                    <div class="toast-body">
                        <Icon class="icon" icon="mingcute:check-circle-fill" />
                        Appointment Rejected Successfully!
                    </div>
                    <button type="button" class="close-btn" data-bs-dismiss="toast">
                        <Icon class="icon" icon="mingcute:close-line" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Appointment Confirmation Modal -->

        <div class="modal fade danger-modal" id="deleteApptModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <Icon icon="mingcute:delete-3-fill" class="me-2" />
                            Delete Appointment
                        </h5>
                    </div>

                    <div class="modal-body">
                        <p class="normal-text">Are you sure you want to delete this appointment?</p>
                        <p class="danger-text">
                            Reference:
                            <strong>{{ selectedApptToDelete?.appointment_reference_number }}</strong>
                            <br>
                            User:
                            <strong>
                                {{ selectedApptToDelete?.user?.firstname }}
                                {{ selectedApptToDelete?.user?.lastname }}
                            </strong>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">
                                No
                            </button>
                            <button @click="confirmDeleteAppointment" class="btn btn-sm btn-danger">
                                Yes, Delete Permanently
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Delete App -->
        <div class="success-toast">
            <div id="deletedApptSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-content">
                    <div class="toast-body">
                        <Icon class="icon" icon="mingcute:check-circle-fill" />
                        Appointment Deleted Successfully!
                    </div>
                    <button type="button" class="close-btn" data-bs-dismiss="toast">
                        <Icon class="icon" icon="mingcute:close-line" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Specific Notification Modal -->

        <div class="modal fade danger-modal" id="deleteNotificationModal" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <Icon class="icon" icon="mingcute:delete-3-fill" />
                            Delete Notification
                        </h5>
                    </div>

                    <div class="modal-body">
                        <p class="normal-text">Are you sure you want to delete this notification?</p>
                        <p class="danger-text">{{ notificationToDelete?.title }}</p>
                    </div>

                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm primary-btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-sm secondary-btn"
                                @click="confirmDeleteNotification">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Delete -->
        <div class="success-toast">
            <div id="deletedSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-content">
                    <div class="toast-body">
                        <Icon class="icon" icon="mingcute:check-circle-fill" />
                        Notification Deleted Successfully!
                    </div>
                    <button type="button" class="close-btn" data-bs-dismiss="toast">
                        <Icon class="icon" icon="mingcute:close-line" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete All Notifications Modal -->

        <div class="modal fade danger-modal" id="deleteAllNotificationsModal" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <Icon class="icon" icon="mingcute:delete-3-fill" />
                            Delete All Notifications
                        </h5>
                    </div>

                    <div class="modal-body">
                        <p class="normal-text">Are you sure you want to delete all notifications?</p>
                        <p class="danger-text">This action cannot be undone.</p>

                    </div>

                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm primary-btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-sm secondary-btn"
                                @click="confirmDeleteAllNotifications">Delete
                                All</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Delete All -->
        <div class="success-toast">
            <div id="deletedAllSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-content">
                    <div class="toast-body">
                        <Icon class="icon" icon="mingcute:check-circle-fill" />
                        All Notification Deleted Successfully!
                    </div>
                    <button type="button" class="close-btn" data-bs-dismiss="toast">
                        <Icon class="icon" icon="mingcute:close-line" />
                    </button>
                </div>
            </div>
        </div>

        <!-- CREATE POST MODAL -->

        <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- HEADER -->
                    <div class="modal-header">
                        <h5>
                            <Icon class="icon me-2" icon="material-symbols:add-2" />
                            Create New Post
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form @submit.prevent="submitPost">

                            <!-- IMAGE UPLOAD -->
                            <div class="image-card">
                                <div v-if="isUploadingImage" class="placeholder-loading">
                                    <div class="spinner-border spinner-border-sm"></div>
                                    <p>Uploading image... Please wait.</p>
                                </div>
                                <div v-if="!previewImage" class="placeholder-text">
                                    Select an Image
                                </div>
                                <img v-if="previewImage" :src="previewImage" class="image-preview" />
                            </div>

                            <span v-if="inputErrors.photo" class="input-error">{{ inputErrors.photo[0] }}</span>

                            <label class="btn select-btn primary-btn mt-2">
                                Select Image
                                <input type="file" accept="image/*" @change="onFileChange" hidden>
                            </label>

                            <!-- ITEM NAME -->
                            <div class="mt-3">
                                <label class="form-label">
                                    Item Name <span class="text-danger">*</span>
                                    <span v-if="inputErrors.item_name" class="input-error">{{
                                        inputErrors.item_name[0]
                                    }}</span>
                                </label>
                                <input type="text" v-model="form.item_name" class="form-control" />
                            </div>

                            <!-- DESCRIPTION -->
                            <div class="mt-3">
                                <label class="form-label">
                                    Description <span class="text-danger">*</span>
                                    <span v-if="inputErrors.description" class="input-error">{{
                                        inputErrors.description[0]
                                    }}</span>
                                </label>
                                <textarea v-model="form.description" class="form-control" rows="5"
                                    placeholder="Color, brand, size, special marks..."></textarea>
                            </div>

                            <!-- CATEGORY -->
                            <div class="mt-3">
                                <label class="form-label">
                                    Category <span class="text-danger">*</span>
                                    <span v-if="inputErrors.item_category" class="input-error">{{
                                        inputErrors.item_category[0]
                                    }}</span>
                                </label>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle w-100 text-start" data-bs-toggle="dropdown">
                                        {{ form.item_category || 'Select Category' }}
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'ID / Student Card'">ID /
                                                Student
                                                Card</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'Electronics'">Electronics</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'Documents'">Documents</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'Accessories'">Accessories</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'Clothing'">Clothing</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'Bags / Wallets'">Bags /
                                                Wallets</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                @click.prevent="form.item_category = 'Others'">Others</a></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- LOCATION FOUND -->
                            <div class="mt-3">
                                <label class="form-label">
                                    Location Found <span class="text-danger">*</span>
                                    <span v-if="inputErrors.location_found" class="input-error">{{
                                        inputErrors.location_found[0]
                                    }}</span>
                                </label>
                                <input type="text" v-model="form.location_found" class="form-control" />
                            </div>

                            <!-- DATE FOUND -->
                            <div class="mt-3">
                                <label class="form-label">
                                    Date Found <span class="text-danger">*</span>
                                    <span v-if="inputErrors.date_found" class="input-error">{{
                                        inputErrors.date_found[0]
                                    }}</span>
                                </label>
                                <input type="date" v-model="form.date_found" class="form-control" />
                            </div>

                            <!-- TIME FOUND -->
                            <div class="mt-3">
                                <label class="form-label">
                                    Time Found <span class="text-danger">*</span>
                                    <span v-if="inputErrors.time_found" class="input-error">{{
                                        inputErrors.time_found[0]
                                    }}</span>
                                </label>
                                <input type="time" v-model="form.time_found" class="form-control" />
                            </div>

                            <!-- ACTION BUTTONS -->
                            <div class="action-buttons mt-4">
                                <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn primary-btn" :disabled="submitting">
                                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ submitting ? 'Creating...' : 'Create Post' }}
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- CREATE POST SUCCESS MODAL -->

        <div class="modal fade success-modal" id="successPostModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <Icon class="icon" icon="mingcute:check-circle-fill" />
                            Post Created Successfully!
                        </h5>
                    </div>

                    <div class="modal-body">
                        <p>Your found item is now posted.</p>
                    </div>

                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn primary-btn" data-bs-dismiss="modal">
                                Okay
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Logout Confirmation Modal -->

        <div class="modal fade danger-modal" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <Icon class="icon" icon="material-symbols:logout" />
                            Confirm Logout
                        </h5>
                    </div>

                    <div class="modal-body">
                        <p class="normal-text">Are you sure you want to log out of your account?</p>
                        <p class="danger-text">
                            You will be redirected to the login page.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm secondary-btn" data-bs-dismiss="modal">
                                No
                            </button>

                            <button type="button" class="btn btn-sm primary-btn" @click="confirmLogout">
                                Yes, Log Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->

        <main id="main-content" class="main-content p-4">
            <slot />
        </main>

        <!-- Footer -->

        <footer id="footer" class="footer">

            <div class="row">

                <div class="col">
                    <img class="logo" src="../../../public/images/dnscfindr-logo-dark.png" alt="logo">
                    <div>
                        <p>Davao del Norte State College</p>
                        <p>Lost & Found System</p>
                    </div>
                </div>

                <div class="col">
                    <p>Links</p>
                    <ul>
                        <li><a href="https://www.dnsc.edu.ph" target="_blank">DNSC Official Website</a></li>
                        <li><a href="https://www.dnsc.edu.ph/privacy-policy/" target="_blank">Privacy Policy</a></li>
                        <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSct44Weo1ea8Zj0m7gxb-ByQ6YWoah8FCS9t7Rk6Ey4nodhXg/viewform"
                                target="_blank">Client Feedback</a></li>
                    </ul>
                </div>

                <div class="col">
                    <p>Help</p>
                    <ul>
                        <li><a href="/about">About</a></li>
                        <li><a href="/how-it-works">How it Works</a></li>
                    </ul>
                </div>
            </div>

            <p class="copy">&copy; {{ new Date().getFullYear() }} <strong>DNSC Findr</strong>. All rights reserved.</p>

        </footer>
    </div>
</template>

<style scoped>
.icon {
    width: 28px;
    height: 28px;
}

@media (min-width: 1025px) {
    .main-content {
        margin-left: 280px;
    }

    .header {
        margin-left: 280px;
    }

    .header .header-left .hamburger-btn {
        display: none;
    }

    .footer {
        margin-left: 280px;
    }
}


@media (max-width: 768px) {
    aside {
        display: none !important;
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    aside {
        display: none !important;
    }
}

/* LAYOUT */

.layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* HEADER */

.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    padding: 0 24px;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0 0 16px 16px;
    background-color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header .header-left {
    display: flex;
    gap: 12px;
}

.header .header-left .title {
    font-size: 28px;
    font-weight: 500;
    margin: 0;
    color: #3C3C3E;
}

.header .header-left .hamburger-btn {
    border: none;
    outline: none;
    padding: 0;
    margin: 0;
    background-color: transparent;
}

.header .header-right {
    display: flex;
    gap: 12px;
}

.header .header-right .btn {
    outline: none;
    border: none;
    padding: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.header .header-right .btn .icon {
    width: 24px;
    height: 24px;
}

.header .header-right .profile-dropdown>.btn {
    outline: none;
    border: none;
    padding: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #48c441;
    border-radius: 99px;
    transition: all 0.4s ease;
}

.header .header-right .profile-dropdown>.btn:hover {
    background-color: #3ca237;
}

.header .header-right .profile-dropdown .profile-dropdown-menu {
    background-color: #ffffff;
    outline: none;
    padding: 8px 8px 4px 8px;
    border-radius: 12px;
    border: 1px solid #48c441;
    margin-top: 8px !important;
}

.header .header-right .profile-dropdown .profile-dropdown-menu .dropdown-item {
    font-weight: 500;
    border-radius: 8px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 24px;
}

.header .header-right .profile-dropdown .profile-dropdown-menu .dropdown-item:hover,
.header .header-right .profile-dropdown .profile-dropdown-menu .dropdown-item:active {
    background-color: #3ca237;
    color: #f2f3f7;
}

/* SIDEBAR*/

#mobileSidebar {
    width: 280px !important;
}

.desktopSidebar,
.mobileSidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background-color: #282829;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 24px;
    overflow-y: auto;
    overflow-x: hidden;
}

.desktopSidebar ul,
.mobileSidebar ul {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.desktopSidebar ul .logo,
.mobileSidebar ul .logo {
    width: 100%;
    margin-bottom: 24px;
}

.desktopSidebar ul li a,
.mobileSidebar ul li a {
    padding: 0 !important;
    color: #fbfbfb !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    background-color: #3C3C3E !important;
    padding: 8px !important;
    border-radius: 16px !important;
    transition: all 0.4s ease !important;
}

.desktopSidebar ul li a:hover,
.mobileSidebar ul li a:hover {
    background-color: #48c441 !important;
    color: #3C3C3E !important;
}

.desktopSidebar .nav-link.active,
.mobileSidebar .nav-link.active {
    background-color: #48c441 !important;
    color: #3C3C3E !important;
}

.desktopSidebar ul li a.logout,
.mobileSidebar ul li a.logout {
    background-color: rgb(215, 0, 0) !important;
}

.desktopSidebar ul li a.logout:hover,
.mobileSidebar ul li a.logout:hover {
    background-color: rgb(200, 0, 0) !important;
}

.desktopSidebar ul li a.create-post,
.mobileSidebar ul li a.create-post {
    border: 3px solid #48c441 !important;
    padding: 7px !important;
}

.desktopSidebar ul li a.create-post:hover .mobileSidebar ul li a.create-post:hover {
    background-color: #48c441 !important;
}

/* MAIN CONTENT */
.main-content {
    flex: 1;
    margin-top: 60px;
    background-color: #f2f3f7;
    padding: 24px;
    min-height: calc(100vh - 60px);
    box-sizing: border-box;
}

/* FOOTER */
.footer {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 24px;
    border-radius: 16px 16px 0 0;
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    color: #333;
}

.footer .row {
    display: flex;
    justify-content: space-between;
    gap: 24px;
}

.footer .col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer .col:first-child .logo {
    width: 250px;
}

.footer .col:first-child div {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.footer .col:first-child div p:nth-child(1) {
    font-weight: 600;
}

.footer .col:first-child div p:nth-child(2) {
    font-weight: 400;
}

.footer .col:nth-child(2) p,
.footer .col:nth-child(3) p {
    font-weight: 600;
}

.footer .col:nth-child(2) ul,
.footer .col:nth-child(3) ul {
    list-style: none;
    padding: 0;
}

.footer .col:nth-child(2) li a,
.footer .col:nth-child(3) li a {
    text-decoration: none;
    color: #333;
}

.footer .col:nth-child(2) li a:hover,
.footer .col:nth-child(3) li a:hover {
    text-decoration: underline;
}

.footer .copy {
    text-align: left;
}

@media (max-width: 1024px) {
    .footer .row {
        justify-content: center;
        flex-wrap: wrap;
        text-align: center;
    }

    .footer .col {
        align-items: center;
    }

    .footer .copy {
        text-align: center;
    }
}

@media (max-width: 600px) {
    .footer .row {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer .col {
        align-items: center;
    }

    .footer .copy {
        text-align: center;
    }
}
</style>
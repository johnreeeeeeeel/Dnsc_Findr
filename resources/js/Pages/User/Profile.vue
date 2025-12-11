<template>
    <UserAppLayout title="Profile">
        <div class="container-fluid layout">

            <div v-if="user" class="card profile-card">

                <div class="icon-container">
                    <Icon class="icon" icon="mingcute:user-4-fill" />

                    <!-- Usertype Badge on Icon -->
                    <span class="user-type-badge badge" :class="user.usertype?.toLowerCase() || 'user'">
                        {{ (user.usertype || 'user').charAt(0).toUpperCase() }}
                    </span>
                </div>

                <!-- STUDENT: No null -->
                <h5 v-if="user.usertype, user.fullname" class="fullname">{{ user.fullname }}</h5>

                <code v-if="user.username, user.user_id" class="username-id">@{{ user.username }} | {{ user.user_id }}
                </code>

                <hr>

                <p v-if="user.email" class="email">{{ user.email }}</p>

                <p v-if="user.sex" class="sex"><span>Sex:</span> {{ user.sex }}</p>
                <p v-if="user.dob" class="dob"><span>Date of Birth:</span> {{ formatDate_1(user.dob) }}</p>

                <p v-if="user.institute" class="institute"><span>Institute:</span> {{ user.institute }}</p>
                <p v-if="user.program" class="program"><span>Program:</span> {{ user.program }}</p>

                <!-- INSTRUCTOR / STAFF: only institute & program may be null -->

                <template v-else-if="user.usertype === 'instructor' || user.usertype === 'staff'">
                    <p v-if="user.institute" class="institute"><span>Institute:</span> {{ user.institute }}</p>
                    <p v-if="user.program" class="program"><span>Program:</span> {{ user.program }}</p>
                </template>

                <!-- ADMIN: only username & email -->
                <template v-else-if="user.usertype === 'admin'">
                    <code v-if="user.username, user.user_id" class="username-id">
                        @{{ user.username }} | {{ user.user_id }}
                    </code>
                    <p v-if="user.email" class="email">{{ user.email }}</p>
                </template>
            </div>

            <!-- Loading -->
            <div v-else="loading" class="loading">
                <div class="spinner-border" role="status"></div>
                <div class="loading-text">Loading profile...</div>
            </div>
        </div>
    </UserAppLayout>
</template>

<script>
import UserAppLayout from '../../Layouts/UserAppLayout.vue';

export default {
    name: "UserProfile",
    components: { UserAppLayout },

    data() {
        return {
            user: null,
        };
    },

    mounted() {
        this.fetchUserInfo();
    },

    methods: {
        async fetchUserInfo() {
            try {
                const res = await fetch('/api/user-info');
                const data = await res.json();
                this.user = data;
            } catch (error) {
                console.error("Error fetching user data:", error);
            }
        },

        formatDate_1(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },
    },
};
</script>

<style scoped>
.layout {
    font-family: 'Montserrat', sans-serif;
    background-color: #f2f3f7 !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 24px;
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
</style>

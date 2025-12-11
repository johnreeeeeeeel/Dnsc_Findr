<template>
  <div class="container-fluid layout">

    <!-- Header -->
    <div class="header">
      <a onclick="history.back()" class="back-link">
        <Icon class="icon" icon="mingcute:left-line" />
        Back
      </a>
    </div>

    <!-- Body -->
    <div class="body">
      <h1 class="title">What is DNSC Findr?</h1>

      <div class="about-row row">
        <div class="col about-col">
          <p class="text-muted">
            <strong>DNSC FINDR</strong> is the official Lost and Found System of
            <strong>Davao del Norte State College (DNSC)</strong>, designed to help students,
            instructors, and staff easily report and recover lost belongings within the campus.
          </p>
        </div>
        <div class="col about-col">
          <p class="text-muted">
            <strong>DNSC FINDR</strong> aims to build a trustworthy and efficient community platform,
            reducing the stress of losing items while improving transparency and accessibility.
          </p>
        </div>
      </div>

      <div class="feature-row row row-cols-1 row-cols-md-3 g-5">

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:map-pin-fill" />
          </div>
          <h5 class="fw-semibold">Centralized Posting</h5>
          <p class="text-muted">
            All found item posts are verified by OSDS staff before going public, ensuring accuracy and reliability.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:clipboard-line" />
          </div>
          <h5 class="fw-semibold">Simple Reporting</h5>
          <p class="text-muted">
            Users can report found items online or at the OSDS office, with submissions reviewed by staff before
            publication.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:user-follow-fill" />
          </div>
          <h5 class="fw-semibold">Efficient Claim Management</h5>
          <p class="text-muted">
            Users can submit claims online, which are promptly reviewed and approved by OSDS staff.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:calendar-fill" />
          </div>
          <h5 class="fw-semibold">Appointment Scheduling</h5>
          <p class="text-muted">
            Users can book appointments for reporting or claiming items, reducing conflicts and wait times.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:notification-fill" />
          </div>
          <h5 class="fw-semibold">Real-Time Notifications</h5>
          <p class="text-muted">
            Users receive instant updates about the status of their claims.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:chat-1-fill" />
          </div>
          <h5 class="fw-semibold">Improved Communication</h5>
          <p class="text-muted">
            A safe messaging system allows clear and direct communication between users and OSDS staff.
          </p>
        </div>

      </div>

    </div>
  </div>
</template>

<style scoped>
.layout {
  font-family: 'Montserrat', sans-serif;
  background-color: #f2f3f7 !important;
  display: flex;
  flex-direction: column;
  gap: 48px;
  color: #333;
  height: 100vh;
  padding: 28px;
}

.header {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.header>a {
  text-decoration: none;
  font-size: 18px;
  font-weight: 500;
  margin: 0;
  color: #333;
  transition: all 0.2s ease;
  cursor: pointer;
}

.header>a:hover {
  color: #48c441;
}

.header>a>.icon {
  width: 28px;
  height: 28px;
  margin: 0;
}

.body {
  display: flex;
  flex-direction: column;
  gap: 32px;
  animation: fadeSlideUp 0.7s ease-out forwards;
}

@keyframes fadeSlideUp {
  0% {
    opacity: 0;
    transform: translateY(15px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.body .title {
  font-size: 34px;
  font-weight: 600;
}

.body .about-row {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  align-items: center;
  gap: 12px;
}

.body .about-row .about-col {
  flex: 1 480px;
  min-height: 180px;
  background-color: transparent;
  border: 2px solid #48c441;
  padding: 34px;
  border-radius: 10px;
  animation: neonGlow 1.5s ease-in-out infinite alternate;
}

@keyframes neonGlow {
  from {
    box-shadow: 0 0 3px #48c441;
  }

  to {
    box-shadow: 0 0 10px #48c441;
  }
}

.body .about-row .about-col p {
  color: #fff;
  text-align: center;
  font-size: 1rem;
  line-height: 1.6;
  margin: 0;
}

.body .feature-row {
  margin-top: 12px;
}

.body .feature-row .col {
  display: flex;
  flex-direction: column;
  align-items: center;
  animation: fadeSlideUp 0.8s ease-out forwards;
}

.body .feature-row .col .feature-icon {
  display: inline-block;
  background-color: #ffffff;
  padding: 25px;
  border-radius: 38px;
  box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
  margin-bottom: 24px;
}

.body .feature-row .col .feature-icon .icon {
  width: 60px;
  height: 60px;
  object-fit: contain;
  transition: all 0.3s ease;
}

.body .feature-row .col .feature-icon:hover {
  background-color: #48c441;
  box-shadow: 0px 0px 35xpx rgba(68, 177, 154, 0.7);
  transform: scale(1.1);
}

.body .feature-row .col .feature-icon:hover .icon {
  filter: brightness(0) invert(1);
}

.body .row .col h5 {
  color: #48c441;
  text-align: center;
  margin-bottom: 12px !important;
}

.body .feature-row .col p {
  text-align: center;
}
</style>

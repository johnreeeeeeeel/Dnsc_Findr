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

      <!-- HOW TO REPORT -->

      <h1 class="title">How to Report Found Items?</h1>

      <!-- OPTION 1 -->

      <h5 class="option-title"><strong>Option 1:</strong> Walk-in Reporting</h5>

      <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-3 row-cols-xl-5 g-5">

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:calendar-fill" />
          </div>
          <h5 class="fw-semibold">Schedule Appointment (Optional)</h5>
          <p class="text-muted">
            Schedule an appointment with OSDS via the system to ensure availability.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:box-fill" />
          </div>
          <h5 class="fw-semibold">Bring Item to OSDS</h5>
          <p class="text-muted">
            Go to the OSDS office and hand the item to the staff on duty.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:clipboard-fill" />
          </div>
          <h5 class="fw-semibold">Provide Complete Details</h5>
          <p class="text-muted">
            Inform OSDS staff where and when you found the item, and any useful identifying details.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:upload-fill" />
          </div>
          <h5 class="fw-semibold">OSDS Records and Uploads Item</h5>
          <p class="text-muted">
            OSDS staff will take a photo, record the item name, category, description, where and when it found, and
            upload it to the DNSC Findr system.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:align-horizontal-center-fill" />
          </div>
          <h5 class="fw-semibold">Item Appears in DNSC Findr</h5>
          <p class="text-muted">
            The item will now be visible in the system for others to view.
          </p>
        </div>
      </div>

      <!-- OPTION 2 -->

      <h5 class="option-title"><strong>Option 2:</strong> System Reporting</h5>

      <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-4 row-cols-xl-4 g-5">

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:upload-fill" />
          </div>
          <h5 class="fw-semibold">Upload Item</h5>
          <p class="text-muted">
            Access the DNSC Findr system and click the "Create Post" button to upload the found item details.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:box-fill" />
          </div>
          <h5 class="fw-semibold">Bring Item to OSDS</h5>
          <p class="text-muted">
            Go to the OSDS office and hand the item to the staff on duty.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:thumb-up-2-fill" />
          </div>
          <h5 class="fw-semibold">Post Approval</h5>
          <p class="text-muted">
            Once the item is in the hands of OSDS, OSDS will approve the post.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:align-horizontal-center-fill" />
          </div>
          <h5 class="fw-semibold">Item Appears in DNSC Findr</h5>
          <p class="text-muted">
            The item will now be visible in the system for others to view.
          </p>
        </div>
      </div>

      <!-- HOW TO CLAIM -->

      <h1 class="title">How to Claim Lost Items?</h1>

      <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-4 row-cols-xl-4 g-5">

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:clipboard-fill" />
          </div>
          <h5 class="fw-semibold">Provide Details</h5>
          <p class="text-muted">
            Once you find your lost item in the system, click the "Claim This Item" button and provide necessary details for verification.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon ">
            <Icon class="icon" icon="mingcute:time-duration-fill" />
          </div>
          <h5 class="fw-semibold">Wait for Verification</h5>
          <p class="text-muted">
            Wait for OSDS to verify your claim. OSDS will either approve or reject your claim and notify you via the system for further steps.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:thumb-up-2-fill" />
          </div>
          <h5 class="fw-semibold">Approved</h5>
          <p class="text-muted">
            Once Approved, schedule an appointment to claim your item at the OSDS office.
          </p>
        </div>

        <div class="col">
          <div class="feature-icon">
            <Icon class="icon" icon="mingcute:thumb-down-2-fill" />
          </div>
          <h5 class="fw-semibold">Rejected</h5>
          <p class="text-muted">
            Once Rejected, you may contact OSDS via system messaging feature for further assistance.
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

.body .row .col {
  display: flex;
  flex-direction: column;
  align-items: center;
  animation: fadeSlideUp 0.8s ease-out forwards;
}

.body .row .col .feature-icon {
  display: inline-block;
  background-color: #ffffff;
  padding: 25px;
  border-radius: 38px;
  box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
  margin-bottom: 24px;
}

.body .row .col .feature-icon .icon {
  width: 60px;
  height: 60px;
  object-fit: contain;
  transition: all 0.3s ease;
}

.body .row .col .feature-icon:hover {
  background-color: #48c441;
  box-shadow: 0px 0px 35xpx rgba(68, 177, 154, 0.7);
  transform: scale(1.1);
}

.body .row .col .feature-icon:hover .icon {
  filter: brightness(0) invert(1);
}

.body .row .col h5 {
  color: #48c441;
  text-align: center;
  margin-bottom: 12px !important;
}

.body .row .col p {
  text-align: center;
}
</style>

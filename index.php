<?php include("header.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Global Hospitals — Register & Login</title>
  <meta name="description" content="Register as a patient or login as a doctor/receptionist at Global Hospitals.">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
  <link rel="stylesheet" href="hms-modern.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
  <style>
    /* Extra page-specific tweaks */
    .form-tab-content { display: none; }
    .form-tab-content.active { display: block; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="hms-navbar">
  <a href="index.php" class="brand">
    <div class="brand-icon"><i class="fa fa-heartbeat"></i></div>
    Global Hospitals
  </a>
  <ul class="nav-links">
    <li><a href="index.php" class="active">Home</a></li>
    <li><a href="services.html">About Us</a></li>
    <li><a href="contact.html">Contact</a></li>
  </ul>
</nav>

<!-- MAIN AUTH PAGE -->
<div class="auth-bg">
  <div class="auth-container">

    <!-- LEFT HERO -->
    <div class="auth-hero">
      <div class="tag">
        <i class="fa fa-shield"></i> Trusted Healthcare Platform
      </div>
      <h1>Your Health,<br>Our Priority</h1>
      <p>Book appointments, track prescriptions, and manage your healthcare journey — all in one place. Trusted by thousands of patients and doctors.</p>
      <div class="auth-stats">
        <div class="stat">
          <div class="stat-number">500+</div>
          <div class="stat-label">Doctors</div>
        </div>
        <div class="stat">
          <div class="stat-number">10K+</div>
          <div class="stat-label">Patients</div>
        </div>
        <div class="stat">
          <div class="stat-number">24/7</div>
          <div class="stat-label">Support</div>
        </div>
      </div>
    </div>

    <!-- RIGHT CARD -->
    <div class="auth-card">
      <div class="card-title">Get Started</div>
      <div class="card-subtitle">Register or login to your account</div>

      <!-- TAB SWITCHER -->
      <div class="tab-switcher" id="mainTabSwitcher">
        <button class="tab-btn active" onclick="switchTab('patient', this)">Patient</button>
        <button class="tab-btn" onclick="switchTab('doctor', this)">Doctor</button>
        <button class="tab-btn" onclick="switchTab('admin', this)">Receptionist</button>
      </div>

      <!-- PATIENT REGISTER TAB -->
      <div class="form-tab-content active" id="tab-patient">
        <form method="post" action="func2.php">
          <div class="form-row">
            <div class="form-group-dark">
              <label class="form-label-dark">First Name *</label>
              <input type="text" class="form-control-dark" placeholder="John" name="fname" onkeydown="return alphaOnly(event);" required />
            </div>
            <div class="form-group-dark">
              <label class="form-label-dark">Last Name *</label>
              <input type="text" class="form-control-dark" placeholder="Doe" name="lname" onkeydown="return alphaOnly(event);" required />
            </div>
          </div>
          <div class="form-group-dark">
            <label class="form-label-dark">Email Address *</label>
            <input type="email" class="form-control-dark" placeholder="john@example.com" name="email" required />
          </div>
          <div class="form-group-dark">
            <label class="form-label-dark">Phone Number *</label>
            <input type="tel" minlength="10" maxlength="10" name="contact" class="form-control-dark" placeholder="10-digit mobile number" />
          </div>
          <div class="form-row">
            <div class="form-group-dark">
              <label class="form-label-dark">Password *</label>
              <input type="password" class="form-control-dark" placeholder="Min 6 chars" id="password" name="password" onkeyup="check();" required />
            </div>
            <div class="form-group-dark">
              <label class="form-label-dark">
                Confirm Password *
                <span id="message" class="password-match"></span>
              </label>
              <input type="password" class="form-control-dark" id="cpassword" placeholder="Repeat password" name="cpassword" onkeyup="check();" required />
            </div>
          </div>
          <div class="form-group-dark">
            <label class="form-label-dark">Gender</label>
            <div class="gender-row">
              <label class="radio-card">
                <input type="radio" name="gender" value="Male" checked>
                <span><i class="fa fa-male"></i> Male</span>
              </label>
              <label class="radio-card">
                <input type="radio" name="gender" value="Female">
                <span><i class="fa fa-female"></i> Female</span>
              </label>
            </div>
          </div>
          <button type="submit" name="patsub1" onclick="return checklen();" class="btn-primary-glow">
            <i class="fa fa-user-plus"></i> &nbsp;Create Account
          </button>
          <div class="auth-link">Already have an account? <a href="index1.php">Sign In</a></div>
        </form>
      </div>

      <!-- DOCTOR LOGIN TAB -->
      <div class="form-tab-content" id="tab-doctor">
        <form method="post" action="func1.php">
          <div class="form-group-dark">
            <label class="form-label-dark">Username</label>
            <input type="text" class="form-control-dark" placeholder="Doctor username" name="username3" required />
          </div>
          <div class="form-group-dark">
            <label class="form-label-dark">Password</label>
            <input type="password" class="form-control-dark" placeholder="Your password" name="password3" required />
          </div>
          <button type="submit" name="docsub1" class="btn-primary-glow">
            <i class="fa fa-sign-in"></i> &nbsp;Login as Doctor
          </button>
        </form>
      </div>

      <!-- RECEPTIONIST/ADMIN LOGIN TAB -->
      <div class="form-tab-content" id="tab-admin">
        <form method="post" action="func3.php">
          <div class="form-group-dark">
            <label class="form-label-dark">Username</label>
            <input type="text" class="form-control-dark" placeholder="Admin username" name="username1" required />
          </div>
          <div class="form-group-dark">
            <label class="form-label-dark">Password</label>
            <input type="password" class="form-control-dark" placeholder="Your password" name="password2" required />
          </div>
          <button type="submit" name="adsub" class="btn-primary-glow">
            <i class="fa fa-sign-in"></i> &nbsp;Login as Receptionist
          </button>
        </form>
      </div>

    </div><!-- /auth-card -->
  </div><!-- /auth-container -->
</div><!-- /auth-bg -->

<script>
  function switchTab(tab, btn) {
    // Deactivate all tabs and buttons
    document.querySelectorAll('.form-tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
  }

  function check() {
    var p = document.getElementById('password').value;
    var c = document.getElementById('cpassword').value;
    var msg = document.getElementById('message');
    if (!c) { msg.innerHTML = ''; return; }
    if (p === c) {
      msg.style.color = '#4ade80';
      msg.innerHTML = '✓ Match';
    } else {
      msg.style.color = '#f87171';
      msg.innerHTML = '✗ No match';
    }
  }

  function alphaOnly(event) {
    var key = event.keyCode;
    return ((key >= 65 && key <= 90) || key == 8 || key == 32);
  }

  function checklen() {
    var pass1 = document.getElementById("password");
    if (pass1.value.length < 6) {
      alert("Password must be at least 6 characters long.");
      return false;
    }
  }
</script>
</body>
</html>
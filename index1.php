<?php include("header.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Global Hospitals — Patient Login</title>
  <meta name="description" content="Sign in to your Global Hospitals patient account.">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
  <link rel="stylesheet" href="hms-modern.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
  <style>
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-12px); }
    }
    .float-anim { animation: float 3s ease-in-out infinite; }
    .features-list { list-style: none; margin: 1.5rem 0; }
    .features-list li {
      display: flex; align-items: center; gap: 0.65rem;
      color: rgba(255,255,255,0.7); font-size: 0.9rem; padding: 0.4rem 0;
    }
    .features-list li .feat-icon {
      width: 28px; height: 28px; border-radius: 8px;
      background: rgba(79,70,229,0.3); border: 1px solid rgba(79,70,229,0.4);
      display: flex; align-items: center; justify-content: center;
      color: #a5b4fc; font-size: 0.75rem; flex-shrink: 0;
    }
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
    <li><a href="index.php">Home</a></li>
    <li><a href="services.html">About Us</a></li>
    <li><a href="contact.html">Contact</a></li>
  </ul>
</nav>

<div class="auth-bg">
  <div class="auth-container">

    <!-- LEFT HERO -->
    <div class="auth-hero">
      <div class="tag">
        <i class="fa fa-lock"></i> Secure Patient Portal
      </div>
      <h1>Welcome<br>Back!</h1>
      <p>Sign in to access your appointments, prescriptions, and complete medical history — securely and instantly.</p>

      <ul class="features-list">
        <li>
          <div class="feat-icon"><i class="fa fa-calendar"></i></div>
          Book &amp; manage appointments online
        </li>
        <li>
          <div class="feat-icon"><i class="fa fa-file-medical" style="font-size:0.7rem"></i></div>
          View prescriptions from your doctor
        </li>
        <li>
          <div class="feat-icon"><i class="fa fa-credit-card"></i></div>
          Download bills &amp; payment records
        </li>
        <li>
          <div class="feat-icon"><i class="fa fa-bell"></i></div>
          Real-time appointment status updates
        </li>
      </ul>

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
    <div class="auth-card" style="max-width:400px;width:100%">
      <div style="text-align:center;margin-bottom:1.75rem">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#4f46e5,#06b6d4);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 8px 24px rgba(79,70,229,0.4)">
          <i class="fa fa-hospital-o" style="font-size:1.5rem;color:white"></i>
        </div>
        <div class="card-title" style="font-size:1.4rem">Patient Sign In</div>
        <div class="card-subtitle">Enter your credentials to continue</div>
      </div>

      <form method="POST" action="func.php">
        <div class="form-group-dark">
          <label class="form-label-dark"><i class="fa fa-envelope" style="margin-right:0.4rem;opacity:0.6"></i>Email Address</label>
          <input type="email" name="email" class="form-control-dark" placeholder="Enter your email" required />
        </div>
        <div class="form-group-dark">
          <label class="form-label-dark"><i class="fa fa-lock" style="margin-right:0.4rem;opacity:0.6"></i>Password</label>
          <input type="password" class="form-control-dark" name="password2" placeholder="Enter your password" required />
        </div>

        <button type="submit" name="patsub" id="inputbtn" class="btn-primary-glow" style="margin-top:1rem">
          <i class="fa fa-sign-in"></i> &nbsp;Sign In to Portal
        </button>
      </form>

      <div class="auth-link" style="margin-top:1.25rem">
        New patient? <a href="index.php">Create an account</a>
      </div>

      <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,0.08)">
        <p style="font-size:0.75rem;color:rgba(255,255,255,0.35);text-align:center;line-height:1.6">
          <i class="fa fa-shield" style="margin-right:0.3rem"></i>
          Your data is encrypted and protected under HIPAA guidelines.
        </p>
      </div>
    </div>

  </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>
</body>
</html>
<?php
session_start();
$con = mysqli_connect("localhost","root","","myhmsdb");
include('newfunc.php');

// ── Add Doctor ──────────────────────────────────────────────────
if(isset($_POST['docsub'])) {
  $q="INSERT INTO doctb(username,password,email,spec,docFees) VALUES('"
    .mysqli_real_escape_string($con,$_POST['doctor'])."','"
    .mysqli_real_escape_string($con,$_POST['dpassword'])."','"
    .mysqli_real_escape_string($con,$_POST['demail'])."','"
    .mysqli_real_escape_string($con,$_POST['special'])."',"
    .(int)$_POST['docFees'].")";
  if(mysqli_query($con,$q)) echo "<script>alert('Doctor added successfully!');</script>";
}

// ── Delete Doctor ───────────────────────────────────────────────
if(isset($_POST['docsub1'])) {
  $de=mysqli_real_escape_string($con,$_POST['demail']);
  if(mysqli_query($con,"DELETE FROM doctb WHERE email='$de'"))
    echo "<script>alert('Doctor removed successfully!');</script>";
  else echo "<script>alert('Unable to delete!');</script>";
}

// ── Add Medicine ────────────────────────────────────────────────
if(isset($_POST['add_medicine'])) {
  $by=isset($_SESSION['username'])?$_SESSION['username']:'admin';
  $q="INSERT INTO medicine_store(name,category,description,price,stock,unit,added_by) VALUES('"
    .mysqli_real_escape_string($con,$_POST['med_name'])."','"
    .mysqli_real_escape_string($con,$_POST['med_category'])."','"
    .mysqli_real_escape_string($con,$_POST['med_desc'])."',"
    .(float)$_POST['med_price'].",".(int)$_POST['med_stock'].",'"
    .mysqli_real_escape_string($con,$_POST['med_unit'])."','$by')";
  if(mysqli_query($con,$q)) echo "<script>alert('Medicine added successfully!');</script>";
  else echo "<script>alert('Error: ".mysqli_error($con)."');</script>";
}

// ── Delete Medicine ─────────────────────────────────────────────
if(isset($_POST['delete_medicine'])) {
  $mid=(int)$_POST['med_id'];
  if(mysqli_query($con,"DELETE FROM medicine_store WHERE med_id=$mid"))
    echo "<script>alert('Medicine deleted!');</script>";
}

// ── Update Stock ────────────────────────────────────────────────
if(isset($_POST['update_stock'])) {
  $mid=(int)$_POST['med_id']; $stk=(int)$_POST['new_stock'];
  mysqli_query($con,"UPDATE medicine_store SET stock=$stk WHERE med_id=$mid");
  echo "<script>alert('Stock updated!');</script>";
}

// ── Update Order Status ─────────────────────────────────────────
if(isset($_POST['update_order_status'])) {
  $oid=(int)$_POST['order_id'];
  $ost=mysqli_real_escape_string($con,$_POST['new_status']);
  mysqli_query($con,"UPDATE medicine_orders SET status='$ost' WHERE order_id=$oid");
  echo "<script>alert('Order status updated!');</script>";
}

// ── Update Operation Status ─────────────────────────────────────
if(isset($_POST['update_op_status'])) {
  $oid=(int)$_POST['op_id'];
  $ost=mysqli_real_escape_string($con,$_POST['op_status']);
  $onotes=mysqli_real_escape_string($con,$_POST['op_notes_update']??'');
  mysqli_query($con,"UPDATE operations SET status='$ost',notes=IF('$onotes'='',notes,'$onotes') WHERE op_id=$oid");
  echo "<script>alert('Operation status updated!');</script>";
}

$adminUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// Summary counts
$cDoctors  = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM doctb"))[0];
$cPatients = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM patreg"))[0];
$cApps     = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM appointmenttb WHERE userStatus=1 AND doctorStatus=1"))[0];
$cQueries  = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM contact"))[0];
$cMeds     = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM medicine_store"))[0];
$cOrders   = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM medicine_orders WHERE status='Pending'"))[0];
$cOps      = mysqli_fetch_row(mysqli_query($con,"SELECT COUNT(*) FROM operations WHERE status='Scheduled'"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Global Hospitals — Admin Panel</title>
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png"/>
  <link rel="stylesheet" href="hms-modern.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
  <style>
    .search-bar{display:flex;gap:.75rem;margin-bottom:1.25rem}
    .search-bar input{flex:1;border:1px solid #e2e8f0;border-radius:var(--radius-md);padding:.6rem .9rem;font-family:'Inter',sans-serif;font-size:.875rem;outline:none}
    .search-bar input:focus{border-color:var(--primary-light);box-shadow:0 0 0 3px rgba(79,70,229,.1)}
    .search-bar .btn-search{padding:.6rem 1.1rem;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;border:none;border-radius:var(--radius-md);font-family:'Inter',sans-serif;font-size:.875rem;font-weight:600;cursor:pointer}
    .form-row-2{display:grid;grid-template-columns:150px 1fr;gap:.75rem;align-items:center;margin-bottom:.85rem}
    .form-row-2 label{font-size:.83rem;font-weight:600;color:var(--text-muted)}
    .form-row-2 .fc{border:1px solid #e2e8f0;border-radius:var(--radius-md);padding:.55rem .85rem;font-family:'Inter',sans-serif;font-size:.875rem;color:var(--text-dark);background:#f8fafc;outline:none;width:100%;transition:border-color .2s}
    .form-row-2 .fc:focus{border-color:var(--primary-light);background:white;box-shadow:0 0 0 3px rgba(79,70,229,.1)}
    .s-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
    .s-card{background:white;border-radius:var(--radius-lg);padding:1.2rem;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,.06);text-align:center}
    .s-card .snum{font-size:1.9rem;font-weight:800;margin:.4rem 0 .2rem}
    .s-card .slbl{font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
    .s-card .sico{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;font-size:1.05rem;color:white}
    select.fc option{background:white}
    .mini-in{width:70px;border:1px solid #e2e8f0;border-radius:6px;padding:4px 7px;font-size:.8rem;font-family:'Inter',sans-serif}
    .mini-sel{border:1px solid #e2e8f0;border-radius:6px;padding:4px 6px;font-size:.78rem;font-family:'Inter',sans-serif}
    .notes-in{border:1px solid #e2e8f0;border-radius:6px;padding:4px 6px;font-size:.78rem;font-family:'Inter',sans-serif;width:100%}
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="hms-navbar">
  <a href="#" class="brand">
    <div class="brand-icon"><i class="fa fa-heartbeat"></i></div>
    Global Hospitals
  </a>
  <div class="nav-user">
    <div class="avatar"><?php echo strtoupper(substr($adminUser,0,2)); ?></div>
    <span><?php echo htmlspecialchars($adminUser); ?> &nbsp;<span style="opacity:.5;font-size:.75rem">Receptionist</span></span>
    <a href="logout1.php" class="btn-logout"><i class="fa fa-sign-out"></i> Logout</a>
  </div>
</nav>

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <span class="sidebar-label">Navigation</span>
    <button class="sidebar-item active" onclick="showAdminTab('dash',this)"><i class="fa fa-th-large"></i> Dashboard</button>
    <button class="sidebar-item" onclick="showAdminTab('docs',this)"><i class="fa fa-user-md"></i> Doctor List</button>
    <button class="sidebar-item" onclick="showAdminTab('pats',this)"><i class="fa fa-users"></i> Patient List</button>
    <button class="sidebar-item" onclick="showAdminTab('apps',this)"><i class="fa fa-calendar"></i> Appointments</button>
    <button class="sidebar-item" onclick="showAdminTab('pres',this)"><i class="fa fa-file-text-o"></i> Prescriptions</button>
    <button class="sidebar-item" onclick="showAdminTab('store',this)"><i class="fa fa-medkit"></i> Medical Store</button>
    <button class="sidebar-item" onclick="showAdminTab('orders',this)"><i class="fa fa-shopping-cart"></i> All Orders</button>
    <button class="sidebar-item" onclick="showAdminTab('ops',this)"><i class="fa fa-stethoscope"></i> Operations</button>
    <button class="sidebar-item" onclick="showAdminTab('adddoc',this)"><i class="fa fa-user-plus"></i> Add Doctor</button>
    <button class="sidebar-item" onclick="showAdminTab('deldoc',this)"><i class="fa fa-user-times"></i> Delete Doctor</button>
    <button class="sidebar-item" onclick="showAdminTab('queries',this)"><i class="fa fa-envelope-o"></i> Queries</button>
    <span class="sidebar-label" style="margin-top:auto">Account</span>
    <a href="logout1.php" class="sidebar-item" style="color:#fca5a5"><i class="fa fa-sign-out"></i> Sign Out</a>
  </aside>

  <!-- MAIN -->
  <main class="main-content">

    <!-- Welcome Bar -->
    <div class="welcome-bar" style="background:linear-gradient(135deg,#1e1b4b,#4f46e5 60%,#06b6d4)">
      <div class="welcome-text">
        <h2>Welcome, <?php echo htmlspecialchars($adminUser); ?>! 🏥</h2>
        <p>Here's the overview of Global Hospitals today.</p>
      </div>
      <i class="fa fa-stethoscope welcome-icon"></i>
    </div>

    <!-- ══ DASHBOARD ══════════════════════════════════════════════ -->
    <div class="tab-pane active" id="atab-dash">
      <div class="s-cards">
        <?php
          $scards=[
            ['fa-user-md','#4f46e5','#7c3aed',$cDoctors,'Doctors'],
            ['fa-users','#0891b2','#06b6d4',$cPatients,'Patients'],
            ['fa-calendar-check-o','#059669','#10b981',$cApps,'Active Appts'],
            ['fa-medkit','#7c3aed','#a855f7',$cMeds,'Medicines'],
            ['fa-shopping-cart','#d97706','#f59e0b',$cOrders,'Pending Orders'],
            ['fa-stethoscope','#be185d','#ec4899',$cOps,'Scheduled Ops'],
            ['fa-envelope-o','#0369a1','#0891b2',$cQueries,'Queries'],
          ];
          foreach($scards as $c): ?>
        <div class="s-card">
          <div class="sico" style="background:linear-gradient(135deg,<?php echo $c[1].','.$c[2]; ?>)"><i class="fa <?php echo $c[0]; ?>"></i></div>
          <div class="snum" style="color:<?php echo $c[1]; ?>"><?php echo $c[3]; ?></div>
          <div class="slbl"><?php echo $c[4]; ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="stat-cards" style="grid-template-columns:repeat(4,1fr)">
        <a class="stat-card" href="#" onclick="showAdminTab('docs',document.querySelectorAll('.sidebar-item')[1]);return false"><div class="card-icon purple"><i class="fa fa-user-md"></i></div><div class="card-label">Manage</div><div class="card-title">Doctors</div><div class="card-action"><i class="fa fa-arrow-right"></i> View</div></a>
        <a class="stat-card" href="#" onclick="showAdminTab('apps',document.querySelectorAll('.sidebar-item')[3]);return false"><div class="card-icon green"><i class="fa fa-calendar"></i></div><div class="card-label">All</div><div class="card-title">Appointments</div><div class="card-action"><i class="fa fa-arrow-right"></i> View</div></a>
        <a class="stat-card" href="#" onclick="showAdminTab('store',document.querySelectorAll('.sidebar-item')[5]);return false"><div class="card-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-medkit"></i></div><div class="card-label">Pharmacy</div><div class="card-title">Medical Store</div><div class="card-action"><i class="fa fa-arrow-right"></i> Manage</div></a>
        <a class="stat-card" href="#" onclick="showAdminTab('ops',document.querySelectorAll('.sidebar-item')[7]);return false"><div class="card-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-stethoscope"></i></div><div class="card-label">Scheduled</div><div class="card-title">Operations</div><div class="card-action"><i class="fa fa-arrow-right"></i> View</div></a>
      </div>
    </div>

    <!-- ══ DOCTOR LIST ════════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-docs">
      <div class="content-card">
        <div class="card-header"><div class="header-icon"><i class="fa fa-user-md"></i></div><h3>Doctor List</h3></div>
        <div class="card-body-inner">
          <form class="search-bar" action="doctorsearch.php" method="post">
            <input type="text" name="doctor_contact" placeholder="Search by email ID...">
            <button type="submit" name="doctor_search_submit" class="btn-search"><i class="fa fa-search"></i> Search</button>
          </form>
        </div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>#</th><th>Name</th><th>Specialization</th><th>Email</th><th>Fees (₹)</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT *,(@row:=@row+1) rn FROM doctb,(SELECT @row:=0) r ORDER BY username");
            while($r=mysqli_fetch_array($res)):?>
            <tr>
              <td><span style="background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700"><?php echo $r['rn']; ?></span></td>
              <td><strong><?php echo $r['username']; ?></strong></td>
              <td><span class="badge-status active"><?php echo $r['spec']; ?></span></td>
              <td><?php echo $r['email']; ?></td>
              <td><strong>₹<?php echo number_format($r['docFees'],0); ?></strong></td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ PATIENT LIST ═══════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-pats">
      <div class="content-card">
        <div class="card-header"><div class="header-icon"><i class="fa fa-users"></i></div><h3>Patient List</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>PID</th><th>Name</th><th>Gender</th><th>Email</th><th>Contact</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM patreg ORDER BY pid");
            while($r=mysqli_fetch_array($res)):?>
            <tr>
              <td><span style="background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $r['pid']; ?></span></td>
              <td><strong><?php echo $r['fname'].' '.$r['lname']; ?></strong></td>
              <td><?php echo $r['gender']; ?></td>
              <td><?php echo $r['email']; ?></td>
              <td><?php echo $r['contact']; ?></td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ APPOINTMENTS ═══════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-apps">
      <div class="content-card">
        <div class="card-header"><div class="header-icon"><i class="fa fa-calendar"></i></div><h3>All Appointments</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>ID</th><th>Patient</th><th>Gender</th><th>Contact</th><th>Doctor</th><th>Fees</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM appointmenttb ORDER BY appdate DESC");
            while($r=mysqli_fetch_array($res)):
              $us=$r['userStatus']; $ds=$r['doctorStatus'];
              if($us==1&&$ds==1){$lbl='Active';$cls='active';}
              elseif($us==0){$lbl='Cancelled by Patient';$cls='cancelled';}
              else{$lbl='Cancelled by Doctor';$cls='cancelled-by';}?>
            <tr>
              <td><span style="background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $r['ID']; ?></span></td>
              <td><strong><?php echo $r['fname'].' '.$r['lname']; ?></strong></td>
              <td><?php echo $r['gender']; ?></td>
              <td><?php echo $r['contact']; ?></td>
              <td><?php echo $r['doctor']; ?></td>
              <td>₹<?php echo $r['docFees']; ?></td>
              <td><?php echo date('d M Y',strtotime($r['appdate'])); ?></td>
              <td><?php echo date('h:i A',strtotime($r['apptime'])); ?></td>
              <td><span class="badge-status <?php echo $cls; ?>"><?php echo $lbl; ?></span></td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ PRESCRIPTIONS ═════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-pres">
      <div class="content-card">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa fa-file-text-o"></i></div><h3>Prescription List</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>Doctor</th><th>Patient</th><th>Appt ID</th><th>Date</th><th>Disease</th><th>Allergy</th><th>Prescription</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM prestb ORDER BY appdate DESC");
            while($r=mysqli_fetch_array($res)):?>
            <tr>
              <td><strong><?php echo $r['doctor']; ?></strong></td>
              <td><?php echo $r['fname'].' '.$r['lname']; ?></td>
              <td><span style="background:#e0f2fe;color:#0891b2;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $r['ID']; ?></span></td>
              <td><?php echo date('d M Y',strtotime($r['appdate'])); ?></td>
              <td><span class="badge-status active"><?php echo $r['disease']; ?></span></td>
              <td style="font-size:.8rem"><?php echo $r['allergy']; ?></td>
              <td style="max-width:200px;white-space:normal;font-size:.78rem"><?php echo $r['prescription']; ?></td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ MEDICAL STORE ══════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-store">
      <!-- Add Medicine Form -->
      <div class="content-card" style="margin-bottom:1.5rem">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-plus-circle"></i></div><h3>Add New Medicine</h3></div>
        <div class="card-body-inner">
          <form method="post" action="admin-panel1.php">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 2rem">
              <div class="form-row-2" style="grid-column:span 2"><label>Medicine Name</label><input type="text" class="fc" name="med_name" placeholder="e.g. Paracetamol 500mg" required></div>
              <div class="form-row-2"><label>Category</label>
                <select name="med_category" class="fc" required>
                  <option value="" disabled selected>Select</option>
                  <option>Painkiller</option><option>Antibiotic</option><option>Antidiabetic</option>
                  <option>Antacid</option><option>Cardiac</option><option>Antiallergic</option>
                  <option>Dermatology</option><option>Neurology</option><option>Respiratory</option>
                  <option>Supplement</option><option>Syrup</option><option>Injection</option><option>Other</option>
                </select>
              </div>
              <div class="form-row-2"><label>Unit</label>
                <select name="med_unit" class="fc" required>
                  <option>Tablet</option><option>Capsule</option><option>Syrup</option>
                  <option>Injection</option><option>Vial</option><option>Softgel</option>
                  <option>Sachet</option><option>Gel</option><option>Tube</option><option>Inhaler</option>
                </select>
              </div>
              <div class="form-row-2"><label>Price per Unit (₹)</label><input type="number" step=".01" min="0" class="fc" name="med_price" placeholder="0.00" required></div>
              <div class="form-row-2"><label>Stock (units)</label><input type="number" min="0" class="fc" name="med_stock" placeholder="0" required></div>
              <div class="form-row-2" style="grid-column:span 2"><label>Description</label><input type="text" class="fc" name="med_desc" placeholder="Usage / indication..."></div>
            </div>
            <div style="margin-top:1rem"><button type="submit" name="add_medicine" class="btn-submit"><i class="fa fa-plus-circle"></i> Add to Store</button></div>
          </form>
        </div>
      </div>
      <!-- Inventory Table -->
      <div class="content-card">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-list"></i></div><h3>Medicine Inventory (<?php echo $cMeds; ?> items)</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Update Stock</th><th>Delete</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM medicine_store ORDER BY category,name");
            while($r=mysqli_fetch_array($res)):
              $sc=$r['stock']<=20?'cancelled':($r['stock']<=50?'cancelled-by':'active');?>
            <tr>
              <td><span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $r['med_id']; ?></span></td>
              <td><strong><?php echo $r['name']; ?></strong><br><small style="color:#94a3b8;font-size:.72rem"><?php echo substr($r['description'],0,55); ?>…</small></td>
              <td><span class="badge-status active" style="background:#ede9fe;color:#7c3aed"><?php echo $r['category']; ?></span></td>
              <td><?php echo $r['unit']; ?></td>
              <td><strong>₹<?php echo number_format($r['price'],2); ?></strong></td>
              <td><span class="badge-status <?php echo $sc; ?>"><?php echo $r['stock']; ?> units</span></td>
              <td>
                <form method="post" action="admin-panel1.php" style="display:flex;gap:.4rem;align-items:center">
                  <input type="hidden" name="med_id" value="<?php echo $r['med_id']; ?>">
                  <input type="number" name="new_stock" value="<?php echo $r['stock']; ?>" min="0" class="mini-in">
                  <button type="submit" name="update_stock" class="btn-sm-primary"><i class="fa fa-refresh"></i></button>
                </form>
              </td>
              <td>
                <form method="post" action="admin-panel1.php" onsubmit="return confirm('Delete this medicine?')">
                  <input type="hidden" name="med_id" value="<?php echo $r['med_id']; ?>">
                  <button type="submit" name="delete_medicine" class="btn-sm-danger"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ ALL ORDERS ═════════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-orders">
      <div class="content-card">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4)"><i class="fa fa-shopping-cart"></i></div><h3>All Medicine Orders</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>#</th><th>Ordered By</th><th>Role</th><th>Medicine</th><th>Qty</th><th>Total</th><th>Date</th><th>Status</th><th>Update Status</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM medicine_orders ORDER BY order_date DESC");
            while($r=mysqli_fetch_array($res)):
              $sc=$r['status']=='Delivered'?'active':($r['status']=='Cancelled'?'cancelled':($r['status']=='Dispatched'?'active':'pending'));?>
            <tr>
              <td><span style="background:#e0f2fe;color:#0891b2;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $r['order_id']; ?></span></td>
              <td><strong><?php echo $r['ordered_by']; ?></strong></td>
              <td><span class="badge-status <?php echo $r['role']=='doctor'?'cancelled-by':'active'; ?>"><?php echo ucfirst($r['role']); ?></span></td>
              <td><?php echo $r['med_name']; ?> <small style="color:#94a3b8">×<?php echo $r['quantity']; ?></small></td>
              <td><?php echo $r['quantity']; ?></td>
              <td><strong>₹<?php echo number_format($r['total_price'],2); ?></strong></td>
              <td><?php echo date('d M Y',strtotime($r['order_date'])); ?></td>
              <td><span class="badge-status <?php echo $sc; ?>"><?php echo $r['status']; ?></span></td>
              <td>
                <form method="post" action="admin-panel1.php" style="display:flex;gap:.4rem;align-items:center">
                  <input type="hidden" name="order_id" value="<?php echo $r['order_id']; ?>">
                  <select name="new_status" class="mini-sel">
                    <?php foreach(['Pending','Confirmed','Dispatched','Delivered','Cancelled'] as $s): ?>
                    <option <?php echo $r['status']==$s?'selected':''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" name="update_order_status" class="btn-sm-primary"><i class="fa fa-check"></i></button>
                </form>
              </td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ OPERATIONS ═════════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-ops">
      <div class="content-card">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-stethoscope"></i></div><h3>All Operations</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>#</th><th>Patient</th><th>Doctor</th><th>Operation</th><th>Date & Time</th><th>Room</th><th>Status</th><th>Notes</th><th>Update</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM operations ORDER BY op_date ASC");
            while($r=mysqli_fetch_array($res)):
              $sc=$r['status']=='Completed'?'active':($r['status']=='Cancelled'?'cancelled':'cancelled-by');?>
            <tr>
              <td><span style="background:#fce7f3;color:#be185d;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $r['op_id']; ?></span></td>
              <td><strong><?php echo $r['fname'].' '.$r['lname']; ?></strong><br><small style="color:#94a3b8">PID: <?php echo $r['pid']; ?></small></td>
              <td><?php echo $r['doctor']; ?></td>
              <td><strong><?php echo $r['op_type']; ?></strong></td>
              <td><?php echo date('d M Y',strtotime($r['op_date'])); ?><br><small><?php echo date('h:i A',strtotime($r['op_time'])); ?></small></td>
              <td><span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-weight:600;font-size:.78rem"><?php echo $r['op_room']; ?></span></td>
              <td><span class="badge-status <?php echo $sc; ?>"><?php echo $r['status']; ?></span></td>
              <td style="max-width:160px;white-space:normal;font-size:.76rem;color:#475569"><?php echo $r['notes']; ?></td>
              <td>
                <form method="post" action="admin-panel1.php" style="display:flex;flex-direction:column;gap:.4rem">
                  <input type="hidden" name="op_id" value="<?php echo $r['op_id']; ?>">
                  <select name="op_status" class="mini-sel">
                    <?php foreach(['Scheduled','Completed','Cancelled'] as $s): ?>
                    <option <?php echo $r['status']==$s?'selected':''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="text" name="op_notes_update" placeholder="Update notes…" class="notes-in">
                  <button type="submit" name="update_op_status" class="btn-sm-primary"><i class="fa fa-check"></i> Save</button>
                </form>
              </td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

    <!-- ══ ADD DOCTOR ═════════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-adddoc">
      <div class="content-card" style="max-width:580px">
        <div class="card-header"><div class="header-icon"><i class="fa fa-user-plus"></i></div><h3>Add New Doctor</h3></div>
        <div class="card-body-inner">
          <form method="post" action="admin-panel1.php">
            <div class="form-row-2"><label>Doctor Name</label><input type="text" class="fc" name="doctor" placeholder="Full name" required></div>
            <div class="form-row-2"><label>Specialization</label>
              <select name="special" class="fc" required>
                <option value="" disabled selected>Select</option>
                <option>General</option><option>Cardiologist</option><option>Neurologist</option>
                <option>Pediatrician</option><option>Orthopedic</option><option>Dermatologist</option>
                <option>Gynecologist</option><option>Ophthalmologist</option><option>ENT Specialist</option>
              </select>
            </div>
            <div class="form-row-2"><label>Email ID</label><input type="email" class="fc" name="demail" placeholder="doctor@hospital.com" required></div>
            <div class="form-row-2"><label>Password</label><input type="password" class="fc" id="dpassword" name="dpassword" onkeyup="checkdp()" required></div>
            <div class="form-row-2"><label>Confirm Password <span id="message" style="font-size:.72rem;font-weight:700"></span></label><input type="password" class="fc" id="cdpassword" name="cdpassword" onkeyup="checkdp()" required></div>
            <div class="form-row-2"><label>Fees (₹)</label><input type="number" class="fc" name="docFees" placeholder="e.g. 500" required></div>
            <div style="margin-top:1rem"><button type="submit" name="docsub" class="btn-submit"><i class="fa fa-plus-circle"></i> Add Doctor</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══ DELETE DOCTOR ══════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-deldoc">
      <div class="content-card" style="max-width:480px">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#dc2626,#ef4444)"><i class="fa fa-user-times"></i></div><h3>Delete Doctor</h3></div>
        <div class="card-body-inner">
          <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem">Enter the doctor's email ID to permanently remove them from the system.</p>
          <form method="post" action="admin-panel1.php">
            <div class="form-row-2"><label>Email ID</label><input type="email" class="fc" name="demail" placeholder="doctor@hospital.com" required></div>
            <div style="margin-top:1rem">
              <button type="submit" name="docsub1" onclick="return confirm('Are you sure?')" class="btn-submit" style="background:linear-gradient(135deg,#dc2626,#ef4444);box-shadow:0 4px 12px rgba(220,38,38,.3)">
                <i class="fa fa-trash"></i> Delete Doctor
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══ QUERIES ════════════════════════════════════════════════ -->
    <div class="tab-pane" id="atab-queries">
      <div class="content-card">
        <div class="card-header"><div class="header-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i class="fa fa-envelope-o"></i></div><h3>Patient Queries</h3></div>
        <div style="overflow-x:auto"><table class="hms-table">
          <thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Message</th></tr></thead>
          <tbody><?php
            $res=mysqli_query($con,"SELECT * FROM contact ORDER BY name");
            while($r=mysqli_fetch_array($res)):?>
            <tr>
              <td><strong><?php echo $r['name']; ?></strong></td>
              <td><?php echo $r['email']; ?></td>
              <td><?php echo $r['contact']; ?></td>
              <td style="max-width:280px;white-space:normal;font-size:.82rem"><?php echo $r['message']; ?></td>
            </tr>
          <?php endwhile;?></tbody>
        </table></div>
      </div>
    </div>

  </main>
</div>

<script>
  function showAdminTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
    document.getElementById('atab-' + tabId).classList.add('active');
    if(btn) btn.classList.add('active');
  }
  function checkdp() {
    var p=document.getElementById('dpassword').value;
    var c=document.getElementById('cdpassword').value;
    var m=document.getElementById('message');
    if(!c){m.innerHTML='';return;}
    if(p===c){m.style.color='#10b981';m.innerHTML='✓';}
    else{m.style.color='#ef4444';m.innerHTML='✗';}
  }
</script>
</body>
</html>
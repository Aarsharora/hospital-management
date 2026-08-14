<?php
include('func1.php');
$con = mysqli_connect("localhost","root","","myhmsdb");
$doctor = $_SESSION['dname'];

// Cancel appointment
if(isset($_GET['cancel'])) {
    mysqli_query($con,"UPDATE appointmenttb SET doctorStatus='0' WHERE ID='".$_GET['ID']."'");
    echo "<script>alert('Appointment cancelled successfully.');</script>";
}

// Place medicine order (by doctor, for patient use)
if(isset($_POST['place_order'])) {
    $mid  = (int)$_POST['med_id'];
    $qty  = max(1,(int)$_POST['quantity']);
    $oby  = mysqli_real_escape_string($con, $doctor);
    $med  = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM medicine_store WHERE med_id=$mid"));
    if($med && $med['stock']>=$qty) {
        $up=$med['price']; $tp=$up*$qty;
        $mn=mysqli_real_escape_string($con,$med['name']);
        mysqli_query($con,"INSERT INTO medicine_orders(pid,ordered_by,role,med_id,med_name,quantity,unit_price,total_price,status) VALUES(0,'$oby','doctor',$mid,'$mn',$qty,$up,$tp,'Confirmed')");
        mysqli_query($con,"UPDATE medicine_store SET stock=stock-$qty WHERE med_id=$mid");
        echo "<script>alert('Order placed successfully! Total: ₹".($up*$qty)."');</script>";
    } else {
        echo "<script>alert('Insufficient stock!');</script>";
    }
}

// Schedule Operation
if(isset($_POST['schedule_op'])) {
    $opid   = (int)$_POST['op_pid'];
    $ofname = mysqli_real_escape_string($con,$_POST['op_fname']);
    $olname = mysqli_real_escape_string($con,$_POST['op_lname']);
    $otype  = mysqli_real_escape_string($con,$_POST['op_type']);
    $odate  = $_POST['op_date'];
    $otime  = $_POST['op_time'];
    $oroom  = mysqli_real_escape_string($con,$_POST['op_room']);
    $onotes = mysqli_real_escape_string($con,$_POST['op_notes']);
    $doc    = mysqli_real_escape_string($con,$doctor);
    $q = "INSERT INTO operations(pid,fname,lname,doctor,op_type,op_date,op_time,op_room,notes,status)
          VALUES($opid,'$ofname','$olname','$doc','$otype','$odate','$otime','$oroom','$onotes','Scheduled')";
    if(mysqli_query($con,$q))
        echo "<script>alert('Operation scheduled successfully!');</script>";
    else
        echo "<script>alert('Error: ".mysqli_error($con)."');</script>";
}

// Summary counts
$totalApps   = mysqli_num_rows(mysqli_query($con,"SELECT * FROM appointmenttb WHERE doctor='$doctor' AND userStatus=1 AND doctorStatus=1"));
$totalPres   = mysqli_num_rows(mysqli_query($con,"SELECT * FROM prestb WHERE doctor='$doctor'"));
$totalOps    = mysqli_num_rows(mysqli_query($con,"SELECT * FROM operations WHERE doctor='$doctor' AND status='Scheduled'"));
$totalOrders = mysqli_num_rows(mysqli_query($con,"SELECT * FROM medicine_orders WHERE ordered_by='$doctor' AND role='doctor'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Global Hospitals — Doctor Panel</title>
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png"/>
  <link rel="stylesheet" href="hms-modern.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
  <style>
    .form-row-2{display:grid;grid-template-columns:160px 1fr;gap:.85rem;align-items:center;margin-bottom:1rem}
    .form-row-2 label{font-size:.85rem;font-weight:600;color:var(--text-muted)}
    .form-row-2 .form-control{border:1px solid #e2e8f0;border-radius:var(--radius-md);padding:.6rem .9rem;font-family:'Inter',sans-serif;font-size:.875rem;color:var(--text-dark);background:#f8fafc;transition:all .25s;outline:none;width:100%}
    .form-row-2 .form-control:focus{border-color:var(--primary-light);background:white;box-shadow:0 0 0 3px rgba(79,70,229,.1)}
    .summary-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
    .summary-card{background:white;border-radius:var(--radius-lg);padding:1.25rem;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,.06);text-align:center}
    .summary-card .s-number{font-size:2rem;font-weight:800;margin:.5rem 0 .25rem}
    .summary-card .s-label{font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
    .summary-card .s-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;font-size:1.1rem;color:white}
    .med-card{border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;background:#fafafa;transition:all .25s}
    .med-card:hover{border-color:#4f46e5;background:white;transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,70,229,.12)}
    .med-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;padding:1.25rem}
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
    <div class="avatar"><?php echo strtoupper(substr($doctor,0,2)); ?></div>
    <span><?php echo htmlspecialchars($doctor); ?> &nbsp;<span style="opacity:.5;font-size:.75rem">Doctor</span></span>
    <a href="logout1.php" class="btn-logout"><i class="fa fa-sign-out"></i> Logout</a>
  </div>
</nav>

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <span class="sidebar-label">Navigation</span>
    <button class="sidebar-item active" onclick="showDocTab('dash',this)"><i class="fa fa-th-large"></i> Dashboard</button>
    <button class="sidebar-item" onclick="showDocTab('apps',this)"><i class="fa fa-calendar"></i> Appointments</button>
    <button class="sidebar-item" onclick="showDocTab('pres',this)"><i class="fa fa-file-text-o"></i> Prescription List</button>
    <button class="sidebar-item" onclick="showDocTab('store',this)"><i class="fa fa-medkit"></i> Medical Store</button>
    <button class="sidebar-item" onclick="showDocTab('ops',this)"><i class="fa fa-stethoscope"></i> Operations</button>
    <button class="sidebar-item" onclick="showDocTab('myorders',this)"><i class="fa fa-shopping-cart"></i> My Orders</button>
    <span class="sidebar-label" style="margin-top:auto">Account</span>
    <a href="logout1.php" class="sidebar-item" style="color:#fca5a5"><i class="fa fa-sign-out"></i> Sign Out</a>
  </aside>

  <!-- MAIN -->
  <main class="main-content">

    <!-- WELCOME BAR -->
    <div class="welcome-bar" style="background:linear-gradient(135deg,#0c4a6e,#0891b2 60%,#06b6d4)">
      <div class="welcome-text">
        <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor); ?>! 🩺</h2>
        <p>Manage your appointments, prescribe medicines, and schedule operations.</p>
      </div>
      <i class="fa fa-user-md welcome-icon"></i>
    </div>

    <!-- ════ DASHBOARD ════ -->
    <div class="tab-pane active" id="dtab-dash">
      <div class="summary-cards">
        <div class="summary-card">
          <div class="s-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa fa-calendar-check-o"></i></div>
          <div class="s-number" style="color:#4f46e5"><?php echo $totalApps; ?></div>
          <div class="s-label">Active Appointments</div>
        </div>
        <div class="summary-card">
          <div class="s-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa fa-file-text-o"></i></div>
          <div class="s-number" style="color:#059669"><?php echo $totalPres; ?></div>
          <div class="s-label">Prescriptions Given</div>
        </div>
        <div class="summary-card">
          <div class="s-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-stethoscope"></i></div>
          <div class="s-number" style="color:#be185d"><?php echo $totalOps; ?></div>
          <div class="s-label">Scheduled Operations</div>
        </div>
        <div class="summary-card">
          <div class="s-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-shopping-cart"></i></div>
          <div class="s-number" style="color:#7c3aed"><?php echo $totalOrders; ?></div>
          <div class="s-label">Medicine Orders</div>
        </div>
      </div>
      <div class="stat-cards" style="grid-template-columns:repeat(3,1fr)">
        <a class="stat-card" href="#" onclick="showDocTab('apps',document.querySelectorAll('.sidebar-item')[1]);return false">
          <div class="card-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"><i class="fa fa-calendar"></i></div>
          <div class="card-label">Manage</div>
          <div class="card-title">Appointments</div>
          <div class="card-action"><i class="fa fa-arrow-right"></i> View & Cancel</div>
        </a>
        <a class="stat-card" href="#" onclick="showDocTab('ops',document.querySelectorAll('.sidebar-item')[4]);return false">
          <div class="card-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-stethoscope"></i></div>
          <div class="card-label">Schedule</div>
          <div class="card-title">Operations</div>
          <div class="card-action"><i class="fa fa-arrow-right"></i> Add / View</div>
        </a>
        <a class="stat-card" href="#" onclick="showDocTab('store',document.querySelectorAll('.sidebar-item')[3]);return false">
          <div class="card-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-medkit"></i></div>
          <div class="card-label">Order</div>
          <div class="card-title">Medical Store</div>
          <div class="card-action"><i class="fa fa-arrow-right"></i> Browse</div>
        </a>
      </div>
    </div>

    <!-- ════ APPOINTMENTS ════ -->
    <div class="tab-pane" id="dtab-apps">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon"><i class="fa fa-calendar"></i></div>
          <h3>My Appointments</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead><tr>
              <th>PID</th><th>Appt ID</th><th>Patient</th><th>Gender</th>
              <th>Contact</th><th>Date</th><th>Time</th><th>Status</th><th>Cancel</th><th>Prescribe</th>
            </tr></thead>
            <tbody>
              <?php
                $q = "SELECT pid,ID,fname,lname,gender,email,contact,appdate,apptime,userStatus,doctorStatus
                      FROM appointmenttb WHERE doctor='$doctor'";
                $res = mysqli_query($con,$q);
                while($row=mysqli_fetch_array($res)):
                  $us=$row['userStatus']; $ds=$row['doctorStatus'];
                  if($us==1&&$ds==1) { $lbl='Active'; $cls='active'; }
                  elseif($us==0&&$ds==1) { $lbl='Cancelled by Patient'; $cls='cancelled'; }
                  else { $lbl='Cancelled'; $cls='cancelled'; }
              ?>
              <tr>
                <td><span style="background:#e0e7ff;color:#4f46e5;padding:2px 7px;border-radius:4px;font-size:.75rem;font-weight:700"><?php echo $row['pid']; ?></span></td>
                <td><span style="background:#e0f2fe;color:#0891b2;padding:2px 7px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $row['ID']; ?></span></td>
                <td><strong><?php echo $row['fname'].' '.$row['lname']; ?></strong><br><small style="color:#94a3b8"><?php echo $row['contact']; ?></small></td>
                <td><?php echo $row['gender']; ?></td>
                <td><?php echo $row['contact']; ?></td>
                <td><?php echo date('d M Y',strtotime($row['appdate'])); ?></td>
                <td><?php echo date('h:i A',strtotime($row['apptime'])); ?></td>
                <td><span class="badge-status <?php echo $cls; ?>"><?php echo $lbl; ?></span></td>
                <td>
                  <?php if($us==1&&$ds==1): ?>
                  <a href="doctor-panel.php?ID=<?php echo $row['ID']; ?>&cancel=update"
                     onclick="return confirm('Cancel this appointment?')">
                    <button class="btn-sm-danger"><i class="fa fa-times"></i> Cancel</button>
                  </a>
                  <?php else: ?><span style="color:#94a3b8;font-size:.8rem">—</span><?php endif; ?>
                </td>
                <td>
                  <?php if($us==1&&$ds==1): ?>
                  <a href="prescribe.php?pid=<?php echo $row['pid']; ?>&ID=<?php echo $row['ID']; ?>&fname=<?php echo $row['fname']; ?>&lname=<?php echo $row['lname']; ?>&appdate=<?php echo $row['appdate']; ?>&apptime=<?php echo $row['apptime']; ?>">
                    <button class="btn-sm-primary"><i class="fa fa-pencil"></i> Prescribe</button>
                  </a>
                  <?php else: ?><span style="color:#94a3b8;font-size:.8rem">—</span><?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════ PRESCRIPTION LIST ════ -->
    <div class="tab-pane" id="dtab-pres">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i class="fa fa-file-text-o"></i></div>
          <h3>Prescription History</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead><tr>
              <th>PID</th><th>Patient</th><th>Appt ID</th><th>Date</th><th>Disease</th><th>Allergy</th><th>Prescription</th>
            </tr></thead>
            <tbody>
              <?php
                $res = mysqli_query($con,"SELECT * FROM prestb WHERE doctor='$doctor' ORDER BY appdate DESC");
                while($row=mysqli_fetch_array($res)):
              ?>
              <tr>
                <td><span style="background:#e0e7ff;color:#4f46e5;padding:2px 7px;border-radius:4px;font-size:.75rem;font-weight:700"><?php echo $row['pid']; ?></span></td>
                <td><strong><?php echo $row['fname'].' '.$row['lname']; ?></strong></td>
                <td><span style="background:#e0f2fe;color:#0891b2;padding:2px 7px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $row['ID']; ?></span></td>
                <td><?php echo date('d M Y',strtotime($row['appdate'])); ?></td>
                <td><span class="badge-status active"><?php echo $row['disease']; ?></span></td>
                <td style="font-size:.82rem"><?php echo $row['allergy']; ?></td>
                <td style="max-width:220px;white-space:normal;font-size:.8rem;color:#334155"><?php echo $row['prescription']; ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════ MEDICAL STORE ════ -->
    <div class="tab-pane" id="dtab-store">
      <div class="welcome-bar" style="background:linear-gradient(135deg,#7c3aed,#a855f7);margin-bottom:1.5rem">
        <div class="welcome-text">
          <h2><i class="fa fa-medkit"></i> &nbsp;Hospital Pharmacy</h2>
          <p>Order medicines for patient treatments. Orders are confirmed immediately.</p>
        </div>
      </div>
      <?php
        $cats = mysqli_query($con,"SELECT DISTINCT category FROM medicine_store ORDER BY category");
        while($cat=mysqli_fetch_array($cats)):
          $meds=mysqli_query($con,"SELECT * FROM medicine_store WHERE category='".$cat['category']."' AND stock>0 ORDER BY name");
      ?>
      <div class="content-card" style="margin-bottom:1.25rem">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-tag"></i></div>
          <h3><?php echo $cat['category']; ?></h3>
        </div>
        <div class="med-grid">
          <?php while($med=mysqli_fetch_array($meds)): ?>
          <div class="med-card">
            <div style="font-size:1.4rem;margin-bottom:.4rem">💊</div>
            <div style="font-weight:700;color:#1e293b;font-size:.9rem;margin-bottom:.25rem"><?php echo $med['name']; ?></div>
            <div style="font-size:.74rem;color:#94a3b8;margin-bottom:.75rem;line-height:1.5"><?php echo substr($med['description'],0,75).'...'; ?></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.6rem">
              <span style="font-size:1.05rem;font-weight:800;color:#7c3aed">₹<?php echo number_format($med['price'],2); ?></span>
              <span style="font-size:.73rem;color:#64748b">per <?php echo $med['unit']; ?></span>
            </div>
            <div style="font-size:.72rem;color:<?php echo $med['stock']<=20?'#dc2626':'#059669'; ?>;margin-bottom:.75rem;font-weight:600">
              <?php echo $med['stock']<=20?'⚠ Low: '.$med['stock'].' left':'✓ Stock: '.$med['stock'].' units'; ?>
            </div>
            <form method="post" action="doctor-panel.php" style="display:flex;gap:.4rem;align-items:center">
              <input type="hidden" name="med_id" value="<?php echo $med['med_id']; ?>">
              <input type="number" name="quantity" value="1" min="1" max="<?php echo min($med['stock'],50); ?>"
                style="width:60px;border:1px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:.85rem;text-align:center">
              <button type="submit" name="place_order"
                style="flex:1;padding:.45rem .5rem;background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;border:none;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif">
                <i class="fa fa-cart-plus"></i> Order
              </button>
            </form>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- ════ OPERATIONS ════ -->
    <div class="tab-pane" id="dtab-ops">

      <!-- Schedule New Operation -->
      <div class="content-card" style="max-width:620px;margin-bottom:1.5rem">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-plus-circle"></i></div>
          <h3>Schedule New Operation</h3>
        </div>
        <div class="card-body-inner">
          <form method="post" action="doctor-panel.php">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem">
              <div class="form-row-2">
                <label>Patient ID</label>
                <input type="number" name="op_pid" class="form-control" placeholder="e.g. 1" required>
              </div>
              <div class="form-row-2">
                <label>Operation Type</label>
                <select name="op_type" class="form-control" required>
                  <option value="" disabled selected>Select Type</option>
                  <option>Appendectomy</option><option>Cataract Surgery</option>
                  <option>Knee Arthroscopy</option><option>Tonsillectomy</option>
                  <option>Hernia Repair</option><option>Coronary Bypass</option>
                  <option>Gallbladder Removal</option><option>Hip Replacement</option>
                  <option>Cesarean Section</option><option>Other</option>
                </select>
              </div>
              <div class="form-row-2">
                <label>Patient First Name</label>
                <input type="text" name="op_fname" class="form-control" placeholder="First name" required>
              </div>
              <div class="form-row-2">
                <label>Patient Last Name</label>
                <input type="text" name="op_lname" class="form-control" placeholder="Last name" required>
              </div>
              <div class="form-row-2">
                <label>Operation Date</label>
                <input type="date" name="op_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
              </div>
              <div class="form-row-2">
                <label>Operation Time</label>
                <input type="time" name="op_time" class="form-control" required>
              </div>
              <div class="form-row-2" style="grid-column:span 2">
                <label>Theatre Room</label>
                <select name="op_room" class="form-control">
                  <option>OT-1</option><option>OT-2</option><option>OT-3</option><option>OT-4</option>
                </select>
              </div>
              <div class="form-row-2" style="grid-column:span 2">
                <label>Pre-op Notes</label>
                <input type="text" name="op_notes" class="form-control" placeholder="Any special instructions or notes...">
              </div>
            </div>
            <div style="margin-top:1rem">
              <button type="submit" name="schedule_op" class="btn-submit">
                <i class="fa fa-calendar-plus-o"></i> Schedule Operation
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- My Operations List -->
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-list"></i></div>
          <h3>My Scheduled Operations</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead><tr><th>#</th><th>Patient</th><th>Operation</th><th>Date & Time</th><th>Room</th><th>Status</th><th>Notes</th></tr></thead>
            <tbody>
              <?php
                $res=mysqli_query($con,"SELECT * FROM operations WHERE doctor='$doctor' ORDER BY op_date ASC");
                if(mysqli_num_rows($res)==0): ?>
              <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8">
                <i class="fa fa-calendar-times-o" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                No operations scheduled yet.
              </td></tr>
              <?php else: while($op=mysqli_fetch_array($res)):
                $sc=$op['status']=='Completed'?'active':($op['status']=='Cancelled'?'cancelled':'cancelled-by'); ?>
              <tr>
                <td><span style="background:#fce7f3;color:#be185d;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $op['op_id']; ?></span></td>
                <td><strong><?php echo $op['fname'].' '.$op['lname']; ?></strong><br><small style="color:#94a3b8">PID: <?php echo $op['pid']; ?></small></td>
                <td><strong><?php echo $op['op_type']; ?></strong></td>
                <td><?php echo date('d M Y',strtotime($op['op_date'])); ?><br><small><?php echo date('h:i A',strtotime($op['op_time'])); ?></small></td>
                <td><span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-weight:600;font-size:.8rem"><?php echo $op['op_room']; ?></span></td>
                <td><span class="badge-status <?php echo $sc; ?>"><?php echo $op['status']; ?></span></td>
                <td style="max-width:200px;white-space:normal;font-size:.8rem;color:#64748b"><?php echo $op['notes']; ?></td>
              </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════ MY ORDERS ════ -->
    <div class="tab-pane" id="dtab-myorders">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-shopping-cart"></i></div>
          <h3>My Medicine Orders</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead><tr><th>#</th><th>Medicine</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
              <?php
                $res=mysqli_query($con,"SELECT * FROM medicine_orders WHERE ordered_by='$doctor' AND role='doctor' ORDER BY order_date DESC");
                if(mysqli_num_rows($res)==0): ?>
              <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8">
                <i class="fa fa-shopping-basket" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                No orders placed yet.
              </td></tr>
              <?php else: while($ord=mysqli_fetch_array($res)):
                $sc=$ord['status']=='Delivered'?'active':($ord['status']=='Cancelled'?'cancelled':($ord['status']=='Dispatched'?'active':'pending')); ?>
              <tr>
                <td><span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $ord['order_id']; ?></span></td>
                <td><strong><?php echo $ord['med_name']; ?></strong></td>
                <td><?php echo $ord['quantity']; ?> units</td>
                <td>₹<?php echo number_format($ord['unit_price'],2); ?></td>
                <td><strong>₹<?php echo number_format($ord['total_price'],2); ?></strong></td>
                <td><?php echo date('d M Y',strtotime($ord['order_date'])); ?></td>
                <td><span class="badge-status <?php echo $sc; ?>"><?php echo $ord['status']; ?></span></td>
              </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
  function showDocTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
    document.getElementById('dtab-' + tabId).classList.add('active');
    if(btn) btn.classList.add('active');
  }
</script>
</body>
</html>
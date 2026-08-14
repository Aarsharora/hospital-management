<?php 
include('func.php');  
include('newfunc.php');
$con=mysqli_connect("localhost","root","","myhmsdb");


  $pid = $_SESSION['pid'];
  $username = $_SESSION['username'];
  $email = $_SESSION['email'];
  $fname = $_SESSION['fname'];
  $gender = $_SESSION['gender'];
  $lname = $_SESSION['lname'];
  $contact = $_SESSION['contact'];



if(isset($_POST['app-submit']))
{
  $pid = $_SESSION['pid'];
  $username = $_SESSION['username'];
  $email = $_SESSION['email'];
  $fname = $_SESSION['fname'];
  $lname = $_SESSION['lname'];
  $gender = $_SESSION['gender'];
  $contact = $_SESSION['contact'];
  $doctor=$_POST['doctor'];
  $email=$_SESSION['email'];
  # $fees=$_POST['fees'];
  $docFees=$_POST['docFees'];

  $appdate=$_POST['appdate'];
  $apptime=$_POST['apptime'];
  $cur_date = date("Y-m-d");
  date_default_timezone_set('Asia/Kolkata');
  $cur_time = date("H:i:s");
  $apptime1 = strtotime($apptime);
  $appdate1 = strtotime($appdate);
	
  if(date("Y-m-d",$appdate1)>=$cur_date){
    if((date("Y-m-d",$appdate1)==$cur_date and date("H:i:s",$apptime1)>$cur_time) or date("Y-m-d",$appdate1)>$cur_date) {
      $check_query = mysqli_query($con,"select apptime from appointmenttb where doctor='$doctor' and appdate='$appdate' and apptime='$apptime'");

        if(mysqli_num_rows($check_query)==0){
          $query=mysqli_query($con,"insert into appointmenttb(pid,fname,lname,gender,email,contact,doctor,docFees,appdate,apptime,userStatus,doctorStatus) values($pid,'$fname','$lname','$gender','$email','$contact','$doctor','$docFees','$appdate','$apptime','1','1')");

          if($query)
          {
            echo "<script>alert('Your appointment successfully booked');</script>";
          }
          else{
            echo "<script>alert('Unable to process your request. Please try again!');</script>";
          }
      }
      else{
        echo "<script>alert('We are sorry to inform that the doctor is not available in this time or date. Please choose different time or date!');</script>";
      }
    }
    else{
      echo "<script>alert('Select a time or date in the future!');</script>";
    }
  }
  else{
      echo "<script>alert('Select a time or date in the future!');</script>";
  }
  
}

if(isset($_GET['cancel']))
  {
    $query=mysqli_query($con,"update appointmenttb set userStatus='0' where ID = '".$_GET['ID']."'");
    if($query)
    {
      echo "<script>alert('Your appointment successfully cancelled');</script>";
    }
  }

// Patient: Place Medicine Order
if(isset($_POST['place_order'])) {
  $mid   = (int)$_POST['med_id'];
  $qty   = max(1,(int)$_POST['quantity']);
  $oby   = $fname.' '.$lname;
  $opid  = $pid;
  $med   = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM medicine_store WHERE med_id=$mid"));
  if($med && $med['stock']>=$qty) {
    $up = $med['price']; $tp = $up*$qty;
    $mn = mysqli_real_escape_string($con,$med['name']);
    $obn= mysqli_real_escape_string($con,$oby);
    mysqli_query($con,"INSERT INTO medicine_orders(pid,ordered_by,role,med_id,med_name,quantity,unit_price,total_price,status) VALUES($opid,'$obn','patient',$mid,'$mn',$qty,$up,$tp,'Pending')");
    mysqli_query($con,"UPDATE medicine_store SET stock=stock-$qty WHERE med_id=$mid");
    echo "<script>alert('Order placed! Total: ₹".($up*$qty)."\nStatus: Pending - Will be ready at hospital pharmacy.');</script>";
  } else {
    echo "<script>alert('Insufficient stock or medicine not found!');</script>";
  }
}


function generate_bill(){
  $con=mysqli_connect("localhost","root","","myhmsdb");
  $pid = $_SESSION['pid'];
  $output='';
  $query=mysqli_query($con,"select p.pid,p.ID,p.fname,p.lname,p.doctor,p.appdate,p.apptime,p.disease,p.allergy,p.prescription,a.docFees from prestb p inner join appointmenttb a on p.ID=a.ID and p.pid = '$pid' and p.ID = '".$_GET['ID']."'");
  while($row = mysqli_fetch_array($query)){
    $output .= '
    <label> Patient ID : </label>'.$row["pid"].'<br/><br/>
    <label> Appointment ID : </label>'.$row["ID"].'<br/><br/>
    <label> Patient Name : </label>'.$row["fname"].' '.$row["lname"].'<br/><br/>
    <label> Doctor Name : </label>'.$row["doctor"].'<br/><br/>
    <label> Appointment Date : </label>'.$row["appdate"].'<br/><br/>
    <label> Appointment Time : </label>'.$row["apptime"].'<br/><br/>
    <label> Disease : </label>'.$row["disease"].'<br/><br/>
    <label> Allergies : </label>'.$row["allergy"].'<br/><br/>
    <label> Prescription : </label>'.$row["prescription"].'<br/><br/>
    <label> Fees Paid : </label>'.$row["docFees"].'<br/>
    
    ';

  }
  
  return $output;
}


if(isset($_GET["generate_bill"])){
  require_once("TCPDF/tcpdf.php");
  $obj_pdf = new TCPDF('P',PDF_UNIT,PDF_PAGE_FORMAT,true,'UTF-8',false);
  $obj_pdf -> SetCreator(PDF_CREATOR);
  $obj_pdf -> SetTitle("Generate Bill");
  $obj_pdf -> SetHeaderData('','',PDF_HEADER_TITLE,PDF_HEADER_STRING);
  $obj_pdf -> SetHeaderFont(Array(PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_MAIN));
  $obj_pdf -> SetFooterFont(Array(PDF_FONT_NAME_MAIN,'',PDF_FONT_SIZE_MAIN));
  $obj_pdf -> SetDefaultMonospacedFont('helvetica');
  $obj_pdf -> SetFooterMargin(PDF_MARGIN_FOOTER);
  $obj_pdf -> SetMargins(PDF_MARGIN_LEFT,'5',PDF_MARGIN_RIGHT);
  $obj_pdf -> SetPrintHeader(false);
  $obj_pdf -> SetPrintFooter(false);
  $obj_pdf -> SetAutoPageBreak(TRUE, 10);
  $obj_pdf -> SetFont('helvetica','',12);
  $obj_pdf -> AddPage();

  $content = '';

  $content .= '
      <br/>
      <h2 align ="center"> Global Hospitals</h2></br>
      <h3 align ="center"> Bill</h3>
      

  ';
 
  $content .= generate_bill();
  $obj_pdf -> writeHTML($content);
  ob_end_clean();
  $obj_pdf -> Output("bill.pdf",'I');

}

function get_specs(){
  $con=mysqli_connect("localhost","root","","myhmsdb");
  $query=mysqli_query($con,"select username,spec from doctb");
  $docarray = array();
    while($row =mysqli_fetch_assoc($query))
    {
        $docarray[] = $row;
    }
    return json_encode($docarray);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Global Hospitals â€” Patient Dashboard</title>
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
  <link rel="stylesheet" href="hms-modern.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="hms-navbar">
  <a href="#" class="brand">
    <div class="brand-icon"><i class="fa fa-heartbeat"></i></div>
    Global Hospitals
  </a>
  <div class="nav-user">
    <div class="avatar"><?php echo strtoupper(substr($fname, 0, 1).substr($lname, 0, 1)); ?></div>
    <span><?php echo $username; ?></span>
    <a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i> Logout</a>
  </div>
</nav>

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <span class="sidebar-label">Navigation</span>
    <button class="sidebar-item active" id="btn-dash"    onclick="showTab('dash', this)">
      <i class="fa fa-th-large"></i> Dashboard
    </button>
    <button class="sidebar-item" id="btn-book"    onclick="showTab('book', this)">
      <i class="fa fa-calendar-plus-o"></i> Book Appointment
    </button>
    <button class="sidebar-item" id="btn-history" onclick="showTab('history', this)">
      <i class="fa fa-history"></i> Appointment History
    </button>
    <button class="sidebar-item" id="btn-pres"    onclick="showTab('pres', this)">
      <i class="fa fa-file-text-o"></i> Prescriptions
    </button>
    <button class="sidebar-item" id="btn-store" onclick="showTab('store', this)">
      <i class="fa fa-medkit"></i> Medical Store
    </button>
    <button class="sidebar-item" id="btn-ops" onclick="showTab('ops', this)">
      <i class="fa fa-stethoscope"></i> My Operations
    </button>

    <span class="sidebar-label" style="margin-top:auto">Account</span>
    <a href="logout.php" class="sidebar-item" style="color:#fca5a5">
      <i class="fa fa-sign-out"></i> Sign Out
    </a>
  </aside>

  <!-- MAIN -->
  <main class="main-content">

    <!-- WELCOME BAR -->
    <div class="welcome-bar">
      <div class="welcome-text">
        <h2>Welcome back, <?php echo $fname; ?>! ðŸ‘‹</h2>
        <p>Here's what's happening with your health today.</p>
      </div>
      <i class="fa fa-hospital-o welcome-icon"></i>
    </div>

    <!-- â”€â”€ DASHBOARD TAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div class="tab-pane active" id="tab-dash">
      <div class="stat-cards">
        <a class="stat-card" href="#" onclick="showTab('book', document.getElementById('btn-book'));return false;">
          <div class="card-icon purple"><i class="fa fa-calendar-plus-o"></i></div>
          <div class="card-label">Appointments</div>
          <div class="card-title">Book My Appointment</div>
          <div class="card-action"><i class="fa fa-arrow-right"></i> Book now</div>
        </a>
        <a class="stat-card" href="#" onclick="showTab('history', document.getElementById('btn-history'));return false;">
          <div class="card-icon cyan"><i class="fa fa-history"></i></div>
          <div class="card-label">History</div>
          <div class="card-title">My Appointments</div>
          <div class="card-action"><i class="fa fa-arrow-right"></i> View history</div>
        </a>
        <a class="stat-card" href="#" onclick="showTab('pres', document.getElementById('btn-pres'));return false;">
          <div class="card-icon green"><i class="fa fa-file-text-o"></i></div>
          <div class="card-label">Prescriptions</div>
          <div class="card-title">My Prescriptions</div>
          <div class="card-action"><i class="fa fa-arrow-right"></i> View list</div>
        </a>
      </div>

      <div class="content-card">
        <div class="card-header">
          <div class="header-icon"><i class="fa fa-user"></i></div>
          <h3>Patient Profile</h3>
        </div>
        <div class="card-body-inner">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div style="padding:0.75rem 1rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
              <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.25rem">Full Name</div>
              <div style="font-weight:600;color:#1e293b"><?php echo $username; ?></div>
            </div>
            <div style="padding:0.75rem 1rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
              <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.25rem">Email</div>
              <div style="font-weight:600;color:#1e293b"><?php echo $email; ?></div>
            </div>
            <div style="padding:0.75rem 1rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
              <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.25rem">Phone</div>
              <div style="font-weight:600;color:#1e293b"><?php echo $contact; ?></div>
            </div>
            <div style="padding:0.75rem 1rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
              <div style="font-size:0.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.25rem">Gender</div>
              <div style="font-weight:600;color:#1e293b"><?php echo $gender; ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- â”€â”€ BOOK APPOINTMENT TAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div class="tab-pane" id="tab-book">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon"><i class="fa fa-calendar-plus-o"></i></div>
          <h3>Book an Appointment</h3>
        </div>
        <div class="card-body-inner">
          <form class="appt-form" method="post" action="admin-panel.php">

            <div class="form-row-2">
              <label for="spec">Specialization</label>
              <select name="spec" class="form-control" id="spec">
                <option value="" disabled selected>Select Specialization</option>
                <?php display_specs(); ?>
              </select>
            </div>

            <script>
              document.addEventListener('DOMContentLoaded', function(){
                document.getElementById('spec').onchange = function() {
                  var spec = this.value;
                  var docs = [...document.getElementById('doctor').options];
                  docs.forEach(function(el){ el.style.display = (el.getAttribute('data-spec') !== spec) ? 'none' : ''; });
                };
                document.getElementById('doctor').onchange = function() {
                  var sel = document.querySelector('#doctor option[value="'+this.value+'"]');
                  document.getElementById('docFees').value = sel ? sel.getAttribute('data-value') : '';
                };
              });
            </script>

            <div class="form-row-2">
              <label for="doctor">Doctor</label>
              <select name="doctor" class="form-control" id="doctor" required>
                <option value="" disabled selected>Select Doctor</option>
                <?php display_docs(); ?>
              </select>
            </div>

            <div class="form-row-2">
              <label for="docFees">Consultancy Fees (â‚¹)</label>
              <input class="form-control" type="text" name="docFees" id="docFees" readonly placeholder="Auto-filled on doctor selection" />
            </div>

            <div class="form-row-2">
              <label for="appdate">Appointment Date</label>
              <input type="date" class="form-control" name="appdate" id="appdate" required />
            </div>

            <div class="form-row-2">
              <label for="apptime">Appointment Time</label>
              <select name="apptime" class="form-control" id="apptime" required>
                <option value="" disabled selected>Select Time Slot</option>
                <option value="08:00:00">8:00 AM</option>
                <option value="10:00:00">10:00 AM</option>
                <option value="12:00:00">12:00 PM</option>
                <option value="14:00:00">2:00 PM</option>
                <option value="16:00:00">4:00 PM</option>
              </select>
            </div>

            <div style="margin-top:1.25rem">
              <button type="submit" name="app-submit" class="btn-submit">
                <i class="fa fa-check-circle"></i> Confirm Appointment
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- â”€â”€ APPOINTMENT HISTORY TAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div class="tab-pane" id="tab-history">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon"><i class="fa fa-history"></i></div>
          <h3>Appointment History</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead>
              <tr>
                <th>Doctor Name</th>
                <th>Fees (â‚¹)</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $con2 = mysqli_connect("localhost","root","","myhmsdb");
                $qhist = "SELECT ID,doctor,docFees,appdate,apptime,userStatus,doctorStatus FROM appointmenttb WHERE fname='$fname' AND lname='$lname';";
                $rhist = mysqli_query($con2, $qhist);
                while ($row = mysqli_fetch_array($rhist)):
              ?>
              <tr>
                <td><strong><?php echo $row['doctor']; ?></strong></td>
                <td>â‚¹<?php echo $row['docFees']; ?></td>
                <td><?php echo date('d M Y', strtotime($row['appdate'])); ?></td>
                <td><?php echo date('h:i A', strtotime($row['apptime'])); ?></td>
                <td>
                  <?php
                    $us = $row['userStatus']; $ds = $row['doctorStatus'];
                    if ($us==1 && $ds==1) echo '<span class="badge-status active"><i class="fa fa-circle" style="font-size:0.55rem"></i> Active</span>';
                    elseif ($us==0 && $ds==1) echo '<span class="badge-status cancelled"><i class="fa fa-times-circle" style="font-size:0.7rem"></i> Cancelled by You</span>';
                    elseif ($us==1 && $ds==0) echo '<span class="badge-status cancelled-by"><i class="fa fa-exclamation-circle" style="font-size:0.7rem"></i> Cancelled by Doctor</span>';
                  ?>
                </td>
                <td>
                  <?php if ($us==1 && $ds==1): ?>
                    <a href="admin-panel.php?ID=<?php echo $row['ID']; ?>&cancel=update"
                       onclick="return confirm('Cancel this appointment?')"
                       class="btn-sm-danger">
                      <i class="fa fa-times"></i> Cancel
                    </a>
                  <?php else: ?>
                    <span style="color:#94a3b8;font-size:0.8rem">Cancelled</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- â”€â”€ PRESCRIPTIONS TAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div class="tab-pane" id="tab-pres">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon"><i class="fa fa-file-text-o"></i></div>
          <h3>Prescriptions</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead>
              <tr>
                <th>Doctor</th>
                <th>Appt ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Disease</th>
                <th>Allergies</th>
                <th>Prescription</th>
                <th>Bill</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $con3 = mysqli_connect("localhost","root","","myhmsdb");
                $qpres = "SELECT doctor,ID,appdate,apptime,disease,allergy,prescription FROM prestb WHERE pid='$pid';";
                $rpres = mysqli_query($con3, $qpres);
                if (!$rpres) echo '<tr><td colspan="8" style="color:red">'.mysqli_error($con3).'</td></tr>';
                while ($row = mysqli_fetch_array($rpres)):
              ?>
              <tr>
                <td><strong><?php echo $row['doctor']; ?></strong></td>
                <td><span style="background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:4px;font-size:0.75rem;font-weight:700">#<?php echo $row['ID']; ?></span></td>
                <td><?php echo date('d M Y', strtotime($row['appdate'])); ?></td>
                <td><?php echo date('h:i A', strtotime($row['apptime'])); ?></td>
                <td><?php echo $row['disease']; ?></td>
                <td><?php echo $row['allergy']; ?></td>
                <td style="max-width:180px;white-space:normal;font-size:0.8rem"><?php echo $row['prescription']; ?></td>
                <td>
                  <form method="get" style="display:inline">
                    <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>"/>
                    <button type="submit" name="generate_bill" onclick="alert('Bill Paid Successfully!');"
                      class="btn-sm-success">
                      <i class="fa fa-download"></i> Pay &amp; Bill
                    </button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    <!-- ══ MEDICAL STORE TAB (Patient) ══════════════════════════ -->
    <div class="tab-pane" id="tab-store">
      <div class="welcome-bar" style="background:linear-gradient(135deg,#7c3aed,#a855f7);margin-bottom:1.5rem">
        <div class="welcome-text">
          <h2><i class="fa fa-medkit"></i> &nbsp;Hospital Pharmacy</h2>
          <p>Browse and order medicines directly from the hospital pharmacy.</p>
        </div>
      </div>

      <?php
        $myOrders=mysqli_query($con,"SELECT * FROM medicine_orders WHERE pid='$pid' ORDER BY order_date DESC LIMIT 5");
        if(mysqli_num_rows($myOrders)>0):
      ?>
      <div class="content-card" style="margin-bottom:1.5rem">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-shopping-cart"></i></div>
          <h3>My Recent Orders</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead><tr><th>#</th><th>Medicine</th><th>Qty</th><th>Total</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
              <?php while($ord=mysqli_fetch_array($myOrders)):
                $sc=$ord['status']=='Delivered'?'active':($ord['status']=='Cancelled'?'cancelled':($ord['status']=='Dispatched'?'active':'pending')); ?>
              <tr>
                <td><span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $ord['order_id']; ?></span></td>
                <td><strong><?php echo $ord['med_name']; ?></strong></td>
                <td><?php echo $ord['quantity']; ?> units</td>
                <td><strong>₹<?php echo number_format($ord['total_price'],2); ?></strong></td>
                <td><?php echo date('d M Y',strtotime($ord['order_date'])); ?></td>
                <td><span class="badge-status <?php echo $sc; ?>"><?php echo $ord['status']; ?></span></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <?php
        $cats=mysqli_query($con,"SELECT DISTINCT category FROM medicine_store ORDER BY category");
        while($cat=mysqli_fetch_array($cats)):
          $meds=mysqli_query($con,"SELECT * FROM medicine_store WHERE category='".$cat['category']."' AND stock>0 ORDER BY name");
          if(mysqli_num_rows($meds)==0) continue;
      ?>
      <div class="content-card" style="margin-bottom:1.25rem">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-tag"></i></div>
          <h3><?php echo $cat['category']; ?></h3>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;padding:1.25rem">
          <?php while($med=mysqli_fetch_array($meds)): ?>
          <div style="border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;background:#fafafa;transition:all .25s"
            onmouseover="this.style.borderColor='#7c3aed';this.style.background='#fff'"
            onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fafafa'">
            <div style="font-size:1.5rem;margin-bottom:.5rem">💊</div>
            <div style="font-weight:700;color:#1e293b;margin-bottom:.2rem;font-size:.9rem"><?php echo $med['name']; ?></div>
            <div style="font-size:.73rem;color:#94a3b8;margin-bottom:.7rem;line-height:1.5"><?php echo substr($med['description'],0,70).'…'; ?></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.6rem">
              <span style="font-size:1.05rem;font-weight:800;color:#7c3aed">₹<?php echo number_format($med['price'],2); ?></span>
              <span style="font-size:.73rem;color:#64748b">per <?php echo $med['unit']; ?></span>
            </div>
            <div style="font-size:.72rem;color:<?php echo $med['stock']<=20?'#dc2626':'#059669'; ?>;margin-bottom:.7rem;font-weight:600">
              <?php echo $med['stock']<=20?'⚠ Low Stock: '.$med['stock'].' left':'✓ In Stock: '.$med['stock'].' units'; ?>
            </div>
            <form method="post" action="admin-panel.php" style="display:flex;gap:.4rem;align-items:center">
              <input type="hidden" name="med_id" value="<?php echo $med['med_id']; ?>">
              <input type="number" name="quantity" value="1" min="1" max="<?php echo min($med['stock'],20); ?>"
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

    <!-- ══ MY OPERATIONS TAB (Patient) ══════════════════════════ -->
    <div class="tab-pane" id="tab-ops">
      <div class="content-card">
        <div class="card-header">
          <div class="header-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-stethoscope"></i></div>
          <h3>My Scheduled Operations</h3>
        </div>
        <div style="overflow-x:auto">
          <table class="hms-table">
            <thead><tr><th>#</th><th>Operation</th><th>Doctor</th><th>Date &amp; Time</th><th>Room</th><th>Status</th><th>Notes</th></tr></thead>
            <tbody>
              <?php
                $ops=mysqli_query($con,"SELECT * FROM operations WHERE pid='$pid' ORDER BY op_date ASC");
                if(mysqli_num_rows($ops)==0): ?>
              <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8">
                <i class="fa fa-check-circle" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#10b981"></i>
                No operations scheduled.
              </td></tr>
              <?php else: while($op=mysqli_fetch_array($ops)):
                $sc=$op['status']=='Completed'?'active':($op['status']=='Cancelled'?'cancelled':'cancelled-by'); ?>
              <tr>
                <td><span style="background:#fce7f3;color:#be185d;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700">#<?php echo $op['op_id']; ?></span></td>
                <td><strong><?php echo $op['op_type']; ?></strong></td>
                <td><?php echo $op['doctor']; ?></td>
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

  </main>
</div>

<script>
  function showTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(function(el){ el.classList.remove('active'); });
    document.querySelectorAll('.sidebar-item').forEach(function(el){ el.classList.remove('active'); });
    document.getElementById('tab-' + tabId).classList.add('active');
    if(btn) btn.classList.add('active');
  }
</script>
</body>
</html>
<div class="tab-pane" id="tab-store" style="margin-left:240px;padding:2rem;display:none">
  <div class="welcome-bar" style="background:linear-gradient(135deg,#7c3aed,#a855f7);margin-bottom:1.5rem">
    <div class="welcome-text">
      <h2><i class="fa fa-medkit"></i> &nbsp;Hospital Pharmacy</h2>
      <p>Browse and order medicines directly from the hospital pharmacy.</p>
    </div>
  </div>

  <?php
    // Show patient's orders
    $myOrders = mysqli_query($con,"SELECT * FROM medicine_orders WHERE pid='$pid' ORDER BY order_date DESC LIMIT 5");
    if(mysqli_num_rows($myOrders)>0):
  ?>
  <div class="content-card" style="margin-bottom:1.5rem">
    <div class="card-header">
      <div class="header-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)"><i class="fa fa-shopping-cart"></i></div>
      <h3>My Recent Orders</h3>
    </div>
    <div style="overflow-x:auto">
      <table class="hms-table">
        <thead><tr><th>#</th><th>Medicine</th><th>Qty</th><th>Total</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php while($ord=mysqli_fetch_array($myOrders)): $sc=$ord['status']=='Delivered'?'active':($ord['status']=='Cancelled'?'cancelled':($ord['status']=='Dispatched'?'active':'pending')); ?>
          <tr>
            <td><span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:4px;font-size:0.75rem;font-weight:700">#<?php echo $ord['order_id']; ?></span></td>
            <td><strong><?php echo $ord['med_name']; ?></strong></td>
            <td><?php echo $ord['quantity']; ?> units</td>
            <td><strong>₹<?php echo number_format($ord['total_price'],2); ?></strong></td>
            <td><?php echo date('d M Y',strtotime($ord['order_date'])); ?></td>
            <td><span class="badge-status <?php echo $sc; ?>"><?php echo $ord['status']; ?></span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Medicine catalog -->
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
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;padding:1.25rem">
      <?php while($med=mysqli_fetch_array($meds)): ?>
      <div style="border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;background:#fafafa;transition:all 0.25s" onmouseover="this.style.borderColor='#7c3aed';this.style.background='#fff'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fafafa'">
        <div style="font-size:1.5rem;margin-bottom:0.5rem">💊</div>
        <div style="font-weight:700;color:#1e293b;margin-bottom:0.25rem;font-size:0.9rem"><?php echo $med['name']; ?></div>
        <div style="font-size:0.75rem;color:#94a3b8;margin-bottom:0.75rem;line-height:1.5"><?php echo substr($med['description'],0,70).'...'; ?></div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
          <span style="font-size:1.1rem;font-weight:800;color:#7c3aed">₹<?php echo number_format($med['price'],2); ?></span>
          <span style="font-size:0.75rem;color:#64748b">per <?php echo $med['unit']; ?></span>
        </div>
        <div style="font-size:0.72rem;color:<?php echo $med['stock']<=20?'#dc2626':'#059669'; ?>;margin-bottom:0.75rem;font-weight:600">
          <?php echo $med['stock']<=20?'⚠ Low Stock: '.$med['stock'].' left':'✓ In Stock: '.$med['stock'].' units'; ?>
        </div>
        <form method="post" action="admin-panel.php" style="display:flex;gap:0.5rem;align-items:center">
          <input type="hidden" name="med_id" value="<?php echo $med['med_id']; ?>">
          <input type="number" name="quantity" value="1" min="1" max="<?php echo min($med['stock'],20); ?>"
            style="width:60px;border:1px solid #e2e8f0;border-radius:8px;padding:5px 8px;font-size:0.85rem;text-align:center">
          <button type="submit" name="place_order"
            style="flex:1;padding:0.45rem 0.5rem;background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;border:none;border-radius:8px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif">
            <i class="fa fa-cart-plus"></i> Order
          </button>
        </form>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endwhile; ?>
</div>

<!-- ═══ MY OPERATIONS TAB (Patient) ═══ -->
<div class="tab-pane" id="tab-ops" style="margin-left:240px;padding:2rem;display:none">
  <div class="content-card">
    <div class="card-header">
      <div class="header-icon" style="background:linear-gradient(135deg,#be185d,#ec4899)"><i class="fa fa-stethoscope"></i></div>
      <h3>My Operations</h3>
    </div>
    <div style="overflow-x:auto">
      <table class="hms-table">
        <thead><tr><th>#</th><th>Operation</th><th>Doctor</th><th>Date &amp; Time</th><th>Room</th><th>Status</th><th>Notes</th></tr></thead>
        <tbody>
          <?php
            $ops = mysqli_query($con,"SELECT * FROM operations WHERE pid='$pid' ORDER BY op_date ASC");
            $cnt = mysqli_num_rows($ops);
            if($cnt==0): ?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8">
            <i class="fa fa-check-circle" style="font-size:2rem;display:block;margin-bottom:0.5rem;color:#10b981"></i>
            No operations scheduled.
          </td></tr>
          <?php else: while($op=mysqli_fetch_array($ops)):
            $sc=$op['status']=='Completed'?'active':($op['status']=='Cancelled'?'cancelled':'cancelled-by'); ?>
          <tr>
            <td><span style="background:#fce7f3;color:#be185d;padding:2px 8px;border-radius:4px;font-size:0.75rem;font-weight:700">#<?php echo $op['op_id']; ?></span></td>
            <td><strong><?php echo $op['op_type']; ?></strong></td>
            <td><?php echo $op['doctor']; ?></td>
            <td><?php echo date('d M Y',strtotime($op['op_date'])); ?><br><small><?php echo date('h:i A',strtotime($op['op_time'])); ?></small></td>
            <td><span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-weight:600;font-size:0.8rem"><?php echo $op['op_room']; ?></span></td>
            <td><span class="badge-status <?php echo $sc; ?>"><?php echo $op['status']; ?></span></td>
            <td style="max-width:200px;white-space:normal;font-size:0.8rem;color:#64748b"><?php echo $op['notes']; ?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>

<script>
  function showTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(function(el){ el.classList.remove('active'); });
    document.querySelectorAll('.sidebar-item').forEach(function(el){ el.classList.remove('active'); });
    document.getElementById('tab-' + tabId).classList.add('active');
    if (btn) btn.classList.add('active');
  }
</script>
</body>
</html>

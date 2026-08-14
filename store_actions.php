<?php
session_start();
$con = mysqli_connect("localhost","root","","myhmsdb");

// ── ADMIN: Add Medicine ──────────────────────────────────────────
if(isset($_POST['add_medicine'])) {
    $name   = mysqli_real_escape_string($con, $_POST['med_name']);
    $cat    = mysqli_real_escape_string($con, $_POST['med_category']);
    $desc   = mysqli_real_escape_string($con, $_POST['med_desc']);
    $price  = (float)$_POST['med_price'];
    $stock  = (int)$_POST['med_stock'];
    $unit   = mysqli_real_escape_string($con, $_POST['med_unit']);
    $by     = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';
    $q = "INSERT INTO medicine_store(name,category,description,price,stock,unit,added_by)
          VALUES('$name','$cat','$desc',$price,$stock,'$unit','$by')";
    if(mysqli_query($con,$q))
        echo "<script>alert('Medicine added successfully!');window.history.back();</script>";
    else
        echo "<script>alert('Error: ".mysqli_error($con)."');window.history.back();</script>";
    exit;
}

// ── ADMIN: Delete Medicine ───────────────────────────────────────
if(isset($_POST['delete_medicine'])) {
    $mid = (int)$_POST['med_id'];
    if(mysqli_query($con,"DELETE FROM medicine_store WHERE med_id=$mid"))
        echo "<script>alert('Medicine deleted!');window.history.back();</script>";
    else
        echo "<script>alert('Error deleting!');window.history.back();</script>";
    exit;
}

// ── ADMIN: Update Stock ──────────────────────────────────────────
if(isset($_POST['update_stock'])) {
    $mid   = (int)$_POST['med_id'];
    $stock = (int)$_POST['new_stock'];
    if(mysqli_query($con,"UPDATE medicine_store SET stock=$stock WHERE med_id=$mid"))
        echo "<script>alert('Stock updated!');window.history.back();</script>";
    exit;
}

// ── ADMIN: Update Order Status ───────────────────────────────────
if(isset($_POST['update_order_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($con, $_POST['new_status']);
    mysqli_query($con,"UPDATE medicine_orders SET status='$status' WHERE order_id=$oid");
    echo "<script>alert('Order status updated!');window.history.back();</script>";
    exit;
}

// ── PATIENT / DOCTOR: Place Order ───────────────────────────────
if(isset($_POST['place_order'])) {
    $mid   = (int)$_POST['med_id'];
    $qty   = max(1,(int)$_POST['quantity']);
    $pid   = (int)($_POST['pid'] ?? 0);
    $oby   = mysqli_real_escape_string($con, $_POST['ordered_by']);
    $role  = mysqli_real_escape_string($con, $_POST['role'] ?? 'patient');

    // Get medicine details
    $med = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM medicine_store WHERE med_id=$mid"));
    if(!$med) { echo "<script>alert('Medicine not found!');window.history.back();</script>"; exit; }
    if($med['stock'] < $qty) { echo "<script>alert('Insufficient stock! Available: ".$med['stock']."');window.history.back();</script>"; exit; }

    $unit_price  = $med['price'];
    $total_price = $unit_price * $qty;
    $med_name    = mysqli_real_escape_string($con, $med['name']);

    $q = "INSERT INTO medicine_orders(pid,ordered_by,role,med_id,med_name,quantity,unit_price,total_price,status)
          VALUES($pid,'$oby','$role',$mid,'$med_name',$qty,$unit_price,$total_price,'Pending')";
    if(mysqli_query($con,$q)) {
        // Reduce stock
        mysqli_query($con,"UPDATE medicine_store SET stock=stock-$qty WHERE med_id=$mid");
        echo "<script>alert('Order placed successfully! Total: ₹".number_format($total_price,2)."');window.history.back();</script>";
    } else {
        echo "<script>alert('Error: ".mysqli_error($con)."');window.history.back();</script>";
    }
    exit;
}

// ── DOCTOR: Schedule Operation ──────────────────────────────────
if(isset($_POST['schedule_op'])) {
    $pid     = (int)$_POST['op_pid'];
    $fname   = mysqli_real_escape_string($con, $_POST['op_fname']);
    $lname   = mysqli_real_escape_string($con, $_POST['op_lname']);
    $doctor  = mysqli_real_escape_string($con, $_POST['op_doctor']);
    $op_type = mysqli_real_escape_string($con, $_POST['op_type']);
    $op_date = $_POST['op_date'];
    $op_time = $_POST['op_time'];
    $op_room = mysqli_real_escape_string($con, $_POST['op_room']);
    $notes   = mysqli_real_escape_string($con, $_POST['op_notes']);

    $q = "INSERT INTO operations(pid,fname,lname,doctor,op_type,op_date,op_time,op_room,notes,status)
          VALUES($pid,'$fname','$lname','$doctor','$op_type','$op_date','$op_time','$op_room','$notes','Scheduled')";
    if(mysqli_query($con,$q))
        echo "<script>alert('Operation scheduled successfully!');window.history.back();</script>";
    else
        echo "<script>alert('Error: ".mysqli_error($con)."');window.history.back();</script>";
    exit;
}

// ── ADMIN: Update Operation Status ──────────────────────────────
if(isset($_POST['update_op_status'])) {
    $oid    = (int)$_POST['op_id'];
    $status = mysqli_real_escape_string($con, $_POST['op_status']);
    $notes  = mysqli_real_escape_string($con, $_POST['op_notes_update'] ?? '');
    mysqli_query($con,"UPDATE operations SET status='$status', notes=IF('$notes'='',notes,'$notes') WHERE op_id=$oid");
    echo "<script>alert('Operation updated!');window.history.back();</script>";
    exit;
}

// Fallback
echo "<script>alert('Invalid action.');window.history.back();</script>";

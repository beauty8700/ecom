<?php
session_start();
include "db.php";

if (isset($_POST["f_name"])) {
	$f_name = $_POST["f_name"];
	$l_name = $_POST["l_name"];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$repassword = $_POST['repassword'];
	$mobile = $_POST['mobile'];
	$address1 = $_POST['address1'];
	$address2 = $_POST['address2'];

	$name = "/^[a-zA-Z ]+$/";
	$emailValidation = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9]+(\.[a-z]{2,4})$/";
	$number = "/^[0-9]+$/";

	if (empty($f_name) || empty($l_name) || empty($email) || empty($password) || empty($repassword) ||
		empty($mobile) || empty($address1) || empty($address2)) {
		echo "
			<div class='alert alert-warning'>
				<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
				<b>Please fill all fields!</b>
			</div>
		";
		exit();
	} else {
		if (!preg_match($name, $f_name)) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>The first name $f_name is not valid!</b>
				</div>
			";
			exit();
		}
		if (!preg_match($name, $l_name)) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>The last name $l_name is not valid!</b>
				</div>
			";
			exit();
		}
		if (!preg_match($emailValidation, $email)) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>The email $email is not valid!</b>
				</div>
			";
			exit();
		}
		if (strlen($password) < 9) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>Password is too short (must be at least 9 characters)</b>
				</div>
			";
			exit();
		}
		if ($password != $repassword) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>Passwords do not match!</b>
				</div>
			";
			exit();
		}
		if (!preg_match($number, $mobile)) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>The mobile number $mobile is not valid</b>
				</div>
			";
			exit();
		}
		if (strlen($mobile) != 10) {
			echo "
				<div class='alert alert-warning'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>Mobile number must be 10 digits</b>
				</div>
			";
			exit();
		}

		// Check for existing email
		$sql = "SELECT user_id FROM user_info WHERE email = '$email' LIMIT 1";
		$check_query = mysqli_query($con, $sql);
		$count_email = mysqli_num_rows($check_query);
		if ($count_email > 0) {
			echo "
				<div class='alert alert-danger'>
					<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
					<b>Email address is already taken. Please try another email address.</b>
				</div>
			";
			exit();
		} else {
			// Hash the password before storing it
			$hashed_password = password_hash($password, PASSWORD_BCRYPT);

			// Insert the user record without specifying user_id
			$sql = "INSERT INTO user_info 
					(first_name, last_name, email, password, mobile, address1, address2) 
					VALUES ('$f_name', '$l_name', '$email', '$hashed_password', '$mobile', '$address1', '$address2')";
			$run_query = mysqli_query($con, $sql);

			if ($run_query) {
				$_SESSION["uid"] = mysqli_insert_id($con);
				$_SESSION["name"] = $f_name;
				$ip_add = getenv("REMOTE_ADDR");

				// Update cart user_id
				$sql = "UPDATE cart SET user_id = '$_SESSION[uid]' WHERE ip_add='$ip_add' AND user_id = -1";
				if (mysqli_query($con, $sql)) {
					echo "register_success";
					echo "<script> location.href='store.php'; </script>";
					exit();
				}
			}
		}
	}
}
?>

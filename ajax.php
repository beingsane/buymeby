<?php

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	header('Location: /');
	die;
}

$action = $_POST['action'];
if($action == 'save_early_access')
{
	$res = save_early_access($_POST);
}
else if($action == 'save_message')
{
	$res = save_message($_POST);
}

echo json_encode($res);
return;






function save_early_access($data)
{
	connect_to_db();
	
	
	$email_reg = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
	if (!preg_match($email_reg, $data['email'])) {
		return array('status' => 'error', 'message' => 'Please write a valid email');
	}
	
	
	$email = mysql_real_escape_string($data['email']);
	$sql = "SELECT email FROM early_access WHERE email = ('".$email."')";
	$res = mysql_query($sql);
	if(mysql_num_rows($res) > 0)
	{
		return array('status' => 'error', 'message' => 'This email already exists in database');
	}

	
	$sql = "INSERT INTO early_access (email) VALUES ('".$email."')";
	$added = mysql_query($sql);
	
	if(!$added)
	{
		return array('status' => 'error', 'message' => 'Database error. Try again');
	}
	
	return array('status' => 'success', 'message' => 'Your email has been added');
}

function save_message($data)
{
	$email_reg = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
	if (!preg_match($email_reg, $data['email'])) {
		return array('status' => 'error', 'message' => 'Please write a valid email');
	}
	
	$res = send_email($data['email'], strip_tags($data['message']));
	if(!$res)
	{
		return array('status' => 'error', 'message' => 'Cannot send email. Try again');
	}
	
	// don't show error messages with database to user, because we have already sent an email
	connect_to_db();
	
	$name = mysql_real_escape_string($data['name']);
	$email = mysql_real_escape_string($data['email']);
	$message = mysql_real_escape_string(strip_tags($data['message']));
	$sql = "INSERT INTO messages (name, email, message) VALUES ('".$name."', '".$email."', '".$message."')";
	mysql_query($sql);
	
	return array('status' => 'success', 'message' => 'Your message has been sent');
}

function send_email($email, $message)
{
	include_once('phpmailer.php');
	
	$mailer = new PHPMailer();
	$mailer->setFrom('noreply@test.local', 'BuyMeBy');
	$mailer->addAddress($email);
	
	$mailer->Subject = '';
	$mailer->Body = $message;
	
	return $mailer->send();
}


function connect_to_db()
{
	$server = 'localhost';
	$user_name = 'root';
	$password = '';
	$database = 'test';
	
	mysql_connect($server, $user_name, $password);
	mysql_select_db($database);
}
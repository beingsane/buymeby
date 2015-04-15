<?php

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	header('Location: /');
	die;
}

try {
	$action = $_POST['action'];
	if($action == 'save_early_access')
	{
		$res = save_early_access($_POST);
	}
	else if($action == 'save_message')
	{
		$res = save_message($_POST);
	}
} catch (MongoException $e) {
	$res = array('status' => 'error', 'message' => 'Database error. Try again');
}

echo json_encode($res);
return;






function save_early_access($data)
{
	$email_reg = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
	if (!preg_match($email_reg, $data['email'])) {
		return array('status' => 'error', 'message' => 'Please write a valid email');
	}
	
	$db = connect_to_db();
	$collection = $db->early_access;
	
	$email = $data['email'];
	$criteria = array(
		'email' => $email,
	);
	$cursor = $collection->find($criteria);
	if($cursor->count() > 0)
	{
		return array('status' => 'error', 'message' => 'This email already exists in database');
	}
	
	$info = array(
		'email' => $email
	);
	$collection->insert($info);
	
	return array('status' => 'success', 'message' => 'Your email has been added');
}

function save_message($data)
{
	$email_reg = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
	if (!preg_match($email_reg, $data['email'])) {
		return array('status' => 'error', 'message' => 'Please write a valid email');
	}
	
	$name = strip_tags($data['name']);
	$email = strip_tags($data['email']);
	$message = strip_tags($data['message']);
	
	$res = send_email($name, $email, $message);
	if(!$res)
	{
		return array('status' => 'error', 'message' => 'Cannot send email. Try again');
	}
	
	
	$db = connect_to_db();
	$collection = $db->messages;
	$info = array(
		'name' => $name,
		'email' => $email,
		'message' => $message,
	);
	$collection->insert($info);

	return array('status' => 'success', 'message' => 'Your message has been sent');
}

function send_email($name, $email, $message)
{
	include_once('phpmailer.php');
	
	$company_email = 'company@test.local';
	
	$mailer = new PHPMailer();
	$mailer->setFrom($email, $name);
	$mailer->addAddress($company_email);
	
	$mailer->Subject = 'Question from site';
	$mailer->Body = $message;
	
	return $mailer->send();
}


function connect_to_db()
{
	$server = 'localhost';
	$database = 'test';
	
	$conn = new MongoClient($server);
	$db = $conn->$database;
	return $db;
}
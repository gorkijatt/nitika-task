<?php
require_once 'functions.php';

$message = '';
$success = false;

if (isset($_GET['email']) && isset($_GET['code'])) {
	$email = $_GET['email'];
	$code = $_GET['code'];

	if (verifySubscription($email, $code)) {
		$message = 'Subscription verified successfully!';
		$success = true;
	} else {
		$message = 'Verification failed. Invalid email or code.';
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	<title>Email Verification</title>
</head>

<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="verification-heading">Subscription Verification</h2>
	<?php if ($message): ?>
		<p><?php echo htmlspecialchars($message); ?></p>
	<?php endif; ?>
	<?php if ($success): ?>
		<p>You will now receive task reminders via email.</p>
	<?php endif; ?>
</body>

</html>
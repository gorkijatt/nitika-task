<?php
require_once 'functions.php';

$message = '';

if (isset($_GET['email'])) {
	$email = $_GET['email'];

	if (unsubscribeEmail($email)) {
		$message = 'You have been successfully unsubscribed from task notifications.';
	} else {
		$message = 'Unsubscribe failed. Email not found in subscribers list.';
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	<title>Unsubscribe</title>
</head>

<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
	<?php if ($message): ?>
		<p><?php echo htmlspecialchars($message); ?></p>
	<?php endif; ?>
</body>

</html>
<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask(string $task_name): bool
{
	$file  = __DIR__ . '/tasks.txt';

	$tasks = getAllTasks();

	// Check for duplicate tasks
	foreach ($tasks as $task) {
		if ($task['name'] === $task_name) {
			return false;
		}
	}

	$new_task = [
		'id' => uniqid(),
		'name' => $task_name,
		'completed' => false
	];

	$tasks[] = $new_task;

	return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array
{
	$file = __DIR__ . '/tasks.txt';

	if (!file_exists($file)) {
		return [];
	}

	$content = file_get_contents($file);
	if ($content === false || empty($content)) {
		return [];
	}

	$tasks = json_decode($content, true);
	return is_array($tasks) ? $tasks : [];
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool
{
	$file  = __DIR__ . '/tasks.txt';

	$tasks = getAllTasks();

	foreach ($tasks as &$task) {
		if ($task['id'] === $task_id) {
			$task['completed'] = $is_completed;
			return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
		}
	}

	return false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask(string $task_id): bool
{
	$file  = __DIR__ . '/tasks.txt';

	$tasks = getAllTasks();

	foreach ($tasks as $index => $task) {
		if ($task['id'] === $task_id) {
			unset($tasks[$index]);
			$tasks = array_values($tasks);
			return file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT)) !== false;
		}
	}

	return false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string
{
	return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): bool
{
	$file = __DIR__ . '/pending_subscriptions.txt';

	$code = generateVerificationCode();

	$pending = [];
	if (file_exists($file)) {
		$content = file_get_contents($file);
		if ($content) {
			$pending = json_decode($content, true) ?: [];
		}
	}

	$pending[$email] = [
		'code' => $code,
		'timestamp' => time()
	];

	file_put_contents($file, json_encode($pending, JSON_PRETTY_PRINT));

	$verification_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/verify.php?email=' . urlencode($email) . '&code=' . $code;

	$subject = 'Verify subscription to Task Planner';
	$body = '<p>Click the link below to verify your subscription to Task Planner:</p>
<p><a id="verification-link" href="' . $verification_link . '">Verify Subscription</a></p>';

	$headers = 'From: no-reply@example.com' . "\r\n" .
		'Content-Type: text/html; charset=UTF-8' . "\r\n";

	return mail($email, $subject, $body, $headers);
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool
{
	$pending_file     = __DIR__ . '/pending_subscriptions.txt';
	$subscribers_file = __DIR__ . '/subscribers.txt';

	if (!file_exists($pending_file)) {
		return false;
	}

	$pending = json_decode(file_get_contents($pending_file), true) ?: [];

	if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) {
		return false;
	}

	unset($pending[$email]);
	file_put_contents($pending_file, json_encode($pending, JSON_PRETTY_PRINT));

	$subscribers = [];
	if (file_exists($subscribers_file)) {
		$content = file_get_contents($subscribers_file);
		if ($content) {
			$subscribers = json_decode($content, true) ?: [];
		}
	}

	if (!in_array($email, $subscribers)) {
		$subscribers[] = $email;
		file_put_contents($subscribers_file, json_encode($subscribers, JSON_PRETTY_PRINT));
	}

	return true;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail(string $email): bool
{
	$subscribers_file = __DIR__ . '/subscribers.txt';

	if (!file_exists($subscribers_file)) {
		return false;
	}

	$subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];

	$key = array_search($email, $subscribers);
	if ($key !== false) {
		unset($subscribers[$key]);
		$subscribers = array_values($subscribers);
		file_put_contents($subscribers_file, json_encode($subscribers, JSON_PRETTY_PRINT));
		return true;
	}

	return false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void
{
	$subscribers_file = __DIR__ . '/subscribers.txt';

	if (!file_exists($subscribers_file)) {
		return;
	}

	$subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
	$all_tasks = getAllTasks();

	$pending_tasks = array_filter($all_tasks, function ($task) {
		return !$task['completed'];
	});

	if (empty($pending_tasks)) {
		return;
	}

	foreach ($subscribers as $email) {
		sendTaskEmail($email, $pending_tasks);
	}
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail(string $email, array $pending_tasks): bool
{
	$subject = 'Task Planner - Pending Tasks Reminder';

	$unsubscribe_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/unsubscribe.php?email=' . urlencode($email);

	$body = '<h2>Pending Tasks Reminder</h2>
<p>Here are the current pending tasks:</p>
<ul>';

	foreach ($pending_tasks as $task) {
		$body .= '<li>' . htmlspecialchars($task['name']) . '</li>';
	}

	$body .= '</ul>
<p><a id="unsubscribe-link" href="' . $unsubscribe_link . '">Unsubscribe from notifications</a></p>';

	$headers = 'From: no-reply@example.com' . "\r\n" .
		'Content-Type: text/html; charset=UTF-8' . "\r\n";

	return mail($email, $subject, $body, $headers);
}

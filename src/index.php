<?php
require_once 'functions.php';

// Handle form submissions
if ($_POST) {
	if (isset($_POST['task-name']) && !empty($_POST['task-name'])) {
		addTask($_POST['task-name']);
	}

	if (isset($_POST['email']) && !empty($_POST['email'])) {
		subscribeEmail($_POST['email']);
	}

	if (isset($_POST['task_id']) && isset($_POST['completed'])) {
		markTaskAsCompleted($_POST['task_id'], $_POST['completed'] === '1');
	}

	if (isset($_POST['delete_task_id'])) {
		deleteTask($_POST['delete_task_id']);
	}
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html>

<head>
	<title>Task Scheduler</title>
</head>

<body>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul class="tasks-list">
		<?php foreach ($tasks as $task): ?>
			<li class="task-item<?php echo $task['completed'] ? ' completed' : ''; ?>">
				<form method="POST" action="" style="display: inline;">
					<input type="checkbox" class="task-status" <?php echo $task['completed'] ? 'checked' : ''; ?>
						onchange="this.form.submit()">
					<input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
					<input type="hidden" name="completed" value="<?php echo $task['completed'] ? '0' : '1'; ?>">
				</form>
				<span><?php echo htmlspecialchars($task['name']); ?></span>
				<form method="POST" action="" style="display: inline;">
					<input type="hidden" name="delete_task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
					<button class="delete-task">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Subscription Form -->
	<form method="POST" action="">
		<input type="email" name="email" required />
		<button type="submit" id="submit-email">Subscribe</button>
	</form>

</body>

</html>
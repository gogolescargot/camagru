<header>
	<nav>
		<ul>
			<li><a href="/home">Home</a></li>
			
			<?php if (isset($_SESSION['user_id'])): ?>
				<li><a href="/logout">Logout</a></li>
			<?php else: ?>
				<li><a href="/login">Login</a></li>
				<li><a href="/register">Register</a></li>
			<?php endif; ?>
		</ul>
	</nav>
	<?php
	if (isset($_SESSION['success'])) {
		echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
		unset($_SESSION['success']);
	}
	if (isset($_SESSION['info'])) {
		echo '<p style="color: blue;">' . htmlspecialchars($_SESSION['info']) . '</p>';
		unset($_SESSION['info']);
	}
	if (isset($_SESSION['error'])) {
		echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
		unset($_SESSION['error']);
	}
	?>
</header>
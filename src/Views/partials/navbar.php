<header>
	<nav>
		<ul>
			<li><a href="/home">Home</a></li>
			
			<?php if (isset($_SESSION['user_id'])): ?>
				<li><a href="/studio">Studio</a></li>
				<li><a href="/settings">Settings</a></li>
				<li><a href="/logout">Logout</a></li>
			<?php else: ?>
				<li><a href="/login">Login</a></li>
				<li><a href="/register">Register</a></li>
			<?php endif; ?>
		</ul>
	</nav>
	<?php
		if (isset($_SESSION['success'])) {
			echo '<p class="success">' . htmlspecialchars($_SESSION['success']) . '</p>';
			unset($_SESSION['success']);
		}
		if (isset($_SESSION['info'])) {
			echo '<p class="info">' . htmlspecialchars($_SESSION['info']) . '</p>';
			unset($_SESSION['info']);
		}
		if (isset($_SESSION['error'])) {
			echo '<p class="error">' . htmlspecialchars($_SESSION['error']) . '</p>';
			unset($_SESSION['error']);
		}
	?>
</header>
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
		foreach (['success', 'info', 'error'] as $type) {
			if (isset($_SESSION[$type])) {
				echo '<div class="banner ' . $type . '">' . htmlspecialchars($_SESSION[$type]) . '</div>';
				unset($_SESSION[$type]);
			}
		}
	?>
</header>
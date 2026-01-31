<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Home</title>
		<link rel="stylesheet" href="/css/style.css">
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<div id="posts-container">
				<?php if (empty($posts)): ?>
					<p class="info" style="text-align:center;">No posts yet.</p>
				<?php else: ?>
					<?php foreach ($posts as $post): ?>
						<div class="post">
							<p class="author"><?= htmlspecialchars($post['username'])?></p>
							<img class="image" src="/uploads/<?= htmlspecialchars($post['image_path']) ?>" alt="<?= htmlspecialchars($post['title'] ?? '') ?>">
							<?php if (!empty($post['title'])): ?>
								<p class="title"><?= htmlspecialchars($post['title']) ?></p>
							<?php endif; ?>
							<p class="date"><?= htmlspecialchars($post['created_at']) ?></p>
							<p class="likes">Likes: <?= htmlspecialchars($post['like_count']) ?></p>
							
							<div class="comments-container">
								<p>Comments:</p>
								<?php if (empty($post['comments'])): ?>
									<p class="info">No comments yet.</p>
								<?php else: ?>
									<?php foreach ($post['comments'] as $comment): ?>
										<div class="comment">
											<p class="author"><?= htmlspecialchars($comment['username'])?></p>
											<p class="content"><?= htmlspecialchars($comment['content']) ?></p>
											<p class="date"><?= htmlspecialchars($comment['created_at'])?></p>
											<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']):?>
												<form method="post" action="/delete-comment?comment_id=<?= htmlspecialchars($comment['id'])?>">
													<button type="submit" class="delete-button">Delete Comment</button>
												</form>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>

							<?php if (isset($_SESSION['user_id'])): ?>
								<form method="post" action="/like?post_id=<?= htmlspecialchars($post['id'])?>">	
									<button type="submit"><?= $post['liked'] ? "Unlike" : "Like" ?></button>
								</form>
								<form method="post" action="/comment?post_id=<?= htmlspecialchars($post['id'])?>">
									<input type="text" name="content" placeholder="Type your comment" maxlength="500" required>
									<button type="submit">Send</button>
								</form>
							<?php endif; ?>

							<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']):?>
								<form method="post" action="/delete-post?post_id=<?= htmlspecialchars($post['id'])?>">
									<button type="submit" class="delete-button">Delete Post</button>
								</form>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="pagination">
				<?php if ($page > 1): ?>
					<a href="/home?page=<?= max(1, $page - 1) ?>" class="auth-link">Previous</a>
				<?php endif; ?>
				<?php if (count($posts) == $limit):?>
					<a href="/home?page=<?= $page + 1 ?>" class="auth-link">Next</a>
				<?php endif; ?>
			</div>
		</main>
		<?php include __DIR__ . '/partials/footer.php'; ?>
	</body>
</html>
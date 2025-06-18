<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Home</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<?php include __DIR__ . '/partials/navbar.php'; ?>
		<main>
			<h1>Home</h1>
			<div id="posts-container">
				<?php if (empty($posts)): ?>
					<p>No posts yet.</p>
				<?php else: ?>
					<?php foreach ($posts as $post): ?>
						<div class="post">
							<p class="author"><?= htmlspecialchars($post['username'])?></p>
							<img class="image" src="/uploads/<?= htmlspecialchars($post['image_path']) ?>" alt="<?= htmlspecialchars($post['title'] ?? '') ?>">
							<p class="title"><?= htmlspecialchars($post['title']) ?></p>
							<p class="date"><?= htmlspecialchars($post['created_at']) ?></p>
							<p>Likes: <?= htmlspecialchars($post['like_count']) ?></p>
							
							<p>Comments:</p>
							<div class="comments-container">
								<?php if (empty($post['comments'])): ?>
									<p>No comments yet.</p>
								<?else: ?>
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
									<input type="text" name="content" required>
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
					<a href="/home?page=<?= max(1, $page - 1) ?>">Previous</a>
				<?php endif; ?>
				<?php if (count($posts) == $limit):?>
					<a href="/home?page=<?= $page + 1 ?>">Next</a>
				<?php endif; ?>
			</div>
		</main>
	</body>
</html>
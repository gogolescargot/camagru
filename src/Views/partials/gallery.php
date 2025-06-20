<?php if (empty($images)): ?>
	<p>No images yet.</p>
<?php else: ?>
	<?php foreach ($images as $image): ?>
		<div class="gallery">
			<img class="image" src="/uploads/<?= htmlspecialchars($image['path']) ?>" draggable="true">

			<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $image['user_id']):?>
				<form method="post" action="/delete-image?image_id=<?= htmlspecialchars($image['id'])?>">
					<button type="submit" class="delete-button">Delete Image</button>
				</form>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
<?php include "inc/head.php" ?>
<body id="<?php echo $current->slug() ?>">

<?php include "inc/header.php" ?>

<div id="primary">
	<article>
		<h1><a href="<?php echo $current->permalink() ?>"><?php echo $current->title() ?></a></h1>
		<?php echo $current->content() ?>
		<footer>
			<div class="pubdate">
				<h3>Posted</h3>
				<p>
					<time datetime="<?php echo $current->published('c') ?>">
						<?php echo $current->published('F jS Y') ?>
					</time>
			</p>
			</div>
			
			<?php if($current->metadata("tags")): ?>
			<div class="tags">
				<h3>Tagged</h3>
				<ul>
				<?php foreach($current->metadata("tags") as $tag): ?>
					<li><a href="<?php echo $site_root ?>/posts/tagged/<?php echo urlencode($tag) ?>"><?php echo $tag ?></a></li>
				<?php endforeach ?>
				</ul>
			</div>
			<?php endif ?>
		</footer>
	</article>
</div>

<?php include "inc/footer.php" ?>

</body>
<?php include "inc/foot.php" ?>
<?php
include 'core/init.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Blogs</title>
	<link rel="stylesheet" href="css/reset.css">
	<link rel="stylesheet" href="css/blogs.css">
</head>
<body>
	<div class="nav">
		<a href="#" class="button left">Home</a>
		<a href="#" class="button left">Volunteer</a>
		<a href="#" class="button left">Intern</a>
		<a href="#" class="button right">About Us</a>
		<a href="#" class="button right">Participate</a>
		<a href="#" class="button right">Research</a>
		<div class="clearfix"></div>
	</div>
	<div class="content">
		<h1>WorkTeen's Blogs.</h1>
		<div class="blogpostHolder">
			<?php 
				if (isset($_GET['id'])) {
					$id = $_GET['id'];
					$result = blogpost_get_by_id($id);
					if ($result === false) {

					} else {
			 ?>
				<div class="blogpost">
					<h1><?php echo text_db_get_by_id($result['title']); ?></h1>
					<div class="author left">Author: <?php echo text_db_get_by_id($result['author']); ?></div>
					<div class="date right"><?php echo text_db_get_by_id($result['date']); ?></div>
					<div class="clearfix"></div>
					<p class="posttext">
						<?php echo text_db_get_by_id($result['posttext']); ?>
					</p>
				</div>
			<?php 
					}
				} else {
			?>
			<script>
				var blogpostHolder = document.getElementsByClassName('blogpostHolder')[0];

				function addBlogPost(id) {
					var xhr = new XMLHttpRequest();
					xhr.open("GET", "ajax_dispatcher.php?function=blogpost_get_by_id&data=" + id);
					xhr.onreadystatechange = function() {
						if (xhr.status == 200 && xhr.readyState == 4) {
							var obj2 = JSON.parse(xhr.responseText);
							var blogpost = document.createElement("div");
							blogpost.className = "blogpost";
					blogpost.innerHTML = '<h1>' + obj2.title + '</h1><div class="author left">Author: ' + obj2.author + '</div><div class="date right">' + obj2.date + '</div><div class="clearfix"></div><p class="posttext">' + obj2.posttext + '</p>';
							blogpostHolder.appendChild(blogpost);
						}
					};
					xhr.send(null);
				}

				var xhr = new XMLHttpRequest();
				xhr.open("GET", "ajax_dispatcher.php?function=blogpost_get_ids_desc&data=");
				xhr.onreadystatechange = function() {
					if (xhr.status == 200 && xhr.readyState == 4) {
						var obj = JSON.parse(xhr.responseText);
						if (obj.status === undefined) {
							for (var i= 0; i < obj.ids.length; i++) {
								addBlogPost(obj.ids[i].id);
							}
						}
						return;	
					}
				};
				xhr.send(null);

			</script>
			<?php
				}
			?>
		</div>
	</div>
</body>
</html>
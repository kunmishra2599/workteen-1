<?php

include 'core/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Workteen | Blogs Panel</title>
	<link rel="stylesheet" href="css/reset.css">

	<link rel="stylesheet" href="css/blogspanel.css">
</head>
<body>
	<div class="alert">
	</div>

	<div class="sidebar">
		<div class="button" id="Add Blogpost">
			Add Blogpost
		</div>
		<div class="button" id="Edit Blogpost">
			Edit Blogpost
		</div>
	</div>

	<div class="workbench">
		<h1>Workbench</h1>
		<div class="widgetHolder">
			<!--<div class="widget">
				<div class="widget-titlebar">
					<h1>title</h1>
					<div class="cross"></div>
				</div>
				<div class="widget-content">
					<div class="blogpost">
						<div class="title">title</div>
						<div class="edit">Edit</div>
						<div class="delete">Delete</div>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>-->
		</div>
	</div>
	<script>
		var body = document.body;
		var sidebar = document.getElementsByClassName('sidebar')[0];
		var workbench = document.getElementsByClassName('workbench')[0];
		var widgetHolder = document.getElementsByClassName('widgetHolder')[0];
		var buttons = sidebar.getElementsByClassName('button');

		function resize() {
			workbench.style.left = (100 + 10) + 'px';
			workbench.style.width = (window.innerWidth - (100 + 20)) + 'px';
		}
		resize();

		window.addEventListener("resize", resize);

		function alert(text) {
			var obj = document.getElementsByClassName('alert')[0];
			obj.style.display = "block";
			obj.innerHTML = text;
			setTimeout(function(){
				obj.innerHTML = "";
				obj.style.display = "none";
			}, 3000);
		}

		function widgetOppened(widgetName) {
			var widgets = widgetHolder.getElementsByClassName('widget');
			for (var i = 0; i < widgets.length; i++) {
				if (widgets[i].id == widgetName) {
					return true;
				}
			}
			return false;
		}

		function buildWidget(widget) {
			var widgetTitlebar = document.createElement("div");
			widgetTitlebar.className = "widget-titlebar";
			widget.appendChild(widgetTitlebar);
			widgetTitlebar.innerHTML = "<h1>" + widget.id + "</h1><div class=\"cross\"></div>";
			var widgetContent = document.createElement("div");
			widgetContent.className = "widget-content";
			widget.appendChild(widgetContent);
			var cross = widget.getElementsByClassName('cross')[0];
			cross.addEventListener("click", function(){
				widgetHolder.removeChild(widget);
			}, false);
			if (widget.id == "Add Blogpost") {
				widgetContent.innerHTML = "<form id=\"addBlogpost\"><input type=\"text\" placeholder=\"Title\" id=\"a_title\"><input type=\"text\" placeholder=\"Date\" id=\"a_date\"><input type=\"text\" placeholder=\"Author\" id=\"a_author\"><textarea cols=\"30\" rows=\"10\" placeholder=\"Blog Post\" id=\"a_posttext\"></textarea><input type=\"submit\" value=\"Add Blog Post\"></form>";
				var addForm = document.getElementById('addBlogpost');
				addForm.addEventListener("submit", function(e){
					e.preventDefault();
					var title = document.getElementById('a_title').value;
					var date = document.getElementById('a_date').value;
					var author = document.getElementById('a_author').value;
					var posttext = document.getElementById('a_posttext').value;

					if (title.length <= 1) {
						alert('Title field is empty');
					} else {
						var xhr = new XMLHttpRequest();
						xhr.onreadystatechange = function() {
							if (xhr.readyState == 4 && xhr.status == 200) {
								alert('Blog Post Was Successfully Added');
								widgetHolder.removeChild(widget);
								addWidget('Add Blogpost');
							}
						};
						xhr.open("GET", "ajax_dispatcher.php?function=blogpost_db_insert&data=" + title + "/-/" + date + "/-/" + author + "/-/" + posttext);
						xhr.send(null);	
					}

				}, false);
			} else {
				addBlogPosts(widgetContent);
			}
		}

		function addBlogPosts(widgetContent) {
			var xhr = new XMLHttpRequest();
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4 && xhr.status == 200) {
					if (JSON.parse(xhr.responseText).status == 0) {
						alert('no new blogposts');
					} else {
						var obj = JSON.parse(xhr.responseText);
						for(var i = 0; i < obj.ids.length; i++) {
							var id = obj.ids[i].id;
							addBlogPost(widgetContent, id);
						}
					}
				}
			};
			xhr.open("GET", "ajax_dispatcher.php?function=blogpost_get_ids_desc&data=");
			xhr.send(null);
		}

		function addBlogPost(widgetContent, id) {
			var xhr2 = new XMLHttpRequest();
			xhr2.onreadystatechange = function(){
				if (xhr2.readyState == 4 && xhr2.status == 200) {
					var obj2 = JSON.parse(xhr2.responseText);
					console.log(obj2);
					var blogpost = document.createElement('div');
					blogpost.id = id;
					blogpost.className = "blogpost";
					blogpost.innerHTML = "<div class=\"title\">" + obj2.title + "</div><div class=\"edit\">Edit</div><div class=\"delete\">Delete</div><div class=\"view\">View</div><div class=\"clearfix\"></div>";
					widgetContent.appendChild(blogpost);
					blogpost.getElementsByClassName('view')[0].addEventListener("click", function(){
						location = '/workteen/blogs.php?id=' + blogpost.id;
					}, false);
					blogpost.getElementsByClassName('delete')[0].addEventListener("click", function(){
						var r = window.confirm("Are u sure, u wannah delte this blog post?");
						if (r === true) {
							alert('Blogpost Deleted');
							var xhr = new XMLHttpRequest();
							xhr.open("GET", "ajax_dispatcher.php?function=blogpost_db_delete&data=" + blogpost.id);
							xhr.send(null);
							widgetContent.innerHTML = "";
							addBlogPosts(widgetContent);
						}
					}, false);
					blogpost.getElementsByClassName('edit')[0].addEventListener("click", function(){
						var xhr = new XMLHttpRequest();
						xhr.open("GET", "ajax_dispatcher.php?function=blogpost_get_by_id&data=" + blogpost.id);
						xhr.onreadystatechange = function(){
							if (xhr.readyState == 4 && xhr.status == 200) {
								var obj = JSON.parse(xhr.responseText);
								var title = obj.title;
								var date = obj.date;
								var author = obj.author;
								var posttext = obj.posttext;
								widgetContent.innerHTML = '<form id="editBlogpost"><input type="text" placeholder="Title" id="e_title" value="' + title + '"><input type="text" placeholder="Date" id="e_date" value="' + date + '"><input type="text" placeholder="Author" id="e_author" value="' + author + '"><textarea cols="30" rows="10" placeholder="Blog Post" id="e_posttext">' + posttext + '</textarea><input type="submit" value="Save Changes"></form>';
								var editForm = document.getElementById('editBlogpost');
								editForm.addEventListener("submit", function(e){
									e.preventDefault();
									var ntitle = document.getElementById('e_title').value;
									var ndate = document.getElementById('e_date').value;
									var nauthor = document.getElementById('e_author').value;
									var nposttext = document.getElementById('e_posttext').value;
									if (ntitle <= 2) {
										alert('Title is too short');
									} else {
										if ((title == ntitle) && (date == ndate) && (author==nauthor) && (posttext == nposttext)) {
											alert('No changes were made');
										} else {
											var xhr3 = new XMLHttpRequest();
											xhr3.open("GET", "ajax_dispatcher.php?function=blogpost_db_update&data=" + ntitle + "/-/" + ndate + "/-/" + nauthor + "/-/" + nposttext + "/-/" + blogpost.id);
											xhr3.send(null);
											alert('Changes Saved');
											widgetContent.innerHTML = "";
											addBlogPosts(widgetContent);
										}
									}
								}, false);
							}
						};
						xhr.send(null);

					});
				}
			};
			xhr2.open("GET", "ajax_dispatcher.php?function=blogpost_get_by_id&data=" + id);
			xhr2.send();
		}

		function addWidget(widgetName) {
			console.log(widgetName);
			if (widgetOppened(widgetName) === false) {
				var widget = document.createElement("div");
				widget.className = "widget";
				widget.id = widgetName;
				widgetHolder.appendChild(widget);
				buildWidget(widget);
				return;
			}
			alert(widgetName + ' Tab is already openned');
			return;
		}
		var i;
		for (i = 0; i < buttons.length; i++) {
			(function() {
				var button = buttons[i];
				button.addEventListener("click", function(){
					addWidget(button.id);
				}, false)
			}());
		}


	</script>
</body>
</html>
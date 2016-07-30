<?php
session_start();
include 'core/init.php';
if (isset($_SESSION['admin_id'])) {
	header("Location: blogspanel.php");
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Admin</title>
	<link rel="stylesheet" href="css/reset.css">
	<link rel="stylesheet" href="css/admin.css">
</head>
<body>
	<div class="formHolder">
		<form action="admin.php" method="post">
			<?php
				if (isset($_POST['admin']) && isset($_POST['password'])) {
					$admin = $_POST['admin'];
					$password = $_POST['password'];
					$login = admin_login($admin, $password);
					if ($login === false) {
						echo "<div class='alert'>" . "Wrong Admin/Password" . "</div>";
					} else {
						$_SESSION['admin_id'] = $login;
						header("Location: blogspanel.php");
						exit();
					}
				}
			?>
			<input type="text" name="admin" placeholder="Admin">
			<input type="password" name="password" placeholder="Password">
			<input type="submit" value="Log In">
		</form>
	</div>
	<script>
		var formHolder = document.getElementsByClassName('formHolder')[0];
		window.addEventListener('resize', function(){
			resize();
		}, false);
		function resize() {
			var height = formHolder.offsetHeight;
			formHolder.style.marginTop = ((window.innerHeight - height) / 2) + 'px';
		}
		resize();
	</script>
</body>
</html>
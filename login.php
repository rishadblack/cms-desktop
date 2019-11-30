<?php  
	include dirname(__FILE__) .'/include/header.php';
	if ($application->isLoggedIn()) {
		redirect('/',$url);
	}
?>
<div class="wrapper wrapper-content">
	<div class="middle-box text-center loginscreen animated fadeInDown">
		<div id="login-form">
			<div>
				<h1>Appsowl</h1>
			</div>
			<h3>Welcome to Appsowl Pos Desktop</h3>
			<p>Software helps you in digitalization and develop your life.
				<!--Continually expanded and constantly improved Inspinia Admin Them (IN+)-->
			</p>
			<p>Login in. To see it in action.</p>
			<form>
				<div class="form-group">
					<input type="text" class="form-control" placeholder="Username" name="username">
				</div>
				<div class="form-group">
					<input type="password" class="form-control" placeholder="Password" name="password">
				</div>
				<button type="submit" class="btn btn-primary block full-width m-b">Login</button>
			</form>
			<p class="m-t"> <small><strong>Powored By</strong>: Software Galaxy Ltd &copy; 2018</small> </p>
		</div>
	</div>
</div>
<?php include dirname(__FILE__) .'/include/footer.php';?> 


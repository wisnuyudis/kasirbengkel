<?= $this->extend('themes/modern/register/layout') ?>
<?= $this->section('content') ?>
<div class="card-header transparent-header">
	<div class="logo">
		<img src="<?php echo $config->baseURL . '/public/images/' . $setting_aplikasi['logo_login']?>">
	</div>
	
	<?php if (!empty($desc)) {
		echo '<p>' . $desc . '</p>';
	}?>
</div>
<div class="card-body">
	<?php
	
	if (!empty($message)) {?>
		<div class="alert alert-danger">
			<?=$message?>
		</div>
	<?php }
	//echo password_hash('admin', PASSWORD_DEFAULT);
	?>
	<form method="post" action="" class="form-horizontal form-login">
		<div class="input-group mb-3">
			<div class="input-group-prepend login-input">
				<span class="input-group-text mt-1">
					<i class="fa fa-user"></i>
				</span>
			</div>
			<input type="text" name="username" value="<?=@$_POST['username']?>" class="form-control login-input" placeholder="Username" aria-label="Username" required>
		</div>
		<div class="input-group mb-3">
			<div class="input-group-prepend login-input">
				<span class="input-group-text mt-1">
					<i class="fa fa-lock"></i>
				</span>
			</div>
			<input type="password"  name="password" class="form-control login-input" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" required>
		</div>
		<div class="form-check">
			<input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberme">
			<label class="form-check-label" for="rememberme" style="font-weight:normal">Remember me</label>
		</div>
		<div class="mb-2 mt-3">
			<button id="btn-submit-login" type="submit" class="form-control btn <?=$setting_aplikasi['btn_login']?>" name="submit">Submit</button>
			<?php
				$form_token = $auth->generateFormToken('login_form_token');
			?>
			<?= csrf_formfield() ?>
		</div>
	</form>
</div>
<div class="card-footer">
	<p>Lupa Password? <a href="<?=$config->baseURL?>recovery">Request reset password</a></p>
</div>

<?= $this->endSection() ?>
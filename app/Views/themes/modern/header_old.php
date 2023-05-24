<?php 
/**
*	App Name	: Admin Template Dashboard Codeigniter 4
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2022
*/

if (empty($_SESSION['user'])) {
	$content = 'Layout halaman ini memerlukan login';
	include ('app/Views/themes/modern/header-error.php');
	exit;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<title><?=$current_module['judul_module']?> | <?=$setting_aplikasi['judul_web']?></title>
<meta name="descrition" content="<?=$current_module['deskripsi']?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="<?=$config->baseURL . 'public/images/favicon.png?r='.time()?>" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/font-awesome/css/all.css'?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/bootstrap-custom.css?r=' . time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/site.css?r='.time()?>"/>

<!-- Data Tables -->
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css?r='.time()?>"/>
<!-- // Data Tables -->

<link rel="stylesheet" id="style-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="style-switch-sidebar" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css?r='.time()?>"/>
<link rel="stylesheet" id="font-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/fonts/'.$app_layout['font_family'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="font-size-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/fonts/font-size-'.$app_layout['font_size'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="logo-background-color-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css?r='.time()?>"/>

<!-- Dynamic styles -->
<?php
if (@$styles) {
	foreach($styles as $file) {
		echo '<link rel="stylesheet" data-type="dynamic-resource-head" type="text/css" href="'.$file.'?r='.time().'"/>' . "\n";
	}
}

?>

<script type="text/javascript">
	var base_url = "<?=$config->baseURL?>";
	var module_url = "<?=$module_url?>";
	var current_url = "<?=current_url()?>";
	var theme_url = "<?=$config->baseURL . '/public/themes/modern/builtin/'?>";
</script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/themes/modern/builtin/js/functions.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/themes/modern/builtin/js/site.js?r='.time()?>"></script>

<!-- Data Tables -->
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js?r='.time()?>"></script>
<!-- // Data Tables -->

<!-- Dynamic scripts -->
<?php
if (@$scripts) {
	foreach($scripts as $file) {
		if (is_array($file)) {
			if ($file['print']) {
				echo '<script data-type="dynamic-resource-head" type="text/javascript">' . $file['script'] . '</script>' . "\n";
			}
		} else {
			echo '<script data-type="dynamic-resource-head" type="text/javascript" src="'.$file.'?r='.time().'"></script>' . "\n";
		}
	}
}

$user = $_SESSION['user'];

?>
</head>
<body>
	<header class="nav-header shadow">
		<div class="nav-header-logo pull-left">
			<a class="header-logo" href="<?=$config->baseURL?>" title="Jagowebdev">
				<img src="<?=$config->baseURL . '/public/images/' . $setting_aplikasi['logo_app']?>"/>
			</a>
		</div>
		<div class="pull-left nav-header-left">
			<ul class="nav-header">
				<li>
					<a href="#" id="mobile-menu-btn">
						<i class="fa fa-bars"></i>
					</a>
				</li>
			</ul>
		</div>
		<div class="pull-right mobile-menu-btn-right">
			<a href="#" id="mobile-menu-btn-right">
				<i class="fa fa-ellipsis-h"></i>
			</a>
		</div>
		<div class="pull-right nav-header nav-header-right">
			
			<ul>
				</li>
				<?php 
				$total_notifikasi = $setting_piutang['jml_lewat_jatuh_tempo'] + $setting_piutang['jml_akan_jatuh_tempo'];
				$show_dropdown = $total_notifikasi > 0 ? true : false;
				if ($total_notifikasi > 99) {
					$total_notifikasi = '99+';
				}
				if ($setting_piutang['notifikasi_show'] == 'Y') 
				{
					echo '<li class="dropdown">
							<a href="#" class="icon-link" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="far fa-bell"></i>
								<span class="badge rounded-pill badge-notification ' . ($total_notifikasi == 0 ? 'bg-success' : 'bg-danger') . ' position-absolute translate-middle" style="font-size:10px; top:20px">' . $total_notifikasi . '</span>
							</a>';
							
							if ($show_dropdown) {
								$periode_piutang = $setting_piutang['periode_penjualan_piutang'];
								echo '
								<div class="dropdown-menu p-3">
									<label>Hutang</label>
									<hr/>
									<div class="d-flex justify-content-between align-items-start text-nowrap py-2">';
										if ($setting_piutang['jml_akan_jatuh_tempo'] > 0) {
											echo '<a title="Daftar Piutang Akan Jatuh Tempo" href="' . base_url() . '/penjualan-tempo?start_date=' . $periode_piutang['start_date']  . '&end_date=' . $periode_piutang['end_date'] . ' &jatuh_tempo=akan_jatuh_tempo" class="pe-2">Jatuh tempo dalam ' . $setting_piutang['notifikasi_periode'] . ' hari</a>';
										} else {
											echo '<span class="pe-2">Jatuh tempo dalam ' . $setting_piutang['notifikasi_periode'] . ' hari</span>';
										}
										echo '<span class="badge rounded-pill ' . ($setting_piutang['jml_akan_jatuh_tempo'] == 0 ? 'bg-success' : 'bg-danger') . '">' . $setting_piutang['jml_akan_jatuh_tempo'] . '</span>
									</div>
									<div class="d-flex justify-content-between align-items-start text-nowrap py-2">';
										if ($setting_piutang['jml_lewat_jatuh_tempo'] > 0) {
											echo '<a title="Daftar Piutang Lewat Jatuh Tempo" href="' . base_url() . '/penjualan-tempo?start_date=' . $periode_piutang['start_date']  . '&end_date=' . $periode_piutang['end_date'] . ' &jatuh_tempo=lewat_jatuh_tempo" class="pe-2">Lewat ' . $setting_piutang['piutang_periode'] . ' hari</a>';
										} else {
											echo '<span class="pe-2">Lewat ' . $setting_piutang['piutang_periode'] . ' hari</span>';
										}
										echo '<span class="badge rounded-pill ' . ($setting_piutang['jml_lewat_jatuh_tempo'] == 0 ? 'bg-success' : 'bg-danger') . '">' . $setting_piutang['jml_lewat_jatuh_tempo'] . ' </span>
									</div>
								</div>';
							}
					echo '
					</li>';
				}
				?>
				</li>
				<?php 
				$total_notifikasi = $setting_piutang['jml_lewat_jatuh_tempo'] + $setting_piutang['jml_akan_jatuh_tempo'];
				$show_dropdown = $total_notifikasi > 0 ? true : false;
				if ($total_notifikasi > 99) {
					$total_notifikasi = '99+';
				}
				if ($setting_piutang['notifikasi_show'] == 'Y') 
				{
					echo '<li class="dropdown">
							<a href="#" class="icon-link" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="far fa-bell"></i>
								<span class="badge rounded-pill badge-notification ' . ($total_notifikasi == 0 ? 'bg-success' : 'bg-danger') . ' position-absolute translate-middle" style="font-size:10px; top:20px">' . $total_notifikasi . '</span>
							</a>';
							
							if ($show_dropdown) {
								$periode_piutang = $setting_piutang['periode_penjualan_piutang'];
								echo '
								<div class="dropdown-menu p-3">
									<label>Piutang</label>
									<hr/>
									<div class="d-flex justify-content-between align-items-start text-nowrap py-2">';
										if ($setting_piutang['jml_akan_jatuh_tempo'] > 0) {
											echo '<a title="Daftar Piutang Akan Jatuh Tempo" href="' . base_url() . '/penjualan-tempo?start_date=' . $periode_piutang['start_date']  . '&end_date=' . $periode_piutang['end_date'] . ' &jatuh_tempo=akan_jatuh_tempo" class="pe-2">Jatuh tempo dalam ' . $setting_piutang['notifikasi_periode'] . ' hari</a>';
										} else {
											echo '<span class="pe-2">Jatuh tempo dalam ' . $setting_piutang['notifikasi_periode'] . ' hari</span>';
										}
										echo '<span class="badge rounded-pill ' . ($setting_piutang['jml_akan_jatuh_tempo'] == 0 ? 'bg-success' : 'bg-danger') . '">' . $setting_piutang['jml_akan_jatuh_tempo'] . '</span>
									</div>
									<div class="d-flex justify-content-between align-items-start text-nowrap py-2">';
										if ($setting_piutang['jml_lewat_jatuh_tempo'] > 0) {
											echo '<a title="Daftar Piutang Lewat Jatuh Tempo" href="' . base_url() . '/penjualan-tempo?start_date=' . $periode_piutang['start_date']  . '&end_date=' . $periode_piutang['end_date'] . ' &jatuh_tempo=lewat_jatuh_tempo" class="pe-2">Lewat ' . $setting_piutang['piutang_periode'] . ' hari</a>';
										} else {
											echo '<span class="pe-2">Lewat ' . $setting_piutang['piutang_periode'] . ' hari</span>';
										}
										echo '<span class="badge rounded-pill ' . ($setting_piutang['jml_lewat_jatuh_tempo'] == 0 ? 'bg-success' : 'bg-danger') . '">' . $setting_piutang['jml_lewat_jatuh_tempo'] . ' </span>
									</div>
								</div>';
							}
					echo '
					</li>';
				}
				?>
				<li><a class="icon-link" href="<?=$config->baseURL?>builtin/setting-layout"><i class="fas fa-cog"></i></a></li>
				<li>
					<?php 
					$img_url = !empty($user['avatar']) && file_exists(ROOTPATH . '/public/images/user/' . $user['avatar']) ? $config->baseURL . '/public/images/user/' . $user['avatar'] : $config->baseURL . '/public/images/user/default.png';
					$account_link = $config->baseURL . 'user';
					?>
					<a class="profile-btn" href="<?=$account_link?>"><img src="<?=$img_url?>" alt="user_img"></a>
					<div class="account-menu-container shadow-sm">
						<?php
						if ($isloggedin) { 
							?>
							<ul class="account-menu">
								<li class="account-img-profile">
									<div class="avatar-profile">
										<a href="<?=$config->baseURL . 'builtin/user/edit?id=' . $user['id_user'];?>">
											<img src="<?=$img_url?>" alt="user_img">
										</a>
									</div>
									<div class="card-content">
									<p><?=strtoupper($user['nama'])?></p>
									<p><small>Email: <?=$user['email']?></small></p>
									</div>
								</li>
								<li><a href="<?=$config->baseURL?>builtin/user/edit-password">Change Password</a></li>
								<li><a href="<?=$config->baseURL?>login/logout">Logout</a></li>
							</ul>
						<?php } else { ?>
							<div class="float-login">
							<form method="post" action="<?=$config->baseURL?>login">
								<input type="email" name="email" value="" placeholder="Email" required>
								<input type="password" name="password" value="" placeholder="Password" required>
								<div class="checkbox">
									<label style="font-weight:normal"><input name="remember" value="1" type="checkbox">&nbsp;&nbsp;Remember me</label>
								</div>
								<button type="submit"  style="width:100%" class="btn btn-success" name="submit">Submit</button>
								<?php
								$form_token = $auth->generateFormToken('login_form_token_header');
								?>
								<input type="hidden" name="form_token" value="<?=$form_token?>"/>
								<input type="hidden" name="login_form_header" value="login_form_header"/>
							</form>
							<a href="<?=$config->baseURL . 'recovery'?>">Lupa password?</a>
							</div>
						<?php }?>
					</div>
				</li>
			</ul>
		
		</div>
	</header>
	<div class="site-content">
		<div class="sidebar shadow">
			<nav>
				<?php
				foreach ($menu as $val) {
					$kategori = $val['kategori'];
					if ($kategori['show_title'] == 'Y') {
						echo '<div class="menu-kategori">
								<div class="menu-kategori-wrapper">
									<h6 class="title">' . $kategori['nama_kategori'] . '</h6>';
									if ($kategori['deskripsi']) {
										echo '<small clas="menu-kategori-desc">' . $kategori['deskripsi'] . '</small>';
									}
						echo '</div>
							</div>';
					}
					$list_menu = menu_list($val['menu']);
					echo build_menu($current_module, $list_menu);
				}
				?>
			</nav>
		</div>
		<div class="content">
		<?=!empty($breadcrumb) ? breadcrumb($breadcrumb) : ''?>
		<div class="content-wrapper">
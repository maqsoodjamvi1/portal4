<!DOCTYPE html>
<html lang="en">
<head>
<title>About Us</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Course Project">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/design-tokens.css?v=20260604') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/school-forms.css?v=20260614b') ?>">
<link href="<?=base_url('assets/plugins/fontawesome-free-5.0.1/css/fontawesome-all.css'); ?>" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/styles/courses_styles.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/styles/courses_responsive.css'); ?>">
<link rel="stylesheet" href="<?=base_url('assets/styles/styles.css'); ?>">
</head>
<body>
<div class="super_container">
	<!-- Header -->
	<header class="header d-flex flex-row">
		<div class="header_content d-flex flex-row align-items-center">
			<!-- Logo -->
			<div class="logo_container">
				<div class="logo">
					<img src="<?=base_url('assets/images/logo.jpg'); ?>" style="width:50px;height:50px;" alt="">
					<span>TIME SS</span>
				</div>
			</div>
<?php print_r($data); ?>
			<!-- Main Navigation -->
			<!-- <nav class="main_nav_container">
				<div class="main_nav">
					<ul class="main_nav_list">
						<li class="main_nav_item"><a href="<?=base_url('/'); ?>">home</a></li>
						<li class="main_nav_item"><a href="<?=base_url('about'); ?>">about us</a></li>
						<li class="main_nav_item"><a href="admissions">admissions</a></li>
						<li class="main_nav_item"><a href="campuses">campuses</a></li>
						<li class="main_nav_item"><a href="events">Events</a></li>
						<li class="main_nav_item"><a href="privacy_policy">Privacy Policy</a></li>
					</ul>
				</div>
			</nav> -->
		</div>
		<div class="header_side d-flex flex-row justify-content-center align-items-center">
			<img src="<?=base_url('assets/images/phone-call.svg'); ?>" alt="">
			<span>+92 51 261 71 60</span>
		</div>

		<!-- Hamburger -->
		<div class="hamburger_container">
			<i class="fas fa-bars trans_200"></i>
		</div>

	</header>

	<!-- Menu -->
	<div class="menu_container menu_mm">

		<!-- Menu Close Button -->
		<div class="menu_close_container">
			<div class="menu_close"></div>
		</div>

		<!-- Menu Items -->
		<div class="menu_inner menu_mm">
			<div class="menu menu_mm">
				<ul class="menu_list menu_mm">
					<li class="menu_item menu_mm"><a href="index">Home</a></li>
					<li class="menu_item menu_mm"><a href="#">About us</a></li>
					<li class="menu_item menu_mm"><a href="admissions">Admissions</a></li>
					<li class="menu_item menu_mm"><a href="campuses">Campuses</a></li>
					<li class="menu_item menu_mm"><a href="events">Events</a></li>
					<li class="menu_item menu_mm"><a href="privacy_policy">privacy policy</a></li>
				</ul>

				<!-- Menu Social -->

				<div class="menu_social_container menu_mm">
					<ul class="menu_social menu_mm">
						<li class="menu_social_item menu_mm"><a href="#"><i class="fab fa-pinterest"></i></a></li>
						<li class="menu_social_item menu_mm"><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
						<li class="menu_social_item menu_mm"><a href="#"><i class="fab fa-instagram"></i></a></li>
						<li class="menu_social_item menu_mm"><a href="#"><i class="fab fa-facebook-f"></i></a></li>
						<li class="menu_social_item menu_mm"><a href="#"><i class="fab fa-twitter"></i></a></li>
					</ul>
				</div>

				
			</div>

		</div>

	</div>

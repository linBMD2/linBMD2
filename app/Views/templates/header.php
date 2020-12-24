<!doctype html>

<html>
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
		
		<!-- this for the autocomplete function -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		
		<style>
			.spinner-border, #districts_staleness_spinner, #districts_refresh_spinner {display:none;}
			.spinner-border.active, #districts_staleness_spinner.active, #districts_refresh_spinner.active {display:block;}
			.ui-autocomplete { max-height: 130px; max-width: 190px; overflow-y: auto; overflow-x: hidden; }
		</style>
		
		<?php $session = session(); ?>
		
		<title><?= esc($session->title); ?></title>
		
		<div class="container-sm">
			 <div class="row d-flex justify-content-between alert alert-info" role="alert">
				<span class="small font-weight-bold"><?= esc($session->title); ?></span>
				<span class="small font-weight-bold"><?= esc($session->realname); ?></span>
				<?php if ( $session->user ): ?>
					<span class="small font-weight-bold"><?= "Total records transcribed by you = ".esc($session->user[0]['BMD_total_records']); ?></span>
				<?php endif ?>
				<span class="small font-weight-bold"><?= esc(date("j/n/Y")); ?></span>
			</div>
		</div>
		</head>
	
	<body>
		<div class="container-sm">
			<div class="<?= esc($session->message_class_1); ?> row pl-0 " role="alert">
				<span class="col-12"><?= esc($session->message_1) ?></span>
			</div>
			<div class="<?= esc($session->message_class_2); ?> row pl-0" role="alert">
				<br>
				<span class="col-12"><?= esc($session->message_2); ?></span>
			</div>

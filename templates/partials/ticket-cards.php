<h5>Tickets Summary</h5>
<?php foreach($report['ticket_status_counts'] as $status => $count){
	$link = 'https://earthsavers.org/wp-admin/edit.php?post_type=ticket&post_status=';
	if($status === 'new'){ $link = 'https://earthsavers.org/wp-admin/edit.php?post_type=ticket&post_status=queued'; }
	if($status === 'old'){ $link = 'https://earthsavers.org/wp-admin/edit.php?post_status=hold&post_type=ticket'; }
	if($status === 'processing'){ $link = 'https://earthsavers.org/wp-admin/edit.php?post_status=proccesing&post_type=ticket'; }
	if($status === 'unassigned'){ $link = 'https://earthsavers.org/wp-admin/edit.php?post_status=unassigned&post_type=ticket&m=0&status=open'; }
	?>
	<div class="col-3">
		<div class="card box-custom" style="width: 18rem;">
			<div class="card-body text-center">
				<span class="card-title"><strong><?= ucfirst($status) ?></strong></span>
				<h5 class="text-center"><a style="text-decoration: none;color: black" href="<?= $link ?>"><?= $count ?></a></h5>
			</div>
		</div>
	</div>
<?php } ?>

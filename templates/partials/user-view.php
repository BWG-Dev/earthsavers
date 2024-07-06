<div class="col-md-9">
	<h5>Invoices</h5>
	<?php if(count($user->orders)){ ?>

		<div class="mt-2"><a class="es_advanced_view" href="/wp-admin/edit.php?s&post_status=all&post_type=shop_order&_customer_user=<?= $user->ID; ?>"><span class="dashicons dashicons-visibility"></span> Advanced View</a></div>
		<table class="table">
			<thead><tr><th scope="col">Invoice ID</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Action</th></tr></thead>
			<tbody>
			<?php foreach ($user->orders as $order){
				$order_obj = wc_get_order( $order->ID );
				$date = date_create($order->post_date);
				?>
				<tr>
					<th scope="row"><?= $order->ID ?></th>
					<td><?= date_format($date,"M d, Y"); ?></td>
                    <td><?= $order_obj->get_status() == 'processing' ? 'completed' : $order_obj->get_status(); ?></td>
					<td>$<?= $order_obj->get_total(); ?></td>
					<td><a target="_blank" href="/wp-admin/post.php?post=<?= $order->ID ?>&action=edit">View</a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php }else{ ?>
		<p class="mt-5 text-center">No orders to show</p>
	<?php } ?>
	<h5 class="mt-5">Subscriptions</h5>
	<?php if(count($user->subscriptions)){ ?>

		<div class="mt-2"><a class="es_advanced_view" href="/wp-admin/edit.php?s&post_status=all&post_type=shop_subscription&_wcs_product&_customer_user=<?= $user->ID; ?>"><span class="dashicons dashicons-visibility"></span> Advanced View</a></div>
		<table class="table">
			<thead><tr><th scope="col">ID</th><th scope="col">Next Payment</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Action</th></tr></thead>
			<tbody>
			<?php foreach ($user->subscriptions as $sub){
				$date = $sub->get_date('next_payment', 'site');
				$next_payment = !empty($date) ? date('M d, Y', strtotime($date)) : 'N/A';

				$interval = intval($sub->get_billing_interval() ) > 1 ? ' / every ' .  $sub->get_billing_interval() . ' months' : ' / ' . $sub->get_billing_period();
				?>
				<tr>
					<th scope="row"><?= $sub->ID ?></th>
					<td><?= $next_payment; ?></td>

					<td><?= $sub->get_status(); ?></td>
					<td>$<?= $sub->get_total() . $interval ?></td>
					<td><a target="_blank" href="/wp-admin/post.php?post=<?= $sub->ID ?>&action=edit">View</a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php }else{ ?>
		<p class="mt-5 text-center">No tickets to show</p>
	<?php } ?>

	<h5 class="mt-5">Tickets</h5>
	<?php if(count($user->tickets)){ ?>

		<div class="mt-2"><a class="es_advanced_view" href="/wp-admin/edit.php?post_status=any&post_type=ticket&m=0&activity=all&author=<?= $user->ID; ?>&id&ticket_type=0&filter_action=Filter"><span class="dashicons dashicons-visibility"></span> Advanced View</a></div>
		<table class="table">
			<thead><tr><th scope="col">Ticket ID</th><th scope="col">Title</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
			<tbody>
			<?php foreach ($user->tickets as $ticket){
				$date = date_create($ticket->post_date);
				$status = get_post_meta( $ticket->ID, '_wpas_status', true);
				?>
				<tr>
					<th scope="row"><?= $ticket->ID ?></th>
					<td><?= $ticket->post_title; ?></td>
					<td><?= date_format($date,"M d, Y"); ?></td>
					<td><?= $status ?></td>
					<td><a target="_blank" href="/wp-admin/post.php?post=<?= $ticket->ID ?>&action=edit">View</a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php }else{ ?>
		<p class="mt-5 text-center">No tickets to show</p>
	<?php } ?>

</div>
<div class="col-md-3 mt-3 ">
	<div class="row es_user_box p-3">
		<div class="col-3 text-center">
			<div class="es_customer_avatar mx-auto"><h5 class="m-0"><?= $user->avatar; ?></h5></div>
			<div class="mt-2"><span class="badge text-bg-secondary">ID: <?= $user->ID  ?></span></div>
			<?php if($user->type === 'Business'){ ?><div class="mt-2"><span class="badge text-bg-<?= $user->status == 'Approved' ? 'success' : 'secondary' ?>"><?= $user->status == -1 ? 'Pending' : $user->status;  ?></span></div><?php } ?>
			<h6 class="mt-3"><?= wc_price($user->balance); ?></h6>
		</div>
		<div class="col-9">
			<h6><a class="dashboard-user-name" href="/wp-admin/user-edit.php?user_id=<?= $user->ID ?>"><?= $user->type === 'Business' && !empty($user->es_company) ? $user->es_company :  $user->display_name; ?> <span class="badge text-bg-info"> <?= $user->type  ?></span></a></h6>

			<?php if($user->type === 'Business' && !empty($user->es_company)){?><div class="mt-2"><!--<span class="dashicons dashicons-building"></span>--> <?= $user->display_name; ?></div><?php } ?>

			<div class="mt-2"><span class="dashicons dashicons-email"></span> <?= $user->user_email; ?></div>

			<?php if(!empty($user->es_phone)){?><div class="mt-2"><span class="dashicons dashicons-phone"></span> <?= $user->es_phone; ?></div><?php } ?>

			<div class="mt-2"><span class="dashicons dashicons-location-alt"></span> <?= $user->routes; ?></div>
			<?php if(!empty($user->es_address)){ ?><div class="mt-2"><span class="dashicons dashicons-location"></span> <?= $user->es_address; ?></div><?php } ?>
			<?php if(!empty($user->es_number_of_employees)){ ?><div class="mt-2"><span class="dashicons dashicons-admin-users"></span> <?= $user->es_number_of_employees . ' employees'; ?></div><?php } ?>
			<?php if(!empty($user->es_business_type)){ ?><div class="mt-2"><strong>Business Type: </strong> <?= $user->es_business_type; ?></div><?php } ?>
			<?php if(!empty($user->es_description)){ ?><div class="mt-2"><strong>Description: </strong> <?= $user->es_description; ?></div><?php } ?>
			<?php if(!empty($user->es_referred)){ ?><div class="mt-2"><strong>Referred: </strong> <?= $user->es_referred; ?></div><?php } ?>
			<?php if(!empty($user->es_items)){ ?><div class="mt-2"><strong>Interested Items: </strong> <?= $user->es_items; ?></div><?php } ?>
			<?php if(!empty($user->es_find)){ ?><div class="mt-2"><strong>How did you find us?: </strong> <?= $user->es_find; ?></div><?php } ?>
			<?php if(!empty($user->es_user_created)){ ?><div class="mt-2"><strong>Request Sent: </strong> <?= $user->es_user_created; ?></div><?php } ?>

			<?php if($user->type === 'Business' && $user->status == -1){ ?>
				<br>
				<button data-action="approve" type="button" id="es_approve_business" class="btn btn-success" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">Approve</button>
				<button data-action="deny" type="button" id="es_deny_business" class="btn btn-danger" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">Deny</button>
			<?php } ?>

		</div>
	</div>
	<div class="row">
		<div class="col-12">
			<a class="btn btn-primary" target="_blank" href="https://earthsavers.org/wp-admin/post-new.php?post_type=ticket&user_creator=<?= $user->ID ?>&user_name=<?= $user->display_name; ?>">Open a ticket</a>
		</div>
	</div>
	<div class="row">
		<div class="col-12 mt-4">
			<?php if($user){ ?>
				<div class="customer_relationship">
					<h6>Relationship</h6>
					<input type="hidden" value="<?= $user->ID ?>" id="es_user_id">
					<textarea class="form-control" id="es_customer_relationship" rows="3"><?= get_user_meta($user->ID, 'description', true) ?></textarea>
					<button id="es_save_relationship" type="button" class="btn btn-primary">Save</button>
				</div>
			<?php } ?>
		</div>
	</div>
</div>

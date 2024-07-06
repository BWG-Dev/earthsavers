<?php

$user = wp_get_current_user();

$user_roles = $user->roles;

$accounts_serialized = get_user_meta(get_current_user_id(), 'subaccount_ids', true);
$accounts            = $accounts_serialized && count(unserialize($accounts_serialized)) > 0 ? unserialize($accounts_serialized) : [];
$stop                = get_user_meta( $user->ID, 'stop_invoice_notification', true);

$payers_cc = get_field( 'es_payers_cc', 'user_' . $user->ID );
$payers = explode(',', $payers_cc) ?? [];
$subscriptions = wcs_get_users_subscriptions($user->ID);
//var_dump($user);
if (  in_array( 'business', $user_roles) || in_array( 'residential-4', $user_roles ) || in_array( 'administrator', $user_roles ) || ( in_array( 'subscriber', $user_roles) && count($subscriptions) > 0 )) {
	?>

    <h3>My Users</h3>

    <p>Give access to an account manager or anyone you want to be able to access your account, pay invoices, and/or submit tickets</p>
    <p>
        <input type="hidden" id="user_id" value="<?php echo $user->ID; ?>">
        <input type="checkbox" id="do_not_send_invoice_emails" <?php echo ! empty($stop) ? 'checked' : ''; ?> >
        <label for="do_not_send">Select this box  if you do not want invoices emailed to the primary address for this account.</label>

        <button class="btn btn-primary ml-4" type="button" id="send_invoice_info">Save</button>
    </p>
    <p><strong>Note:</strong> At least one email address must receive invoices.</p>

    <div class="row">
        <div class="col-12">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAccount">Add User</button>

        </div>
    </div>

    <div class="container pb-3 mb-3 my-account-content" style="position: relative">

        <div class="mask-content" style="display: none;background: black;height: 100%;width: 100%;position: absolute;z-index: 9;justify-content: center;align-items: center;opacity: 0.8;"><p style="color: white" class="text-center">Loading</p></div>

        <div class="row mt-4">
            <div class="col-1"><strong>ID</strong></div>
            <div class="col-3"><strong>Name</strong></div>
            <div class="col-3"><strong>Email</strong></div>
            <div class="col-2"><strong>Phone</strong></div>
            <div class="col-1 text-center"><strong>Receive Invoice</strong></div>
            <div class="col-2 text-center"><strong>Actions</strong></div>
        </div>
		<?php foreach ($accounts as $account){
			$user = get_user_by( 'ID', $account );
			$phone = get_user_meta($user->ID, 'user_phone', true );
			if($user){
				?>
                <div class="row p-1">
                    <div class="col-1"><?php echo $user->ID ?></div>
                    <div class="col-3"><?php echo $user->display_name ?></div>
                    <div class="col-3"><?php echo $user->user_email ?></div>
                    <div class="col-2"><?php echo $phone; ?></div>
                    <div class="col-1 text-center"><input type="checkbox" <?php echo in_array( $user->user_email, $payers ) ? 'checked' : ''; ?> data-email="<?php echo $user->user_email; ?>" class="invoice_checkbox" id="invoice_<?php echo $user->ID; ?>"></div>
                    <div class="col-2 text-center"><button type="button" class="btn btn-sm btn-danger remove-user-btn" data-id="<?php echo $user->ID ?>">Remove</button></div>
                </div>

				<?php
			}
		} ?>
    </div>

    <!-- Button trigger modal -->

    <!-- Modal -->
    <div class="modal fade" id="addAccount" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="false" data-dismiss="modal">

        <div class="modal-dialog">

            <div class="modal-content">
                <div id="modal-loading-mask" style="position:absolute;background: black;opacity: 0.7;z-index: 9999;display: none; height: 100%;width: 100%; justify-content: center;align-items: center">
                    <p style="font-weight: bold; font-size: 18px">Loading...</p>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Sub-account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="account_name">Full Name</label>
                        <input type="email" class="form-control" id="account_name" aria-describedby="emailHelp">
                    </div>

                    <div class="form-group">
                        <label for="account_email">Email address</label>
                        <input type="email" class="form-control" id="account_email" aria-describedby="emailHelp">
                    </div>

                    <div class="form-group">
                        <label for="account_phone">Phone</label>
                        <input type="number" class="form-control" id="account_phone" aria-describedby="emailHelp">
                    </div>

                    <input type="hidden" id="user_id" value="<?php echo get_current_user_id() ?>">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="add_subaccount" type="button" class="btn btn-primary">Add</button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

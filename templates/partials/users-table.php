<?php
global $wpdb;


$users = $wpdb->get_results("SELECT * FROM wp_bxcl_users U INNER JOIN wp_bxcl_usermeta UM ON U.ID = UM.user_id
                                    WHERE UM.meta_key = 'es_time_accessed' ORDER BY UM.meta_value DESC LIMIT 5");

?>
<h5>Recently Active Clients</h5>
<div class="row">
	<div class="col-3"><strong>Name</strong></div>
	<div class="col-3"><strong>Email</strong></div>
	<div class="col-3"><strong>Account Type</strong></div>
	<div class="col-3"><strong></strong></div>
</div>
<?php

foreach ($users as $user){
    $user_meta = get_userdata($user->ID);
    $user_roles = $user_meta->roles;
    $business = in_array('business', $user_roles);

	$company = get_user_meta($user->ID, '_es_company', true);
	if(empty($company)){
		$company = get_user_meta( $user->ID, 'billing_company', true );
	}

	if(empty( $company )) {
		$company = get_user_meta( $user->ID, '_es_company', true );

	}

	?>
	<div class="row orders-header py-2">
		<div class="col-3"><?= !empty($company) && $company != $user->display_name  ?  $company . " - " . $user->display_name : $user->display_name  ?></div>
		<div class="col-3"><?= $user->user_email ?></div>
		<div class="col-3"><?= $business ? 'Business' : 'Residential' ?></div>
		<div class="col-3"><a href="<?php site_url() ?>/wp-admin/admin.php?page=es-users&user_id=<?= $user->ID ?>">View</a></div>
	</div>

	<?php
}


/*$count = count($orders) > 0 ? intval($all[0]->total) : 0;
$pages = ceil($count/$limit);*/
?>

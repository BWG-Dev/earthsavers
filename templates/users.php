<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
<script src="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid es_customer_dashboard">
    <div class="row">
        <div class="col-9">
			<?php if($user){ echo '<a href="'.site_url().'/wp-admin/admin.php?page=es-users">&#8701; All users</a>'; } ?>
        </div>
        <div class="col-3">
            <label class="form-label" for="es_search_user">Search User by Email/Name/Company Name</label>
            <input class="form-control" type="text" id="es_search_user">
            <ul class="es_user_list" style="height: 500px;overflow: scroll">

            </ul>
        </div>
    </div>

    <div class="row">
		<?php if($user){
			update_user_meta( $user->ID, 'es_time_accessed', strtotime(gmdate('m/d/Y h:i:s' ) ) );
            include_once ES_PLUGIN_PATH . 'templates/partials/user-view.php'; } ?>
    </div>

	<?php
	if(!isset($_GET['user_id'])){
		?>

        <br>

        <div class="row">
	        <?php include_once ES_PLUGIN_PATH . 'templates/partials/ticket-cards.php'?>
        </div>
        <br>
        <div class="row">

        <?php include_once ES_PLUGIN_PATH . 'templates/partials/ticket-activity.php'?>


            <div class="col-md-4 ">
                <h5>Recent Activities</h5>
                <div class="table-wrapper box-custom p-3 es_section_scroll">
                    <table class="recent-activities-tbl ">
                        <tbody>
                        <?php
                        global $wpdb;
                        $users = $wpdb->get_results("SELECT * FROM wp_bxcl_users U INNER JOIN wp_bxcl_usermeta UM ON U.ID = UM.user_id
                                                             WHERE UM.meta_key = 'wp_bxcl_capabilities' AND  UM.meta_value LIKE '%business%' ORDER BY UM.user_id DESC LIMIT 10");


                        foreach ( $users as $user ) {
                            $company   = get_user_meta( $user->ID, 'billing_company', true ) ? get_user_meta( $user->ID, 'billing_company', true ) : get_user_meta( $user->ID, '_es_company', true );
	                        $user_name = $company ?? $user->data->display_name;

                            ?>
                            <tr>
                                <td>
                                    <strong><a href="https://earthsavers.org/wp-admin/admin.php?page=es-users&user_id=<?= $user->ID ?>"><?= $user_name?></a></strong> has sent a Business Request</a>
                                    <span class="last-act-time"><?=  es_timeago($user->user_registered) ?></span>
                                </td>
                            </tr>
                        <?php } ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <br>

		<?php include_once ES_PLUGIN_PATH . 'templates/partials/users-table.php' ?>

        <hr>
        <!--<div class="row">
	        <?php /*include_once ES_PLUGIN_PATH . 'templates/partials/pagination.php' */?>
        </div>-->

        <div class="row">
            <div class="col-md-5">
                <h5>Monthly Recurring Revenue</h5>
                <div id="chartdiv" class="box-custom" style="width: 100%;height: 300px"></div>
            </div>
            <div class="col-md-7">
                <h5>Outstanding Invoices</h5>
                <div class="container">
                    <div class="row box-custom">
                        <div class="col-md-8 p-0">
                            <div id="chartdiv2"  style="width: 100%;height: 200px"></div>
                        </div>
                        <div class="col-md-4 p-0">
                            <div class="row stats-lineal-wrapper">
                                <div class="col-4 p-0">
                                    <p><span class="total_outstanding">$0.00</span></p>
                                    <p class="total-outstanding-subtext">Total Outstanding</p>
                                </div>
                                <div class="col-8">
                                    <p><span class="label-line">0-30 Days</span> <span class="total-line term_30">$0</span></p>
                                    <p><span class="label-line">31-60 Days</span> <span class="total-line term_3160">$0</span></p>
                                    <p><span class="label-line">61-90 Days</span> <span class="total-line term_6190">$0</span></p>
                                    <p><span class="label-line">90+ Days</span> <span class="total-line term_90">$0</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12"><p>You have <strong><?= $report['today_total'] ?></strong> of online payments on the way expected on <?= date('M. d, Y') ?></p></div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	?>

</div>
<?php include_once ES_PLUGIN_PATH . 'templates/partials/dashboard-styles.php' ?>

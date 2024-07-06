<?php
include 'import-request.php';

if(isset($_POST['import_routes'])){
	global $wpdb;

	$file_name = sanitize_file_name($_FILES['file_import_routes']['name']);
	$file_tmp = sanitize_text_field($_FILES['file_import_routes']['tmp_name']);
	move_uploaded_file($file_tmp, ES_PLUGIN_PATH . 'uploads/' . $file_name );

	if (($handle = fopen(ES_PLUGIN_PATH . 'uploads/' . $file_name, "r")) !== FALSE) {
		$cont=0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if($cont === 0){ $cont++; continue; }

			if('' != trim($data[0])){


				$email = trim($data[0]);
				$home_phone = trim($data[1]);
				$mobile_phone = trim($data[2]);

				$user = get_user_by('email', $email);


				if(empty($user)){
					es_write_log("The user " . $email . ' was not found');
					$cont++;
					continue;
				}


				update_user_meta($user->ID, 'billing_phone', $home_phone);
				update_user_meta($user->ID, 'mobile_phone', $mobile_phone);
				es_write_log("User ID: " . $user->ID . " " . $email . " main: " . $home_phone . ' mobile: ' .  $mobile_phone);


			}else{
				es_write_log("Info on index " . $cont . " was not imported, required info was not found");

			}

			$cont++;
		}
		fclose($handle);
	}else{
		echo "There was an error in your file.";
	}
}
?>

<h3 class="mt-4 pt-4">Import Subscriptions and Account</h3>
<hr>
<div class="container-fluid">
    <form action="" method="post" enctype="multipart/form-data">
        <br><br>
        <div class="form-group row">
            <input type="checkbox" class=" col-sm-3" name="is_personal" id="is_personal" >
            <label for="is_personal" class="form-control-sm col-sm-2">Is Personal</label>

        </div>
        <div class="form-group row">
            <label style="margin-top: 10px" class="form-control-sm col-sm-2 mt-2">Upload file</label>
            <input type="file" class=" col-sm-9" name="file_import" id="file_import" >
        </div>


        <div class="form-group">
            <input type="submit" class="btn btn-info btn-sm" name="import_users" id="import_users" value="Import Users">
        </div>

        <div class="import_result">

            <?php

            /*if(isset($imported_msgs)){
                echo '<h5 class="text-center"> ' . __('Import results','wr_price_list') . '</h5>';

                if(isset($_POST['import_new_price_list'])){
                    $new_price_list_name = sanitize_text_field($_POST['import_new_price_list']);
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong> ' . __('Price list ','wr_price_list') . $new_price_list_name . ' ' . __('was created. ','wr_price_list') .'</strong>' . ' ' . __('The products price were inserted in this new list','wr_price_list').
                        '</div>';
                }
                $count_success = 0;
                $count_failure = 0;
                $msgs = '';
                foreach ($imported_msgs as $msg){
                    if($msg['type']=='success'){
                        $count_success++;
                    }else{
                        $count_failure++;
                        $msgs .= $msg['msg'] . '</br>';
                    }

                }
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>' . __('It were inserted or updated ' ,'wr_price_list') . $count_success . __(' products successfully.','wr_price_list') .
                    '</div>';
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>' . __('It were found ','wr_price_list') . $count_failure . __(' errors, below a list with the details.','wr_price_list') .'
                     </div>';
                echo $msgs;
            }*/
            ?>
        </div>

    </form>


</div>

<h3 class="mt-4 pt-4">Import Subscriptions Products</h3>
<hr>
<div class="container-fluid">
    <form action="" method="post" enctype="multipart/form-data">
        <br><br>
        <div class="form-group row">
            <label style="margin-top: 10px" class="form-control-sm col-sm-2 mt-2">Upload file</label>
            <input type="file" class=" col-sm-9" name="file_import_product" id="file_import" >
        </div>


        <div class="form-group">
            <input type="submit" class="btn btn-info btn-sm" name="import_products" id="import_products" value="Import Products">
        </div>

        <div class="import_result">

			<?php

			/*if(isset($imported_msgs)){
				echo '<h5 class="text-center"> ' . __('Import results','wr_price_list') . '</h5>';

				if(isset($_POST['import_new_price_list'])){
					$new_price_list_name = sanitize_text_field($_POST['import_new_price_list']);
					echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong> ' . __('Price list ','wr_price_list') . $new_price_list_name . ' ' . __('was created. ','wr_price_list') .'</strong>' . ' ' . __('The products price were inserted in this new list','wr_price_list').
						'</div>';
				}
				$count_success = 0;
				$count_failure = 0;
				$msgs = '';
				foreach ($imported_msgs as $msg){
					if($msg['type']=='success'){
						$count_success++;
					}else{
						$count_failure++;
						$msgs .= $msg['msg'] . '</br>';
					}

				}
				echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong>' . __('It were inserted or updated ' ,'wr_price_list') . $count_success . __(' products successfully.','wr_price_list') .
					'</div>';
				echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong>' . __('It were found ','wr_price_list') . $count_failure . __(' errors, below a list with the details.','wr_price_list') .'
					 </div>';
				echo $msgs;
			}*/
			?>
        </div>

    </form>


</div>

<h3 class="mt-4 pt-4">Import Routes</h3>
<hr>
<div class="container-fluid">
    <form action="" method="post" enctype="multipart/form-data">
        <br><br>
        <div class="form-group row">
            <label style="margin-top: 10px" class="form-control-sm col-sm-2 mt-2">Upload file</label>
            <input type="file" class=" col-sm-9" name="file_import_routes" id="file_import" >
        </div>


        <div class="form-group">
            <input type="submit" class="btn btn-info btn-sm" name="import_routes" id="import_routes" value="Import Routes">
        </div>


    </form>


</div>


<?php
function es_write_log($message) {
	if(is_array($message)) {
		$message = json_encode($message);
	}
	$file = fopen(ES_PLUGIN_PATH . "/log/custom_log.log","a");
	echo fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message);
	fclose($file);
}

if(isset($_POST['import_products'])){
	global $wpdb;

	$file_name = sanitize_file_name($_FILES['file_import_product']['name']);
	$file_tmp = sanitize_text_field($_FILES['file_import_product']['tmp_name']);
	move_uploaded_file($file_tmp, ES_PLUGIN_PATH . 'uploads/' . $file_name );

	if (($handle = fopen(ES_PLUGIN_PATH . 'uploads/' . $file_name, "r")) !== FALSE) {
		$cont=0;
		$current_primary_account = 0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if($cont === 0){ $cont++; continue; }

			if('' != trim($data[0]) && '' != trim($data[1])){

				$email = trim($data[0]);
				$last4 = trim($data[1]);

				$user = get_user_by( 'email', $email );

				if ( $user ) {

					$tokens = $wpdb->get_results( "SELECT * from $wpdb->prefix" .  "woocommerce_payment_tokens WHERE  user_id = '$user->ID' LIMIT 1"  );

					if( count($tokens) > 0 ){
						$payment_token_id = (int) $tokens[0]->token_id;
						$wpdb->query($wpdb->prepare("UPDATE $wpdb->prefix" .  "woocommerce_payment_tokenmeta SET meta_value='$last4' WHERE payment_token_id='$payment_token_id' AND meta_key = 'last4'"));
						es_write_log( $user->ID . '- ' . $email . ' updated last: ' . $last4  );
					}

					//es_write_log( $email . ' updated id: ' . $id  );
					//update_field('customer_id', $id, 'user_' . $user->ID);
				}

			}else{
				echo "Product on index " . $cont . " was not imported, required info was not found<br>";
			}

			$cont++;
		}
		fclose($handle);
	}else{
		echo "There was an error in your file.";
	}
}



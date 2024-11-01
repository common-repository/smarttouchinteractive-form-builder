<?php

if ( ! defined( 'ABSPATH' ) ){ exit;}

global $wpdb;

$st_form=$atts;

// Get global settings----

$sti_settings 	= get_option( 'sti-settings' );

// Settings - Place Address labels above fields

$settings_address_labels	= isset( $sti_settings['address-labels'] ) ? false : true;

// Extract shortcode attributes, set defaults

extract( shortcode_atts( array(

	'id' => ''

	), $atts )

);

// Add JavaScript files to the front-end, only once

if ( !$this->add_scripts )

	$this->scripts();

// Get form id.  Allows use of [sti id=1] or [sti 1]

$form_id = ( isset( $id ) && !empty( $id ) ) ? (int) $id : key( $atts );

// If form is submitted, show success message, otherwise the form

if ( isset( $_POST['sti-submit'] ) && isset( $_POST['form_id'] ) && $_POST['form_id'] == $form_id ) {

	$output = $this->confirmation();

	return;

}

$order = sanitize_sql_orderby( 'form_id DESC' );

$form  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );

// Return if no form found

if ( !$form )

	return;

// Get fields

$order_fields   = sanitize_sql_orderby( 'field_sequence ASC' );

$fields         = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d ORDER BY $order_fields", $form_id ) );

$field_len=count($fields);





$form_register  = $wpdb->prefix . 'form_register';

$mykey = $wpdb->get_results( "SELECT * FROM $form_register where status =1" );

$smart_touch_form_builder = $wpdb->prefix . 'smarttouch_form_builder_forms';

$st_form_id = $wpdb->get_results( "SELECT * FROM $smart_touch_form_builder where form_id ='".$st_form['id']."'  " );

if($st_form_id[0]->form_notification_setting ==1){
$cpt_position='';
$cpt_position_array = $wpdb->get_results("SELECT SUM(form_notification_setting) as totalcpt FROM $smart_touch_form_builder  WHERE form_id <= '".$st_form['id']."' AND form_notification_setting IS NOT NULL GROUP BY form_notification_setting  ORDER BY form_id DESC");

if (!empty($cpt_position_array)){ $cpt_position=$cpt_position_array[0]->totalcpt; } 
}

?>



<!-- form validation start here  -->

<script type="text/javascript">	

		<?php	if($st_form_id[0]->form_notification_setting ==1){  ?>
				function cptValidation<?php echo $cpt_position; ?>(response, formclcickedflag)
				{
				  if(response!="")
				  {  
				    stelement<?php echo $cpt_position; ?>=1; 
				  }
				}
		<?php	}   ?>



	function validData<?php echo $form_id; ?>(){



		    var letters = /^[a-zA-Z ]*$/;

            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

            var iChars = "!@#$%^&*()+=-[]\';,./{}|\":<>?"; 



		<?php for($s=0;$s<$field_len;$s++){   ?>

			  jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('');

			  

			<?php if($fields[$s]->field_required=='yes' AND $fields[$s]->field_type=='select'){ ?>

				var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

				if(x<?= $s ?>=='0')

				{

					jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Please Select <?php echo $fields[$s]->field_name; ?>');

					document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

					return false;

				}

				<?php }else if($fields[$s]->field_required=='yes'){ ?>

				var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

				if(x<?= $s ?>=='')

				{

					jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Please Enter <?php echo $fields[$s]->field_name; ?>');

					document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

					return false;

				}

				<?php } ?> 



				<?php

                 if($fields[$s]->field_required=='yes'){

				 if($fields[$s]->field_validation=='number'){ ?>

					var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

					if(isNaN(x<?= $s ?>))

					{

						jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Only numaric value allow in <?php echo $fields[$s]->field_name; ?>');

						document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

						return false;

					}

					<?php } } ?>

                <?php if($fields[$s]->field_required=='yes'){

                	if($fields[$s]->field_name=='Phone'){ ?>

					 var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim(); 

					 var txt=x<?= $s ?>; 

					        if (!txt.match(/^(\+?1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/)) { 

					        jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Enter Valid <?php echo $fields[$s]->field_name; ?>');

					            document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

					            return false;             

					           }					        

					<?php } } ?>

					<?php if($fields[$s]->field_required=='yes'){ 

						if($fields[$s]->field_validation=='alphabet'){ ?>

						var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

						if(!x<?= $s ?>.match(letters))

						{

							jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Please Enter Characters only in <?php echo $fields[$s]->field_name; ?>');

							document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

							return false;

						}

						<?php } } ?>

						<?php if($fields[$s]->field_validation=='email'){ ?>

							var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

							var atpos = x<?= $s ?>.indexOf("@");

							var  dotpos = x<?= $s ?>.lastIndexOf(".");

							if (atpos < 1 || ( dotpos - atpos < 2 ))

							{

								jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Please enter valid <?php echo $fields[$s]->field_name; ?>');

								document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

								return false;

							}

							<?php } ?>

							<?php if($fields[$s]->field_required=='yes'){

								if($fields[$s]->field_validation=='date'){ ?>

								var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

								var date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/ ;

								if(!(date_regex.test(x<?= $s ?>)))

								{

									jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Enter valid date formate(mm/dd/yyyy.) <?php echo $fields[$s]->field_name; ?>');

									document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

									return false;

								}

								<?php } } ?>

								<?php if($fields[$s]->field_required=='yes'){

									if($fields[$s]->field_validation=='url'){ ?>

									var x<?= $s ?>=document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').value.trim();

									var RegExp = /^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/;

									if(!RegExp.test(x<?= $s ?>)){

										jQuery('#val_<?php echo $fields[$s]->field_key.$form_id; ?>').html('Enter valid formate(http://www.abc.com) <?php echo $fields[$s]->field_name; ?>');

										document.getElementById('<?php echo $fields[$s]->field_key.$form_id; ?>').focus();

										return false;

									}

									<?php } } ?>





									<?php  } ?>	







					<?php	if($st_form_id[0]->form_notification_setting ==1){ ?>
										if(stelement<?php echo $cpt_position; ?>!=1){
										jQuery('#error_st_element<?php echo $cpt_position; ?>').html('Please confirm that you are not a robot.');
									    stelement<?php echo $cpt_position; ?>= 0;
									    return false;
									    }								      
								<?php	} ?>



								}





										jQuery(function(){



											var appendthis =  ("<div class='stimod-overlay js-stimod-close'></div>");

											jQuery('a[data-stimod-id]').click(function(e) {

													e.preventDefault();

											    jQuery("body").append(appendthis);

											    jQuery(".stimod-overlay").fadeTo(500, 0.7);

											    //jQuery(".js-modalbox").fadeIn(500);

													var modalBox = jQuery(this).attr('data-stimod-id');

													jQuery('#'+modalBox).fadeIn(jQuery(this).data());

												});   

											jQuery(".js-stimod-close, .stimod-overlay").click(function() {

											    jQuery(".sti-box, .stimod-overlay").fadeOut(500, function() {

											        jQuery(".stimod-overlay").remove();

											    });

											 

											}); 

											jQuery(window).resize(function() {

											    jQuery(".sti-box").css({

											     

											        left: (jQuery(window).width() - jQuery(".sti-box").outerWidth()) / 2

											    });

											});

											jQuery(window).resize(); 

											});

						</script>

							<?php

							

							$count = 1;

							$open_fieldset = $open_section = false;

							$submit = 'Submit';

							$verification = '';

							$label_alignment = ( $form->form_label_alignment !== '' ) ? esc_attr( " $form->form_label_alignment" ) : '';

							/************************ Call Rest Api  *************************/

							$loginurl = 'https://services.smarttouchinteractive.com/login';

							$method = 'POST';

							$headers = array(

								'Accept: application/json',

								'Content-Type: application/json',

								);

							$data = json_encode(array(

								'UserName' => $mykey[0]->username,

								'Password' => $mykey[0]->password,

								'ApiKey' =>   $mykey[0]->varification_key

								));

							$handle = curl_init();

							curl_setopt($handle, CURLOPT_URL, $loginurl);

							curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

							curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

							curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);

							curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

							switch($method) {

								case 'GET':

								break;

								case 'POST':

								curl_setopt($handle, CURLOPT_POST, true);

								curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

								break;

								case 'PUT': 

								curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');

								curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

								break;

								case 'DELETE':

								curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');

								break;

							}

							$response = curl_exec($handle);

							$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

							$response = json_decode($response, TRUE); 

							$tokenkey=$response['access_token'];

							$acname=$mykey[0]->account_name;

							/*   get account  Id  from rest */

							$idurl = 'https://services.smarttouchinteractive.com/accounts';

							$method = 'GET';

		    # headers and data (this is API dependent, some uses XML)

							$idheaders = array(

								'Accept: application/json',

								'Content-Type: application/json',

								'Authorization: Bearer '.$tokenkey.''

								);

							$idhandle = curl_init();

							curl_setopt($idhandle, CURLOPT_URL, $idurl);

							curl_setopt($idhandle, CURLOPT_HTTPHEADER, $idheaders);

							curl_setopt($idhandle, CURLOPT_RETURNTRANSFER, true);

							curl_setopt($idhandle, CURLOPT_SSL_VERIFYHOST, false);

							curl_setopt($idhandle, CURLOPT_SSL_VERIFYPEER, false);

							switch($method) {

								case 'GET':

								break;

								case 'POST':

								curl_setopt($idhandle, CURLOPT_POST, true);

								curl_setopt($idhandle, CURLOPT_POSTFIELDS, $data);

								break;

								case 'PUT': 

								curl_setopt($idhandle, CURLOPT_CUSTOMREQUEST, 'PUT');

								curl_setopt($idhandle, CURLOPT_POSTFIELDS, $data);

								break;

								case 'DELETE':

								curl_setopt($idhandle, CURLOPT_CUSTOMREQUEST, 'DELETE');

								break;

							}

							$allids = curl_exec($idhandle);

							$allids=json_decode($allids);

							$all_len=$allids->Accounts;

							$all_len=count($all_len);

							for($l=0;$l<$all_len;$l++){

								$nm=$allids->Accounts[$l]->Name; 

								if($nm==$acname){ 

									$accountid=$allids->Accounts[$l]->Id;

								}

							} 

/*********************** Refine field section *******************/

$btm='stbtn'.$form_id;

if(isset($_POST[$btm])){

	

	$form_data=$_POST;

	$AccountId=array('AccountId'=>$accountid);

	$form_data=array_merge($AccountId,$form_data);

	$form_lenth=count($form_data);

	$ptarray=array();

	$cfArray=array();

	$array=array();

	foreach ($form_data as $key => $value) {

		if($key=='AccountId'){ 

			$finalJson .='"'.$key.'":'.$value.',';	

		}	

		else if($key=='FirstName'){ 

			$finalJson .='"'.$key.'":"'.$value.'",';	

		}

		else if($key=='LastName'){ 

			$finalJson .='"'.$key.'":"'.$value.'",';	

		}

		else if($key=='Emails'){ 

			$finalJson .='"'.$key.'":[{"EmailId":"'.$value.'","IsPrimary":true,"EmailStatusValue":"0"}],';

		}

		else if($key=='Phones'){ 

			$finalJson .='"'.$key.'":[{"Number":"'.$value.'","IsPrimary":true,"PhoneType":74,"PhoneTypeName":"Work"}],';

		}

		else if($key=='Communities'){

			$finalJson .='"'.$key.'":[{"DropdownValueID":"'.$value.'"}],';

		}

		else if($key=='SelectedLeadSource')

		{

			$finalJson .='"'.$key.'":[{"DropdownValueID":"'.$value.'"}],';

		}

		else if($key!='')

		{

			$cf='';  $cfvalue='';

			$cf = substr($key, 0, 2);

			$cfvalue=substr($key,2);

			if($cf=='cf')

			{	$cusArrayLenth=''; $cfListvalue='';
				if(is_array($value)){  
					$cusArrayLenth=count($value); 
					for($cv=0;$cv<$cusArrayLenth;$cv++)
					{
						$cfListvalue .=$value[$cv].',';
					}
										
					$value=substr($cfListvalue,0,-1);

				   }

				$cfArray=array_merge($cfArray,array("CustomFieldId"=>$cfvalue,"Value"=>$value));

				array_push($array,$cfArray);

			}else 

		{ 

			$cf=''; 

			$cf = substr($key, 0, 2);

			if($cf!='cf')

			{	

				$form_builder=$wpdb->prefix.'smarttouch_form_builder_fields'; 

				$custom_row = $wpdb->get_results("SELECT field_key,fieldid,typeid FROM $form_builder where form_id=$form_id AND iscustom='1' AND field_key='".$key."' ");

				if(!empty($custom_row)){

					$finalJson .='"'.$key.'":[{"'.$key.'":"'.$value.'"}],';

				}

				else{

					$finalJson .='"'.$key.'":"'.$value.'",';	

				} 

			}

		}

		}

		

	}

	$cus_json=json_encode($array);

    $today=date("Y/m/d");

	$finalJson .='"LifecycleStage":"100","LastUpdatedOn":"'.$today.'","CreatedOn":"'.$today.'"'.',';

	$finalJson .='"FirstContactSource":"5"'.',';

	$finalJson .='"CustomFields":'.$cus_json.''.',';

	$finalJson .='"FormId":'.$st_form_id[0]->st_form_id.''.',';

	$finalJson='{'. substr($finalJson,0,-1) .'}';  // print_r($finalJson);  die; 

	/************************form post custom code ********************/

	/* start custmization  updated   */

	$personurl = 'https://services.smarttouchinteractive.com/Person';

	$method = 'POST'; 

    # headers and data (this is API dependent, some uses XML)

	$headers = array(

		'Accept: application/json',

		'Content-Type: application/json',

		'Authorization: Bearer '.$tokenkey.''

		);

	$data =$finalJson;    

	$handle = curl_init();

	curl_setopt($handle, CURLOPT_URL, $personurl);

	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);

	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);   

	switch($method) {

		case 'GET':

		break;

		case 'POST':

		curl_setopt($handle, CURLOPT_POST, true);

		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

		break;

		case 'PUT': 

		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');

		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

		break;

		case 'DELETE':

		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');

		break;

	}

	$response = json_decode(curl_exec($handle));

	$tct=count($response);

	if($tct>0){  $loc=$st_form_id[0]->form_email_from_name; echo '<script>window.location.href = "'.$loc.'";</script>';     }else{ echo curl_exec($handle);  }

}

/**************************** finish responce from API **********************/

$output .='<a class="js-open-stimod title-cta" href="#" data-stimod-id="popup'.$form_id.'">'.$titlecode['title'].'</a> ';

$output .='<div id="popup'.$form_id.'" class="sti-box" style="inline:block;">

  <div class="stimod-body sti-popup"><div class="form-cta">'.$titlecode['title'].'</div>';

$output .= sprintf( '<div id="sti-form-%d" class="smart-touch-form-builder-container">', $form_id );

$output .= sprintf(

	'<form method="post" id="form-post" name="stform'.$form_id.'"  onsubmit="return validData'.$form_id.'()">',

	esc_attr( $form->form_key ),

	$form_id,

	"sti-form-$form_id",

	$label_alignment,

	absint( $form->form_id )

	);

foreach ( $fields as $field ) :

	$field_id		= absint( $field->field_id );

$field_key		=$field->field_key;

$field_type 	= esc_html( $field->field_type );

$field_name		= esc_html( stripslashes( $field->field_name ) );

$required_span 	= ( !empty( $field->field_required ) && $field->field_required === 'yes' ) ? ' <span class="sti-required-asterisk">*</span>' : '';

$required 		= ( !empty( $field->field_required ) && $field->field_required === 'yes' ) ? esc_attr( 'required' ) : '';

$validation 	= ( !empty( $field->field_validation ) ) ? esc_attr( " $field->field_validation" ) : '';

$css 			= ( !empty( $field->field_css ) ) ? esc_attr( " $field->field_css" ) : '';

$id_attr 		= "sti-{$field_id}";

$size			= ( !empty( $field->field_size ) ) ? esc_attr( " sti-$field->field_size" ) : '';

$layout 		= ( !empty( $field->field_layout ) ) ? esc_attr( " sti-$field->field_layout" ) : '';

$default 		= ( !empty( $field->field_default ) ) ? wp_specialchars_decode( esc_html( stripslashes( $field->field_default ) ), ENT_QUOTES ) : '';

$description	= ( !empty( $field->field_description ) ) ? wp_specialchars_decode( esc_html( stripslashes( $field->field_description ) ), ENT_QUOTES ) : '';

$field_requ		=$field->field_required;

$opt_value      =$field->fieldid;

$typeid		=$field->typeid;

	// Close each section

if ( $open_section == true ) :

		// If this field's parent does NOT equal our section ID

	if ( $sec_id && $sec_id !== absint( $field->field_parent ) ) :

		$output .= '</div><div class="sti-clear"></div>';

	$open_section = false;

	endif;

	endif;

	if ( $field_type == 'fieldset' ) :

		// Close each fieldset

		if ( $open_fieldset == true )

			$output .= '</div>';

		// Only display Legend if field name is not blank

		$legend = !empty( $field_name ) ? sprintf( '<div class="sti-legend"><h3>%s</h3></div>', $field_name ) : '&nbsp;';

		$output .= sprintf(

			'<fieldset class="sti-fieldset sti-fieldset-%1$d %2$s %3$s" name="'.$field_key.'" id="item-%4$s">%5$s<div class="sti-section sti-section-%1$d">',

			$count,

			esc_attr( $field->field_key ),

			$css,

			$id_attr,

			$legend

			);

		$open_fieldset = true;

		$count++;

		elseif ( $field_type == 'section' ) :

			$output .= sprintf(

				'<div id="item-%1$s" name="'.$field_key.'" class="sti-section-div %2$s"><h4>%3$s</h4>',

				$id_attr,

				$css,

				$field_name

				);

		// Save section ID for future comparison

		$sec_id = $field_id;

		$open_section = true;

		elseif ( !in_array( $field_type, array( 'verification', 'secret', 'submit' ) ) ) :

			$columns_choice = ( !empty( $field->field_size ) && in_array( $field_type, array( 'radio', 'checkbox' ) ) ) ? esc_attr( " sti-$field->field_size" ) : '';

		if ( $field_type !== 'hidden' ) :

			// Don't add for attribute for certain form items

			$for = !in_array( $field_type, array( 'checkbox', 'radio', 'time', 'address', 'instructions' ) ) ? ' ' : '';

        if($typeid=='no'){ $hide='display:none';}else{$hide='';}

        $addhide='';

	     if($field_key=='SelectedLeadSource')

			{ 			

						

						  

							if(isset($_COOKIE['adwords_ad_google'])||isset($_GET['ad']))

							{ 		

									$addhide='leadhide'; 

							}

							else

								{  

									$addhide='';    

								}

						  

			}

		

				if($st_form_id[0]->form_email_from_override ==1)

					{	

							$output .= sprintf(

								'<div class="sti-item '.$addhide.' sti-item-%1$s %2$s %3$s" id="item-%4$s" style="'.$hide.'"><label' . $for . ' class="sti-desc">%5$s %6$s</label>',

								$field_type,

								$columns_choice,

								$layout,

								$id_attr,

								$field_name,

								$required_span

								);

						}else{



					if($field_type=='radio' || $field_type=='checkbox'){

								$output .= sprintf(

								'<div class="sti-item '.$addhide.' sti-item-%1$s %2$s %3$s" id="item-%4$s" style="'.$hide.'"><label' . $for . ' class="sti-desc">%5$s %6$s</label>',

								$field_type,

								$columns_choice,

								$layout,

								$id_attr,

								$field_name,

								$required_span

								);

							}else{

							$output .= sprintf(

								'<div class="sti-item '.$addhide.' sti-item-%1$s %2$s %3$s" id="item-%4$s" style="'.$hide.'">',

								$field_type,

								$columns_choice,

								$layout,

								$id_attr,

								$required_span

								);
						   }

						}





		endif;

		elseif ( in_array( $field_type, array( 'verification', 'secret' ) ) ) :

			if ( $field_type == 'verification' ) :

				$verification .= sprintf(

					'<fieldset class="sti-fieldset sti-fieldset-%1$d %2$s %3$s" id="item-%4$s" style="display:block"><div class="sti-legend"><h3>%5$s</h3></div><div class="sti-section sti-section-%1$d">',

					$count,

					esc_attr( $field->field_key ),

					$css,

					$id_attr,

					$field_name

					);

			endif;

			if ( $field_type == 'secret' ) :

			// Default logged in values

				$logged_in_display = $logged_in_value = '';

			// If the user is logged in, fill the field in for them

			if ( is_user_logged_in() ) :

				// Hide the secret field if logged in

				$logged_in_display = ' style="display:none;"';

			$logged_in_value = 14;

				// Get logged in user details

			$user = wp_get_current_user();

			$user_identity = ! empty( $user->ID ) ? $user->display_name : '';

			$logged_in_as = sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. Verification not required.', 'smart-touch-form-builder-pro' ), admin_url( 'profile.php' ), $user_identity );

            

			$verification .= sprintf(

				'<div class="sti-item" id="%1$s">%2$s</div>',

				$id_attr,

				$logged_in_as

				);

			endif;

			$verification .= sprintf(

				'<div class="sti-item sti-item-%1$s" %2$s style="display:block"><label  class="sti-desc">%4$s%5$s</label>',

				$field_type,

				$logged_in_display,

				$id_attr,

				$field_name,

				$required_span

				);

			// Set variable for testing if required is Yes/No

			$verification .= ( empty( $required ) ) ? '<input type="hidden" name="_sti-required-secret" value="0" />' : '';

			// Set hidden secret to matching input

			$verification .= sprintf( '<input type="hidden" name="_sti-secret" value="sti-%d" />', $field_id );

			$validation = '{digits:true,maxlength:2,minlength:2}';

			$verification_item = sprintf(

				'<input type="text" name="sti-%1$d" id="%2$s" value="%3$s" class="sti-text %4$s %5$s %6$s %7$s" style="display:block" />',

				$field_id,

				$id_attr,

				$logged_in_value,

				$size,

				$required,

				$validation,

				$css

				);

			$verification .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span">%1$s<label>%2$s</label></div>', $verification_item, $description ) : $verification_item;

			endif;

			endif;

			switch ( $field_type ) {  

				case 'text' :

				case 'email' :

				case 'url' :

				case 'currency' :

				case 'number' :

				case 'phone' :

			

				if ( in_array( $field_type, array( 'email', 'url' ) ) )

					$type = esc_attr( $field_type );

				elseif ( 'phone' == $field_type )

					$type = 'tel';

				else

					$type = 'text';

				$placeHolder='';

				if($field_key=='Emails'){ $id='email'; $emailstatus='<div id="sti-status"></div>'; }else{$id='';$emailstatus='';}



				if($st_form_id[0]->form_email_from_name_override ==1)

					{	if($field->field_required === 'yes'){ $stric=' *'; }else{ $stric=''; }	$placeHolder =$field_name.$stric;  }

				$form_item = sprintf(

					'<input type="%8$s" name="'.$field_key.'" id="'.$field_key.$form_id.'"  value="%3$s" class="form-control" placeholder="'.$placeHolder.'" /><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>'.$emailstatus,

					$field_id,

					$id_attr,

					$default,

					$size,

					$required,

					$validation,

					$css,

					$type

					);

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span">%1$s<label>%2$s</label></div>', $form_item, $description ) : $form_item;

				break;

				case 'hidden' :

				$form_item = sprintf(

					'<input type="hidden" name="'.$field_key.'"   value="'.$default.'"  />',

					'',

					'',

					'',

					''

					);

				$output .=$form_item;

				break;

				case 'textarea' :



				$placeHolder='';

				if($st_form_id[0]->form_email_from_name_override ==1)

					{ if($field->field_required === 'yes'){ $stric=' *'; }else{ $stric=''; }	$placeHolder =$field_name.$stric;  }

				$form_item = sprintf(

					'<textarea name="'.$field_key.'" id="'.$field_key.$form_id.'" class="sti-textarea %4$s %5$s %6$s"  placeholder="'.$placeHolder.'" >%3$s</textarea><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

					$field_id,

					$id_attr,

					$default,

					$size,

					$required,

					$css

					);

				$output .= '<div>';

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span"><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				$output .= '</div>';

				break;

				case 'select' :

                if($field_key=='SelectedLeadSource'){

							// check for cookie

				

					if(isset($_COOKIE['adwords_ad_google'])||isset($_GET['ad']))

					{ 	

					   $form_lead='';

					   $form_lead=$_GET['ad']; if($form_lead==''){ $form_lead=$_COOKIE['adwords_ad_google']; }

					   $output .='<input type="hidden" name="SelectedLeadSource" value="'.$form_lead.'">';

					}

					else{

		                 $field_options = maybe_unserialize( $field->field_options );

						$options = '';

					// Loop through each option and output
						$opt_show='';
						$opt_show=explode('|',$opt_value);
						$opt=1;

						if($st_form_id[0]->form_email_from_name_override ==1)

						{

						  if($field->field_required === 'yes'){ $options .='<option value="0">'.$field_name.' *</option>'; }else{ $options .='<option value="0">'.$field_name.'</option>'; }

					    }

					// Loop through each option and output

						foreach ( $field_options as $option => $value ) {  $valprint='';

							$valuedata=explode('!',$value);



							$valcnt=count($valuedata);

							if($valcnt>0){ for($v=1;$v<$valcnt;$v++){ $valprint .=$valuedata[$v].'!'; } }

							$valprint=substr($valprint,0,-1);


							if (in_array($valuedata[0], $opt_show)){
							$options .= sprintf( '<option value="'.$valuedata[0].'"%2$s>'.$valprint.'</option>', esc_attr(trim( stripslashes( $valuedata[1] ) ) ), selected( $default, $valuedata[0], 0 ) );
						    }
						    $opt++;
						}

		                 

						$form_item = sprintf(

							'<select name="'.$field_key.'" id="'.$field_key.$form_id.'" class="form-control" >%6$s</select><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

							$field_id,

							$id_attr,

							$size,

							$required,

							$css,

							$options

							);

						$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span"><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

		                     }



				}

				else{

				$field_options = maybe_unserialize( $field->field_options );

				$options = '';

				// Loop through each option and output
					$opt_show='';
					$opt_show=explode('|',$opt_value);
					$opt=1;

				  if($st_form_id[0]->form_email_from_name_override ==1)

						{

						  if($field->field_required === 'yes'){ $options .='<option value="0">'.$field_name.' *</option>'; }else{ $options .='<option value="0">'.$field_name.'</option>'; }

					    }

			// Loop through each option and output

				foreach ( $field_options as $option => $value ) {  $valprint='';

					$valuedata=explode('!',$value);



					$valcnt=count($valuedata);

							if($valcnt>0){ for($v=1;$v<$valcnt;$v++){ $valprint .=$valuedata[$v].'!'; } }

							$valprint=substr($valprint,0,-1);


					if (in_array($valuedata[0], $opt_show)){		
					$options .= sprintf( '<option value="'.$valuedata[0].'"%2$s>'.$valprint.'</option>', esc_attr(trim( stripslashes( $valuedata[1] ) ) ), selected( $default, $valuedata[0], 0 ) );
					}

					 $opt++;

				}

                 

				$form_item = sprintf(

					'<select name="'.$field_key.'" id="'.$field_key.$form_id.'" class="form-control" >%6$s</select><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

					$field_id,

					$id_attr,

					$size,

					$required,

					$css,

					$options

					);

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span"><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				}

				break;

				case 'selectmul' :



				$field_options = maybe_unserialize( $field->field_options );

				$options = '';

			// Loop through each option and output

				foreach ( $field_options as $option => $value ) {  

					$valuedata=explode('!',$value);

					$options .= sprintf( '<option value="'.$valuedata[0].'"%2$s>%1$s</option>', esc_attr(trim( stripslashes( $valuedata[1] ) ) ), selected( $default, ++$option, 0 ) );

				}

                 

				$form_item = sprintf(

					'<select multiple name="'.$field_key.'" id="'.$field_key.$form_id.'" class="form-control" >%6$s</select><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

					$field_id,

					$id_attr,

					$size,

					$required,

					$css,

					$options

					);

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span"><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				

				break;

				case 'radio' :

				$field_options = maybe_unserialize( $field->field_options );

				$options = '';

			// Loop through each option and output

				foreach ( $field_options as $option => $value ) {

					$option++;

					$valueradio=explode('!',$value);

					$options .= sprintf(

						'<div class="sti-span"><input type="radio" name="'.$field_key.'" id="'.$field_key.$form_id.'" value="'.$valueradio[0].'" class="sti-radio %4$s %5$s"%8$s /><div id="val_'.$field_key.$form_id.'" class="sti-error"></div><label  class="sti-choice">'.$valueradio[1].'</label></div>',

						$field_id,

						$id_attr,

						$option,

						$required,

						$css,

						esc_attr( trim( stripslashes( $value ) ) ),

						wp_specialchars_decode( stripslashes( $value ) ),

						checked( $default, $option, 0 )

						);

				}

				$form_item = $options;

				$output .= '<div>';

				$output .= ( !empty( $description ) ) ? sprintf( '<div><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				$output .= '<div style="clear:both"></div></div>';

				break;

				case 'checkbox' :

				$field_options = maybe_unserialize( $field->field_options );

				$options = '';

			// Loop through each option and output

				foreach ( $field_options as $option => $value ) {

					$valuecheck=explode('!',$value);

					$options .= sprintf(

						'<div class="sti-span"><input type="checkbox" name="'.$field_key.'[]" id="'.$field_key.$form_id.'" value="'.$valuecheck[0].'" class="sti-checkbox %4$s %5$s"%8$s /><div id="val_'.$field_key.$form_id.'" class="sti-error"></div><label  class="sti-choice">'.$valuecheck[1].'</label></div>',

						$field_id,

						$id_attr,

						$option,

						$required,

						$css,

						esc_attr( trim( stripslashes( $value ) ) ),

						wp_specialchars_decode( stripslashes( $value ) ),

						checked( $default, $valuecheck[0], 0 )

						);

				}

				$form_item = $options;

				$output .= '<div>';

				$output .= ( !empty( $description ) ) ? sprintf( '<div><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				$output .= '<div style="clear:both"></div></div>';

				break;

				case 'address' :

				$address = '';

				$address_parts = array(

					'address'    => array(

						'label'    => __( 'Street Address', 'smart-touch-form-builder' ),

						'layout'   => 'full'

						),

					'address-2'  => array(

						'label'    => __( 'Apt, Suite, Bldg. (optional)', 'smart-touch-form-builder' ),

						'layout'   => 'full'

						),

					'city'       => array(

						'label'    => __( 'City', 'smart-touch-form-builder' ),

						'layout'   => 'left'

						),

					'state'      => array(

						'label'    => __( 'State / Province / Region', 'smart-touch-form-builder' ),

						'layout'   => 'right'

						),

					'zip'        => array(

						'label'    => __( 'Postal / Zip Code', 'smart-touch-form-builder' ),

						'layout'   => 'left'

						),

					'country'    => array(

						'label'    => __( 'Country', 'smart-touch-form-builder' ),

						'layout'   => 'right'

						)

					);

				$address_parts = apply_filters( 'sti_address_labels', $address_parts, $form_id );

				$label_placement = apply_filters( 'sti_address_labels_placement', $settings_address_labels, $form_id );

				$placement_bottom = ( $label_placement ) ? '<label >%5$s</label>' : '';

				$placement_top    = ( !$label_placement ) ? '<label >%5$s</label>' : '';

				foreach ( $address_parts as $parts => $part ) :

				// Make sure the second address line is not required

					$addr_required = ( 'address-2' !== $parts ) ? $required : '';

				if ( 'country' == $parts ) :

					$options = '';

				foreach ( $this->countries as $country ) {

					$options .= sprintf( '<option value="%1$s"%2$s>%1$s</option>', $country, selected( $default, $country, 0 ) );

				}

				$address .= sprintf(

					'<div class="sti-%3$s">' . $placement_top . '<select name="sti-%1$d[%4$s]" class="form-control" id="%2$s-%4$s">%6$s</select><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>' . $placement_bottom . '</div>',

					$field_id,

					$id_attr,

					esc_attr( $part['layout'] ),

					esc_attr( $parts ),

					esc_html( $part['label'] ),

					$options,

					$addr_required,

					$css

					);

				else :

					$address .= sprintf(

						'<div class="sti-%3$s">' . $placement_top . '<input type="text" name="sti-%1$d[%4$s]" id="%2$s-%4$s" maxlength="150" class="sti-text sti-medium %7$s %8$s" />' . $placement_bottom . '</div>',

						$field_id,

						$id_attr,

						esc_attr( $part['layout'] ),

						esc_attr( $parts ),

						esc_html( $part['label'] ),

						$size,

						$addr_required,

						$css

						);

				endif;

				endforeach;

				$output .= '<div>';

				$output .= !empty( $description ) ? "<div class='sti-span'><label>$description</label></div>$address" : $address;

				$output .= '</div>';

				break;

				case 'date' :

			// Load jQuery UI datepicker library

				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_enqueue_script( 'sti-datepicker-i18n' );  

				$options = maybe_unserialize( $field->field_options );

				$dateFormat = ( $options ) ? $options['dateFormat'] : '';

				$form_item = sprintf(

					'<input type="text" name="'.$field_key.'" id="'.$field_key.$form_id.'" value="%3$s" class="sti-text sti-date-picker %4$s %5$s %6$s" data-dp-dateFormat="%7$s" /><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

					$field_id,

					$id_attr,

					$default,

					$size,

					$required,

					$css,

					$dateFormat

					);

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span">%1$s<label>%2$s</label></div>', $form_item, $description ) : $form_item;

				break;

				case 'time' :

				$hour = $minute = $ampm = '';

			// Get the time format (12 or 24)

				$time_format = str_replace( 'time-', '', $validation );

				$time_format 	= apply_filters( 'sti_time_format', $time_format, $form_id );

				$total_mins 	= apply_filters( 'sti_time_min_total', 55, $form_id );

				$min_interval 	= apply_filters( 'sti_time_min_interval', 5, $form_id );

			// Set whether we start with 0 or 1 and how many total hours

				$hour_start = ( $time_format == '12' ) ? 1 : 0;

				$hour_total = ( $time_format == '12' ) ? 12 : 23;

			// Hour

				for ( $i = $hour_start; $i <= $hour_total; $i++ ) {

					$hour .= sprintf( '<option value="%1$02d">%1$02d</option>', $i );

				}

			// Minute

				for ( $i = 0; $i <= $total_mins; $i += $min_interval ) {

					$minute .= sprintf( '<option value="%1$02d">%1$02d</option>', $i );

				}

			// AM/PM

				if ( $time_format == '12' ) {

					$ampm = sprintf(

						'<div class="sti-time"><select name="'.$field_key.'" id="'.$field_key.$form_id.'" class="sti-select %5$s %6$s"><option value="AM">AM</option><option value="PM">PM</option></select><label >AM/PM</label></div><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

						$field_id,

						$id_attr,

						$hour,

						$minute,

						$required,

						$css

						);

				}

				$form_item = sprintf(

					'<div class="sti-time"><select name="sti-%1$d[hour]" id="%2$s-hour" class="sti-select %5$s %6$s">%3$s</select><label >HH</label></div>' .

					'<div class="sti-time"><select name="sti-%1$d[min]" id="%2$s-min" class="sti-select %5$s %6$s">%4$s</select><label >MM</label></div>' .

					'%7$s',

					$field_id,

					$id_attr,

					$hour,

					$minute,

					$required,

					$css,

					$ampm

					);

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span"><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				$output .= '<div class="clear"></div>';

				break;

				case 'html' :

			//Load CKEditor library

				wp_enqueue_script( 'sti-ckeditor' );  

				$form_item = sprintf(

					'<textarea name="'.$field_key.'" id="'.$field_key.$form_id.'" class="sti-textarea ckeditor %4$s %5$s %6$s">%3$s</textarea><div id="val_'.$field_key.$form_id.'" class="sti-error"></div>',

					$field_id,

					$id_attr,

					$default,

					$size,

					$required,

					$css

					);

				$output .= '<div>';

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span"><label>%2$s</label></div>%1$s', $form_item, $description ) : $form_item;

				$output .= '</div>';

				break;

				case 'file-upload' :

				$options = maybe_unserialize( $field->field_options );

				$accept = ( !empty( $options[0] ) ) ? " {accept:'$options[0]'}" : '';

				$form_item = sprintf(

					'<input type="file" name="'.$field_key.'" id="'.$field_key.$form_id.'" value="%3$s" class="sti-text %4$s %5$s %6$s %7$s %8$s" />',

					$field_id,

					$id_attr,

					$default,

					$size,

					$required,

					$validation,

					$css,

					$accept

					);

				$output .= ( !empty( $description ) ) ? sprintf( '<div class="sti-span">%1$s<label>%2$s</label></div>', $form_item, $description ) : $form_item;

				break;

				case 'instructions' :

				$output .= wp_specialchars_decode( esc_html( stripslashes( $description ) ), ENT_QUOTES );

				break;

				case 'submit' :

				if($st_form_id[0]->form_notification_setting ==1){ 
				$submit = sprintf(
					'<div class="sti-item"><div id="st_element'.$cpt_position.'" class="recaptchaST"></div></div>
					<div class="sti-item"><div class="sti-error" id="error_st_element'.$cpt_position.'"></div>
					</div>
              		<div class="sti-item sti-item-submit" id="item-%2$s">
					<input type="submit" id="%2$s" value="%3$s" class="formSubmit %4$s" name="stbtn'.$form_id.'"  />
				</div>
				',
				$field_id,
				$id_attr,
				wp_specialchars_decode( esc_html( $field_name ), ENT_QUOTES ),
				$css
				);
			}else{ 

				$submit = sprintf(

					'<div class="sti-item sti-item-submit" id="item-%2$s">

					<input type="submit" id="%2$s" value="%3$s" class="formSubmit %4$s" name="stbtn'.$form_id.'"  />

				</div>

				',

				$field_id,

				$id_attr,

				wp_specialchars_decode( esc_html( $field_name ), ENT_QUOTES ),

				$css

				);

			}

				break;

		default:

				echo '';

			}

	// Closing </li>

			$output .= ( !in_array( $field_type , array( 'verification', 'secret', 'submit', 'fieldset', 'section' ) ) ) ? '</div>' : '';

			endforeach;

// Close user-added fields

			$output .= '</div>';

// Output our security test

			$output .= sprintf(

				$verification .

				'<div class="sti-btn">

				%2$s</div>

			',

			__( 'This box is for spam protection - <strong>please leave it blank</strong>' , 'smart-touch-form-builder'),

			$submit

			);

// Close the form out

			

			$output .= '<input type="hidden" id="STITrackingID" name="STITrackingID" value="" /></form>';

// Close form container

			$output .= '</div>';

			$output .= '<a href="#" class="sti-popclose js-stimod-close"></a></div></div>';

			force_balance_tags( $output );

		

	

		wp_enqueue_script( 'mailgun-validator' ); 

		wp_enqueue_script( 'mailgun-page' ); 

			?>


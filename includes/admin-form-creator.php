<?php
if ( ! defined( 'ABSPATH' ) ){ exit;}
$order = sanitize_sql_orderby( 'form_id DESC' );
$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_nav_selected_id ) );
if ( !$form || $form->form_id !== $form_nav_selected_id )
wp_die( 'You must select a form' );
$form_id = $form->form_id;
$form_key = stripslashes( $form->form_key );
$form_title = stripslashes( $form->form_title );
$st_form_id  = stripslashes( $form->st_form_id );
$form_subject = stripslashes( $form->form_email_subject );
$form_email_from_name = stripslashes( $form->form_email_from_name );
$form_email_from = stripslashes( $form->form_email_from);
$form_email_from_override= stripslashes( $form->form_email_from_override);
$form_email_from_name_override = stripslashes( $form->form_email_from_name_override);
$form_email_to = ( is_array( unserialize( $form->form_email_to ) ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) );
$form_success_type 	= stripslashes( $form->form_success_type );
$form_success_message = stripslashes( $form->form_success_message );
$form_notification_setting 	= stripslashes( $form->form_notification_setting );
$form_notification_email_name = stripslashes( $form->form_notification_email_name );
$form_notification_email_from = stripslashes( $form->form_notification_email_from );
$form_notification_email 	= stripslashes( $form->form_notification_email );
$form_notification_subject 	= stripslashes( $form->form_notification_subject );
$form_notification_message 	= stripslashes( $form->form_notification_message );
$form_notification_entry 	= stripslashes( $form->form_notification_entry );
$form_label_alignment 		= stripslashes( $form->form_label_alignment );
// Only show required text fields for the sender name override
$senders = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_name FROM $this->field_table_name WHERE form_id = %d AND field_type IN( 'text', 'name' ) AND field_validation = '' AND field_required = 'yes'", $form_nav_selected_id ) );
// Only show required email fields for the email override
$emails = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_name FROM $this->field_table_name WHERE (form_id = %d AND field_type='text' AND field_validation = 'email' AND field_required = 'yes') OR (form_id = %d AND field_type='email' AND field_validation = 'email' AND field_required = 'yes')", $form_nav_selected_id, $form_nav_selected_id ) );
$screen = get_current_screen();
$class = 'columns-' . get_current_screen()->get_columns();
$page_main = $this->_admin_pages[ 'sti' ];
?>
<style  type="text/css">
    #dvLoading
    {
    background:#000 url('<?php echo plugin_dir_url( __FILE__ ).'key-loader.gif'; ?>') no-repeat center center;
     height: 100%;
       width: 100%;
       position: fixed;
       z-index: 999999;
       left: 12%;
       top: 5%;
       opacity: 0.4;
    }
    </style>
<div id="dvLoading" style="display:none;" ></div>
<div id="sti-form-builder-frame" class="metabox-holder <?php echo $class; ?>">
	<div id="sti-postbox-container-1" class='sti-postbox-container'>
   	<form id="form-items" class="nav-menu-meta" method="post" action="">
			<input name="action" type="hidden" value="create_field" />
			<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
			<?php
			wp_nonce_field( 'create-field-' . $form_nav_selected_id );
			do_meta_boxes( $page_main, 'side', null );
			?>
	</form>
</div> 
 <div id="sti-postbox-container-2" class='sti-postbox-container'>
   <div id="sti-form-builder-main">
        <div id="sti-form-builder-management">
	            <div class="form-edit">
<form method="post" id="smart-touch-form-builder-update" action="">
	<input name="action" type="hidden" value="update_form" />
	<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
    <input name="st_form_id" type="hidden" value="<?php echo $st_form_id; ?>" /> 
    <?php wp_nonce_field( 'sti_update_form' ); ?>
	<div id="form-editor-header">
        <?php if($_GET['msg']){ echo'<div style="color:red;font-weight:bold;">'.str_replace('-',' ',$_GET['msg']).'</div>'; }  ?>
    	<div id="submitpost" class="submitbox">
        	<div class="sti-major-publishing-actions">
        		<label for="form-name" class="menu-name-label howto open-label">
                   <span class="sender-labels"><?php _e( 'Form Name&nbsp;' , 'smart-touch-form-builder'); ?></span>
                   <input type="text" value="<?php echo ( isset( $form_title ) ) ? $form_title : ''; ?>" placeholder="<?php _e( 'Enter form name here' , 'smart-touch-form-builder'); ?>" class="menu-name regular-text menu-item-textbox required" id="form-name" name="form_title" />
               </label>
               <input type="hidden" value="1" placeholder="<?php _e( 'Enter User ID' , 'smart-touch-form-builder'); ?>" class="menu-name regular-text menu-item-textbox required" id="form_key" name="form_key" />
             <!--  <label for="form-name" class="menu-name-label howto open-label">
                   <span class="sender-labels"><?php// _e( 'User ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' , 'smart-touch-form-builder'); ?></span>
                   <input type="text" value="<?php/* echo ( isset( $form_key ) ) ? $form_key : ''; */?>" placeholder="<?php/* _e( 'Enter User ID' , 'smart-touch-form-builder'); */?>" class="menu-name regular-text menu-item-textbox required" id="form_key" name="form_key" />
               </label> -->
                <label for="form-url" class="menu-name-label howto open-label">
                   <span class="sender-labels"><?php _e( 'Redirect URL' , 'smart-touch-form-builder'); ?></span>
                   <input type="text" value="<?php echo  $form_email_from_name; ?>" placeholder="Enter redirect url here" class="menu-name regular-text menu-item-textbox required" id="form-url" name="form_email_from_name" />
                </label><br />

                  <label for="form-url" class="menu-name-label howto open-label">
                     <input <input type="checkbox" value="1" <?php if($form_email_from_override==1){ echo'checked="checked"'; } ?>  class="menu-name regular-text menu-item-textbox required" id="form-url" name="form_email_from_override" />
                   <span class="sender-labels"><?php _e( 'Display Labels' , 'smart-touch-form-builder'); ?></span> 
                </label>

                  <label for="form-url" class="menu-name-label howto open-label">               
                   <input type="checkbox" value="1" <?php if($form_email_from_name_override==1){ echo'checked="checked"'; } ?>  class="menu-name regular-text menu-item-textbox required" id="form-url" name="form_email_from_name_override" />
                    <span class="sender-labels"><?php _e( 'Display Placeholders' , 'smart-touch-form-builder'); ?></span>
                </label>

                <label for="form-url" class="menu-name-label howto open-label">               
                   <input type="checkbox" value="1" <?php if($form_notification_setting==1){ echo'checked="checked"'; } ?>  class="menu-name regular-text menu-item-textbox required" id="form-url" name="form_notification_setting" />
                    <span class="sender-labels"><?php _e( 'Display Captcha Code' , 'smart-touch-form-builder'); ?></span>
                </label>

                <br class="clear" />
                <?php
					// Get the Form Setting drop down and accordion settings, if any
					$user_form_settings = get_user_meta( $user_id, 'sti-form-settings' );
					// Setup defaults for the Form Setting tab and accordion
					$settings_tab = 'closed';
					$settings_accordion = 'general-settings';
					// Loop through the user_meta array
					foreach( $user_form_settings as $set ) :
						// If form settings exist for this form, use them instead of the defaults
						if ( isset( $set[ $form_id ] ) ) :
							$settings_tab 		= $set[ $form_id ]['form_setting_tab'];
							$settings_accordion = $set[ $form_id ]['setting_accordion'];
						endif;
					endforeach;
					// If tab is opened, set current class
					$opened_tab = ( $settings_tab == 'opened' ) ? 'current' : '';
				?>
                <div class="sti-button-group">
                  <?php  $newformlink="http://$_SERVER[HTTP_HOST]"."/wp-admin/admin.php?page=sti-add-new";
                   if($_GET['duplicate']=='false'){ echo"<div style='color:red;font-weight:bold;''>A duplicate copy of this form is already available in our NextGen CRM. Please create new form. <a href=".$newformlink."> <input  class='button button-primary customprimary' type='button'   value='Create New Form'></a>"; }else{ ?>
					<a href="#form-settings" id="form-settings-button" class="sti-button sti-settings <?php echo $opened_tab; ?>">
						<?php _e( 'Settings' , 'smart-touch-form-builder'); ?>
						<span class="sti-interface-icon sti-interface-settings"></span>
					</a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=smart-touch-form-builder&amp;action=copy_form&amp;form=' . $form_nav_selected_id ), 'copy-form-' . $form_nav_selected_id ) ); ?>" class="sti-button sti-duplicate">
                    	<?php _e( 'Duplicate' , 'smart-touch-form-builder'); ?>
                    	<span class="sti-interface-icon sti-interface-duplicate"></span>
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=smart-touch-form-builder&amp;action=delete_form&amp;form=' . $form_nav_selected_id ), 'delete-form-' . $form_nav_selected_id ) ); ?>" class="sti-button sti-delete sti-last menu-delete">
                    	<?php _e( 'Delete' , 'smart-touch-form-builder'); ?>
                    	<span class="sti-interface-icon sti-interface-trash"></span>
                    </a>

                   <input id="save_form" class="button button-primary customprimary" type="button" value="Save" name="save_form" onclick="jQuery('#dvLoading').show();" >
                  <?php } ?>
                </div>
                    <div id="form-settings" class="<?php echo $opened_tab; ?>">
                        <!-- General settings section -->
                            <a href="#general-settings" class="settings-links<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>"><?php _e( 'General', 'smart-touch-form-builder' ); ?><span class="sti-large-arrow"></span></a>
                        <div id="general-settings" class="form-details<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>">
                            <!-- Label Alignment -->
                            <p class="description description-wide">
                            <label for="form-label-alignment">
                                <?php _e( 'Label Alignment' , 'smart-touch-form-builder'); ?>
                                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Label Alignment', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Set the field labels for this form to be aligned either on top, to the left, or to the right.  By default, all labels are aligned on top of the inputs.' ); ?>">(?)</span>
            					<br />
                             </label>
                                <select name="form_label_alignment" id="form-label-alignment" class="widefat">
                                    <option value="" <?php selected( $form_label_alignment, '' ); ?>><?php _e( 'Top Aligned' , 'smart-touch-form-builder'); ?></option>
                                    <option value="left-label" <?php selected( $form_label_alignment, 'left-label' ); ?>><?php _e( 'Left Aligned' , 'smart-touch-form-builder'); ?></option>
                                    <option value="right-label" <?php selected( $form_label_alignment, 'right-label' ); ?>><?php _e( 'Right Aligned' , 'smart-touch-form-builder'); ?></option>
                                </select>
                            </p>
                            <br class="clear" />
                        </div> <!-- #general-settings -->
                        <!-- Email section -->
                        <a href="#email-details" class="settings-links<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>"><?php _e( 'Email', 'smart-touch-form-builder' ); ?><span class="sti-large-arrow"></span></a>
                        <div id="email-details" class="form-details<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>">
                            <p><em><?php _e( 'The forms you build here will send information to one or more email addresses when submitted by a user on your site.  Use the fields below to customize the details of that email.' , 'smart-touch-form-builder'); ?></em></p>
                            <!-- E-mail Subject -->
                            <p class="description description-wide">
                            <label for="form-email-subject">
                                <?php _e( 'E-mail Subject' , 'smart-touch-form-builder'); ?>
                                <span class="sti-tooltip" title="<?php esc_attr_e( 'About E-mail Subject', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'This option sets the subject of the email that is sent to the emails you have set in the E-mail(s) To field.', 'smart-touch-form-builder' ); ?>">(?)</span>
            					<br />
                                <input type="text" value="<?php echo stripslashes( $form_subject ); ?>" class="widefat" id="form-email-subject" name="form_email_subject" />
                            </label>
                            </p>
                            <br class="clear" />
                            <!-- Sender Name -->
                            <p class="description description-thin">
                            <label for="form-email-sender-name">
                                <?php _e( 'Your Name or Company' , 'smart-touch-form-builder'); ?>
                                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Your Name or Company', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'This option sets the From display name of the email that is sent to the emails you have set in the E-mail(s) To field.', 'smart-touch-form-builder' ); ?>">(?)</span>
            					<br />
                              <!--  <input type="text" value="<?php // echo $form_email_from_name; ?>" class="widefat" id="form-email-sender-name" name="form_email_from_name"<?php // echo ( $form_email_from_name_override != '' ) ? ' readonly="readonly"' : ''; ?> />  -->
                            </label>
                            </p>

                            <!-- Sender E-mail -->
                            <p class="description description-thin">
                            <label for="form-email-sender">
                                <?php _e( 'Reply-To E-mail' , 'smart-touch-form-builder'); ?>
                                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Reply-To Email', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Manually set the email address that users will reply to.', 'smart-touch-form-builder' ); ?>">(?)</span>
            					<br />
                                <input type="text" value="<?php echo $form_email_from; ?>" class="widefat" id="form-email-sender" name="form_email_from"<?php echo ( $form_email_from_override != '' ) ? ' readonly="readonly"' : ''; ?> />
                            </label>
                            </p>
                            
                            <!-- E-mail(s) To -->
                            <?php
                                // Basic count to keep track of multiple options
                                $count = 1;
                                // Loop through the options
                                foreach ( $form_email_to as $email_to ) :
                            ?>
                            <div id="clone-email-<?php echo $count; ?>" class="option">
                                <p class="description description-wide">
                                    <label for="form-email-to-<?php echo "$count"; ?>" class="clonedOption">
                                    <?php _e( 'E-mail(s) To' , 'smart-touch-form-builder'); ?>
                                    <span class="sti-tooltip" title="<?php esc_attr_e( 'About E-mail(s) To', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'This option sets single or multiple emails to send the submitted form data to. At least one email is required.', 'smart-touch-form-builder' ); ?>">(?)</span>
            					<br />
                                        <input type="text" value="<?php echo stripslashes( $email_to ); ?>" name="form_email_to[]" class="widefat" id="form-email-to-<?php echo "$count"; ?>" />
                                    </label>
                                    <a href="#" class="addEmail sti-interface-icon sti-interface-plus" title="<?php esc_attr_e( 'Add an Email', 'visua-form-builder' ); ?>">
                                    	<?php _e( 'Add', 'smart-touch-form-builder' ); ?>
                                    </a>
                                    <a href="#" class="deleteEmail sti-interface-icon sti-interface-minus" title="<?php esc_attr_e( 'Delete Email', 'smart-touch-form-builder' ); ?>">
                                    	<?php _e( 'Delete', 'smart-touch-form-builder' ); ?>
                                    </a>
                                </p>
                                <br class="clear" />
                            </div>
                            <?php
                                    $count++;
                                endforeach;
                            ?>
                            <div class="clear"></div>
                        </div>
                        <!-- Confirmation section -->
                        <a href="#confirmation" class="settings-links<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>"><?php _e( 'Confirmation', 'smart-touch-form-builder' ); ?><span class="sti-large-arrow"></span></a>
                        <div id="confirmation-message" class="form-details<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>">
                            <p><em><?php _e( "After someone submits a form, you can control what is displayed. By default, it's a message but you can send them to another WordPress Page or a custom URL." , 'smart-touch-form-builder'); ?></em></p>
                            <label for="form-success-type-text" class="menu-name-label open-label">
                                <input type="radio" value="text" id="form-success-type-text" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'text' ); ?> />
                                <span><?php _e( 'Text' , 'smart-touch-form-builder'); ?></span>
                            </label>
                            <label for="form-success-type-page" class="menu-name-label open-label">
                                <input type="radio" value="page" id="form-success-type-page" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'page' ); ?>/>
                                <span><?php _e( 'Page' , 'smart-touch-form-builder'); ?></span>
                            </label>
                            <label for="form-success-type-redirect" class="menu-name-label open-label">
                                <input type="radio" value="redirect" id="form-success-type-redirect" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'redirect' ); ?>/>
                                <span><?php _e( 'Redirect' , 'smart-touch-form-builder'); ?></span>
                            </label>
                            <br class="clear" />
                            <p class="description description-wide">
                            <?php
                            $default_text = '';
                            /* If there's no text message, make sure there is something displayed by setting a default */
                            if ( $form_success_message === '' )
                                $default_text = sprintf( '<p id="form_success">%s</p>', __( 'Your form was successfully submitted. Thank you for contacting us.' , 'smart-touch-form-builder') );
                            ?>
                            <textarea id="form-success-message-text" class="form-success-message<?php echo ( 'text' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_text"><?php echo $default_text; ?><?php echo ( 'text' == $form_success_type ) ? $form_success_message : ''; ?></textarea>
                            <?php
                            /* Display all Pages */
                            wp_dropdown_pages( array(
                                'name' => 'form_success_message_page',
                                'id' => 'form-success-message-page',
                                'class' => 'widefat',
                                'show_option_none' => __( 'Select a Page' , 'smart-touch-form-builder'),
                                'selected' => $form_success_message
                            ));
                            ?>
                            <input type="text" value="<?php echo ( 'redirect' == $form_success_type ) ? $form_success_message : ''; ?>" id="form-success-message-redirect" class="form-success-message regular-text<?php echo ( 'redirect' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_redirect" placeholder="http://" />
                            </p>
                        <br class="clear" />
                        </div>
                        <!-- Notification section -->
                        <a href="#notification" class="settings-links<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>"><?php _e( 'Notification', 'smart-touch-form-builder' ); ?><span class="sti-large-arrow"></span></a>
                        <div id="notification" class="form-details<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>">
                            <p><em><?php _e( "When a user submits their entry, you can send a customizable notification email." , 'smart-touch-form-builder'); ?></em></p>
                            <label for="form-notification-setting">
                                <input type="checkbox" value="1" id="form-notification-setting" class="form-notification" name="form_notification_setting" <?php echo ( $form_notification_setting != '' ) ? ' readonly="readonly"' : ''; ?>  style="margin-top:-1px;margin-left:0;"/>
                                <?php _e( 'Send Confirmation Email to User' , 'smart-touch-form-builder'); ?>
                            </label>
                            <br class="clear" />
                            <div id="notification-email">
                                <p class="description description-wide">
                                <label for="form-notification-email-name">
                                    <?php _e( 'Sender Name or Company' , 'smart-touch-form-builder'); ?>
                                    <span class="sti-tooltip" title="<?php esc_attr_e( 'About Sender Name or Company', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Enter the name you would like to use for the email notification.', 'smart-touch-form-builder' ); ?>">(?)</span>
            						<br />
                                    <input type="text" value="<?php echo $form_notification_email_name; ?>" class="widefat" id="form-notification-email-name" name="form_notification_email_name" />
                                </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                <label for="form-notification-email-from">
                                    <?php _e( 'Reply-To E-mail' , 'smart-touch-form-builder'); ?>
                                    <span class="sti-tooltip" title="<?php esc_attr_e( 'About Reply-To Email', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Manually set the email address that users will reply to.', 'smart-touch-form-builder' ); ?>">(?)</span>
            						<br />
                                    <input type="text" value="<?php echo $form_notification_email_from; ?>" class="widefat" id="form-notification-email-from" name="form_notification_email_from" />
                                </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                    <label for="form-notification-email">
                                        <?php _e( 'E-mail To' , 'smart-touch-form-builder'); ?>
                                        <span class="sti-tooltip" title="<?php esc_attr_e( 'About E-mail To', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Select a required email field from your form to send the notification email to.', 'smart-touch-form-builder' ); ?>">(?)</span>
            							<br />
                                        <?php if ( empty( $emails ) ) : ?>
                                        <span><?php _e( 'No required email fields detected', 'smart-touch-form-builder' ); ?></span>
                                        <?php else : ?>
                                        <select name="form_notification_email" id="form-notification-email" class="widefat">
                                            <option value="" <?php selected( $form_notification_email, '' ); ?>></option>
                                            <?php
                                            foreach( $emails as $email ) {
                                                echo sprintf( '<option value="%1$d"%2$s>%3$s</option>',
                                                	$email->field_id,
                                                	selected( $form_notification_email, $email->field_id, 0 ),
                                                	$email->field_name
                                                );
                                            }
                                            ?>
                                        </select>
                                        <?php endif; ?>
                                    </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                <label for="form-notification-subject">
                                   <?php _e( 'E-mail Subject' , 'smart-touch-form-builder'); ?>
                                   <span class="sti-tooltip" title="<?php esc_attr_e( 'About E-mail Subject', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'This option sets the subject of the email that is sent to the emails you have set in the E-mail To field.', 'smart-touch-form-builder' ); ?>">(?)</span>
            						<br />
                                    <input type="text" value="<?php echo $form_notification_subject; ?>" class="widefat" id="form-notification-subject" name="form_notification_subject" />
                                </label>
                                </p>
                                <br class="clear" />
                                <p class="description description-wide">
                                <label for="form-notification-message"><?php _e( 'Message' , 'smart-touch-form-builder'); ?></label>
                                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Message', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Insert a message to the user. This will be inserted into the beginning of the email body.', 'smart-touch-form-builder' ); ?>">(?)</span>
            					<br />
                                <textarea id="form-notification-message" class="form-notification-message widefat" name="form_notification_message"><?php echo $form_notification_message; ?></textarea>
                                </p>
                                <br class="clear" />
                                <label for="form-notification-entry">
                                <input type="checkbox" value="1" id="form-notification-entry" class="form-notification" name="form_notification_entry" <?php checked( $form_notification_entry, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
                                <?php _e( "Include a Copy of the User's Entry" , 'smart-touch-form-builder'); ?>
                            </label>
                            <br class="clear" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="post-body">
     <span class="sti-tooltip custom-tooltip" rel="Set the field labels for this form to be aligned either on top, to the left, or to the right. By default, all labels are aligned on top of the inputs." title="About Label Alignment">(?)</span>
        <div id="post-body-content">
        <div id="sti-fieldset-first-warning" class="error"><?php printf( '<p><strong>%1$s </strong><br>%2$s</p>', __( 'Warning &mdash; Missing Fieldset', 'smart-touch-form-builder' ), __( 'Your form may not function or display correctly. Please be sure to add or move a Fieldset to the beginning of your form.' , 'smart-touch-form-builder') ); ?></div>
        <!-- !Field Items output -->
		<ul id="sti-menu-to-edit" class="menu ui-sortable droppable">
		<?php echo $this->field_output( $form_nav_selected_id ); ?>
		</ul>
        </div>
        <br class="clear" />
     </div>
     <br class="clear" />
    <div id="form-editor-footer">
    	<div class="sti-major-publishing-actions">
            <div class="publishing-action" style="display:none;">
            	<?php submit_button( __( 'Save Form', 'smart-touch-form-builder' ), 'primary', 'save_form', false ); ?>
            </div> <!-- .publishing-action -->
        </div> <!-- .sti-major-publishing-actions -->
    </div> <!-- #form-editor-footer -->
</form>
	            </div> <!-- .form-edit -->
	        </div> <!-- #sti-form-builder-management -->
	    </div> <!-- sti-form-builder-main -->
    </div> <!-- .sti-postbox-container -->
</div> <!-- #sti-form-builder-frame -->
<?php
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

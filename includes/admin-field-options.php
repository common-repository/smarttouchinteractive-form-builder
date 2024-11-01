<?php
if ( ! defined( 'ABSPATH' ) ){ exit;}
global $wpdb;
$field_where = ( isset( $field_id ) && !is_null( $field_id ) ) ? "AND field_id = $field_id" : '';
// Display all fields for the selected form
$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d $field_where ORDER BY field_sequence ASC", $form_nav_selected_id ) );
$depth = 1;
$parent = $last = 0;
$st=1;
// Loop through each field and display
foreach ( $fields as &$field ) :
	// If we are at the root level
	if ( !$field->field_parent && $depth > 1 ) {
		// If we've been down a level, close out the list
		while ( $depth > 1 ) {
			echo '</li></ul>';
			$depth--;
		}
		// Close out the root item
		echo '</li>';
	}
	// first item of <ul>, so move down a level
	elseif ( $field->field_parent && $field->field_parent == $last ) {
		echo '<ul class="parent">';
		$depth++;
	}
	// Close up a <ul> and move up a level
	elseif ( $field->field_parent && $field->field_parent != $parent ) {
		echo '</li></ul></li>';
		$depth--;
	}
	// Same level so close list item
	elseif ( $field->field_parent && $field->field_parent == $parent )
		echo '</li>';
	// Store item ID and parent ID to test for nesting
	$last = $field->field_id;
	$parent = $field->field_parent;
?>

<li id="form_item_<?php echo $field->field_id; ?>" class="form-item<?php echo ( in_array( $field->field_type, array( 'submit', 'secret', 'verification' ) ) ) ? ' ui-state-disabled' : ''; ?><?php echo ( !in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) ) ? ' mjs-nestedSortable-no-nesting' : ''; ?>">
<dl class="menu-item-bar sti-menu-item-inactive">
	<dt class="sti-menu-item-handle sti-menu-item-type-<?php echo esc_attr( $field->field_type ); ?>">
		<span class="item-title"><?php echo stripslashes( esc_attr( $field->field_name ) ); ?><?php echo ( $field->field_required == 'yes' ) ? ' <span class="is-field-required">*</span>' : ''; ?></span>
        <span class="item-controls">
			<span class="item-type"><?php echo strtoupper( str_replace( '-', ' ', $field->field_type ) ); ?></span>
			<a href="#" title="<?php _e( 'Edit Field Item' , 'smart-touch-form-builder'); ?>" id="edit-<?php echo $field->field_id; ?>" class="item-edit"><?php _e( 'Edit Field Item' , 'smart-touch-form-builder'); ?></a>
		</span>
	</dt>
</dl>
<div id="form-item-settings-<?php echo $field->field_id; ?>" class="menu-item-settings field-type-<?php echo $field->field_type; ?> blind-txt" >
<?php if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) ) : ?>
	<p class="description description-wide">
		<label for="edit-form-item-name-<?php echo $field->field_id; ?>"><?php echo ( in_array( $field->field_type, array( 'fieldset', 'verification' ) ) ) ? 'Legend' : 'Name'; ?>
        <span class="sti-tooltip" rel="<?php esc_attr_e( 'For Fieldsets, a Legend is simply the name of that group. Use general terms that describe the fields included in this Fieldset.', 'smart-touch-form-builder' ); ?>" title="<?php esc_attr_e( 'About Legend', 'smart-touch-form-builder' ); ?>">(?)</span>
            <br />
			<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
		</label>
	</p>
    <p class="description description-wide">
        <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
            <?php _e( 'CSS Classes' , 'smart-touch-form-builder'); ?>
            <span class="sti-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder' ); ?>">(?)</span>
            <br />
            <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
        </label>
    </p>
<?php elseif( $field->field_type == 'instructions' ) : ?>
	<!-- Instructions -->
	<p class="description description-wide">
		<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
				<?php _e( 'Name' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Name', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( "A field's name is the most visible and direct way to describe what that field is for.", 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
				<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
		</label>
	</p>
	<!-- Description -->
	<p class="description description-wide">
		<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
        	<?php _e( 'Description (HTML tags allowed)', 'smart-touch-form-builder' ); ?>
        	<span class="sti-tooltip" title="<?php esc_attr_e( 'About Instructions Description', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'The Instructions field allows for long form explanations, typically seen at the beginning of Fieldsets or Sections. HTML tags are allowed.', 'smart-touch-form-builder' ); ?>">(?)</span>
            <br />
			<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo $field->field_id; ?>" /><?php echo stripslashes( $field->field_description ); ?></textarea>
		</label>
	</p>
	<!-- CSS Classes -->
<p class="description description-thin">
    <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
        <?php _e( 'CSS Classes' , 'smart-touch-form-builder-pro'); ?>
        <span class="sti-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder-pro' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder-pro' ); ?>">(?)</span>
        <br />
        <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
    </label>
</p>
<!-- Field Layout -->
<p class="description description-thin">
	<label for="edit-form-item-layout">
		<?php _e( 'Hidden Field' , 'smart-touch-form-builder-pro'); ?>
        <span class="sti-tooltip" title="<?php esc_attr_e( 'About Hidden Field', 'smart-touch-form-builder-pro' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'smart-touch-form-builder-pro' ); ?>">(?)</span>
    <br />
		<select name="field_layout-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-layout-<?php echo $field->field_id; ?>">
		     <option value="no" <?php selected( $field->field_layout, 'no' ); ?>><?php _e( 'No' , 'smart-touch-form-builder-pro'); ?></option>
            <option value="yes" <?php selected( $field->field_layout, 'yes' ); ?>><?php _e( 'Yes' , 'smart-touch-form-builder-pro'); ?></option>
           
            
            <?php apply_filters( 'sti_admin_field_layout', $field->field_layout ); ?>
		</select>
	</label>
</p>
<?php else: ?>
	<!-- Name -->
	<p class="description description-wide">
		<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
			<?php _e( 'Name' , 'smart-touch-form-builder'); ?>
            <span class="sti-tooltip" title="<?php esc_attr_e( 'About Name', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( "A field's name is the most visible and direct way to describe what that field is for.", 'smart-touch-form-builder' ); ?>">(?)</span>
            <br />
			<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
		</label>
	</p>
	<?php if($field->field_type=='hidden'): echo '<div class="blind-txt">'; endif; ?>
	<?php if ( $field->field_type == 'submit' ) : ?>
		<!-- CSS Classes -->
        <p class="description description-wide">
            <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                <?php _e( 'CSS Classes' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
                <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
            </label>
        </p>
	<?php elseif ( $field->field_type !== 'submit' ) : ?>
		<!-- Description -->
		<p class="description description-wide">
			<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
				<?php _e( 'Description' , 'smart-touch-form-builder'); ?>
                 <span class="sti-tooltip" title="<?php esc_attr_e( 'About Description', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'A description is an optional piece of text that further explains the meaning of this field. Descriptions are displayed below the field. HTML tags are allowed.', 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
				<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo $field->field_id; ?>" /><?php echo stripslashes( $field->field_description ); ?></textarea>
			</label>
		</p>
		<?php
			// Display the Options input only for radio, checkbox, and select fields
			if ( in_array( $field->field_type, array( 'radio', 'checkbox', 'select' ) ) ) : ?>
			<!-- Options -->
			<p class="description description-wide" >
				<?php _e( 'Options' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Options', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'This property allows you to set predefined options to be selected by the user.  Use the plus and minus buttons to add and delete options.  At least one option must exist.', 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
			<?php
				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) )
						$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				}
				// Otherwise, present some default options
				else
					$opts_vals = array( 'Option 1', 'Option 2', 'Option 3' );
				// Basic count to keep track of multiple options
				$count = 1;
?>
			<div class="sti-cloned-options">

			<?php $showopt_value='';  $cked='';
                  $showopt_value=explode('|',$field->fieldid);
                  
			 foreach ( $opts_vals as $options ) :     $opt_values=explode('!',$options);    
            ?>
			<div id="clone-<?php echo $field->field_id . '-' . $count; ?>" class="option">
				<label for="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" class="clonedOption">
					<input type="checkbox" value="<?php echo $opt_values[0]; ?>" name="showopt-<?php echo $field->field_id; ?>[]"  <?php if (in_array($opt_values[0], $showopt_value)){ $cked='checked="checked"';}else{ $cked=''; } echo $cked; ?> >
					<input type="checkbox" class="sti_default-<?php echo $field->field_id; ?>" value="<?php echo $opt_values[0]; ?>" name="field_default-<?php echo $field->field_id; ?>" <?php checked( $field->field_default, $opt_values[0] ); ?> />

					<input type="hidden" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" />
					<input type="text" readonly="readonly"  value="<?php echo $opt_values[1]; ?>" />
				</label>
			<!-- 	<a href="#" class="deleteOption sti-interface-icon sti-interface-minus" title="Delete Option">
					<?php// _e( 'Delete', 'smart-touch-form-builder' ); ?>
				</a>  -->
				<span class="sti-interface-icon sti-interface-sort" title="<?php esc_attr_e( 'Drag and Drop to Sort Options', 'smart-touch-form-builder-pro' ); ?>"></span>
			</div>
			<?php
					$count++;
				endforeach;
			?>
			<script type="text/javascript">
		        jQuery('.sti_default-<?php echo $field->field_id; ?>').on('change', function() {
		            jQuery('.sti_default-<?php echo $field->field_id; ?>').not(this).prop('checked', false);  
		        });
			</script>
			</div> <!-- .sti-cloned-options -->
			<div class="clear"></div>
			<!-- <div class="sti-add-options-group">
				<a href="#" class="sti-button sti-add-option" title="Add Option">
					<?php// _e( 'Add Option', 'smart-touch-form-builder' ); ?>
					<span class="sti-interface-icon sti-interface-plus"></span>
				</a>
			</div>  -->
			</p>
		<?php
			// Unset the options for any following radio, checkboxes, or selects
			unset( $opts_vals );
			endif;
		?>
		<?php if ( in_array( $field->field_type, array( 'file-upload' ) ) ) : ?>
        	<!-- File Upload Accepts -->
			<p class="description description-wide" >
                <?php
				$opts_vals = array( '' );
				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) )
						$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : unserialize( $field->field_options );
				}
				// Loop through the options
				foreach ( $opts_vals as $options ) {
			?>
				<label for="edit-form-item-options-<?php echo $field->field_id; ?>">
					<?php _e( 'Accepted File Extensions' , 'smart-touch-form-builder'); ?>
                    <span class="sti-tooltip" title="<?php esc_attr_e( 'About Accepted File Extensions', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Control the types of files allowed.  Enter extensions without periods and separate multiples using the pipe character ( | ).', 'smart-touch-form-builder' ); ?>">(?)</span>
            		<br />
                    <input type="text" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id; ?>" />
				</label>
            </p>
        <?php
				}
			// Unset the options for any following radio, checkboxes, or selects
			unset( $opts_vals );
			endif;
		?>
		<?php if ( in_array( $field->field_type, array( 'date' ) ) ) : ?>
	    	<!-- Date Format -->
			<p class="description description-wide">
				<?php
					$opts_vals = maybe_unserialize( $field->field_options );
					$dateFormat = ( isset( $opts_vals['dateFormat'] ) ) ? $opts_vals['dateFormat'] : 'mm/dd/yy';
				?>
				<label for="edit-form-item-date-dateFormat-<?php echo $field->field_id; ?>">
					<?php _e( 'Date Format', 'smart-touch-form-builder' ); ?>
					<span class="sti-tooltip" title="<?php esc_attr_e( 'About Date Format', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Set the date format for each date picker.', 'smart-touch-form-builder' ); ?>">(?)</span>
					<br />
					<input type="text" value="<?php echo esc_attr( $dateFormat ); ?>" name="field_options-<?php echo $field->field_id; ?>[dateFormat]" class="widefat" id="edit-form-item-date-dateFormat-<?php echo $field->field_id; ?>" />
				</label>
	        </p>
		<?php
			// Unset the options for any following radio, checkboxes, or selects
			unset( $opts_vals );
			endif;
		?>
		<!-- Validation -->
		<p class="description description-thin">
			<label for="edit-form-item-validation">
				<?php _e( 'Validation' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Validation', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Ensures user-entered data is formatted properly. For more information on Validation, refer to the Help tab at the top of this page.', 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
			   <?php if ( in_array( $field->field_type , array( 'text', 'time', 'number','textarea' ) ) ) : ?>
			   
				   <select name="field_validation-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-validation-<?php echo $field->field_id; ?>">
				   		<?php if ( $field->field_type == 'time' ) : ?>
						<option value="time-12" <?php selected( $field->field_validation, 'time-12' ); ?>><?php _e( '12 Hour Format' , 'smart-touch-form-builder'); ?></option>
						<option value="time-24" <?php selected( $field->field_validation, 'time-24' ); ?>><?php _e( '24 Hour Format' , 'smart-touch-form-builder'); ?></option>
						<?php elseif ( in_array( $field->field_type, array( 'number' ) ) ) : ?>
                        <option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'smart-touch-form-builder'); ?></option>
						<option value="alphabet" <?php selected( $field->field_validation, 'alphabet' ); ?>><?php _e( 'Alphabet' , 'smart-touch-form-builder'); ?></option>
						<?php else : ?>
						<option value="" <?php selected( $field->field_validation, '' ); ?>><?php _e( 'None' , 'smart-touch-form-builder'); ?></option>
						<option value="email" <?php selected( $field->field_validation, 'email' ); ?>><?php _e( 'Email' , 'smart-touch-form-builder'); ?></option>
						<option value="url" <?php selected( $field->field_validation, 'url' ); ?>><?php _e( 'URL' , 'smart-touch-form-builder'); ?></option>
						<option value="date" <?php selected( $field->field_validation, 'date' ); ?>><?php _e( 'Date' , 'smart-touch-form-builder'); ?></option>
						<option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'smart-touch-form-builder'); ?></option>
						<option value="alphabet" <?php selected( $field->field_validation, 'alphabet' ); ?>><?php _e( 'Alphabet' , 'smart-touch-form-builder'); ?></option>
						<option value="phone" <?php selected( $field->field_validation, 'phone' ); ?>><?php _e( 'Phone' , 'smart-touch-form-builder'); ?></option>
						<?php endif; ?>
				   </select>
			   <?php   else :
				   $field_validation = '';
				   switch ( $field->field_type ) {
					   case 'email' :
						case 'url' :
						case 'phone' :
							$field_validation = $field->field_type;
						break;
						case 'currency' :
							$field_validation = 'number';
						break;
						case 'number' :
							$field_validation = 'digits';
						break;
				   }
			   ?>
			   <input type="text" class="widefat" name="field_validation-<?php echo $field->field_id; ?>" value="number" readonly="readonly" />
			   <?php endif; ?>
			</label>
		</p>
		<!-- Required -->
		<p class="field-link-target description description-thin" >
			<label for="edit-form-item-required">
				<?php _e( 'Required' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Required', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Requires the field to be completed before the form is submitted. By default, all fields are set to No.', 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
				<select name="field_required-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-required-<?php echo $field->field_id; ?>">
					<option value="no" <?php selected( $field->field_required, 'no' ); ?>><?php _e( 'No' , 'smart-touch-form-builder'); ?></option>
					<option value="yes" <?php selected( $field->field_required, 'yes' ); ?>><?php _e( 'Yes' , 'smart-touch-form-builder'); ?></option>
				</select>
			</label>
		</p>
		<?php if ( !in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
			<!-- Size -->
			<p class="description description-thin" >
				<label for="edit-form-item-size">
					<?php _e( 'Size' , 'smart-touch-form-builder'); ?>
                    <span class="sti-tooltip" title="<?php esc_attr_e( 'About Size', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Control the size of the field.  By default, all fields are set to Medium.', 'smart-touch-form-builder' ); ?>">(?)</span>
                    <br />
					<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>">
                    	<option value="small" <?php selected( $field->field_size, 'small' ); ?>><?php _e( 'Small' , 'smart-touch-form-builder'); ?></option>
						<option value="medium" <?php selected( $field->field_size, 'medium' ); ?>><?php _e( 'Medium' , 'smart-touch-form-builder'); ?></option>
						<option value="large" <?php selected( $field->field_size, 'large' ); ?>><?php _e( 'Large' , 'smart-touch-form-builder'); ?></option>
					</select>
				</label>
			</p>
		<?php elseif ( in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
			<!-- Options Layout -->
			<p class="description description-thin" >
				<label for="edit-form-item-size">
					<?php _e( 'Options Layout' , 'smart-touch-form-builder'); ?>
                    <span class="sti-tooltip" title="<?php esc_attr_e( 'About Options Layout', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Control the layout of radio buttons or checkboxes.  By default, options are arranged in One Column.', 'smart-touch-form-builder' ); ?>">(?)</span>
                    <br />
					<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>"<?php echo ( $field->field_type == 'time' ) ? ' disabled="disabled"' : ''; ?>>
						<option value="" <?php selected( $field->field_size, '' ); ?>><?php _e( 'One Column' , 'smart-touch-form-builder'); ?></option>
                        <option value="two-column" <?php selected( $field->field_size, 'two-column' ); ?>><?php _e( 'Two Columns' , 'smart-touch-form-builder'); ?></option>
						<option value="three-column" <?php selected( $field->field_size, 'three-column' ); ?>><?php _e( 'Three Columns' , 'smart-touch-form-builder'); ?></option>
                        <option value="auto-column" <?php selected( $field->field_size, 'auto-column' ); ?>><?php _e( 'Auto Width' , 'smart-touch-form-builder'); ?></option>
					</select>
				</label>
			</p>
		<?php endif; ?>
			<!-- Field Layout -->
			<p class="description description-thin" >
				<label for="edit-form-item-layout">
 <?php if($field->field_type=='select'){  _e( 'Display Field' , 'smart-touch-form-builder'); ?>
 <span class="sti-tooltip" title="<?php esc_attr_e( 'About Hidden Field', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'smart-touch-form-builder' ); ?>">(?)</span>
 <br />
<select name="typeid-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-typeid-<?php echo $field->field_id; ?>">
<option value="yes" <?php selected( $field->typeid, 'yes' ); ?>><?php _e( 'Yes' , 'smart-touch-form-builder-pro'); ?></option>
<option value="no" <?php selected( $field->typeid, 'no' ); ?>><?php _e( 'No' , 'smart-touch-form-builder-pro'); ?></option>

 <?php }else{  _e( 'Hidden Field' , 'smart-touch-form-builder'); ?> 
  <span class="sti-tooltip" title="<?php esc_attr_e( 'About Hidden Field', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Used to create advanced layouts. Align fields side by side in various configurations.', 'smart-touch-form-builder' ); ?>">(?)</span>
 <br />
<select name="field_layout-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-layout-<?php echo $field->field_id; ?>">
<option value="no" <?php selected( $field->field_layout, 'no' ); ?>><?php _e( 'No' , 'smart-touch-form-builder-pro'); ?></option>
<option value="yes" <?php selected( $field->field_layout, 'yes' ); ?>><?php _e( 'Yes' , 'smart-touch-form-builder-pro'); ?></option>
 <?php } ?>
					
           
				</select>
				</label>
			</p>
		<?php if($field->field_type=='hidden'): echo '</div>'; endif; ?>	
		<?php if ( !in_array( $field->field_type, array( 'radio', 'select', 'checkbox', 'time', 'address' ) ) ) : ?>
		<!-- Default Value -->
		<p class="description description-wide">
            <label for="edit-form-item-default-<?php echo $field->field_id; ?>">
                <?php _e( 'Default Value' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Default Value', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Set a default value that will be inserted automatically.', 'smart-touch-form-builder' ); ?>">(?)</span>
            	<br />
                <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_default ) ); ?>" name="field_default-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-default-<?php echo $field->field_id; ?>" maxlength="255" />
            </label>
		</p>
		<?php elseif( in_array( $field->field_type, array( 'address' ) ) ) : ?>
		<!-- Default Country -->
		<p class="description description-wide">
            <label for="edit-form-item-default-<?php echo $field->field_id; ?>">
                <?php _e( 'Default Country' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About Default Country', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'Select the country you would like to be displayed by default.', 'smart-touch-form-builder' ); ?>">(?)</span>
            	<br />
                <select name="field_default-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-default-<?php echo $field->field_id; ?>">
                <?php
                foreach ( $this->countries as $country ) {
					echo '<option value="' . $country . '" ' . selected( $field->field_default, $country, 0 ) . '>' . $country . '</option>';
				}
				?>
				</select>
            </label>
		</p>
		<?php endif; ?>
		<!-- CSS Classes -->
		<p class="description description-wide">
            <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                <?php _e( 'CSS Classes' , 'smart-touch-form-builder'); ?>
                <span class="sti-tooltip" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder' ); ?>" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder' ); ?>">(?)</span>
                <br />
                <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" maxlength="255" />
            </label>
		</p>
	<?php endif; ?>
<?php endif; ?>
<?php  if ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) : ?>
		<!-- Delete link -->
		<a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=smart-touch-form-builder&amp;action=delete_field&amp;form=' . $form_nav_selected_id . '&amp;field=' . $field->field_id ), 'delete-field-' . $form_nav_selected_id ) ); ?>" class="sti-button sti-delete item-delete submitdelete deletion">
			<?php _e( 'Delete' , 'smart-touch-form-builder'); ?>
			<span class="sti-interface-icon sti-interface-trash"></span>
		</a>
<?php endif; ?>
<input type="hidden" name="field_id[<?php echo $field->field_id; ?>]" value="<?php echo $field->field_id; ?>" />
</div>
<?php
$st++;
endforeach;
// This assures all of the <ul> and <li> are closed
if ( $depth > 1 ) {
	while( $depth > 1 ) {
		echo '</li>
			</ul>';
		$depth--;
	}
}
// Close out last item
echo '</li>';
echo ob_get_clean();
?>
<script>
jQuery.noConflict();
(function( $ ) {
$("#save_form").click(function () {
var arr = [];
$("#post-body-content span.item-title").each(function(index, elem){
arr.push($(this).text());
});
if(jQuery.inArray("Lead Source", arr)!== -1) {
$('#smart-touch-form-builder-update').get(0).submit(); 
}else {
//alert("Lead Source Is Required");
$('#smart-touch-form-builder-update').get(0).submit(); 
}
});})(jQuery);
</script>
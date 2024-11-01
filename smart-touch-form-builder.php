<?php
if ( ! defined( 'ABSPATH' ) ){ exit;}
/*
Plugin Name: SmartTouch Interactive form builder
Description: Dynamically build forms using a simple interface. Forms include jQuery library.
Author: SmartTouch Team
Author URI: http://www.smarttouchinteractive.com/
Version: 1.1.19
*/
// Version number to output as meta tag
define( 'STI_VERSION', '1.1.19' );
/*
This program is free software for smart touch user; 
*/

add_action('wp_head','sti_head');
add_action('wp_footer','sti_foot');

function sti_head(){
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/hphp.php' );
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/hscript.php' );
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/hcptscript.php' );
}
function sti_foot(){
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/fphp.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/fscript.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/fcptscript.php' );		
}

// Instantiate new class
$smart_touch_form_builder = new SmartTouch_Form_Builder();
// SmartTouch Form Builder class
class SmartTouch_Form_Builder{
	/**
	 * The DB version. Used for SQL install and upgrades.
	 *
	 * Should only be changed when needing to change SQL
	 * structure or custom capabilities.
	 *
 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $sti_db_version = '1.3';
	/**
	 * Flag used to add scripts to front-end only once
	 *
	 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $add_scripts = false;
	/**
	 * An array of countries to be used throughout plugin
	 *
	 * @since 1.0
	 * @var array
	 * @access public
	 */
	public $countries = array( "", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d\'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia, The", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepa", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe" );
	/**
	 * Admin page menu hooks
	 *
	 * @since 2.7.2
	 * @var array
	 * @access private
	 */
	private $_admin_pages = array();
	/**
	 * Flag used to display post_max_vars error when saving
	 *
	 * @since 2.7.6
	 * @var string
	 * @access protected
	 */
	protected $post_max_vars = false;
	/**
	 * field_table_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $field_table_name;
	/**
	 * form_table_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $form_table_name;
	/**
	 * entries_table_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $entries_table_name;
	/**
	 * load_dev_files
	 *
	 * @var mixed
	 * @access public
	 */
	public $load_dev_files;
	/**
	 * Constructor. Register core filters and actions.
	 *
	 * @access public
	 */
	public function __construct(){
		global $wpdb;
		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'smarttouch_form_builder_fields';
		$this->form_table_name 		= $wpdb->prefix . 'smarttouch_form_builder_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'smarttouch_form_builder_entries';
		// Add suffix to load dev files
		$this->load_dev_files = ( defined( 'STI_SCRIPT_DEBUG' ) && STI_SCRIPT_DEBUG ) ? '' : '.min';
		// Saving functions
		add_action( 'admin_init', array( &$this, 'save_add_new_form' ) );
		add_action( 'admin_init', array( &$this, 'save_update_form' ) );
		add_action( 'admin_init', array( &$this, 'save_trash_delete_form' ) );
    	add_action( 'admin_init', array( &$this, 'save_copy_form' ) );
		add_action( 'admin_init', array( &$this, 'save_settings' ) );
		// Build options and settings pages.
		add_action( 'admin_menu', array( &$this, 'add_admin' ) );
		add_action( 'admin_menu', array( &$this, 'additional_plugin_setup' ) );
		// Register AJAX functions
		$actions = array(
		// Form Builder
			'sort_field',
			'create_field',
			'delete_field',
			'form_settings',
			// Media button
			'media_button',
			);
	// Add all AJAX functions
		foreach( $actions as $name ) {
			add_action( "wp_ajax_smart_touch_form_builder_$name", array( &$this, "ajax_$name" ) );
		}
		// Adds additional media button to insert form shortcode
		add_action( 'media_buttons', array( &$this, 'add_media_button' ), 999 );
		// Adds a Dashboard widget
		add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widget' ) );
		// Adds a Settings link to the Plugins page
		add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
		// Check the db version and run SQL install, if needed
		add_action( 'plugins_loaded', array( &$this, 'update_db_check' ) );
		// Display update messages
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		// Load i18n
		add_action( 'plugins_loaded', array( &$this, 'languages' ) );
		// Print meta keyword
		add_action( 'wp_head', array( &$this, 'add_meta_keyword' ) );
		add_shortcode( 'sti', array( &$this, 'form_code' ) );
		add_shortcode( 'popup_sti', array( &$this, 'popup_code' ) );
		add_action( 'init', array( &$this, 'email' ), 10 );
		add_action( 'init', array( &$this, 'confirmation' ), 12 );
		// Add CSS to the front-end
		add_action( 'wp_enqueue_scripts', array( &$this, 'css' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'csspop' ) );
	}
	/**
	 * Allow for additional plugin code to be run during admin_init
	 * which is not available during the plugin __construct()
	 *
	 * @since 2.7
	 */
	public function additional_plugin_setup() {
		$page_main = $this->_admin_pages[ 'sti' ];
		if ( !get_option( 'sti_dashboard_widget_options' ) ) {
			$widget_options['sti_dashboard_recent_entries'] = array(
				'items' => 5,
				);
			update_option( 'sti_dashboard_widget_options', $widget_options );
		}
	}
	/**
	 * Output plugin version number to help with troubleshooting
	 *
	 * @since 2.7.5
	 */
	public function add_meta_keyword() {
		// Get global settings
		$sti_settings 	= get_option( 'sti-settings' );
		// Settings - Disable meta tag version
		$settings_meta	= isset( $sti_settings['show-version'] ) ? '' : '<!-- <meta name="sti" version="'. STI_VERSION . '" /> -->' . "\n";
		echo apply_filters( 'sti_show_version', $settings_meta );
	}
	/**
	 * Load localization file
	 *
	 * @since 2.7
	 */
	public function languages() {
		load_plugin_textdomain( 'smart-touch-form-builder', false , 'smart-touch-form-builder/languages' );
	}
	/**
	 * Adds extra include files
	 *
	 * @since 1.2
	 */
	public function includes(){
		global $entries_list, $entries_detail;
		// Load the Entries List class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-entries-list.php' );
		$entries_list = new SmartTouchFormBuilder_Entries_List();
        // Load the Entries Details class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-entries-detail.php' );
		$entries_detail = new SmartTouchFormBuilder_Entries_Detail();
	}
	public function include_forms_list() {
		global $forms_list;
		// Load the Forms List class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-forms-list.php' );
		$forms_list = new SmartTouchFormBuilder_Forms_List();
	}
	/**
	 * Add Settings link to Plugins page
	 *
	 * @since 1.8
	 * @return $links array Links to add to plugin name
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) )
			$links[] = '<a href="admin.php?page=smart-touch-form-builder">' . __( 'Settings' , 'smart-touch-form-builder') . '</a>';
		return $links;
	}
	/**
	 * Adds the media button image
	 *
	 * @since 2.3
	 */
	public function add_media_button(){
		if ( current_user_can( 'manage_options' ) ) :
			?>
		<a href="<?php echo add_query_arg( array( 'action' => 'smart_touch_form_builder_media_button', 'width' => '450' ), admin_url( 'admin-ajax.php' ) ); ?>" class="button add_media thickbox" title="Add Smart Touch Form Builder form">
			<span class="dashicons dashicons-feedback" style="color:#888; display: inline-block; width: 18px; height: 18px; vertical-align: text-top; margin: 0 4px 0 0;"></span>
			<?php _e( 'Add Form', 'smart-touch-form-builder' ); ?>
		</a>
		<?php
		endif;
	}
	/**
	 * Adds the dashboard widget
	 *
	 * @since 2.7
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget( 'sti-dashboard', __( 'Recent Smart Touch Form Builder Entries', 'smart-touch-form-builder' ), array( &$this, 'dashboard_widget' ), array( &$this, 'dashboard_widget_control' ) );
	}
	/**
	 * Displays the dashboard widget content
	 *
	 * @since 2.7
	 */
	public function dashboard_widget() {
		global $wpdb;
		// Get the date/time format that is saved in the options table
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$widgets = get_option( 'sti_dashboard_widget_options' );
		$total_items = isset( $widgets['sti_dashboard_recent_entries'] ) && isset( $widgets['sti_dashboard_recent_entries']['items'] ) ? absint( $widgets['sti_dashboard_recent_entries']['items'] ) : 5;
		$forms = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->form_table_name}" );
		if ( !$forms ) :
			echo sprintf(
				'<p>%1$s <a href="%2$s">%3$s</a></p>',
				__( 'You currently have no forms.', 'smart-touch-form-builder' ),
				esc_url( admin_url( 'admin.php?page=sti-add-new' ) ),
				__( 'Get started!', 'smart-touch-form-builder' )
				);
		return;
		endif;
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT forms.form_title, entries.entries_id, entries.form_id, entries.sender_name, entries.sender_email, entries.date_submitted FROM $this->form_table_name AS forms INNER JOIN $this->entries_table_name AS entries ON entries.form_id = forms.form_id ORDER BY entries.date_submitted DESC LIMIT %d", $total_items ) );
		if ( !$entries ) :
			echo sprintf( '<p>%1$s</p>', __( 'You currently do not have any entries.', 'smart-touch-form-builder' ) );
		else :
			$content = '';
		foreach ( $entries as $entry ) :
			$content .= sprintf(
				'<li><a href="%1$s">%4$s</a> via <a href="%2$s">%5$s</a> <span class="rss-date">%6$s</span><cite>%3$s</cite></li>',
				esc_url( add_query_arg( array( 'action' => 'view', 'entry' => absint( $entry->entries_id ) ), admin_url( 'admin.php?page=sti-entries' ) ) ),
				esc_url( add_query_arg( 'form-filter', absint( $entry->form_id ), admin_url( 'admin.php?page=sti-entries' ) ) ),
				esc_html( $entry->sender_name ),
				esc_html( $entry->sender_email ),
				esc_html( $entry->form_title ),
				date( "$date_format $time_format", strtotime( $entry->date_submitted ) )
				);
		endforeach;
		echo "<div class='rss-widget'><ul>$content</ul></div>";
		endif;
	}
	/**
	 * Displays the dashboard widget form control
	 *
	 * @since 2.7
	 */
	public function dashboard_widget_control() {
		if ( !$widget_options = get_option( 'sti_dashboard_widget_options' ) )
			$widget_options = array();
		if ( !isset( $widget_options['sti_dashboard_recent_entries'] ) )
		$widget_options['sti_dashboard_recent_entries'] = array();
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['sti-widget-recent-entries'] ) ) {
			$number = absint( $_POST['sti-widget-recent-entries']['items'] );
			$widget_options['sti_dashboard_recent_entries']['items'] = $number;
			update_option( 'sti_dashboard_widget_options', $widget_options );
		}
		$number = isset( $widget_options['sti_dashboard_recent_entries']['items'] ) ? (int) $widget_options['sti_dashboard_recent_entries']['items'] : '';
		echo sprintf(
			'<p>
			<label for="comments-number">%1$s</label>
			<input id="comments-number" name="sti-widget-recent-entries[items]" type="text" value="%2$d" size="3" />
		</p>',
		__( 'Number of entries to show:', 'smart-touch-form-builder' ),
		$number
		);
	}
	/**
	 * Register contextual help. This is for the Help tab dropdown
	 *
	 * @since 1.0
	 */
	public function help(){
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id' => 'sti-help-tab-getting-started',
			'title' => 'Getting Started',
			'content' => '<ul>
			<li>Click on the + tab, give your form a name and click Create Form.</li>
			<li>Select form fields from the box on the left and click a field to add it to your form.</li>
			<li>Edit the information for each form field by clicking on the down arrow.</li>
			<li>Drag and drop the elements to put them in order.</li>
			<li>Click Save Form to save your changes.</li>
		</ul>'
		) );
		$screen->add_help_tab( array(
			'id' => 'sti-help-tab-item-config',
			'title' => 'Form Item Configuration',
			'content' => "<ul>
			<li><em>Name</em> will change the display name of your form input.</li>
			<li><em>Description</em> will be displayed below the associated input.</li>
			<li><em>Validation</em> allows you to select from several of jQuery's Form Validation methods for text inputs. For more about the types of validation, read the <em>Validation</em> section below.</li>
			<li><em>Required</em> is either Yes or No. Selecting 'Yes' will make the associated input a required field and the form will not submit until the user fills this field out correctly.</li>
			<li><em>Options</em> will only be active for Radio and Checkboxes.  This field contols how many options are available for the associated input.</li>
			<li><em>Size</em> controls the width of Text, Textarea, Select, and Date Picker input fields.  The default is set to Medium but if you need a longer text input, select Large.</li>
			<li><em>CSS Classes</em> allow you to add custom CSS to a field.  This option allows you to fine tune the look of the form.</li>
		</ul>"
		) );
$screen->add_help_tab( array(
	'id' => 'sti-help-tab-validation',
	'title' => 'Validation',
	'content' => "<p>SmartTouch Form Builder uses the <a href='http://docs.jquery.com/Plugins/Validation/Validator'>jQuery Form Validation plugin</a> to perform clientside form validation.</p>
	<ul>
		<li><em>Email</em>: makes the element require a valid email.</li>
		<li><em>URL</em>: makes the element require a valid url.</li>
		<li><em>Date</em>: makes the element require a date. <a href='http://docs.jquery.com/Plugins/Validation/Methods/date'>Refer to documentation for various accepted formats</a>.
			<li><em>Number</em>: makes the element require a decimal number.</li>
			<li><em>Digits</em>: makes the element require digits only.</li>
			<li><em>Phone</em>: makes the element require a US or International phone number. Most formats are accepted.</li>
			<li><em>Time</em>: choose either 12- or 24-hour time format (NOTE: only available with the Time field).</li>
		</ul>"
		) );
$screen->add_help_tab( array(
	'id' => 'sti-help-tab-confirmation',
	'title' => 'Confirmation',
	'content' => "<p>Each form allows you to customize the confirmation by selecing either a Text Message, a WordPress Page, or to Redirect to a URL.</p>
	<ul>
		<li><em>Text</em> allows you to enter a custom formatted message that will be displayed on the page after your form is submitted. HTML is allowed here.</li>
		<li><em>Page</em> displays a dropdown of all WordPress Pages you have created. Select one to redirect the user to that page after your form is submitted.</li>
		<li><em>Redirect</em> will only accept URLs and can be used to send the user to a different site completely, if you choose.</li>
	</ul>"
	) );
$screen->add_help_tab( array(
	'id' => 'sti-help-tab-notification',
	'title' => 'Notification',
	'content' => "<p> email  has been successfully submitted.</p>
	"
	) );
$screen->add_help_tab( array(
	'id' => 'sti-help-tab-tips',
	'title' => 'Tips',
	'content' => "<ul>
	<li>Fieldsets, a way to group form fields, are an essential piece of this plugin's HTML. As such, at least one fieldset is required and must be first in the order. Subsequent fieldsets may be placed wherever you would like to start your next grouping of fields.</li>
	<li>Security verification is automatically included on very form. It's a simple logic question and should keep out most, if not all, spam bots.</li>
	<li>There is a hidden spam field, known as a honey pot, that should also help deter potential abusers of your form.</li>
	<li>Nesting is allowed underneath fieldsets and sections.  Sections can be nested underneath fieldsets.  Nesting is not required, however, it does make reorganizing easier.</li>
</ul>"
) );
}
	/**
	 * Adds the Screen Options tab to the Entries screen
	 *
	 * @since 1.0
	 */
	public function screen_options(){
		$screen = get_current_screen();
		$page_main		= $this->_admin_pages[ 'sti' ];
		$page_entries 	= $this->_admin_pages[ 'sti-entries' ];
		switch( $screen->id ) {
			case $page_entries :
			add_screen_option( 'per_page', array(
				'label'		=> __( 'Entries per page', 'smart-touch-form-builder' ),
				'default'	=> 20,
				'option'	=> 'sti_entries_per_page'
				) );
			break;
			case $page_main :
			if ( isset( $_REQUEST['form'] ) ) :
				add_screen_option( 'layout_columns', array(
					'max'		=> 2,
					'default'	=> 2
					) );
			else :
				add_screen_option( 'per_page', array(
					'label'		=> __( 'Forms per page', 'smart-touch-form-builder' ),
					'default'	=> 20,
					'option'	=> 'sti_forms_per_page'
					) );
			endif;
			break;
		}
	}
	/**
	 * Saves the Screen Options
	 *
	 * @since 1.0
	 */
	public function save_screen_options( $status, $option, $value ){
		if ( $option == 'sti_entries_per_page' )
			return $value;
		elseif ( $option == 'sti_forms_per_page' )
			return $value;
	}
	/**
	 * Add meta boxes to form builder screen
	 *
	 * @since 1.8
	 */
	public function add_meta_boxes() {
		global $current_screen;
		$page_main = $this->_admin_pages[ 'sti' ];
		if ( $current_screen->id == $page_main && isset( $_REQUEST['form'] ) ) {
			add_meta_box( 'sti_form_items_meta_box', __( 'Custom Form Items', 'smart-touch-form-builder' ), array( &$this, 'meta_box_form_items' ), $page_main, 'side', 'high' );
			
		}
	}
	/**
	 * Output for Form Items meta box
	 *
	 * @since 1.8
	 */
	public function meta_box_form_items() {
		global $wpdb;
		$form_register= $wpdb->prefix . 'form_register';
		$mytoken = $wpdb->get_results( "SELECT * FROM $form_register where status =1" );
		$username=$mytoken[0]->username;
		$password=$mytoken[0]->password;
		$apikey=$mytoken[0]->varification_key;
		$acname=$mytoken[0]->account_name;
		/**************  tomorrow start here    ****************/   
		/*  get token key from account  */
		$url = 'https://services.smarttouchinteractive.com/Login';
		$method = 'POST';
    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			);
		$data = json_encode(array(
			'UserName' => $username,
			'Password' => $password,
			'ApiKey' =>   $apikey
			));
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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
		/*  token key finish here   */
		/*   get account  Id  from rest */
           $acname=str_replace ( ' ','%20',$acname);
	       $idurl = "https://services.smarttouchinteractive.com/api/Accounts?accountName=$acname";
           
		   $method = 'GET';
		    # headers and data (this is API dependent, some uses XML)
		$idheaders = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Bearer '.$tokenkey.''
			);
		$achandle = curl_init();
		curl_setopt($achandle, CURLOPT_URL, $idurl);
		curl_setopt($achandle, CURLOPT_HTTPHEADER, $idheaders);
		curl_setopt($achandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($achandle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($achandle, CURLOPT_SSL_VERIFYPEER, false);
		switch($method) {
			case 'GET':
			break;
			case 'POST':
			curl_setopt($achandle, CURLOPT_POST, true);
			curl_setopt($achandle, CURLOPT_POSTFIELDS, $data);
			break;
			case 'PUT': 
			curl_setopt($achandle, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($achandle, CURLOPT_POSTFIELDS, $data);
			break;
			case 'DELETE':
			curl_setopt($achandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		}
		$allids = curl_exec($achandle);
         
		$allids=json_decode($allids);       
        $accountid=$allids->AccountViewModel->AccountID;
       
           
		
		/* finish account Id  here */
		/* get fieldschema fields here */        
		$bearerurl = 'https://services.smarttouchinteractive.com/getschema';
		$method = 'GET';
		    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Bearer '.$tokenkey.''
			);
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $bearerurl);
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
		$Jsondata=json_decode($response);
		$Jsondata=$Jsondata->Schema;
		$Jsondata=json_decode($Jsondata);
		$cdata=$Jsondata->Properties; 
		$customFields=$Jsondata->ObjectTypes;
		$no_of_field=count($cdata);    
		$valueurl_contact = "https://services.smarttouchinteractive.com/DropDownValueFields?accountId=$accountid";
								$method = 'GET';
		  
								$fieldvalue = array(
									'Accept: application/json',
									'Content-Type: application/json',
									'Authorization: Bearer '.$tokenkey.''
									);
								$handlevalue = curl_init();
								curl_setopt($handlevalue, CURLOPT_URL, $valueurl_contact);
								curl_setopt($handlevalue, CURLOPT_HTTPHEADER, $fieldvalue);
								curl_setopt($handlevalue, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($handlevalue, CURLOPT_SSL_VERIFYHOST, false);
								curl_setopt($handlevalue, CURLOPT_SSL_VERIFYPEER, false);
								switch($method) {
									case 'GET':
									break;
									case 'POST':
									curl_setopt($handlevalue, CURLOPT_POST, true);
									curl_setopt($handlevalue, CURLOPT_POSTFIELDS, $datavalue);
									break;
									case 'PUT': 
									curl_setopt($handlevalue, CURLOPT_CUSTOMREQUEST, 'PUT');
									curl_setopt($handlevalue, CURLOPT_POSTFIELDS, $datavalue);
									break;
									case 'DELETE':
									curl_setopt($handlevalue, CURLOPT_CUSTOMREQUEST, 'DELETE');
									break;
								}
								$optionvalue = curl_exec($handlevalue);
								$optionvalue=json_decode($optionvalue);
                                $optionvalue=$optionvalue->DropdownValuesViewModel;
                                
                                $optvalue_lenth=count($optionvalue);
								if($optvalue_lenth==0){ echo '<h3 style="background-color: #f2dede;border-color: #ebccd1;color: #a94442; padding:3px;">Please Contact NextGen Support Team to Configure Your CRM Account </h3>'; 
								 die; }								
$form_id =$_GET['form'];
if($form_id){
$formdata=$wpdb->get_results( $wpdb->prepare( "SELECT field_name FROM $this->field_table_name WHERE form_id = %d ", $form_id ) );
$flen=count($formdata);
for($fl=0;$fl<=$flen;$fl++)
{
  $formvalue .=$formdata[$fl]->field_name.'|';
}
$formd=substr($formvalue,0,-2);
}else{
	$formd='FirstName|LastName|EmailId|Phones|SelectedLeadSource';
}
$formarray=explode('|',$formd);
?>
		<div class="taxonomydiv">
			<p>  
				<ul class="posttype-tabs add-menu-item-tabs" id="sti-field-tabs">
					<li class="tabs"><a href="#standard-fields1" class="nav-tab-link sti-field-types"><?php _e( 'Contact Fields' , 'smart-touch-form-builder'); ?></a></li>
					<li class="">
						<span class="sti-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder' ); ?>">(?)</span>
					<a href="#standard-fields2" class="nav-tab-link sti-field-types"><?php _e( 'Custom Fields' , 'smart-touch-form-builder'); ?></a></li>
					</ul>
					<div id="get-data"> 
						<span class="sti-tooltip" rel="Set the field labels for this form to be aligned either on top, to the left, or to the right. By default, all labels are aligned on top of the inputs." title="About Label Alignment">(?)</span>
						<?php
						echo'<div id="standard-fields1" class="tabs-panel tabs-panel-active">  <ul class="sti-fields-col-1" style="width:85% !important">';
						for($i=0;$i<=$no_of_field;$i++){
							$ftype=''; $fcode=''; $ftitle='';   $contactfieldid='';  $finalcontactvalue='';  
						    $ftitle=$cdata[$i]->Title;
							$ftype=$cdata[$i]->InputType;          
							$fcode=$cdata[$i]->JsonPropertyName;
							$IsArray=$cdata[$i]->IsArray;
							$ObjectType=$cdata[$i]->ObjectType;
         
                          if(! in_array($ftitle,$formarray)){
							if($ftype=='Dropdown'){  $contactfieldid=$cdata[$i]->DropdownId;  }else{ $contactfieldid=''; }
                          //AND $IsArray==''
                            if($ftitle=='Communities'){ $IsArray=''; }                          
							if($contactfieldid>0 AND $ftype=='Dropdown' AND $IsArray==''){							
                                $dropvalue='';
                                 
                                for($np=0;$np<$optvalue_lenth;$np++){
                                  
                                 $dropvalue=$optionvalue[$np]->DropdownID;
                                 if($dropvalue==$contactfieldid){
                                 	$dropvaluelist=$optionvalue[$np]->DropdownValuesList;
                                    $dropvalue_listcount=count($dropvaluelist);
                                     for($dvl=0;$dvl<$dropvalue_listcount;$dvl++){
                                     $finalcontactvalue .=$dropvaluelist[$dvl]->DropdownValueID."!".$dropvaluelist[$dvl]->DropdownValue.",";
                                     	
                                     }
                                 }
                                }
                              
                              
							} 
							if($ftype=='Dropdown' OR $ftype=='Number' OR $ftype=='Boolean'){ $cvalid='number';   }else{ $cvalid='';}     
							$finalcontactvalue=substr($finalcontactvalue,0,-1);
							
                            if($ftype=='Boolean'){ $true='1!True'; $false='0!False';  $finalcontactvalue=$true.",".$false;  }
							if($ObjectType==''){  $IsArray=0;  
								$allcontactdata=$fcode.'|'.$IsArray.'|'.''.'|'.$finalcontactvalue.'|'.$cvalid; // echo $contactfieldid;
								if($ftype=='Text'){
									echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-text" fieldkey="'.$allcontactdata.'" fieldtype="Text"><b></b>'.$cdata[$i]->Title.'</a></li>';
								}
								if($ftype=='Dropdown'){
									echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-select" fieldkey="'.$allcontactdata.'" fieldtype="Select"><b></b>'.$cdata[$i]->Title.'</a></li>';
								}
								if($ftype=='Boolean'){
									echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-radio" fieldkey="'.$allcontactdata.'" fieldtype="Radio"><b></b>'.$cdata[$i]->Title.'</a></li>';
								}
								if($ftype=='Number'){
									echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-text" fieldkey="'.$allcontactdata.'" fieldtype="Text"><b></b>'.$cdata[$i]->Title.'</a></li>';
								}
							}  else{
								if($ObjectType!='')
								{
									$no_customFields=count($customFields);
                 // print_r($customFields);
									for($cf=0;$cf<$no_customFields;$cf++){
                // echo  $customData=$customFields[$cf]->Properties[0]->Title; 
										$customData=$customFields[$cf]->ObjectType;
                      // print_r($customData);
										if($customData==$ObjectType){  
											$innerData=$customFields[$cf]->Properties;   
											$innerData_lenth=count($innerData);
											for($indl=0;$indl<$innerData_lenth;$indl++){
												$inTitle=''; $inInputType=''; $inJsonPropertyName=''; $inIsArray=''; $inObjectType='';  $incontactfieldid='';
												$inallcontactdata='';  $infinalcontactvalue='';  $incontactfieldid='';
												$inTitle=$innerData[$indl]->Title;
												$inInputType=$innerData[$indl]->InputType;
												$inJsonPropertyName=$innerData[$indl]->JsonPropertyName;
												$inIsArray=$innerData[$indl]->IsArray;
												$inObjectType=$innerData[$indl]->ObjectType;
												if($inInputType=='Dropdown'){  $incontactfieldid=$innerData[$indl]->DropdownId;  }else{ $incontactfieldid=''; }
					                            //AND $IsArray==''
					                            if($inTitle=='Number'){ $inTitle='Lead Source'; $incontactfieldid='5'; $inInputType='Dropdown'; $inJsonPropertyName='SelectedLeadSource';   }
												if($incontactfieldid>0 AND $inInputType=='Dropdown' ){
					                               // print_r($optionvalue);
					                                $optvalue_lenth=count($optionvalue);
					                                $indropvalue='';
					                                for($np=0;$np<$optvalue_lenth;$np++){
					                                  
					                                 $indropvalue=$optionvalue[$np]->DropdownID;
					                                  
					                                 if($indropvalue==$incontactfieldid){
					                                 	$indropvaluelist=$optionvalue[$np]->DropdownValuesList;
					                                    $indropvalue_listcount=count($indropvaluelist);
														$infinalcontactvalue='0!--Select--,';
					                                     for($indvl=0;$indvl<$indropvalue_listcount;$indvl++){
					                                     $infinalcontactvalue .=$indropvaluelist[$indvl]->DropdownValueID."!".$indropvaluelist[$indvl]->DropdownValue.",";
					                                     	
					                                     }
					                                 }
					                                }
					                                                           
												} 
		   //echo $finalcontactvalue; 
                                               if(! in_array($inTitle,$formarray)){
												if($inInputType=='Dropdown' OR $inInputType=='Number' OR $inInputType=='Boolean'){ $icvalid='number';   }else{ $icvalid='';}     
												 $infinalcontactvalue=substr($infinalcontactvalue,0,-1);
												 if($inInputType=='Boolean'){ $true='1!True'; $false='0!False';  $infinalcontactvalue=$true.",".$false;  }
												$inallcontactdata=$inJsonPropertyName.'|'.$inIsArray.'|'.''.'|'.$infinalcontactvalue.'|'.$icvalid; 
                                                  
												if($inInputType=='Text'){
													echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-text" fieldkey="'.$inallcontactdata.'" fieldtype="Text"><b></b>'.$inTitle.'</a></li>';
												}
												if($inInputType=='Dropdown'){
													echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-select" fieldkey="'.$inallcontactdata.'" fieldtype="Select"><b></b>'.$inTitle.'</a></li>';
												}
												if($inInputType=='Boolean'){
													echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-radio" fieldkey="'.$inallcontactdata.'" fieldtype="Radio"><b></b>'.$inTitle.'</a></li>';
												}
												if($inInputType=='Number'){
													echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-text" fieldkey="'.$inallcontactdata.'" fieldtype="Text"><b></b>'.$inTitle.'</a></li>';
												} 
											  }                      
											}
										}
									}
								}
							}         
						}
                    }     
						echo'</ul><div class="clear"></div></div>';
						echo'<div id="standard-fields2" class="tabs-panel tabs-panel-inactive">  <ul class="sti-fields-col-1" style="width:85% !important">';
						/* get custom fields here */
						$bearerurl = 'https://services.smarttouchinteractive.com/getallfields';
						$method = 'GET';
		                # headers and data (this is API dependent, some uses XML)
						$headers = array(
							'Accept: application/json',
							'Content-Type: application/json',
							'Authorization: Bearer '.$tokenkey.''
							);
						$handle = curl_init();
						curl_setopt($handle, CURLOPT_URL, $bearerurl);
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
						$cdata=json_decode($response);
						$no_of_field=count($cdata);  
					$valueurl = "https://services.smarttouchinteractive.com/customfieldsvalueoptions?accountId=$accountid";
								$method = 'GET';
		    # headers and data (this is API dependent, some uses XML)
								$fieldvalue = array(
									'Accept: application/json',
									'Content-Type: application/json',
									'Authorization: Bearer '.$tokenkey.''
									);
								$handlevalue = curl_init();
								curl_setopt($handlevalue, CURLOPT_URL, $valueurl);
								curl_setopt($handlevalue, CURLOPT_HTTPHEADER, $fieldvalue);
								curl_setopt($handlevalue, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($handlevalue, CURLOPT_SSL_VERIFYHOST, false);
								curl_setopt($handlevalue, CURLOPT_SSL_VERIFYPEER, false);
								switch($method) {
									case 'GET':
									break;
									case 'POST':
									curl_setopt($handlevalue, CURLOPT_POST, true);
									curl_setopt($handlevalue, CURLOPT_POSTFIELDS, $datavalue);
									break;
									case 'PUT': 
									curl_setopt($handlevalue, CURLOPT_CUSTOMREQUEST, 'PUT');
									curl_setopt($handlevalue, CURLOPT_POSTFIELDS, $datavalue);
									break;
									case 'DELETE':
									curl_setopt($handlevalue, CURLOPT_CUSTOMREQUEST, 'DELETE');
									break;
								}
								$optionvalue = curl_exec($handlevalue);
								$optionvalue=json_decode($optionvalue);
						for($j=0;$j<=$no_of_field;$j++){
							$ftype=''; $wtype=''; $fieldid=''; $fieldinputtype=''; $alldata='';  $finalvalue=''; $fvalid='';
							$ftype=$cdata[$j]->FieldInputTypeId;
							$wtype=$cdata[$j]->IsCustomField;
							/************ custom fields values start here *************/
							$fieldid=$cdata[$j]->FieldId;
							$fieldinputtype=$cdata[$j]->FieldInputTypeId;
							if($wtype=='1'){
								
								$optvaluelenth=count($optionvalue);
							    if($ftype=='11'){ $finalvalue='0!--Select--,'; }
								for($ol=0;$ol<$optvaluelenth;$ol++){
									$optvalue=$optionvalue[$ol]->CustomFieldId;
									if($optvalue==$fieldid){ 

									$finalvalue .=$optionvalue[$ol]->CustomFieldValueOptionId."!".$optionvalue[$ol]->Value.","; }
								}
							} 
							if($ftype=='5' OR $ftype=='6' OR $ftype=='11'){ $fvalid='number';   }else{ $fvalid='';}     
							$finalvalue=substr($finalvalue,0,-1);
							$alldata='cf'.$fieldid.'|'.'1'.'|'.'CustomFields'.'|'.$finalvalue.'|'.$fvalid;
							if($ftype=='1' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-checkbox"  fieldkey="'.$alldata.'" fieldtype="Checkbox"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='3' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-text" fieldkey="'.$alldata.'" fieldtype="Text"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='5' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-digits" fieldkey="'.$alldata.'" fieldtype="Number"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='6' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-radio" fieldkey="'.$alldata.'" fieldtype="Radio"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='8' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-text" fieldkey="'.$alldata.'" fieldtype="Text"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='10' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-url" fieldkey="'.$alldata.'" fieldtype="URL"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='11' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-select" fieldkey="'.$alldata.'" fieldtype="Select"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='12' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-selectmul" fieldkey="'.$alldata.'" fieldtype="Selectmul"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='13' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-datepicker" fieldkey="'.$alldata.'" fieldtype="Date"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
							if($ftype=='14' AND $wtype=='1'){
								echo'<li><a href="#" class="sti-draggable-form-items" id="form-element-textarea" fieldkey="'.$alldata.'" fieldtype="Textarea"><b></b>'.$cdata[$j]->Title.'</a></li>';
							}
						}
						echo'</ul><div class="clear"></div></div>';  
						?>
						
					</div>  
					<!-- #standard-fields -->
				</div> <!-- .taxonomydiv -->
				<div class="clear"></div>
                
				<?php
			}
	/**
	 * Output for the Display Forms meta box
	 *
	 * @since 1.8
	 */
	public function meta_box_display_forms() {
		?>
		<p><?php _e( 'Add forms to your Posts or Pages by locating the <strong>Add Form</strong> button in the area above your post/page editor.', 'smart-touch-form-builder' ); ?></p>
		<p><?php _e( 'You may also manually insert the shortcode into a post/page.', 'smart-touch-form-builder' ); ?></p>
		<p>
			<?php _e( 'Shortcode', 'smart-touch-form-builder' ); ?>
			<input value="[sti id='<?php echo (int) $_REQUEST['form']; ?>']" readonly />
		</p>
		<?php
	}
	/**
	 * Check database version and run SQL install, if needed
	 *
	 * @since 2.1
	 */
	public function update_db_check() {
        // Add a database version to help with upgrades and run SQL install
        if ( !get_option( 'sti_db_version' ) ) {
            update_option( 'sti_db_version', $this->sti_db_version );
            $this->install_db();
        }
        // If database version doesn't match, update and run SQL install
        if ( version_compare( get_option( 'sti_db_version' ), $this->sti_db_version, '<' ) ) {
			$this->update_db();
            update_option( 'sti_db_version', $this->sti_db_version );
            
        }
    }
    /**
     * Install database tables
     *
     * @since 1.0
     */
       static function install_db() {
        global $wpdb;
        $field_table_name     = $wpdb->prefix . 'smarttouch_form_builder_fields';
        $form_table_name      = $wpdb->prefix . 'smarttouch_form_builder_forms';
        $entries_table_name   = $wpdb->prefix . 'smarttouch_form_builder_entries';
        $form_register        = $wpdb->prefix . 'form_register';
        // Explicitly set the character set and collation when creating the tables
        $charset = ( defined( 'DB_CHARSET' && '' !== DB_CHARSET ) ) ? DB_CHARSET : 'utf8';
        $collate = ( defined( 'DB_COLLATE' && '' !== DB_COLLATE ) ) ? DB_COLLATE : 'utf8_general_ci';
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $field_sql = "CREATE TABLE $field_table_name (
            field_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            form_id BIGINT(20) NOT NULL,
            field_key VARCHAR(255) NOT NULL,
            field_type VARCHAR(25) NOT NULL,
            field_options TEXT,
            field_description TEXT,
            field_name TEXT NOT NULL,
            field_sequence BIGINT(20) DEFAULT '0',
            field_parent BIGINT(20) DEFAULT '0',
            field_validation VARCHAR(25),
            field_required VARCHAR(25),
            field_size VARCHAR(25) DEFAULT 'medium',
            field_css VARCHAR(255),
            field_layout VARCHAR(255),
            field_default TEXT,
            iscustom INT(20),
            fieldid TEXT,
            typeid VARCHAR(255),
            PRIMARY KEY  (field_id)
            ) DEFAULT CHARACTER SET $charset COLLATE $collate;";
$form_sql = "CREATE TABLE $form_table_name (
    form_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    form_key TINYTEXT NOT NULL,
    form_title TEXT NOT NULL,
    st_form_id TEXT NOT NULL,
    form_email_subject TEXT,
    form_email_to TEXT,
    form_email_from VARCHAR(255),
    form_email_from_name VARCHAR(255),
    form_email_from_override VARCHAR(255),
    form_email_from_name_override VARCHAR(255),
    form_success_type VARCHAR(25) DEFAULT 'text',
    form_success_message TEXT,
    form_notification_setting VARCHAR(25),
    form_notification_email_name VARCHAR(255),
    form_notification_email_from VARCHAR(255),
    form_notification_email VARCHAR(25),
    form_notification_subject VARCHAR(255),
    form_notification_message TEXT,
    form_notification_entry VARCHAR(25),
    form_label_alignment VARCHAR(25),
    PRIMARY KEY  (form_id)
    ) DEFAULT CHARACTER SET $charset COLLATE $collate;";
$entries_sql = "CREATE TABLE $entries_table_name (
    entries_id BIGINT(20) NOT NULL AUTO_INCREMENT,
    form_id BIGINT(20) NOT NULL,
    data LONGTEXT NOT NULL,
    subject TEXT,
    sender_name VARCHAR(255),
    sender_email VARCHAR(255),
    emails_to TEXT,
    date_submitted DATETIME,
    ip_address VARCHAR(25),
    entry_approved VARCHAR(20) DEFAULT '1',
    PRIMARY KEY  (entries_id)
    ) DEFAULT CHARACTER SET $charset COLLATE $collate;";
$form_regis = "CREATE TABLE IF NOT EXISTS $form_register (
    form_d int(55) NOT NULL,
    username varchar(555) NOT NULL,
    password varchar(555) NOT NULL,
    varification_key text NOT NULL,
    account_name varchar(555) NOT NULL,
    account_id text NOT NULL,
    resturl text NOT NULL,
    token text NOT NULL,
    status int(11) NOT NULL
    ) DEFAULT CHARACTER SET $charset COLLATE $collate;";
        // Create or Update database tables


dbDelta( $field_sql );
dbDelta( $form_sql );
dbDelta( $entries_sql );
dbDelta( $form_regis );

}



static function update_db() {

                global $wpdb;
                $form_register = $wpdb->prefix . 'form_register';
                 $wpdb->query("ALTER TABLE $form_register ADD `account_id` VARCHAR(55) NOT NULL AFTER `account_name`");
                
 /* Add account id for our existing account user in form_register  script */

        $mytoken = $wpdb->get_results( "SELECT * FROM $form_register where status =1" );
    if(is_array($mytoken)){
        $username=$mytoken[0]->username;
        $password=$mytoken[0]->password;
        $apikey=$mytoken[0]->varification_key;
        $acname=$mytoken[0]->account_name;
        

        $url = 'https://services.smarttouchinteractive.com/login';
        $method = 'POST';
    # headers and data (this is API dependent, some uses XML)
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            );
        $data = json_encode(array(
            'UserName' => $username,
            'Password' => $password,
            'ApiKey' => $apikey
            ));
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
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
        $token=$response['access_token'];
        $accountid='';
            $idurl = 'https://services.smarttouchinteractive.com/accounts';
            $method = 'GET';
         # headers and data (this is API dependent, some uses XML)
            $idheaders = array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer '.$token.''
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
       if($accountid){
                $form_d =$mytoken[0]->form_d;
                $table = $wpdb->prefix . 'form_register';
                $data_array = array('account_id'=>$accountid);
                $where = array('form_d' => $form_d);
                $wpdb->update( $table, $data_array, $where );
          }
        }
     /*  Script finish to add account_id for existing account */
    }

	/**
	 * Queue plugin scripts for sorting form fields
	 *
	 * @since 1.0
	 */
                   
	public function admin_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'postbox' );	
		wp_enqueue_script( 'jquery-form-validation', plugins_url( '/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.9.0', true );
		wp_enqueue_script( 'sti-admin', plugins_url( "/js/sti-admin$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '20140412', true ); 
		wp_enqueue_script( 'nested-sortable', plugins_url( "/js/jquery.ui.nestedSortable$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-ui-sortable' ), '1.3.5', true );
		wp_enqueue_style( 'smart-touch-form-builder-style', plugins_url( "/css/smart-touch-form-builder-admin$this->load_dev_files.css", __FILE__ ), array(), '20140412' );
		wp_localize_script( 'sti-admin', 'stiAdminPages', array( 'sti_pages' => $this->_admin_pages ) );
		wp_deregister_script( 'jquery' ); // deregisters the default WordPress jQuery  
		$link = 'http://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js';
		$try_url = @fopen($link,'r');
        if( $try_url !== false ) {
            // If it's available, get it registered
            wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js');
		}  else { wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js'); }
		wp_enqueue_script('jquery');
	}
	/**
	 * Queue form validation scripts
	 *
	 * Scripts loaded in form-output.php, when field is present:
	 *	jQuery UI date picker
	 *	CKEditor
	 *
	 * @since 1.0
	 */
	public function scripts() {
		// Make sure scripts are only added once via shortcode
		$this->add_scripts = true;
       
		wp_register_script( 'jquery-form-validation', plugins_url( '/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.9.0', true );
		wp_register_script( 'smart-touch-form-builder-validation', plugins_url( "/js/sti-validation$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '20140412', true );
		wp_register_script( 'smart-touch-form-builder-metadata', plugins_url( '/js/jquery.metadata.js', __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '2.0', true );
		//wp_register_script( 'sti-ckeditor', plugins_url( '/js/ckeditor/ckeditor.js', __FILE__ ), array( 'jquery' ), '4.1', true );
        
		wp_enqueue_script( 'jquery-form-validation' );
		wp_enqueue_script( 'smart-touch-form-builder-validation' );
		wp_enqueue_script( 'smart-touch-form-builder-metadata' );
		$locale = get_locale();
		$translations = array(
        	'cs_CS',	// Czech
        	'de_DE',	// German
        	'el_GR',	// Greek
        	'en_US',	// English (US)
        	'en_AU',	// English (AU)
        	'en_GB',	// English (GB)
        	'es_ES',	// Spanish
        	'fr_FR',	// French
        	'he_IL', 	// Hebrew
        	'hu_HU',	// Hungarian
        	'id_ID',	// Indonseian
        	'it_IT',	// Italian
        	'ja_JP',	// Japanese
        	'ko_KR',	// Korean
        	'nl_NL',	// Dutch
        	'pl_PL',	// Polish
        	'pt_BR',	// Portuguese (Brazilian)
        	'pt_PT',	// Portuguese (European)
        	'ro_RO',	// Romanian
        	'ru_RU',	// Russian
        	'sv_SE',	// Swedish
        	'tr_TR', 	// Turkish
        	'zh_CN',	// Chinese
        	'zh_TW',	// Chinese (Taiwan)
        	);
		// Load localized vaidation and datepicker text, if translation files exist
		if ( in_array( $locale, $translations ) ) {
			
		
		wp_register_script( 'sti-validation-i18n', plugins_url( "/js/i18n/validate/messages-$locale.js", __FILE__ ), array( 'jquery-form-validation' ), '1.9.0', true );
		wp_register_script( 'sti-datepicker-i18n', plugins_url( "/js/i18n/datepicker/datepicker-$locale.js", __FILE__ ), array( 'jquery-ui-datepicker' ), '1.0', true );
		wp_register_script( 'mailgun-validator', plugins_url( '/js/mailgun_validator.js', __FILE__ ), true );
		wp_register_script( 'mailgun-page', plugins_url( '/js/mailgun_page.js', __FILE__ ),  true );
	}
        // Otherwise, load English translations
		else {
			wp_register_script( 'sti-validation-i18n', plugins_url( "/js/i18n/validate/messages-en_US.js", __FILE__ ), array( 'jquery-form-validation' ), '1.9.0', true );
			wp_register_script( 'sti-datepicker-i18n', plugins_url( "/js/i18n/datepicker/datepicker-en_US.js", __FILE__ ), array( 'jquery-ui-datepicker' ), '1.0', true );
			wp_enqueue_script( 'sti-validation-i18n' );
		}
	}
	/**
	 * Add form CSS to wp_head
	 *
	 * @since 1.0
	 */
	public function css() {
		$sti_settings = get_option( 'sti-settings' );
		wp_register_style( 'sti-jqueryui-css', apply_filters( 'sti-date-picker-css', plugins_url( '/css/smoothness/jquery-ui-1.10.3.min.css', __FILE__ ) ), array(), '20131203' );
		wp_register_style( 'smart-touch-form-builder-css', apply_filters( 'smart-touch-form-builder-css', plugins_url( "/css/smart-touch-form-builder$this->load_dev_files.css", __FILE__ ) ), array(), '20140412' );
		// Settings - Always load CSS
		if ( isset( $sti_settings['always-load-css'] ) ) {
			wp_enqueue_style( 'smart-touch-form-builder-css' );
			wp_enqueue_style( 'sti-jqueryui-css' );
			return;
		}
		// Settings - Disable CSS
		if ( isset( $sti_settings['disable-css'] ) )
			return;
		// Get active widgets
		$widget = is_active_widget( false, false, 'sti_widget' );
		// If no widget is found, test for shortcode
		if ( empty( $widget ) ) {
			// If WordPress 3.6, use internal function. Otherwise, my own
			if ( function_exists( 'has_shortcode' ) ) {
				global $post;
				// If no post exists, exit
				if ( !$post )
					return;
				if ( !has_shortcode( $post->post_content, 'sti' ) )
					return;
			} elseif ( !$this->has_shortcode( 'sti' ) ) {
				return;
			}
		}
		wp_enqueue_style( 'smart-touch-form-builder-css' );
		wp_enqueue_style( 'sti-jqueryui-css' );
	}


		function csspop(){

		
		$sti_settings = get_option( 'sti-settings' );
		wp_register_style( 'sti-jqueryui-css', apply_filters( 'sti-date-picker-css', plugins_url( '/css/smoothness/jquery-ui-1.10.3.min.css', __FILE__ ) ), array(), '20131203' );
		wp_register_style( 'smart-touch-form-builder-css', apply_filters( 'smart-touch-form-builder-css', plugins_url( "/css/smart-touch-form-builder$this->load_dev_files.css", __FILE__ ) ), array(), '20140412' );
		// Settings - Always load CSS
		if ( isset( $sti_settings['always-load-css'] ) ) {
			wp_enqueue_style( 'smart-touch-form-builder-css' );
			wp_enqueue_style( 'sti-jqueryui-css' );
			return;
		}
		// Settings - Disable CSS
		if ( isset( $sti_settings['disable-css'] ) )
			return;
		// Get active widgets
		$widget = is_active_widget( false, false, 'sti_widget' );
		// If no widget is found, test for shortcode
		if ( empty( $widget ) ) {
			// If WordPress 3.6, use internal function. Otherwise, my own
			if ( function_exists( 'has_shortcode' ) ) {
				global $post;
				// If no post exists, exit
				if ( !$post )
					return;
				if ( !has_shortcode( $post->post_content, 'popup_sti' ) )
					return;
			} elseif ( !$this->has_shortcode( 'popup_sti' ) ) {
				return;
			}

		}
		wp_enqueue_style( 'smart-touch-form-builder-css' );
		wp_enqueue_style( 'sti-jqueryui-css' );

	}

	
	/**
	 * Save new forms on the STI > Add New page
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_add_new_form() {
		global $wpdb;
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;
		if ( 'sti-add-new' !== $_GET['page'] )
			return;
		if ( 'create_form' !== $_REQUEST['action'] )
			return;
		check_admin_referer( 'create_form' );
		$form_title 	= esc_html( $_REQUEST['form_title'] );
		$createdid 		= esc_html( $_REQUEST['createdid'] );
		$form_from_name = esc_html( $_REQUEST['form_email_from_name'] );
		$form_email_from_override = '1';
		$form_email_from_name_override=esc_html( $_REQUEST['form_email_from_name_override'] );
		$form_subject 	= esc_html( $_REQUEST['form_email_subject'] );
		$form_from 		= esc_html( $_REQUEST['form_email_from'] );
		$form_to 		= serialize( $_REQUEST['form_email_to'] );
		
        
        /* api section  */
        $form_register= $wpdb->prefix . 'form_register';
		$mytoken = $wpdb->get_results( "SELECT * FROM $form_register where status =1" );
		$username=$mytoken[0]->username;
		$password=$mytoken[0]->password;
		$apikey=$mytoken[0]->varification_key;
		$acname=$mytoken[0]->account_name;
		$account_id=$mytoken[0]->account_id;
		/*  get token key from account  */
		$url = 'https://services.smarttouchinteractive.com/login';
		$method = 'POST';
    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			);
		$data = json_encode(array(
			'UserName' => $username,
			'Password' => $password,
			'ApiKey' =>   $apikey
			));
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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
        $acname=str_replace ( ' ','%20',$acname);
         $idurl = "https://services.smarttouchinteractive.com/api/accounts?accountName=$acname";
           
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
            
            $accountid=$allids->AccountViewModel->AccountID; 



    /*  Added form id in database as well as in smartouch CRM  */
		$burl = 'https://services.smarttouchinteractive.com/CreateForm';
		$bmethod = 'POST';
        # headers and data (this is API dependent, some uses XML)
		$bheaders = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Bearer '.$tokenkey.''
			);
		$bdata = json_encode(array(
			'AccountId' => $account_id,
			'Name'   => $form_title,
			'CreatedBy' =>   $createdid
			));

		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $burl);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $bheaders);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		switch($bmethod) {
			case 'GET':
			break;
			case 'POST':
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, $bdata);
			break;
			case 'PUT': 
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($handle, CURLOPT_POSTFIELDS, $bdata);
			break;
			case 'DELETE':
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		}
		$bresponse = curl_exec($handle);
		$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$bresponse = json_decode($bresponse, TRUE);
        
      if($bresponse['ViewModel']['Id']){
		/*  IF Form Name is unik save into db   */
		                       $emp_account_id=$bresponse['ViewModel']['Id'];

                                $valueurl_contact = "https://services.smarttouchinteractive.com/DropDownValueFields?accountId=$accountid";
									$method = 'GET';
									$fieldvalue = array(
										'Accept: application/json',
										'Content-Type: application/json',
										'Authorization: Bearer '.$tokenkey.''
										);
									$handlevalue = curl_init();
									curl_setopt($handlevalue, CURLOPT_URL, $valueurl_contact);
									curl_setopt($handlevalue, CURLOPT_HTTPHEADER, $fieldvalue);
									curl_setopt($handlevalue, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($handlevalue, CURLOPT_SSL_VERIFYHOST, false);
									curl_setopt($handlevalue, CURLOPT_SSL_VERIFYPEER, false);
									switch($method) {
										case 'GET':
										break;
										case 'POST':
										curl_setopt($handlevalue, CURLOPT_POST, true);
										curl_setopt($handlevalue, CURLOPT_POSTFIELDS, $datavalue);
										break;
										case 'PUT': 
										curl_setopt($handlevalue, CURLOPT_CUSTOMREQUEST, 'PUT');
										curl_setopt($handlevalue, CURLOPT_POSTFIELDS, $datavalue);
										break;
										case 'DELETE':
										curl_setopt($handlevalue, CURLOPT_CUSTOMREQUEST, 'DELETE');
										break;
									}
									$inoptionvalue1 = curl_exec($handlevalue);
	                                $inoptionvalue=json_decode($inoptionvalue1);


	                          $totalDropDown=count($inoptionvalue->DropdownValuesViewModel); 
	                           if($totalDropDown==0)
							   { echo '<h4 >Please Contact NextGen Support Team to Configure Your CRM Account (LeadSource is not configured.)<input  type="button" class="button button-primary" onClick="history.go(-1)" value="Back"></h4>'; 
							   die; }
							  
							  for($dp=0;$dp<$totalDropDown;$dp++){
	                          	if($inoptionvalue->DropdownValuesViewModel[$dp]->Dropdownname=='Lead Sources'){
								
	                          		$leadValue=$inoptionvalue->DropdownValuesViewModel[$dp]->DropdownValuesList;
	                          		//echo'<pre>';print_r($leadValue);echo'</pre>';
	                          		$leadLen=count($leadValue);
	                          		$StringLeadID='';
	                          		for($dpv=0;$dpv<$leadLen;$dpv++)
	                          		{
	                          				
	                          			 $infinalcontactvalue .=$leadValue[$dpv]->DropdownValueID."!".$leadValue[$dpv]->DropdownValue.",";
	                          			 $StringLeadID .=$leadValue[$dpv]->DropdownValueID."|";
	                          		}
	                          		if($infinalcontactvalue)
	                          		{
	                          		$infinalcontactvalue=substr($infinalcontactvalue,0,-1);
                                    
                                   $lData=explode(',',$infinalcontactvalue);
                              
                                  $lDatalen=count($lData)+1;
                                   for($i=1;$i<$lDatalen;$i++){
                                   	$valueLen=strlen($lData[$i]);
                                    $Lfill .='i:'.$i.';s:'.$valueLen.':'.'"'.$lData[$i].'"'.';';
                                   }
                                   
                                   if($Lfill!=''){   $LeadOptionData='a:'.$lDatalen.':{i:0;s:12:"0!--Select--";'.$Lfill.'}'; }
                                 
                               }
	                      	}
	                   }
							
       // echo $tokenkey=$token[0]->token; 
		// Create the form
        $newdata = array(
			'form_key' 				=> $createdid,
			'form_title' 			=> $form_title,
			'st_form_id'            => $emp_account_id,
			'form_email_from_name'	=> $form_from_name,
			'form_email_from_override' => $form_email_from_override,
			'form_email_from_name_override' => $form_email_from_name_override,
			'form_email_subject'	=> $form_subject,
			'form_email_from'		=> $form_from,
			'form_email_to'			=> $form_to,
			'form_success_message'	=> '<p id="form_success">Your form was successfully submitted. Thank you for contacting us.</p>'
			);

		$wpdb->insert( $this->form_table_name, $newdata );
		// Get form ID to add our first field
		$new_form_selected = $wpdb->insert_id;
         
		// Setup the initial fieldset
		$initial_fieldset = array(
			'form_id' 			=> $wpdb->insert_id,
			'field_key' 		=> 'FirstName',
			'field_type' 		=> 'text',
			'field_name' 		=> 'First Name',
			'field_required'    =>  'yes',
			'field_sequence' 	=> 0
			);
		// Add the first fieldset to get things started
		$wpdb->insert( $this->field_table_name, $initial_fieldset );
		// Setup the initial fieldset
		$initial_fieldset = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'LastName',
			'field_type' 		=> 'text',
			'field_name' 		=> 'Last Name',
			'field_required'    =>  'yes',
			'field_sequence' 	=> 1
			);
		// Add the first fieldset to get things started
		$wpdb->insert( $this->field_table_name, $initial_fieldset );
		// Setup the initial fieldset
		$initial_fieldset = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'Emails',
			'field_type' 		=> 'text',
			'field_name' 		=> 'EmailId',
			'field_validation'  =>  'email',
			'field_required'    =>  'yes',
			'field_sequence' 	=> 2
			);
		// Add the first fieldset to get things started
		$wpdb->insert( $this->field_table_name, $initial_fieldset );
		// Setup the initial fieldset
		$initial_fieldset = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'Phones',
			'field_type' 		=> 'text',
			'field_name' 		=> 'Phone',
			'field_validation'  => 'phone',
			'field_required'    =>  '',
			'field_sequence' 	=> 3
			);
  		// Add the first fieldset to get things started
		$wpdb->insert( $this->field_table_name, $initial_fieldset );
       //echo  $LeadOptionData  die;
		// Setup the initial fieldset
		$StringLeadID="0|".substr($StringLeadID,0,-1);
		$initial_fieldset = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'SelectedLeadSource',
			'field_type' 		=> 'select',
			'field_name' 		=> 'Lead Source',
			'field_options'     => "$LeadOptionData",
			'field_validation'  =>  '',
			'typeid'            =>  'yes',
			'field_sequence' 	=> 4,
			'fieldid'           => "$StringLeadID"
		);   
  		// Add the first fieldset to get things started
        $wpdb->insert( $this->field_table_name, $initial_fieldset );  
		// Make the submit last in the sequence
		$submit = array(
			'form_id' 			=> $new_form_selected,
			'field_key' 		=> 'submit',
			'field_type' 		=> 'submit',
			'field_name' 		=> 'Submit',
			'field_parent' 		=> $verify_fieldset_parent_id,
			'field_sequence' 	=> 5
			);
		// Insert the submit field
		$wpdb->insert( $this->field_table_name, $submit );
		// Redirect to keep the URL clean (use AJAX in the future?)
		wp_redirect( 'admin.php?page=smart-touch-form-builder&action=edit&form=' . $new_form_selected );
	 

	  }else{
	  	$spam=str_replace(' ','-',$bresponse['Exception']['Message']);
        wp_redirect( 'admin.php?page=sti-add-new&msg='.$spam );
	  }
		exit();
	}
	/**
	 * Save the form
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_update_form() {

		global $wpdb;
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;
		if ( 'smart-touch-form-builder' !== $_GET['page'] )
			return;
		if ( 'update_form' !== $_REQUEST['action'] )
			return;
		check_admin_referer( 'sti_update_form' );
		$form_id 						= absint( $_REQUEST['form_id'] );
		$form_key 						= $_REQUEST['form_key'];
		$form_title 					= $_REQUEST['form_title'];
		$st_form_id 					= $_REQUEST['st_form_id'];
		$form_subject 					= $_REQUEST['form_email_subject'];
		$form_to 						= serialize( array_map( 'sanitize_email', $_REQUEST['form_email_to'] ) );
		$form_from 						= sanitize_email( $_REQUEST['form_email_from'] );
		$form_from_name 				= $_REQUEST['form_email_from_name'];
		$form_from_override 			= isset( $_REQUEST['form_email_from_override'] ) ? $_REQUEST['form_email_from_override'] : '';
		$form_from_name_override 		= isset( $_REQUEST['form_email_from_name_override'] ) ? $_REQUEST['form_email_from_name_override'] : '';
		$form_success_type 				= $_REQUEST['form_success_type'];
		$form_notification_setting 		= isset( $_REQUEST['form_notification_setting'] ) ? $_REQUEST['form_notification_setting'] : '';
		$form_notification_email_name 	= isset( $_REQUEST['form_notification_email_name'] ) ? $_REQUEST['form_notification_email_name'] : '';
		$form_notification_email_from 	= isset( $_REQUEST['form_notification_email_from'] ) ? sanitize_email( $_REQUEST['form_notification_email_from'] ) : '';
		$form_notification_email 		= isset( $_REQUEST['form_notification_email'] ) ? $_REQUEST['form_notification_email'] : '';
		$form_notification_subject 		= isset( $_REQUEST['form_notification_subject'] ) ? $_REQUEST['form_notification_subject'] : '';
		$form_notification_message 		= isset( $_REQUEST['form_notification_message'] ) ? wp_richedit_pre( $_REQUEST['form_notification_message'] ) : '';
		$form_notification_entry 		= isset( $_REQUEST['form_notification_entry'] ) ? $_REQUEST['form_notification_entry'] : '';
		$form_label_alignment 			= $_REQUEST['form_label_alignment'];
		// Add confirmation based on which type was selected
		switch ( $form_success_type ) {
			case 'text' :
			$form_success_message = wp_richedit_pre( $_REQUEST['form_success_message_text'] );
			break;
			case 'page' :
			$form_success_message = $_REQUEST['form_success_message_page'];
			break;
			case 'redirect' :
			$form_success_message = $_REQUEST['form_success_message_redirect'];
			break;
		}
    
    /* Get Token From Existing Client */
     $form_register= $wpdb->prefix . 'form_register';
		$mytoken = $wpdb->get_results( "SELECT * FROM $form_register where status =1" );
		$username=$mytoken[0]->username;
		$password=$mytoken[0]->password;
		$apikey=$mytoken[0]->varification_key;
		$acname=$mytoken[0]->account_name;
		$account_id=$mytoken[0]->account_id;
		/*  get token key from account  */
		$url = 'https://services.smarttouchinteractive.com/login';
		$method = 'POST';
    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			);
		$data = json_encode(array(
			'UserName' => $username,
			'Password' => $password,
			'ApiKey' =>   $apikey
			));
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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
    /* Check and update smarttouch CRM */
     	$burl = 'https://services.smarttouchinteractive.com/updateformname';
		$bmethod = 'POST';
        # headers and data (this is API dependent, some uses XML)
		$bheaders = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Bearer '.$tokenkey.''
			);
		$bdata = json_encode(array(
			'AccountId' => $account_id,
			'Id'        => $st_form_id,
			'LastModifiedBy'   => $form_key,
			'Name'           => $form_title
			));
       
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $burl);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $bheaders);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		switch($bmethod) {
			case 'GET':
			break;
			case 'POST':
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, $bdata);
			break;
			case 'PUT': 
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($handle, CURLOPT_POSTFIELDS, $bdata);
			break;
			case 'DELETE':
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		}
		$bresponse = curl_exec($handle);
		$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$bresponse = json_decode($bresponse, TRUE);
       
	   if (!empty($bresponse['Exception'])) { 
	         $actual_link ="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	         $spam=str_replace(' ','-',$bresponse['Exception']['Message']);
             wp_redirect( "$actual_link&msg=".$spam );
         }else{ 
		
		$newdata = array(
			'form_key' 						=> $form_key,
			'form_title' 					=> $form_title,
			'st_form_id' 					=> $st_form_id,
			'form_email_subject' 			=> $form_subject,
			'form_email_to' 				=> $form_to,
			'form_email_from' 				=> $form_from,
			'form_email_from_name' 			=> $form_from_name,
			'form_email_from_override' 		=> $form_from_override,
			'form_email_from_name_override' => $form_from_name_override,
			'form_success_type' 			=> $form_success_type,
			'form_success_message' 			=> $form_success_message,
			'form_notification_setting' 	=> $form_notification_setting,
			'form_notification_email_name' 	=> $form_notification_email_name,
			'form_notification_email_from' 	=> $form_notification_email_from,
			'form_notification_email' 		=> $form_notification_email,
			'form_notification_subject' 	=> $form_notification_subject,
			'form_notification_message' 	=> $form_notification_message,
			'form_notification_entry' 		=> $form_notification_entry,
			'form_label_alignment' 			=> $form_label_alignment
			);

$where = array( 'form_id' => $form_id );
		// Update form details
$wpdb->update( $this->form_table_name, $newdata, $where );
$field_ids = array();
		// Get max post vars, if available. Otherwise set to 1000
$max_post_vars = ( ini_get( 'max_input_vars' ) ) ? intval( ini_get( 'max_input_vars' ) ) : 1000;
		// Set a message to be displayed if we've reached a limit
if ( count( $_POST, COUNT_RECURSIVE ) > $max_post_vars )
	$this->post_max_vars = true;
foreach ( $_REQUEST['field_id'] as $fields ) :
	$field_ids[] = $fields;
endforeach;
		// Initialize field sequence
$field_sequence = 0;
		// Loop through each field and update
foreach ( $field_ids as $id ) :
	$id = absint( $id );
$field_name 		= ( isset( $_REQUEST['field_name-' . $id] ) ) ? trim( $_REQUEST['field_name-' . $id] ) : '';
			//$field_key 			= sanitize_key( sanitize_title( $field_name, $id ) );
$field_desc 		= ( isset( $_REQUEST['field_description-' . $id] ) ) ? trim( $_REQUEST['field_description-' . $id] ) : '';
$field_options 		= ( isset( $_REQUEST['field_options-' . $id] ) ) ? serialize( array_map( 'trim', $_REQUEST['field_options-' . $id] ) ) : '';
$field_validation 	= ( isset( $_REQUEST['field_validation-' . $id] ) ) ? $_REQUEST['field_validation-' . $id] : '';
$field_required 	= ( isset( $_REQUEST['field_required-' . $id] ) ) ? $_REQUEST['field_required-' . $id] : '';
$field_size 		= ( isset( $_REQUEST['field_size-' . $id] ) ) ? $_REQUEST['field_size-' . $id] : '';
$field_css 			= ( isset( $_REQUEST['field_css-' . $id] ) ) ? $_REQUEST['field_css-' . $id] : '';
$field_layout 		= ( isset( $_REQUEST['field_layout-' . $id] ) ) ? $_REQUEST['field_layout-' . $id] : '';
$field_default 		= ( isset( $_REQUEST['field_default-' . $id] ) ) ? trim( $_REQUEST['field_default-' . $id] ) : '';

$field_showopt 		= ( isset( $_REQUEST['showopt-' . $id] ) ) ? $_REQUEST['showopt-' . $id]  : '';

$field_optvalue=NULL;
$field_optvalue1='';
$opt='';
if(is_array($field_showopt)){ foreach($field_showopt as $opt){   $field_optvalue1 .=$opt.'|';  $field_optvalue=substr($field_optvalue1,0,-1); }  }

$typeid 		    = ( isset( $_REQUEST['typeid-' . $id] ) ) ? trim( $_REQUEST['typeid-' . $id] ) : '';
if($field_layout=='yes'){ $field_type='hidden';
$field_data = array(
	'field_type' 		=> $field_type,
	'field_name' 		=> $field_name,
	'field_description' => $field_desc,
	'field_options'		=> $field_options,
	'field_validation' 	=> $field_validation,
	'field_required' 	=> $field_required,
	'field_size' 		=> $field_size,
	'field_css' 		=> $field_css,
	'field_layout' 		=> $field_layout,
	'field_sequence' 	=> $field_sequence,
	'field_default' 	=> $field_default,
	'fieldid'          => $field_optvalue,
	'typeid' 	=> $typeid
	);
}else{   
	$field_data = array(
		//'field_key' 		=> $field_key,
		'field_name' 		=> $field_name,
		'field_description' => $field_desc,
		'field_options'		=> $field_options,
		'field_validation' 	=> $field_validation,
		'field_required' 	=> $field_required,
		'field_size' 		=> $field_size,
		'field_css' 		=> $field_css,
		'field_layout' 		=> $field_layout,
		'field_sequence' 	=> $field_sequence,
		'field_default' 	=> $field_default,
		'fieldid'          => $field_optvalue,
		'typeid' 	=> $typeid
		);	
}
$where = array(
	'form_id' 	=> $form_id,
	'field_id' 	=> $id
	);
			// Update all fields
$wpdb->update( $this->field_table_name, $field_data, $where );
$field_sequence++;
endforeach;

       $actual_link ="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
       $actual_red=explode("&msg",$actual_link);
	   wp_redirect( "$actual_red[0]");

 }  
}
	/**
	 * Handle trashing and deleting forms
	 *
	 * This is a placeholder function since all processing is handled in includes/class-forms-list.php
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_trash_delete_form() {
		global $wpdb;
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;
		if ( 'smart-touch-form-builder' !== $_GET['page'] )
			return;
		if ( 'delete_form' !== $_REQUEST['action'] )
			return;
		$id = absint( $_REQUEST['form'] );
		check_admin_referer( 'delete-form-' . $id );
		// Delete form and all fields
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->form_table_name WHERE form_id = %d", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE form_id = %d", $id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->entries_table_name WHERE form_id = %d", $id ) );
		// Redirect to keep the URL clean (use AJAX in the future?)
		wp_redirect( add_query_arg( 'action', 'deleted', 'admin.php?page=smart-touch-form-builder' ) );
		exit();
	}
	/**
	 * Handle form duplication
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_copy_form() {
		global $wpdb;
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;
		if ( 'smart-touch-form-builder' !== $_GET['page'] )
			return;
		if ( 'copy_form' !== $_REQUEST['action'] )
			return;
		$id = absint( $_REQUEST['form'] );
		check_admin_referer( 'copy-form-' . $id );
		// Get all fields and data for the request form
		$fields    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d", $id ) );
		$forms     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d", $id ) );
		$override  = $wpdb->get_var( $wpdb->prepare( "SELECT form_email_from_override, form_email_from_name_override, form_notification_email FROM $this->form_table_name WHERE form_id = %d", $id ) );
		$from_name = $wpdb->get_var( null, 1 );
		$notify    = $wpdb->get_var( null, 2 );


		/* add form in crm */

        $form_register= $wpdb->prefix . 'form_register';
		$mytoken = $wpdb->get_results( "SELECT * FROM $form_register where status =1" );
		$username=$mytoken[0]->username;
		$password=$mytoken[0]->password;
		$apikey=$mytoken[0]->varification_key;
		$acname=$mytoken[0]->account_name;
		$account_id=$mytoken[0]->account_id;
		/*  get token key from account  */
		$url = 'https://services.smarttouchinteractive.com/login';
		$method = 'POST';
    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			);
		$data = json_encode(array(
			'UserName' => $username,
			'Password' => $password,
			'ApiKey' =>   $apikey
			));
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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
        $acname=str_replace ( ' ','%20',$acname);
         $idurl = "https://services.smarttouchinteractive.com/api/accounts?accountName=$acname";
           
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
            
            $accountid=$allids->AccountViewModel->AccountID; 



    /*  Added form id in database as well as in smartouch CRM  */
		$burl = 'https://services.smarttouchinteractive.com/CreateForm';
		$bmethod = 'POST';
        # headers and data (this is API dependent, some uses XML)
		$bheaders = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization: Bearer '.$tokenkey.''
			);


		$bdata = json_encode(array(
			'AccountId' => $account_id,
			'Name'   => $forms[0]->form_title.' copy',
			'CreatedBy' =>   $forms[0]->form_key
			));
		
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $burl);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $bheaders);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		switch($bmethod) {
			case 'GET':
			break;
			case 'POST':
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, $bdata);
			break;
			case 'PUT': 
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($handle, CURLOPT_POSTFIELDS, $bdata);
			break;
			case 'DELETE':
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		}
		$bresponse = curl_exec($handle);
		$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$bresponse = json_decode($bresponse, TRUE);
        
        if($bresponse['ViewModel']['Id']){
		
		                       $emp_account_id=$bresponse['ViewModel']['Id'];

  	    /* finish get crm form id */         
		
		// Copy this form and force the initial title to denote a copy
		foreach ( $forms as $form ) {
			$data = array(
				'form_key'						=> sanitize_title( $form->form_key),
				'form_title' 					=> $form->form_title.' copy',
				'st_form_id' 					=> $emp_account_id,
				'form_email_subject' 			=> $form->form_email_subject,
				'form_email_to' 				=> $form->form_email_to,
				'form_email_from' 				=> $form->form_email_from,
				'form_email_from_name' 			=> $form->form_email_from_name,
				'form_email_from_override' 		=> $form->form_email_from_override,
				'form_email_from_name_override' => $form->form_email_from_name_override,
				'form_success_type' 			=> $form->form_success_type,
				'form_success_message' 			=> $form->form_success_message,
				'form_notification_setting' 	=> $form->form_notification_setting,
				'form_notification_email_name' 	=> $form->form_notification_email_name,
				'form_notification_email_from' 	=> $form->form_notification_email_from,
				'form_notification_email' 		=> $form->form_notification_email,
				'form_notification_subject' 	=> $form->form_notification_subject,
				'form_notification_message' 	=> $form->form_notification_message,
				'form_notification_entry' 		=> $form->form_notification_entry,
				'form_label_alignment' 			=> $form->form_label_alignment
				);
$wpdb->insert( $this->form_table_name, $data );
}
		// Get form ID to add our first field
$new_form_selected = $wpdb->insert_id;
		// Copy each field and data
foreach ( $fields as $field ) {
	$data_dup = array(
		'form_id' 			=> $new_form_selected,
		'field_key' 		=> $field->field_key,
		'field_type' 		=> $field->field_type,
		'field_name' 		=> $field->field_name,
		'field_description' => $field->field_description,
		'field_options' 	=> $field->field_options,
		'field_sequence' 	=> $field->field_sequence,
		'field_validation' 	=> $field->field_validation,
		'field_required' 	=> $field->field_required,
		'field_size' 		=> $field->field_size,
		'field_css' 		=> $field->field_css,
		'field_layout' 		=> $field->field_layout,
		'field_parent' 		=> $field->field_parent,
		'field_default'		=> $field->field_default,
		'iscustom' 			=> $field->iscustom,
		'fieldid' 			=> $field->fieldid,
		'typeid'			=> $field->typeid,
		);

	$wpdb->insert( $this->field_table_name, $data_dup );
			// If a parent field, save the old ID and the new ID to update new parent ID
	if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) )
		$parents[ $field->field_id ] = $wpdb->insert_id;
	if ( $override == $field->field_id )
		$wpdb->update( $this->form_table_name, array( 'form_email_from_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
	if ( $from_name == $field->field_id )
		$wpdb->update( $this->form_table_name, array( 'form_email_from_name_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
	if ( $notify == $field->field_id )
		$wpdb->update( $this->form_table_name, array( 'form_notification_email' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
}
		// Loop through our parents and update them to their new IDs
foreach ( $parents as $k => $v ) {
	$wpdb->update( $this->field_table_name, array( 'field_parent' => $v ), array( 'form_id' => $new_form_selected, 'field_parent' => $k ) );
}

}else{   $statusMessage=$bresponse['Exception']['Message'];
         $error_status=substr($statusMessage, -15,-1); 
         if($error_status=='different name') {
         			$actual_link ="http://$_SERVER[HTTP_HOST]"."/wp-admin/admin.php?page=smart-touch-form-builder&action=edit&form=$id";
                    wp_redirect( "$actual_link&duplicate=false" );
         }else{
	  	     $actual_link ="http://$_SERVER[HTTP_HOST]"."/wp-admin/admin.php?page=smart-touch-form-builder&action=edit&form=$id";
	         $spam=str_replace(' ','-',$bresponse['Exception']['Message']);
             wp_redirect( "$actual_link&msg=".$spam );
         }
	  }
		exit();

}
	/**
	 * Save options on the STI > Settings page
	 *
	 * @access public
	 * @since 2.8.1
	 * @return void
	 */
	public function save_settings() {
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;
		if ( 'sti-settings' !== $_GET['page'] )
			return;
		if ( 'sti_settings' !== $_REQUEST['action'] )
			return;
		check_admin_referer( 'sti-update-settings' );
		$data = array();
		foreach ( $_POST['sti-settings'] as $key => $val ) {
			$data[ $key ] = esc_html( $val );
		}
		update_option( 'sti-settings', $data );
	}
	/**
	 * The jQuery field sorting callback
	 *
	 * @since 1.0
	 */
	public function ajax_sort_field() {
		global $wpdb;
		$data = array();
		foreach ( $_REQUEST['order'] as $k ) :
			if ( 'root' !== $k['item_id'] && !empty( $k['item_id'] ) ) :
				$data[] = array(
					'field_id' 	=> $k['item_id'],
					'parent' 	=> $k['parent_id']
					);
			endif;
			endforeach;
			foreach ( $data as $k => $v ) :
			// Update each field with it's new sequence and parent ID
				$wpdb->update( $this->field_table_name, array(
					'field_sequence'	=> $k,
					'field_parent'  	=> $v['parent'] ),
			array( 'field_id' => $v['field_id'] ),
			'%d'
			);
			endforeach;
			die(1);
		}
	/**
	 * The jQuery create field callback
	 *
	 * @since 1.9
	 */
	public function ajax_create_field() {
		global $wpdb;
		$data = array();
		$field_options = $field_validation = '';
		foreach ( $_REQUEST['data'] as $k ) {
			$data[ $k['name'] ] = $k['value'];
		}
		check_ajax_referer( 'create-field-' . $data['form_id'], 'nonce' );
		$form_id 	= absint( $data['form_id'] );
		
		$field_key 	= $_REQUEST['field_key'];
		$list=explode('|',$field_key);
		$ct=sizeof($list);
		if($ct>1)
		{
			$field_key=$list[0];
			$iscustom=$list[1];
			$type_id=$list[2];
			$option_value=$list[3];
			$field_validation=$list[4];	
		}else{
			$iscustom='';
			$type_id='yes';
			$field_id='';
			$namesArray[]='';
		}

		if($type_id=='' || $type_id==NULL){ $type_id='yes'; }
		
		$field_name = esc_html( $_REQUEST['field_name'] );
		
		$field_type = strtolower( sanitize_title( $_REQUEST['field_type'] ) );
       
		$option_value=str_replace('\\','',$option_value);
		$op_array=explode(',',$option_value);
		$op_lenth=count($op_array);
                //$arrStr=array();
		$namesArray;
		for($ov=0;$ov<$op_lenth;$ov++){
			$strValue=$op_array[$ov];
			$namesArray[]=$strValue;
		}
                    // print_r($namesArray);
		$customDropdownID='';
		for($did=0;$did<$op_lenth;$did++){
			$cidValue=explode('!',$op_array[$did]);
			$customDropdownID .=$cidValue[0].'|';
		}
		$customDropdownID=substr($customDropdownID,0,-1);
        // $option_value='108!Community 1','109!Community 2','110!Community 3','111!Community 4'; 
		// Set defaults for validation
		switch ( $field_type ) {
			case 'select' :
			case 'radio' :
			case 'checkbox' :
			$field_options = serialize( $namesArray );
			$field_id 	=$customDropdownID;
			break;
			case 'email' :
			case 'url' :
			case 'phone' :
			$field_validation = $field_type;
			break;
			case 'currency' :
			$field_validation = 'number';
			break;
			case 'number' :
			$field_validation = 'digits';
			break;
			case 'time' :
			$field_validation = 'time-12';
			break;
			case 'file-upload' :
			$field_options = serialize( array( 'png|jpe?g|gif' ) );
			break;
		}
        // if($field_key=='Communities'){ $field_type='hidden'; }
		// Get the last row's sequence that isn't a Verification
		$sequence_last_row = $wpdb->get_var( $wpdb->prepare( "SELECT field_sequence FROM $this->field_table_name WHERE form_id = %d AND field_type = 'verification' ORDER BY field_sequence DESC LIMIT 1", $form_id ) );
		// If it's not the first for this form, add 1
		$field_sequence = ( !empty( $sequence_last_row ) ) ? $sequence_last_row : 0;
		$newdata = array(
			'form_id' 			=> $form_id,
			'field_key' 		=> $field_key,
			'field_name' 		=> $field_name,
			'field_type' 		=> $field_type,
			'field_options' 	=> $field_options,
			'field_sequence' 	=> $field_sequence,
			'field_validation' 	=> $field_validation,
			'iscustom' 		    => $iscustom,
			'typeid' 		    => $type_id,
			'fieldid' 		    => "$field_id"
			);
		// Create the field
		$wpdb->insert( $this->field_table_name, $newdata );
		$insert_id = $wpdb->insert_id;
		// VIP fields
		$vip_fields = array( 'verification', 'secret', 'submit' );
		// Move the VIPs
		foreach ( $vip_fields as $update ) {
			$field_sequence++;
			$where = array(
				'form_id' 		=> absint( $data['form_id'] ),
				'field_type' 	=> $update
				);
			$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), $where );
		}
		echo $this->field_output( $data['form_id'], $insert_id );
		die(1);
	}
	/**
	 * The jQuery delete field callback
	 *
	 * @since 1.9
	 */
	public function ajax_delete_field() {
		global $wpdb;
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'smart_touch_form_builder_delete_field' ) {
			$form_id = absint( $_REQUEST['form'] );
			$field_id = absint( $_REQUEST['field'] );
			check_ajax_referer( 'delete-field-' . $form_id, 'nonce' );
			if ( isset( $_REQUEST['child_ids'] ) ) {
				foreach ( $_REQUEST['child_ids'] as $children ) {
					$parent = absint( $_REQUEST['parent_id'] );
					// Update each child item with the new parent ID
					$wpdb->update( $this->field_table_name, array( 'field_parent' => $parent ), array( 'field_id' => $children ) );
				}
			}
			// Delete the field
			$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE field_id = %d", $field_id ) );
		}
		die(1);
	}
	/**
	 * The jQuery form settings callback
	 *
	 * @since 2.2
	 */
	public function ajax_form_settings() {
		global $current_user;
		get_currentuserinfo();
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'smart_touch_form_builder_form_settings' ) {
			$form_id 	= absint( $_REQUEST['form'] );
			$status 	= isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'opened';
			$accordion 	= isset( $_REQUEST['accordion'] ) ? $_REQUEST['accordion'] : 'general-settings';
			$user_id 	= $current_user->ID;
			$form_settings = get_user_meta( $user_id, 'sti-form-settings', true );
			$array = array(
				'form_setting_tab' 	=> $status,
				'setting_accordion' => $accordion
				);
			// Set defaults if meta key doesn't exist
			if ( !$form_settings || $form_settings == '' ) {
				$meta_value[ $form_id ] = $array;
				update_user_meta( $user_id, 'sti-form-settings', $meta_value );
			}
			else {
				$form_settings[ $form_id ] = $array;
				update_user_meta( $user_id, 'sti-form-settings', $form_settings );
			}
		}
		die(1);
	}
	/**
	 * Display the additional media button
	 *
	 * Used for inserting the form shortcode with desired form ID
	 *
	 * @since 2.3
	 */
	public function ajax_media_button(){
		global $wpdb;
		// Sanitize the sql orderby
		$order = sanitize_sql_orderby( 'form_id ASC' );
		// Build our forms as an object
		$forms = $wpdb->get_results( "SELECT form_id, form_title FROM $this->form_table_name ORDER BY $order" );
		?>
		<div id="sti_form">
			<form id="add_sti_form" class="media-upload-form type-form validate">
				<h3 class="media-title">Insert Smart Touch Form Builder Form</h3>
				<p>Select a form below to insert into any Post or Page.</p>
				<select id="sti_forms" name="sti_forms">
					<?php foreach( $forms as $form ) : ?>
						<option value="<?php echo $form->form_id; ?>"><?php echo $form->form_title; ?></option>
					<?php endforeach; ?>
				</select>
				<p><input type="submit" class="button-primary" value="Insert Form" /></p>
			</form>
		</div>
		<?php
		die(1);
	}
	/**
	 * All Forms output in admin
	 *
	 * @since 2.5
	 */
	public function all_forms() {
		global $wpdb, $forms_list;
		$order = sanitize_sql_orderby( 'form_title ASC' );
		$where = apply_filters( 'sti_pre_get_forms', '' );
		$forms = $wpdb->get_results( "SELECT form_id, form_title FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );
		if ( !$forms ) :
			echo '<div class="sti-form-alpha-list"><h3 id="sti-no-forms">You currently have no forms. <a href="' . esc_url( admin_url( 'admin.php?page=sti-add-new' ) ) . '">Click Here</a> to add SmartTouch forms to your word press site.</h3></div>';
		return;
		endif;
		echo '<form id="forms-filter" method="post" action="">';
		$forms_list->views();
		$forms_list->prepare_items();
		$forms_list->search_box( 'search', 'search_id' );
		$forms_list->display();
		echo '</form>';
		?>
		<?php
	}
	/**
	 * Build field output in admin
	 *
	 * @since 1.9
	 */
	public function field_output( $form_nav_selected_id, $field_id = NULL ) {
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-field-options.php' );
	}
	/**
	 * Display admin notices
	 *
	 * @since 1.0
	 */
	public function admin_notices(){
		if ( !isset( $_REQUEST['action'] ) || !isset( $_GET['page'] ) )
			return;
		if ( !in_array( $_GET['page'], array( 'smart-touch-form-builder', 'sti-add-new', 'sti-entries', 'sti-email-design', 'sti-reports', 'sti-import', 'sti-export', 'sti-settings' ) ) )
			return;
		switch( $_REQUEST['action'] ) {
			case 'create_form' :
			echo '<div id="message" class="updated"><p>' . __( 'Form created.' , 'smart-touch-form-builder' ) . '</p></div>';
			break;
			case 'update_form' :
			echo '<div id="message" class="updated"><p>' . __( 'Form updated.' , 'smart-touch-form-builder' ) . '</p></div>';
			if ( $this->post_max_vars ) :
					// Get max post vars, if available. Otherwise set to 1000
				$max_post_vars = ( ini_get( 'max_input_vars' ) ) ? intval( ini_get( 'max_input_vars' ) ) : 1000;
			echo '<div id="message" class="error"><p>' . sprintf( __( 'Error saving form. The maximum amount of data allowed by your server has been reached. Please update <a href="%s" target="_blank">max_input_vars</a> in your php.ini file to allow more data to be saved. Current limit is <strong>%d</strong>', 'smart-touch-form-builder' ), 'http://www.php.net/manual/en/info.configuration.php#ini.max-input-vars', $max_post_vars ) . '</p></div>';
			endif;
			break;
			case 'deleted' :
			echo '<div id="message" class="updated"><p>' . __( 'Item permanently deleted.' , 'smart-touch-form-builder') . '</p></div>';
			break;
			case 'copy_form' :
			echo '<div id="message" class="updated"><p>' . __( 'Item successfully duplicated.' , 'smart-touch-form-builder') . '</p></div>';
			break;
			case 'sti_settings' :
			echo sprintf( '<div id="message" class="updated"><p>%s</p></div>', __( 'Settings saved.' , 'smart-touch-form-builder' ) );
			break;
		}
	}
	/**
	 * Add options page to Settings menu
	 *
	 *
	 * @since 1.0
	 * @uses add_options_page() Creates a menu item under the Settings menu.
	 */
	public function add_admin() {
		global $wpdb;
		$sttable = $wpdb->prefix . 'form_register';
		$statusmenu = $wpdb->query("select * from $sttable where status=1 "); 
		if($statusmenu!='1'){ 
			$current_pages[ 'sti' ] = add_menu_page( __( 'Form Login', 'smart-touch-form-builder' ), __( 'Form Login', 'smart-touch-form-builder' ), 'manage_options', 'smart-touch-form-builder', array( &$this, 'admin' ), plugins_url( 'smarttouchinteractive-form-builder/images/sti_icon.png' ) );
		} if($statusmenu=='1'){
			$current_pages[ 'sti' ] = add_menu_page( __( 'SmartTouch form builder', 'smart-touch-form-builder' ), __( 'SmartTouch form builder', 'smart-touch-form-builder' ), 'manage_options', 'smart-touch-form-builder', array( &$this, 'admin' ), plugins_url( 'smarttouchinteractive-form-builder/images/sti_icon.png' ) );
			$current_pages[ 'sti-export' ] = add_submenu_page( 'smart-touch-form-builder', __( 'Update account setting', 'smart-touch-form-builder' ), __( 'Update account setting', 'smart-touch-form-builder' ), 'manage_options', 'sti-export', array( &$this, 'admin_export' ) );
			add_submenu_page( 'smart-touch-form-builder', __( 'SmartTouch Form Builder', 'smart-touch-form-builder' ), __( 'All Forms', 'smart-touch-form-builder' ), 'manage_options', 'smart-touch-form-builder', array( &$this, 'admin' ) );
			$current_pages[ 'sti-add-new' ] = add_submenu_page( 'smart-touch-form-builder', __( 'Add New Form', 'smart-touch-form-builder' ), __( 'Add New Form', 'smart-touch-form-builder' ), 'manage_options', 'sti-add-new', array( &$this, 'admin_add_new' ) );
		}   $current_pages[ 'sti-header-footer' ] = add_submenu_page( 'smart-touch-form-builder', __( 'Add Header Footer', 'smart-touch-form-builder' ), __( 'Add Header Footer', 'smart-touch-form-builder' ), 'manage_options', 'sti-header-footer', array( &$this, 'admin_header_footer' ) );
		// All plugin page load hooks
		foreach ( $current_pages as $key => $page ) :
			// Load the jQuery and CSS we need if we're on our plugin page
			add_action( "load-$page", array( &$this, 'admin_scripts' ) );
			// Load the Help tab on all pages
		add_action( "load-$page", array( &$this, 'help' ) );
		endforeach;
		// Save pages array for filter/action use throughout plugin
		$this->_admin_pages = $current_pages;
		// Adds a Screen Options tab to the Entries screen
		add_action( 'load-' . $current_pages['sti'], array( &$this, 'screen_options' ) );
		add_action( 'load-' . $current_pages['sti-entries'], array( &$this, 'screen_options' ) );
		// Add meta boxes to the form builder admin page
		add_action( 'load-' . $current_pages['sti'], array( &$this, 'add_meta_boxes' ) );
		// Include Entries and Import files
		add_action( 'load-' . $current_pages['sti-entries'], array( &$this, 'includes' ) );
		add_action( 'load-' . $current_pages['sti'], array( &$this, 'include_forms_list' ) );
	}   
	/**
	 * Display Add New Form page
	 *
	 *
	 * @since 2.7.2
	 */
	public function admin_add_new() {
		?>
		<div class="wrap">
			<div class="st-logo"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>"></div>
			<span class="sti-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder' ); ?>">(?)</span>
			<?php
			include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-new-form.php' );
			?>
		</div>
		<?php
	}

   /***** function to add data in heder and footer section *****/
	public function admin_header_footer() {

	  	if($_GET['hf']=='update')
		{ 
			if (isset($_POST["hfdata"])) {

				if (file_exists(plugin_dir_path( __FILE__ ))."includes/hphp.php"){
				        $hphp=stripslashes($_POST['hp_code']);
				        $myfile = fopen(plugin_dir_path( __FILE__ )."includes/hphp.php", "w") or die("Unable to open file!");
						fwrite($myfile,$hphp);
					    fclose($myfile); 				                           
				     }
				     if (file_exists(plugin_dir_path( __FILE__ ))."includes/hscript.php"){
				        $hphp=stripslashes($_POST['hs_code']);
				        $myfile = fopen(plugin_dir_path( __FILE__ )."includes/hscript.php", "w") or die("Unable to open file!");
						fwrite($myfile,$hphp);
					    fclose($myfile); 				                           
				     }
				     if (file_exists(plugin_dir_path( __FILE__ ))."includes/fphp.php"){
				        $hphp=stripslashes($_POST['fp_code']);
				        $myfile = fopen(plugin_dir_path( __FILE__ )."includes/fphp.php", "w") or die("Unable to open file!");
						fwrite($myfile,$hphp);
					    fclose($myfile); 				                           
				     }
				     if (file_exists(plugin_dir_path( __FILE__ ))."includes/fscript.php"){
				        $hphp=stripslashes($_POST['fs_code']);
				        $myfile = fopen(plugin_dir_path( __FILE__ )."includes/fscript.php", "w") or die("Unable to open file!");
						fwrite($myfile,$hphp);
					    fclose($myfile); 				                           
				     }

				     if (file_exists(plugin_dir_path( __FILE__ ))."includes/fophp.php"){
				        $fophp=stripslashes($_POST['fo_code']);
				        $myfile = fopen(plugin_dir_path( __FILE__ )."includes/fophp.php", "w") or die("Unable to open file!");
						fwrite($myfile,$fophp);
					    fclose($myfile); 				                           
				     }

				     if (file_exists(plugin_dir_path( __FILE__ ))."includes/cptkey.php"){
				        $cptscript=stripslashes($_POST['captch_key']);
				        $myfile = fopen(plugin_dir_path( __FILE__ )."includes/cptkey.php", "w") or die("Unable to open file!");
						fwrite($myfile,$cptscript);
					    fclose($myfile); 				                           
				     }

				
			} 
		}
		?>
		<div class="wrap">
			<div class="st-logo"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>"></div>
			<span class="sti-tooltip" rel="<?php esc_attr_e( 'For each field, you can insert your own CSS class names which can be used in your own stylesheets.', 'smart-touch-form-builder' ); ?>" title="<?php esc_attr_e( 'About CSS Classes', 'smart-touch-form-builder' ); ?>">(?)</span>
			<?php
			include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-header-footer.php' );
			?>
		</div>
		<?php
	  }


	/**
	 * Display Entries
	 *
	 *
	 * @since 2.7.2
	 */
	public function admin_entries() {
		global $entries_list, $entries_detail;
		?>
		<div class="wrap">
			<h2>
				<?php _e( 'Entries', 'smart-touch-form-builder' ); ?>
				<?php
			// If searched, output the query
				if ( isset( $_POST['s'] ) && !empty( $_POST['s'] ) )
					echo '<span class="subtitle">' . sprintf( __( 'Search results for "%s"' , 'smart-touch-form-builder' ), esc_html( $_POST['s'] ) );
				?>
			</h2>
			<?php
			if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'view', 'edit', 'update_entry' ) ) ) :
				$entries_detail->entries_detail();
			else :
				$entries_list->views();
			$entries_list->prepare_items();
			?>
			<form id="entries-filter" method="post" action="">
				<?php
				$entries_list->search_box( 'search', 'search_id' );
				$entries_list->display();
				?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
	/**
	 * Display Export
	 *
	 *
	 * @since 2.7.2
	 */
	public function admin_export() {
		global $export;
		global $wpdb;
		error_reporting(0);
		?>
		<?php 
		if($_GET['mode'])
		{

        $url = 'https://services.smarttouchinteractive.com/login';
		$method = 'POST';
    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			);
		$data = json_encode(array(
			'UserName' => $_POST['username'],
			'Password' => $_POST['password'],
			'ApiKey' => $_POST['varification_key']
			));
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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
		$token=$response['access_token'];
        $accountid='';
        $acname=$_POST['account_name'];
            $idurl = 'https://services.smarttouchinteractive.com/accounts';
			$method = 'GET';
         # headers and data (this is API dependent, some uses XML)
			$idheaders = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer '.$token.''
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
       if($accountid){

			if (isset($_POST["username"])) {
				$username = $_POST["username"];
				$password = $_POST["password"];
				$varification_key = $_POST["varification_key"];
				$account_name=$_POST["account_name"];
				$form_d = $_POST["form_d"];
				$table = $wpdb->prefix . 'form_register';
				$data_array = array('username' => $username, 'password' => $password, 'varification_key' => $varification_key,'account_name'=> $account_name,'account_id'=>$accountid);
				$where = array('form_d' => $form_d);
				$wpdb->update( $table, $data_array, $where );
				
			} 

		  }else{ echo'</br><div class="update-nag mandatory">Error: Invalid User Credential </div>';  }	
		}
		$form_register  = $wpdb->prefix . 'form_register';
		$thekey = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $form_register WHERE status=1" ), ARRAY_A );
		if(!empty($thekey)){   
			?>
			<div class="wrap">
				<div class="st-logo"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>"></div>
				<form name="" action="<?php echo get_admin_url(); ?>admin.php?page=sti-export&mode=update" method="post">
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="form-name">Account Name <span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="account_name" required value="<?= $thekey['account_name']; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="form-name">User Name <span class="mandatory">*</span></label>
							</th>
							<td> <input type="hidden" name="form_d" value="<?= $thekey['form_d']; ?>" >
								<input class="regular-text required" type="text" name="username" required value="<?= $thekey['username']; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="form-name">Account Password<span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="password" required value="<?= $thekey['password']; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="form-name">Account ApiKey <span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="varification_key" required value="<?= $thekey['varification_key']; ?>">
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">&nbsp;
								
							</th>
							<td>
								<p class="submit"><input type="submit" value="update" class="button button-primary"></p>
							</td>
						</tr>
					</table>
				</form> 	
			</div>
			<?php
		}
	}
	/**
	 * admin_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_settings() {
		$sti_settings = get_option( 'sti-settings' );
		?>
		<div class="wrap">
			<h2><?php _e( 'Settings', 'smart-touch-form-builder' ); ?></h2>
			<form id="sti-settings" method="post">
				<input name="action" type="hidden" value="sti_settings" />
				<?php wp_nonce_field( 'sti-update-settings' ); ?>
				<h3><?php _e( 'Global Settings', 'smart-touch-form-builder' ); ?></h3>
				<p><?php _e( 'These settings will affect all forms on your site.', 'smart-touch-form-builder' ); ?></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'CSS', 'smart-touch-form-builder-pro' ); ?></th>
						<td>
							<fieldset>
								<?php
								$disable = array(
									'always-load-css'     => __( 'Always load CSS', 'smart-touch-form-builder' ),
								'disable-css'         => __( 'Disable CSS', 'smart-touch-form-builder' ),	// SmartTouch-form-builder-css
								);
								foreach ( $disable as $key => $title ) :
									$sti_settings[ $key ] = isset( $sti_settings[ $key ] ) ? $sti_settings[ $key ] : '';
								?>
								<label for="sti-settings-<?php echo $key; ?>">
									<input type="checkbox" name="sti-settings[<?php echo $key; ?>]" id="sti-settings-<?php echo $key; ?>" value="1" <?php checked( $sti_settings[ $key ], 1 ); ?> /> <?php echo $title; ?>
								</label>
								<br>
							<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Form Output', 'smart-touch-form-builder-pro' ); ?></th>
					<td>
						<fieldset>
							<?php
							$disable = array(
								'address-labels'      => __( 'Place Address labels above fields', 'smart-touch-form-builder' ),	// sti_address_labels_placement
								'show-version'        => __( 'Disable meta tag version', 'smart-touch-form-builder' ),	// sti_show_version
								);
							foreach ( $disable as $key => $title ) :
								$sti_settings[ $key ] = isset( $sti_settings[ $key ] ) ? $sti_settings[ $key ] : '';
							?>
							<label for="sti-settings-<?php echo $key; ?>">
								<input type="checkbox" name="sti-settings[<?php echo $key; ?>]" id="sti-settings-<?php echo $key; ?>" value="1" <?php checked( $sti_settings[ $key ], 1 ); ?> /> <?php echo $title; ?>
							</label>
							<br>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="sti-settings-spam-points"><?php _e( 'Spam word sensitivity', 'smart-touch-form-builder' ); ?></label></th>
				<td>
					<?php $sti_settings['spam-points'] = isset( $sti_settings['spam-points'] ) ? $sti_settings['spam-points'] : '4'; ?>
					<input type="number" min="1" name="sti-settings[spam-points]" id="sti-settings-spam-points" value="<?php echo $sti_settings['spam-points']; ?>" class="small-text" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="sti-settings-max-upload-size"><?php _e( 'Max Upload Size', 'smart-touch-form-builder' ); ?></label></th>
				<td>
					<?php $sti_settings['max-upload-size'] = isset( $sti_settings['max-upload-size'] ) ? $sti_settings['max-upload-size'] : '25'; ?>
					<input type="number" name="sti-settings[max-upload-size]" id="sti-settings-max-upload-size" value="<?php echo $sti_settings['max-upload-size']; ?>" class="small-text" /> MB
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="sti-settings-sender-mail-header"><?php _e( 'Sender Mail Header', 'smart-touch-form-builder' ); ?></label></th>
				<td>
					<?php
						// Use the admin_email as the From email
					$from_email = get_site_option( 'admin_email' );
						// Get the site domain and get rid of www.
					$sitename = strtolower( $_SERVER['SERVER_NAME'] );
					if ( substr( $sitename, 0, 4 ) == 'www.' )
						$sitename = substr( $sitename, 4 );
						// Get the domain from the admin_email
					list( $user, $domain ) = explode( '@', $from_email );
						// If site domain and admin_email domain match, use admin_email, otherwise a same domain email must be created
					$from_email = ( $sitename == $domain ) ? $from_email : "wordpress@$sitename";
					$sti_settings['sender-mail-header'] = isset( $sti_settings['sender-mail-header'] ) ? $sti_settings['sender-mail-header'] : $from_email;
					?>
					<input type="text" name="sti-settings[sender-mail-header]" id="sti-settings-sender-mail-header" value="<?php echo $sti_settings['sender-mail-header']; ?>" class="regular-text" />
					<p class="description"><?php _e( 'Some server configurations require an existing email on the domain be used when sending emails.', 'smart-touch-form-builder' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Save', 'smart-touch-form-builder' ), 'primary', 'submit', false ); ?>
	</form>
</div>
<?php
}
	/**
	 * Builds the options settings page
	 *
	 * @since 1.0
	 */
	public function admin() {
		global $wpdb, $current_user;
		get_currentuserinfo();
		// Save current user ID
		$user_id = $current_user->ID;
		// Set variables depending on which tab is selected
		$form_nav_selected_id = ( isset( $_REQUEST['form'] ) ) ? $_REQUEST['form'] : '0';
		/* start custmization  updated by pradeep  */
		$url = 'https://services.smarttouchinteractive.com/login';
		$method = 'POST';
    # headers and data (this is API dependent, some uses XML)
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			);
		$data = json_encode(array(
			'UserName' => $_POST['username'],
			'Password' => $_POST['password'],
			'ApiKey' => $_POST['apikey']
			));
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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
		$token=$response['access_token'];
        $accountid='';
        $acname=$_POST['account_name'];
            $idurl = 'https://services.smarttouchinteractive.com/accounts';
			$method = 'GET';
         # headers and data (this is API dependent, some uses XML)
			$idheaders = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer '.$token.''
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
   if($accountid){

		if($token){
			$form_register  = $wpdb->prefix . 'form_register';
			$post_id = $wpdb->get_var("SELECT COUNT(*) FROM $form_register WHERE (username='".$_POST['username']."')");
			if($post_id<1)
			{
				$wpdb->query("INSERT INTO $form_register (username, password, varification_key,account_name,account_id,resturl,token,status) VALUES ('".$_POST['username']."','".$_POST['password']."','".$_POST['apikey']."','".$_POST['account_name']."','".$accountid."','".$url."','".$token."','1')"  );
			} 
		}
	  }


		$form_register  = $wpdb->prefix . 'form_register';
		$status=$wpdb->get_var("SELECT status FROM $form_register WHERE (status='1')");    
		if($status!='1'){ ?>
		<div class="wrap"> 
			<div class="st-logo"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>"></div>
			<?php echo'</br><div class="update-nag mandatory">Error: Invalid User </div>'; ?>
				<form name="" action="<?php echo get_admin_url(); ?>admin.php?page=smart-touch-form-builder" method="post">
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="form-name">Account Name <span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="account_name" required>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="form-name">User Name <span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="username" required>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="form-name">Account Password<span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="password" required>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="form-name">Account ApiKey <span class="mandatory">*</span></label>
							</th>
							<td>
								<input class="regular-text required" type="text" name="apikey" required>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">&nbsp;
								
							</th>
							<td>
								<p class="submit"><input type="submit" value="VERIFY" class="button button-primary"></p>
							</td>
						</tr>
					</table>
				</FORM>
			</div>
			<?php   } if($status=='1'){ ?>   
			<div id="light" class="white_content"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/loader.gif'; ?>"></div>
			<div id="fade" class="black_overlay"></div>
			<div class="wrap">
				<div class="st-logo"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>"></div>
				<h2>
					<?php _e( 'SmartTouch Interactive Form Builder', 'smart-touch-form-builder' ); ?>
					<?php
			// Add New link
					//echo sprintf( ' <a href="%1$s" class="add-new-h2">%2$s</a>', esc_url( admin_url( 'admin.php?page=sti-add-new' ) ), esc_html( __( 'Add New', 'smart-touch-form-builder' ) ) );
			// If searched, output the query
					if ( isset( $_POST['s'] ) && !empty( $_POST['s'] ) )
						echo '<span class="subtitle">' . sprintf( __( 'Search results for "%s"' , 'smart-touch-form-builder' ), esc_html( $_POST['s'] ) );
					?>
				</h2>
				<?php if ( empty( $form_nav_selected_id ) ) : ?>
					<div id="sti-main" class="sti-order-type-list">
						<?php $this->all_forms(); ?>
					</div> 
				</div> 
				<?php
				elseif ( !empty( $form_nav_selected_id ) && $form_nav_selected_id !== '0' ) :
					include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-form-creator.php' );
				endif;
				?>
			</div>
			<?php
		}
	}
	/**
	 * Handle confirmation when form is submitted
	 *
	 * @since 1.3
	 */
	function confirmation(){
		global $wpdb;
		$form_id = ( isset( $_REQUEST['form_id'] ) ) ? (int) esc_html( $_REQUEST['form_id'] ) : '';
		if ( !isset( $_REQUEST['sti-submit'] ) )
			return;
		// Get forms
		$order = sanitize_sql_orderby( 'form_id DESC' );
		$forms 	= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );
		foreach ( $forms as $form ) :
			// If text, return output and format the HTML for display
			if ( 'text' == $form->form_success_type )
				return stripslashes( html_entity_decode( wp_kses_stripslashes( $form->form_success_message ) ) );
			// If page, redirect to the permalink
			elseif ( 'page' == $form->form_success_type ) {
				$page = get_permalink( $form->form_success_message );
				wp_redirect( $page );
				exit();
			}
			// If redirect, redirect to the URL
			elseif ( 'redirect' == $form->form_success_type ) {
				wp_redirect( esc_url( $form->form_success_message ) );
				exit();
			}
			endforeach;
		}
	/**
	 * Output form via shortcode
	 *
	 * @since 1.0
	 */
	public function form_code( $atts, $output = '' ) {
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/form-output.php' );
		return $output;
	}

	public function popup_code( $atts, $output = '' ) {

		 $titlecode = shortcode_atts( array(
        'title' => 'Form CTA',
                ), $atts );

		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/popup-form-output.php' );
		return $output;
	}

	/**
	 * Handle emailing the content
	 *
	 * @since 1.0
	 * @uses wp_mail() E-mails a message
	 */
	public function email() {
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/email.php' );
	}
	/**
	 * Validate the input
	 *
	 * @since 2.2
	 */
	public function validate_input( $data, $name, $type, $required ) {
		if ( 'yes' == $required && strlen( $data ) == 0 )
			wp_die( "<h1>$name</h1><br>" . __( 'This field is required and cannot be empty.', 'smart-touch-form-builder' ), $name, array( 'back_link' => true ) );
		if ( strlen( $data ) > 0 ) :
			switch( $type ) :
		case 'email' :
		if ( !is_email( $data ) )
			wp_die( "<h1>$name</h1><br>" . __( 'Not a valid email address', 'smart-touch-form-builder' ), '', array( 'back_link' => true ) );
		break;
		case 'number' :
		case 'currency' :
		if ( !is_numeric( $data ) )
			wp_die( "<h1>$name</h1><br>" . __( 'Not a valid number', 'smart-touch-form-builder' ), '', array( 'back_link' => true ) );
		break;
		case 'phone' :
		if ( strlen( $data ) > 9 && preg_match( '/^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/', $data ) )
			return true;
		else
			wp_die( "<h1>$name</h1><br>" . __( 'Not a valid phone number. Most US/Canada and International formats accepted.', 'smart-touch-form-builder' ), '', array( 'back_link' => true ) );
		break;
		case 'url' :
		if ( !preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $data ) )
			wp_die( "<h1>$name</h1><br>" . __( 'Not a valid URL.', 'smart-touch-form-builder' ), '', array( 'back_link' => true ) );
		break;
		default :
		return true;
		break;
		endswitch;
		endif;
	}
	/**
	 * Sanitize the input
	 *
	 * @since 2.5
	 */
	public function sanitize_input( $data, $type ) {
		if ( strlen( $data ) > 0 ) :
			switch( $type ) :
		case 'text' :
		return sanitize_text_field( $data );
		break;
		case 'textarea' :
		return wp_strip_all_tags( $data );
		break;
		case 'email' :
		return sanitize_email( $data );
		break;
		case 'html' :
		return wp_kses_data( force_balance_tags( $data ) );
		break;
		case 'min' :
		case 'max' :
		case 'digits' :
		return preg_replace( '/\D/i', '', $data );
		break;
		case 'address' :
		$allowed_html = array( 'br' => array() );
		return wp_kses( $data, $allowed_html );
		break;
		default :
		return wp_kses_data( $data );
		break;
		endswitch;
		endif;
	}
	/**
	 * Make sure the User Agent string is not a SPAM bot
	 *
	 * @since 1.3
	 */
	public function isBot() {
		$bots = apply_filters( 'sti_blocked_spam_bots', array(
			'<', '>', '&lt;', '%0A', '%0D', '%27', '%3C', '%3E', '%00', 'href',
			'binlar', 'casper', 'cmsworldmap', 'comodo', 'diavol',
			'dotbot', 'feedfinder', 'flicky', 'ia_archiver', 'jakarta',
			'kmccrew', 'nutch', 'planetwork', 'purebot', 'pycurl',
			'skygrid', 'sucker', 'turnit', 'vikspider', 'zmeu',
			)
		);
		$isBot = false;
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_kses_data( $_SERVER['HTTP_USER_AGENT'] ) : '';
		do_action( 'sti_isBot', $user_agent, $bots );
		foreach ( $bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false )
				$isBot = true;
		}
		return $isBot;
	}
	public function build_array_form_item( $value, $type = '' ) {
		$output = '';
		// Basic check for type when not set
		if ( empty( $type ) ) :
			if ( is_array( $value ) && array_key_exists( 'address', $value ) )
				$type = 'address';
			elseif ( is_array( $value ) && array_key_exists( 'hour', $value ) && array_key_exists( 'min', $value ) )
				$type = 'time';
			elseif ( is_array( $value ) )
				$type = 'checkbox';
			else
				$type = 'default';
			endif;
		// Build array'd form item output
			switch( $type ) :
			case 'time' :
			$output = ( array_key_exists( 'ampm', $value ) ) ? substr_replace( implode( ':', $value ), ' ', 5, 1 ) : implode( ':', $value );
			break;
			case 'address' :
			if ( !empty( $value['address'] ) )
				$output .= $value['address'];
			if ( !empty( $value['address-2'] ) ) {
				if ( !empty( $output ) )
					$output .= '<br>';
				$output .= $value['address-2'];
			}
			if ( !empty( $value['city'] ) ) {
				if ( !empty( $output ) )
					$output .= '<br>';
				$output .= $value['city'];
			}
			if ( !empty( $value['state'] ) ) {
				if ( !empty( $output ) && empty( $value['city'] ) )
					$output .= '<br>';
				elseif ( !empty( $output ) && !empty( $value['city'] ) )
					$output .= ', ';
				$output .= $value['state'];
			}
			if ( !empty( $value['zip'] ) ) {
				if ( !empty( $output ) && ( empty( $value['city'] ) && empty( $value['state'] ) ) )
					$output .= '<br>';
				elseif ( !empty( $output ) && ( !empty( $value['city'] ) || !empty( $value['state'] ) ) )
					$output .= ' ';
				$output .= $value['zip'];
			}
			if ( !empty( $value['country'] ) ) {
				if ( !empty( $output ) )
					$output .= '<br>';
				$output .= $value['country'];
			}
			break;
			case 'checkbox' :
			$output = esc_html( implode( ', ', $value ) );
			break;
			default :
			$output = wp_specialchars_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES );
			break;
			endswitch;
			return $output;
		}
	/**
	 * Check whether the content contains the specified shortcode
	 *
	 * @access public
	 * @param string $shortcode (default: '')
	 * @return void
	 */
	function has_shortcode($shortcode = '') {
		$post_to_check = get_post(get_the_ID());
		// false because we have to search through the post content first
		$found = false;
		// if no short code was provided, return false
		if (!$shortcode) {
			return $found;
		}
		// check the post content for the short code
		if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
			// we have found the short code
			$found = true;
		}
		// return our final results
		return $found;
	}
}
// The STI widget
require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-widget.php' );
// Special case to load Export class so AJAX is registered
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-export.php' );
if ( !isset( $export ) )
	$export = new SmartTouchFormBuilder_Export();

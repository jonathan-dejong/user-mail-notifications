<?php
/*
Plugin Name: User Mail Notifications
Plugin URI: https://github.com/jonathan-dejong/user-mail-notifications
Description: Sends an email to all users in a specific role when a new post has been made
Version: 0.0.1
Author: Jonathan de Jong
Author URI: tigerton.se
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action('init', 'init_smr', 0);

function init_smr() {
	
	// If if multisite	    
    if (function_exists('is_multisite') && is_multisite()) {
		require_once (ABSPATH . 'wp-admin/includes/ms.php');
	}
	
	// Define constants
	define( 'SMR_PATH', plugin_dir_path(__FILE__) );
	define( 'SMR_URL', plugin_dir_url(__FILE__) );
	
	// Set plugin version option
	if (!defined('SMR_KEY')){
		define('SMR_KEY', 'buv_version');
	}
	if (!defined('SMR_NUM')){
		define('SMR_NUM', "0.0.1");
	}
	add_option(SMR_KEY, SMR_NUM);
	
	// Localisation
	load_plugin_textdomain( 'mail_notifications_lang', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
	// Register style
	add_action('admin_enqueue_scripts', 'smr_register_styles');
	
	// Add subpage action
	add_action( 'add_meta_boxes', 'smr_add_custom_box' );
	
	// create custom plugin settings menu
	add_action('admin_menu', 'smr_plugin_menu');
	//call register settings function
	add_action( 'admin_init', 'smr_register_settings' );
	
	
}

/*
*  Register styles
*
*  @description:
*  @since 1
*  @created: 2013-05-16
*/
function smr_register_styles(){
	wp_register_style('smr_style', SMR_URL.'assets/style.css');
	wp_enqueue_style('smr_style');
}

/*
*  Register an admin submenu under "settings" page.
*
*  @description:
*  @since 0.1
*  @created: 06/01/13
*/
function smr_plugin_menu() {
	//create submenu page which loads the tgr_settings_page function
	add_options_page(__('Mail Notifications','mail_notifications_lang'), __('Mail Notifications','mail_notifications_lang'), 'manage_options', 'mail_notifications', 'smr_settings_page');
}

/*
*  Register the options
*
*  @description:
*  @since 0.1
*  @created: 06/01/13
*/
function smr_register_settings() {

	//save theme name in the smr option
	register_setting( 'smr_settings_group', 'smr_settings_group', 'smr_validate_group' );	
	
	//the section for the email template optinos
	add_settings_section('smr_main', __('Email template','mail_notifications_lang'), 'smr_section_text', 'smr_settings'); 
	add_settings_field('smr_mail_from', __('Email from:','mail_notifications_lang'), 'smr_mail_from', 'smr_settings', 'smr_main');
	add_settings_field('smr_mail_subject', __('Email subject:','mail_notifications_lang'), 'smr_mail_subject', 'smr_settings', 'smr_main');
	add_settings_field('smr_mail_content', __('Email content:','mail_notifications_lang'), 'smr_mail_content', 'smr_settings', 'smr_main', array());
	
	//the section for the additional settings
	add_settings_section('smr_admin', __('Additional settings','mail_notifications_lang'), 'smr_admin_section_text', 'smr_settings');
	add_settings_field('smr_admin_roles', __('Roles:','mail_notifications_lang'), 'smr_admin_roles', 'smr_settings', 'smr_admin');
	
	
}

/*
*  Create admin subpage
*
*  @description:
*  @since 0.1
*  @created: 06/01/13
*/
function smr_settings_page()
{	
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e('Mail Notifications Settings','mail_notifications_lang'); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'smr_settings_group' ); ?> 
			<?php do_settings_sections('smr_settings'); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/*
*  Section description for email template
*
*  @description: Set a description for the settings page section
*  @since 0.1
*  @created: 06/01/13
*/
function smr_section_text() {
	echo '<p>'.__('There\'s a few tags you can use to dynamically insert content in your email. These are:', 'mail_notifications_lang').'</p>';
	echo '<dl>';
	echo '<dt><dfn>{title}</dfn></dt><dd>'.__('The posts title','mail_notifications_lang').'</dd>';
	echo '<dt><dfn>{excerpt}</dfn></dt><dd>'.__('The posts excerpt, does not retrieve the automatic excerpt if no manual has been written','mail_notifications_lang').'</dd>';
	echo '<dt><dfn>{url}</dfn></dt><dd>'.__('The posts url, you can use this in a link instead of an url to create a working link','mail_notifications_lang').'</dd>';
	echo '</dl>';
} 

/*
*  Section description for additional settings
*
*  @description: Set a description for the settings page section
*  @since 0.1
*  @created: 06/01/13
*/
function smr_admin_section_text() {
	echo '<p>'.__('You may set what roles the user should be able to send emails to. ', 'mail_notifications_lang').'</p>';
} 

/*
*  Mail from input
*
*  @description: Output the settings inputs
*  @since 0.1
*  @created: 06/01/13
*/
function smr_mail_from() {
	$content = get_option('smr_settings_group');
	$from = ($content['mail_from'] ? $content['mail_from'] : 'WordPress'); //set default value if no option is set
	echo '<input type="text" name="smr_settings_group[mail_from]" id="smr_email_from" value="'.$from.'" size="50" />'; // Textinput for from
}

/*
*  Mail subject input
*
*  @description: Output the settings inputs
*  @since 0.1
*  @created: 06/01/13
*/
function smr_mail_subject() {
	$content = get_option('smr_settings_group');
	echo '<input type="text" name="smr_settings_group[mail_subject]" id="smr_email_subject" value="'.$content['mail_subject'].'" size="50" />'; //textinput for subject
}

/*
*  Mail content editor
*
*  @description: Output the settings inputs
*  @since 0.1
*  @created: 06/01/13
*/
function smr_mail_content(){
	$content = get_option('smr_settings_group');
	$settings = array( //tinyMCE settings
		'textarea_name' => 'smr_settings_group[mail_content]',
		'wpautop' => true
	);
	wp_editor($content['mail_content'], 'smreditor', $settings ); //create an editor
}

/*
*  Mail from input
*
*  @description: Output the settings inputs
*  @since 0.1
*  @created: 06/01/13
*/
function smr_admin_roles() {
	$content = get_option('smr_settings_group');
	$allowed_roles = $content['admin_roles'];
	$roles = get_editable_roles();
	foreach($roles as $key => $value){	
		echo '<div class="admin-roles"><label for="role-'.$key.'">' . $value['name'] . ': </label>';
		if($allowed_roles != ''){
			if(in_array($key, $allowed_roles)){
				echo '<input type="checkbox" checked="checked" id="role-'.$key.'" name="smr_settings_group[admin_roles][]" value="'.$key.'"  /></div>';
			}else{
				echo '<input type="checkbox" id="role-'.$key.'" name="smr_settings_group[admin_roles][]" value="'.$key.'"  /></div>';
			}
		}else{
			echo '<input type="checkbox" id="role-'.$key.'" name="smr_settings_group[admin_roles][]" value="'.$key.'"  /></div>';
		}
	}
}


/*
*  Validate options
*
*  @description: We dont validate shit!
*  @since 0.1
*  @created: 06/01/13
*/
function smr_validate_group($input) {
	return $input;
}


/*
*  Register our custom meta box
*
*  @description:
*  @since 1
*  @created: 2013-05-16
*/
function smr_add_custom_box() {
    add_meta_box(
        'smr_sectionid',
        __( 'Send notification to role', 'mail_notifications_lang' ),
        'smr_inner_custom_box',
        'post',
        'side',
        'default'
    );
}


/*
*  Print custom meta box content
*
*  @description:
*  @since 1
*  @created: 2013-05-16
*/
function smr_inner_custom_box($post){

	// Use nonce for verification
	wp_nonce_field(plugins_url(__FILE__), 'smr_noncename');
	
	// The actual fields for data entry
	// Use get_post_meta to retrieve an existing value from the database and use the value for the form
	
	$roles = get_editable_roles();
	$content = get_option('smr_settings_group');
	$allowed_roles = $content['admin_roles'];
	
	//print_r($roles);
	//print_r($allowed_roles);
	//die();
	echo '<p>'.__('Check what roles should be sent a notification about this post: ','mail_notifications_lang').'</p>';
	foreach($roles as $key => $value){	
		if($allowed_roles != ''){ //we have some selected roles
			if(in_array($key, $allowed_roles)){ //only show those
				echo '<div><label for="role-'.$key.'">' . $value['name'] . ': </label>';
				echo '<input type="checkbox" id="role-'.$key.'" name="roles[]" value="'.$key.'"  /></div>';
			}
		}else{ //if not, just show all roles
			echo '<div class="admin-roles"><label for="role-'.$key.'">' . $value['name'] . ': </label>';
			echo '<input type="checkbox" id="role-'.$key.'" name="roles[]" value="'.$key.'"  /></div>';
		}
				
	}
}

/*
*  Set up mail content template
*
*  @description:
*  @since 1
*  @created: 2013-05-16
*/	
function smr_mail_template($post, $post_url, $content, $type){
	
	if($type == 'content'){
	
		$new_content = str_replace('{title}', $post->post_title, $content);
		$new_content = str_replace('{excerpt}', $post->post_excerpt, $new_content);
		$new_content = str_replace('{url}', $post_url, $new_content);
		$new_content = '<div id="mail-wrapper">'.$new_content.'</div>';
		
	}else if($type == 'from'){
	
		$new_content = str_replace('{title}', $post->post_title, $content);
		$new_content = str_replace('{excerpt}', $post->post_excerpt, $new_content);
		$new_content = str_replace('{url}', $post_url, $new_content);
		$new_content = 'From: ' . $new_content;
		
	}else if($type == 'subject'){
	
		$new_content = str_replace('{title}', $post->post_title, $content);
		$new_content = str_replace('{excerpt}', $post->post_excerpt, $new_content);
		$new_content = str_replace('{url}', $post_url, $new_content);
		
	}else{
		$new_content = '';
	}
	
	return $new_content;
}


/*
*  Send email to all users in the checked roles
*
*  @description:
*  @since 1
*  @created: 2013-05-16
*/	
add_action( 'save_post', 'smr_send_email', 10, 2 );
function smr_send_email($post_id, $post){
	
	//verify post is not a revision
	if ( !wp_is_post_revision( $post_id ) ) {
		
		if(!empty($_POST['roles'])){ //we don't need to do nothing unless some roles are set
		
			$post_url = get_permalink( $post_id );
			
			//Get saved template info
			$content = get_option('smr_settings_group');
			//set up subject
			$subject = $content['mail_subject'];
			$subject = smr_mail_template($post, $post_url, $subject, 'subject');
			//Set up headers
			$from = $content['mail_from'];
			$from = smr_mail_template($post, $post_url, $from, 'from');
			//set up message
			$mail_content = $content['mail_content'];
			$mail_content = smr_mail_template($post, $post_url, $mail_content, 'content');
			
			
			$mail_style = '<style>body{ background:#ecebeb; padding:20px; } h1{ color:#808080; } #mail-wrapper{ padding:10px 10px 10px 30px; background:#FFF; border-radius:10px; -moz-border-radius:10px; -webkit-border-radius:10px; -webkit-box-shadow:  1px 2px 4px 0px rgba(0, 0, 0, 0.3); box-shadow:  1px 2px 4px 0px rgba(0, 0, 0, 0.3); } </style>';
			$message = $mail_style.$mail_content;
	
			//Set up array of emails
			$selected_roles = $_POST['roles'];
			$user_array = array();
			foreach($selected_roles as $single_role){ //loop through all selected roles
				$users = get_users(array('role' => $single_role, 'fields' => array('user_email')));
				foreach($users as $single_user){
					$user_array[] = $single_user->user_email;
				}
			}
			
			//Send it!
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
			wp_mail($user_array, $subject, $message, $from);
		}
		
		
	}
	
}

/*
*  Change content type to HTML
*
*  @description:
*  @since 1
*  @created: 2013-05-16
*/
function smr_set_html_content_type()
{
	return 'text/html';
}


?>
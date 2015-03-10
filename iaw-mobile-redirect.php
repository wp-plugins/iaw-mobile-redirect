<?php
/*
Plugin Name: IAW Mobile Redirect
Description: Select a mobile web app to point mobile users to
Author: LaPorte Consulting, LLC
Version: 2.0.0
Author URI: http://laporteconsult.com/
*/

/*	Copyright 2015 LaPorte Consulting, LLC (email : info@laporteconsult.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

*/

$ios_mobile_redirect = new IAW_Mobile_Redirect();

register_uninstall_hook( __FILE__, 'uninstall_iaw_mobile_redirect' );
function uninstall_iaw_mobile_redirect() {
	delete_option( 'iawmobileredirectapp' );
	delete_option( 'iawmobileredirecttoggle' );
	delete_option( 'iawmobileredirecttablet' );
}

class IAW_Mobile_Redirect{

	function __construct() { //init function
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'wp_head', array( &$this, 'iaw_mobile_redirect' ), 1 );
		add_action( 'wp_footer', array( &$this, 'iaw_add_footer_link' ), 1);
        remove_filter( 'wp_footer', 'strip_tags' );
		if ( get_option( 'iawmobileredirecttoggle' ) == 'true' )
			update_option( 'iawmobileredirecttoggle', true );
	}

	function admin_init() {
		add_filter( 'plugin_action_links_'. plugin_basename( __FILE__ ), array( &$this, 'plugin_action_links' ), 10, 4 );
	}

	function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		if ( is_plugin_active( $plugin_file ) )
			$actions[] = '<a href="' . admin_url('options-general.php?page=iaw-mobile-redirect/iaw-mobile-redirect.php') . '">Configure</a>';
		return $actions;
	}

	function admin_menu() {
		add_submenu_page( 'options-general.php', __( 'IAW Mobile Redirect', 'iaw-mobile-redirect' ), __( 'IAW Mobile Redirect', 'iaw-mobile-redirect' ), 'administrator', __FILE__, array( &$this, 'page' ) );
	}

	function page() { //admin options page
 
		//do stuff if form is submitted
		if ( isset( $_POST['mobileapp'] ) ) {
			update_option( 'iawmobileredirectapp', $_POST['mobileapp'] );
			update_option( 'iawmobileredirecttoggle', isset( $_POST['mobiletoggle'] ) ? true : false );
			update_option( 'iawmobileredirecttablet', isset( $_POST['mobileredirecttablet'] ) );

			echo '<div class="updated"><p>' . __( 'Updated', 'iaw-mobile-redirect' ) . '</p></div>';
		}

		?>
		<div class="wrap"><h2><?php _e( 'IAW Mobile Redirect', 'iaw-mobile-redirect' ); ?></h2>
		<p>
			<?php _e( 'If the checkbox is checked, and a valid Mobile App Code is inputted, this site will redirect to the specified Mobile Web App when visited by a mobile device.', 'iaw-mobile-redirect' ); ?>
		</p>

		<form method="post">
		<p>
			<label for="mobiletoggle"><?php _e( 'Enable Redirect:', 'iaw-mobile-redirect' ); ?>
			<input type="checkbox" value="1" name="mobiletoggle" id="mobiletoggle" <?php checked( get_option('iawmobileredirecttoggle', ''), 1 ); ?> /></label>
		</p>
		<p>
			<label for="mobileapp"><?php _e( 'Mobile App Code:', 'iaw-mobile-redirect' ); ?>
			<input type="text" name="mobileapp" id="mobileapp" value="<?php echo get_option('iawmobileredirectapp', ''); ?>" /></label>
		</p>
		<p>
			<label for="mobileredirecttablet"><?php _e( 'Redirect Tablets:', 'iaw-mobile-redirect' ); ?>
			<input type="checkbox" value="1" name="mobileredirecttablet" id="mobileredirecttablet" <?php checked( get_option('iawmobileredirecttablet', ''), 1 ); ?> /></label>
		</p>
			<?php submit_button(); ?>
		</form>
		</div>

<!--		<div class="copyFooter">Plugin written by <a href="http://laporteconsult.com">LaPorte Consulting, LLC</a>.</div>-->
		<?php
	}

	function iaw_mobile_redirect() {
		//check if tablet box is checked
		if( get_option('iawmobileredirecttablet') == 0){
			//redirect phone only
			$script_name = "phone";
		} else {
			// redirect phone and tablet
			$script_name = "phoneandtablet";
		}

		// not enabled
		if ( ! get_option('iawmobileredirecttoggle') )
			return;

		$app_code = get_option('iawmobileredirectapp', '');
		// empty url
		if ( empty( $app_code ) )
			return;

		if ( strpos($app_code,'/') ) {
			$slashpos = strpos($app_code,'/');
			$full_code = $app_code;
			$app_code =  substr($full_code, $slashpos + 1);
			$code_path = substr($full_code, 0, $slashpos);
		} else {
			$code_path = 'www.1tap.mobi';
		}

?>
<script language="javascript">var mobileId = "<?php echo $app_code ?>";</script> <script type="text/javascript" src="http://<?php echo $code_path ?>/include/js/external/<?php echo $script_name ?>.js"></script> 


<?	}

	function iaw_add_footer_link() {
		// not enabled
		if ( ! get_option('iawmobileredirecttoggle') )
			return;

		$app_code = get_option('iawmobileredirectapp', '');
		// empty url
		if ( empty( $app_code ) )
			return;
			
		if ( !strpos($app_code,'/') ) {
			$app_code = 'http://www.1tap.mobi/' . $app_code ;
		}
		
		if (isset($_COOKIE['mobile_dontredirect']) && $_COOKIE['mobile_dontredirect'] == true){
			echo '<div align="center"> <span style="z-index: 10; color: rgba(0,0,0,1); background-color: rgba(255,255,255,1); border-radius: 5px; padding-top: 5px; padding-right: 10px; padding-bottom: 5px; padding-left: 10px;"><a href="'.$app_code.'">Mobile Site</a></span></div>';
		}
	}
	
}

<?php

// Make sure Gravity Forms is active and already loaded.
if ( ! class_exists( 'GFForms' ) ) {
	die();
}

// The Add-On Framework is not loaded by default.
GFForms::include_feed_addon_framework();

class GF_MotoPress_Demo extends GFFeedAddon {

	protected $_multiple_feeds = false;

	// The following class variables are used by the Framework.
	// They are defined in GFAddOn and should be overridden.

	// The version number is used for example during add-on upgrades.
	protected $_version = GF_MOTOPRESS_DEMO_VERSION;

	// The Framework will display an appropriate message on the plugins page if necessary
	protected $_min_gravityforms_version = '2.0';

	// A short, lowercase, URL-safe unique identifier for the add-on.
	// This will be used for storing options, filters, actions, URLs and text-domain localization.
	protected $_slug = 'gf-motopress';

	// Relative path to the plugin from the plugins folder.
	protected $_path = 'gf-motopress/gravityview-customize-motopress.php';

	// Full path the the plugin.
	protected $_full_path = __FILE__;

	// Title of the plugin to be used on the settings page, form settings and plugins page.
	protected $_title = 'Gravity Forms MotoPress Demo Builder';

	// Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
	protected $_short_title = 'MotoPress Demo';

	protected $_capabilities = array(
		'gravityformsmotopress_uninstall',
		'gravityformsmotopress_settings',
		'gravityformsmotopress_form_settings',
	);

	protected $_capabilities_settings_page = 'gravityformsmotopress_settings';
	protected $_capabilities_form_settings = 'gravityformsmotopress_form_settings';
	protected $_capabilities_uninstall = 'gravityformsmotopress_uninstall';
	protected $_enable_rg_autoupgrade = true;

	private static $_instance = null;

	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new GF_MotoPress_Demo();
		}

		return self::$_instance;
	}
	
	/**
	 * Override this function to customize the form settings icon
	 * 
	 * @since 1.0.1
	 */
	public function get_menu_icon() {
		return 'dashicons-networking';
	}

	/**
	 * Prepare settings to be rendered on feed settings tab.
	 *
	 * @since 1.0
	 *
	 * @return array $fields - The feed settings fields
	 */
	public function feed_settings_fields() {
		return array(
			array(
				'fields'     => array(
					array(
						'name'     => 'site',
						'label'    => 'Site to Clone',
						'type'     => 'select',
						'choices'  => $this->get_network_site_choices(),
						'required' => true,
					),
					array(
						'name'      => 'fields',
						'label'     => __( 'Map Fields', 'gf-motopress' ),
						'type'      => 'field_map',
						'field_map' => array(
							array(
								'name'       => 'email_address',
								'label'      => __( 'Email Address', 'gf-motopress' ),
								'required'   => true,
								'field_type' => array( 'email' ),
							),
						),
						'tooltip'   => '<h6>' . __( 'Map Fields', 'gf-motopress' ) . '</h6>' . __( 'Select which Gravity Form fields pair with their respective Constant Contact fields.', 'gf-motopress' ),
					),
					array(
						'name'    => 'feed_condition',
						'label'   => __( 'Conditional Logic', 'gf-motopress' ),
						'type'    => 'feed_condition',
						'tooltip' => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Conditional Logic', 'gf-motopress' ),
							esc_html__( 'When conditional logic is enabled, form submissions will only be exported to Constant Contact when the conditions are met. When disabled all form submissions will be exported.', 'gf-motopress' )
						),

					),
				),
			),
		);
	}

	/**
	 * Returns a list of network sites
	 *
	 * @return array label/value pairs for each public site on the network.
	 */
	private function get_network_site_choices() {

		$sites = get_sites( array( 'fields' => 'ids', 'public' => 1 ) );

		$choices = array();

		foreach ( $sites as $site_id ) {
			$choices[] = array(
				'label' => get_blog_option( $site_id, 'blogname' ) . ' (' . $site_id . ')',
				'value' => $site_id,
			);
		}

		return $choices;
	}

	/**
	 * Processes the feed, subscribes the user to the list.
	 *
	 * @since 1.0
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return array|null Returns a modified entry object or null.
	 */
	public function process_feed( $feed, $entry, $form ) {

		$_post_backup = $_POST;

		$email_field_id = rgars( $feed, 'meta/fields_email_address' );
		$email_value = $this->get_field_value( $form, $entry, $email_field_id );

		$_POST = array(
			'mp_demo_create_sandbox' => 1,
			'mp_source_id' => (int) rgars( $feed, 'meta/site' ), // Sandbox site ID to clone for created site
			'mp_email' => $email_value, // email
			'action' => 'route_url',
			'mp_demo_action' => 'send_response',
			'controller' => 'mail',
			'mp_demo_url' => rgar( $entry, 'source_url', site_url() ),
			'security' => wp_create_nonce('mp-ajax-nonce'),
		);

		$result = motopress_demo\classes\Shortcodes::get_instance()->send_invintation();

		$_POST = $_post_backup;

		return $entry;
	}
}

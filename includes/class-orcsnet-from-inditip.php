<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class OrcsNet_from_IndiTip {

	/**
	 * The single instance of OrcsNet_from_IndiTip.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'OrcsNet_from_inditip';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new OrcsNet_from_IndiTip_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();

		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
		add_action( 'init', array( $this, 'setup_plugin' ), 10 );

	} // End __construct ()

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new OrcsNet_from_IndiTip_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new OrcsNet_from_IndiTip_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'orcsnet-from-inditip', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'orcsnet-from-inditip';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main OrcsNet_from_IndiTip Instance
	 *
	 * Ensures only one instance of OrcsNet_from_IndiTip is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see OrcsNet_from_IndiTip()
	 * @return Main OrcsNet_from_IndiTip instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Register shortcodes and (conditionally) the_content hook
	 * @access  public
	 * @since   1.0.0 Conditional on value of "orcsnet_placement" setting
	 * @since   1.0.2 Register shortcodes
	 * @return  void
	 */
	public function setup_plugin () {
		add_shortcode( 'orc', array( $this, 'insert_widget' ) );
		add_shortcode( 'inditip', array( $this, 'insert_widget' ) );
		$placement = get_option( 'orcsnet_placement' );
		// error_log ( 'called setup_plugin: placement=' . $placement );
		if( $placement == 'end' || $placement == 'on' ) {
			add_filter( 'the_content', array( $this, 'append_to_post' ) );
		}
	} // End setup_plugin ()

	/**
	 * Append widget to post
	 * @access  public
	 * @since   1.0.2 invoke do_shortcode on the $content
	 * @since   1.0.3 don't auto-append to pages (shortcode still works)
	 * @return  void
	 */
	public function append_to_post ( $content ) {
		// error_log ( 'called append_to_post' );
		$widget_html = is_page() ? '' : $this->insert_widget( );
		$parsed_content = do_shortcode( $content );
		return $parsed_content . $widget_html;
	}

	/**
	 * Conditionally generate iframe html
	 * @access  public
	 * @since   1.0.2
	 * @since   1.0.3 shortcode now accepts attributes
	 * @return  String
	 */
	public function insert_widget ( $atts = [], $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array)$atts, CASE_LOWER );
 
	    // override default attributes with user attributes
    	$orc_atts = shortcode_atts(['variant' => null], $atts, $tag);
 
		// error_log ( 'called insert_widget' );
		// error_log ( 'is_single=' . is_single() . ',in_the_loop=' . in_the_loop() . ',is_main_query=' . is_main_query() );
		$widget_html = (is_main_query() && in_the_loop( )) ? $this->generate_widget_html( $orc_atts ) : '';
		// error_log ( $widget_html );
		return ( $widget_html );
	} // End insert_widget ()

	/**
	 * Construct iframe html
	 * @access  private
	 * @since   1.0.0
	 * @since   1.0.2 Renamed from generate_iframe_html to generate_widget_html
	 * @since   1.0.3 Pass shortcode 'variant' parameter through if available
	 * @since   1.0.10 'variant' now in config
	 * @return  HTML for IndiTip button's iframe
	 */
	private function generate_widget_html ( $atts ) {
		// IndiTip needs these to register the tip
		$userid = get_option( 'orcsnet_userid' );
		$publisher_lock = get_option( 'orcsnet_publisher_lock' );
		$variant = trim(get_option( 'orcsnet_variant' ));

		$src = 'https://plugin.orcsnet.com/';
		if ($publisher_lock == 'on') {
			$src .= $userid . '/';
		}
		$src .= '?o=' . urlencode( $userid );
		// $src .= '&wt=' . urlencode( get_the_title( ) );
		// $src .= '&wa=' . urlencode( get_the_author( ) );
		$src .= '&wl=' . urlencode( get_permalink( ) );
		$src .= '&pv=' . urlencode( $this->_version );

		// variant from shortcode gets precedence
		if ($atts['variant'] != null) {
			$src .= '&variant=' . urlencode($atts['variant']);

		// variant in config
		} else if ($variant != '') {
			$src .= '&variant=' . urlencode($variant);
		}

		// Style the inditip box
		$style = 'border:none;border-radius:0px;margin:0px;width:100%;';

		// sandbox="allow-top-navigation-by-user-activation" ..?

		$iframe_id = uniqid();
		$src .= '&ifid=' . $iframe_id;

		$orcbox = '<iframe ';
		$orcbox .= ' id="' . $iframe_id . '"';
		$orcbox .= ' src="' . $src . '"';
		$orcbox .= ' style="' . $style . '"';
		$orcbox .= '>';
		$orcbox .= '</iframe>';

		return $orcbox;

	} // End generate_widget_html ()

}

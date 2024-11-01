<?php /*

**************************************************************************

Plugin Name:  WordPress Download Counter
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/wordpress-download-counter/
Version:      1.0.2
Description:  Show the number of WordPress downloads on your site! Requires WordPress 2.8+ and PHP 5.2.0+.
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

TODO:

* Settings page
* Add shortcode and/or function for non-widget use
* Add phpdocs

**************************************************************************/

// Start up the plugin class
add_action( 'plugins_loaded', 'WordPress_Download_Counter' );
function WordPress_Download_Counter() { global $WordPressDownloadCounter; $WordPressDownloadCounter = new WordPress_Download_Counter(); }


// The non-widget part of the plugin
class WordPress_Download_Counter {
	public $cachetime = 15; // Seconds between AJAX powered refreshes

	public function __construct() {
		// PHP and WordPress version check
		if ( !function_exists('json_decode') || !function_exists('esc_html') )
			return;

		wp_enqueue_script('jquery');

		// Hooks!
		add_action( 'init',         array(&$this, 'localization_start') );
		add_action( 'init',         array(&$this, 'maybe_ajax') );
		add_action( 'widgets_init', array(&$this, 'register_widget') );
		add_action( 'wp_head',      array(&$this, 'ajax_javascript') );
	}


	// Start up localization. In it's own function so it can run at "init" for translation plugins.
	public function localization_start() {
		load_plugin_textdomain( 'wordpress-download-counter', false, '/wordpress-download-counter/localization' );
	}


	// Register the widget
	public function register_widget() {
		register_widget( 'WordPress_Download_Counter_Widget' );
	}


	// Get the stats. This is sourced either from a cache or a remote request.
	public function get_data() {
		// Check for a cached copy (we don't want to do an HTTP request too often)
		$cache = get_transient('wpdlcounter');
		if ( false !== $cache )
			return $cache;

		$data = array();

		// Fetch the data
		if ( $response = wp_remote_retrieve_body( wp_remote_get( 'http://wordpress.org/download/counter/?json=1' ) ) ) {
			// Decode the json response
			if ( $response = json_decode( $response, true ) ) {
				// Double check we have all our data
				if ( !empty($response['wpcounter']) && !empty($response['wpcounter']['branch']) && !empty($response['wpcounter']['downloads']) ) {
					$data = $response['wpcounter'];
				}
			}
		}
		// On a failed scrape, cache that fail for a full minute
		else {
			set_transient( 'wpdlcounter', $data, 60 );
		}

		// Cache the data for future usage
		if ( $this->cachetime < 2 )
			$this->cachetime = 2;
		set_transient( 'wpdlcounter', $data, $this->cachetime - 1 );

		return $data;
	}


	// Maybe handle AJAX request based on a GET variable
	public function maybe_ajax() {
		if ( empty($_GET['wpdlcounter']))
			return;

		nocache_headers();

		$data = $this->get_data();

		if ( !empty($data['downloads']) )
			echo number_format_i18n( $data['downloads'] ); 

		exit();
	}


	// Output the AJAX Javascript for the head
	public function ajax_javascript() { ?>
	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready(function($){
			if ( $(".wpdlcounter").length > 0 ) {
				var wpdlcounter_refreshes = 0;
				var wpdlcounter = setInterval(function() {
					wpdlcounter_refreshes = wpdlcounter_refreshes + 1;
					if ( wpdlcounter_refreshes > 20 )
						return;
					$.get("<?php echo esc_js( add_query_arg( 'wpdlcounter', 1, get_bloginfo('wpurl') . '/wp-load.php' ) ); ?>", function(count) {
						if ( count )
							$(".wpdlcounter").fadeOut("fast", function() {
								$(".wpdlcounter").html( count );
								$(".wpdlcounter").fadeIn("slow");
							});
					});
				}, <?php echo $this->cachetime * 1000; ?> );
			}
		});
	//]]>
	</script>
<?php
	}
}


// The widget class
class WordPress_Download_Counter_Widget extends WP_Widget {

	// Contruct the widget
	function WordPress_Download_Counter_Widget() {
		$widget_ops = array( 'classname' => 'widget_wpdlcounter', 'description' => __( 'Show the total number of downloads for the current version of WordPress.', 'wordpress-download-counter' ) );
		parent::WP_Widget( 'wpdlcounter', __( 'WP Downloads', 'wordpress-download-counter' ), $widget_ops );
	}

	// Output the widget
	function widget($args, $instance) {
		global $WordPressDownloadCounter;

		extract( $args );

		if ( empty($instance['title']) )
			$instance['title'] = __( 'WordPress Downloads', 'wordpress-download-counter' );

		?>
			<?php echo $before_widget; ?> 
				<?php echo $before_title . $instance['title'] . $after_title; ?> 
				<p><?php
					$data = $WordPressDownloadCounter->get_data();

					if ( empty($data) )
						echo '<em>' . __( 'Error fetching download statistics.', 'wordpress-download-counter' ) . '</em>';
					else
						printf( __( 'WordPress %1$s has been downloaded %2$s times.', 'wordpress-download-counter' ), $data['branch'], '<span class="wpdlcounter">' . number_format_i18n( $data['downloads'] ) . '</span>' );
				?></p>
			<?php echo $after_widget; ?> 
		<?php
	}

	// Handle settings form submits
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	// The settings form
	function form($instance) {
		$title = ( isset($instance['title']) ) ? esc_attr($instance['title']) : '';
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		<?php 
	}
}

?>
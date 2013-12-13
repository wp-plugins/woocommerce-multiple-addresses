<?php
/**
 * Woocommerce Multiple Addresses plugin.
 *
 * @package   WC_Multiple_addresses
 * @author    Alexander Tinyaev <alexander.tinyaev@n3wnormal.com>
 * @license   GPL-2.0+
 * @link      http://n3wnormal.com
 * @copyright 2013 N3wNormal
 */

/**
 * Plugin class.
 *
 * @package WC_Multiple_addresses
 * @author  Alexander Tinyaev <alexander.tinyaev@n3wnormal.com>
 */
class WC_Multiple_addresses {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.2
	 *
	 * @var     string
	 */
	protected $version = '1.0.2';

	/**
	 * Unique identifier for the plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'woocommerce-multiple-addresses';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * $lang variable for future releases
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	public static $lang = array(
		'notification'  => 'If you have more than one shipping address, then you may choose a default one here.',
		'btn_items'     => 'Configure Address'
	);

	/**
	 * Initialize the plugin by setting filters and administration functions.
	 *
	 * @since     1.0.2
	 */
	private function __construct() {

		// Load public-facing style sheet.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_action( 'woocommerce_before_my_account', array( $this, 'rewrite_edit_url_on_my_account' ), 25 );

		add_shortcode( 'woocommerce_multiple_shipping_addresses', array( $this, 'multiple_shipping_addresses' ) );

		add_action( 'template_redirect', array( $this, 'save_multiple_shipping_addresses' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'before_checkout_form' ) );

		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'save_shipping_as_default' ));
		add_action( 'woocommerce_created_customer', array( $this, 'created_customer_save_shipping_as_default' ));

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		global $woocommerce;

		$page_id = woocommerce_get_page_id( 'multiple_shipping_addresses' );

		if ($page_id == -1) {
			// get the checkout page
			$account_id = woocommerce_get_page_id( 'myaccount' );

			// add page and assign
			$page = array(
				'menu_order'        => 0,
				'comment_status'    => 'closed',
				'ping_status'       => 'closed',
				'post_author'       => 1,
				'post_content'      => '[woocommerce_multiple_shipping_addresses]',
				'post_name'         => 'multiple-shipping-addresses',
				'post_parent'       => $account_id,
				'post_title'        => 'Manage Multiple Addresses',
				'post_type'         => 'page',
				'post_status'       => 'publish',
				'post_category'     => array(1)
			);

			$page_id = wp_insert_post($page);

			update_option( 'woocommerce_multiple_shipping_addresses_page_id', $page_id);
		}
	}


	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}


	/**
	 * Point edit address button on my account to edit multiple shipping addresses
	 *
	 * @since    1.0.2
	 */
	public function rewrite_edit_url_on_my_account() {
		$page_id = woocommerce_get_page_id( 'multiple_shipping_addresses' );
		$site_url = home_url('/');
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('.woocommerce .col2-set.addresses .col-2 .title a').attr('href', '<?php echo $site_url . "?page_id=" . $page_id; ?>');
			});
		</script>
	<?php
	}

	/**
	 * Multiple shipping addresses page
	 *
	 * @since    1.0.2
	 */
	public function multiple_shipping_addresses() {
		global $woocommerce;
		require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';

		$checkout   = new WC_Order();
		$user       = wp_get_current_user();

		$shipFields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );
		$shipFields['shipping_city']['label'] = $shipFields['shipping_city']['placeholder'] = "City";

		if ($user->ID == 0) return;

		$otherAddr = get_user_meta($user->ID, 'wc_multiple_shipping_addresses', true);
		echo '<div class="woocommerce">';
		echo '<form action="" method="post" id="address_form">';
		if (! empty($otherAddr)) {
			echo '<div id="addresses">';

			foreach ($otherAddr as $idx => $address) {
				echo '<div class="shipping_address address_block" id="shipping_address_'. $idx .'">';
				echo '<p align="right"><a href="#" class="button delete">delete</a></p>';
				do_action( 'woocommerce_before_checkout_shipping_form', $checkout);

				foreach ($shipFields as $key => $field) {
					$val = '';

					if (isset($address[$key])) {
						$val = $address[$key];
					}
					$key .= '[]';
					woocommerce_form_field( $key, $field, $val );
				}

				$is_checked = $address['shipping_address_is_default'] == 'true' ? "checked" : "";
				echo '<input type="checkbox" class="default_shipping_address" '.$is_checked.' value="'. $address['shipping_address_is_default'] .'"> Mark this shipping address as default';
				echo '<input type="hidden" class="hidden_default_shipping_address" name="shipping_address_is_default[]" value="'. $address['shipping_address_is_default'] .'" />';

				do_action( 'woocommerce_after_checkout_shipping_form', $checkout);
				echo '</div>';
			}
			echo '</div>';
		} else {

			$shipping_address = array(
				'shipping_first_name' 	=> get_user_meta( $user->ID, 'shipping_first_name', true ),
				'shipping_last_name'		=> get_user_meta( $user->ID, 'shipping_last_name', true ),
				'shipping_company'		=> get_user_meta( $user->ID, 'shipping_company', true ),
				'shipping_address_1'		=> get_user_meta( $user->ID, 'shipping_address_1', true ),
				'shipping_address_2'		=> get_user_meta( $user->ID, 'shipping_address_2', true ),
				'shipping_city'			=> get_user_meta( $user->ID, 'shipping_city', true ),
				'shipping_state'			=> get_user_meta( $user->ID, 'shipping_state', true ),
				'shipping_postcode'		=> get_user_meta( $user->ID, 'shipping_postcode', true ),
				'shipping_country'		=> get_user_meta( $user->ID, 'shipping_country', true )
			);

			echo '<div id="addresses">';
			foreach ($shipFields as $key => $field) :
				$val = $shipping_address[$key];
				$key .= '[]';

				woocommerce_form_field( $key, $field, $val );
			endforeach;

			echo '<input type="checkbox" class="default_shipping_address" checked value="true"> Mark this shipping address as default';
			echo '<input type="hidden" class="hidden_default_shipping_address" name="shipping_address_is_default[]" value="true" />';

			echo '</div>';
		}
		echo '<div class="form-row">
                <input type="hidden" name="shipping_account_address_action" value="save" />
                <input type="submit" name="set_addresses" value="'. __( 'Save Addresses', 'wc_multiple_shipping_addresses' ) .'" class="button alt" />
                <a class="button add_address" href="#">'. __( 'Add another', 'wc_multiple_shipping_addresses' ) .'</a>
            </div>';
		echo '</form>';
		echo '</div>';
		?>
		<script type="text/javascript">
			var tmpl = '<div class="shipping_address address_block"><p align="right"><a href="#" class="button delete">delete</a></p>';

			tmpl += '\
        <?php
        foreach ($shipFields as $key => $field) :
            $key .= '[]';
            $val = '';
            $field['return'] = true;
            $row = woocommerce_form_field( $key, $field, $val );
            echo str_replace("\n", "\\\n", $row);
        endforeach;
        ?>
			';

			tmpl += '<input type="checkbox" class="default_shipping_address" value="false"> Mark this shipping address as default';
			tmpl += '<input type="hidden" class="hidden_default_shipping_address" name="shipping_address_is_default[]" value="false" />';
			tmpl += '</div>';
			jQuery(".add_address").click(function(e) {
				e.preventDefault();

				jQuery("#addresses").append(tmpl);

				jQuery('html,body').animate({
							scrollTop: jQuery('#addresses .shipping_address:last').offset().top},
						'slow');
			});

			jQuery(".delete").live("click", function(e) {
				e.preventDefault();
				jQuery(this).parents("div.address_block").remove();
			});

			jQuery(document).ready(function() {

				jQuery(document).on("click", ".default_shipping_address", function(){
					if (this.checked) {
						jQuery("input.default_shipping_address").not(this).removeAttr("checked");
						jQuery("input.default_shipping_address").not(this).val("false");
						jQuery("input.hidden_default_shipping_address").val("false");
						jQuery(this).next().val('true');
						jQuery(this).val('true');
					}
					else {
						jQuery("input.default_shipping_address").val("false");
						jQuery("input.hidden_default_shipping_address").val("false");
					}
				});

				jQuery("#address_form").submit(function() {
					var valid = true;
					jQuery("input[type=text],select").each(function() {
						if (jQuery(this).prev("label").children("abbr").length == 1 && jQuery(this).val() == "") {
							jQuery(this).focus();
							valid = false;
							return false;
						}
					});
					return valid;
				});
			});
		</script>
	<?php
	}

	/**
	 * Save multiple shipping addresses
	 *
	 * @since    1.0.0
	 */
	public function save_multiple_shipping_addresses() {
		global $woocommerce;

		require_once $woocommerce->plugin_path() .'/classes/class-wc-checkout.php';
		$woocommerce->checkout = new WC_Checkout();
		$checkout   = $woocommerce->checkout;
		//$fields = apply_filters( 'woocommerce_shipping_fields', array() );
		$fields = $woocommerce->countries->get_address_fields( $woocommerce->countries->get_base_country(), 'shipping_' );

		if (isset($_POST['shipping_account_address_action']) && $_POST['shipping_account_address_action'] == 'save' ) {
			unset($_POST['shipping_account_address_action']);

			$addresses = array();
			$is_default = false;
			foreach ($_POST as $key => $values) {
				if ($key == 'shipping_address_is_default') {
					foreach ($values as $idx => $val) {
						if ($val == 'true') {
							$is_default = $idx;
						}
					}
				}
				if (! is_array($values)){
					continue;
				}

				foreach ($values as $idx => $val) {
					$addresses[$idx][$key] = $val;
				}
			}

			$user = wp_get_current_user();

			if ($is_default !== false) {
				$default_address = $addresses[$is_default];
				foreach ($default_address as $key => $field) :
					if($key == 'shipping_address_is_default') {
						continue;
					}
					update_user_meta( $user->ID, $key, $field );
				endforeach;
			}

			update_user_meta($user->ID, 'wc_multiple_shipping_addresses', $addresses);
			$woocommerce->add_message(__( 'Addresses have been saved', 'wc_shipping_multiple_address' ) );
			$page_id = woocommerce_get_page_id( 'myaccount' );
			wp_redirect(get_permalink($page_id));
			exit;
		}
	}

	/**
	 * Add possibility to configure addresses on checkout page
	 *
	 * @since    1.0.0
	 */
	public function before_checkout_form() {
		global $woocommerce;

		$page_id = woocommerce_get_page_id( 'multiple_shipping_addresses' );
		if ( is_user_logged_in() ) {
			echo '<p class="woocommerce-info woocommerce_message">
	                '. self::$lang['notification'] .'
	                <a class="button" href="'. get_permalink($page_id) .'">'. self::$lang['btn_items'] .'</a>
	              </p>';
		}
	}

	/**
	 * Find the default shipping address amongst all shipping addresses
	 *
	 * @since    1.0.0
	 *
	 * @param    array    $array
	 * @param    string    $key
	 * @param    string    $value
	 * @return   string
	 */
	public function search($array, $key, $value) {
		$result = false;

		if (is_array($array))
		{
			if (isset($array[$key]) && $array[$key] == $value)
				$result = $key;
			else
				foreach ($array as $id => $subarray) {
					if ($this->search($subarray, $key, $value) == $key)
						$result = $id;
				}
		}

		return $result;
	}

	/**
	 * Save specific shipping as default one to use on checkout
	 *
	 * @since    1.0.0
	 *
	 * @param    string    $post
	 */
	public function save_shipping_as_default($post) {
		parse_str($post, $output);
		$user = wp_get_current_user();
		$addresses = get_user_meta($user->ID, 'wc_multiple_shipping_addresses');
		if ( isset($addresses[0]) ) {
			$addresses = $addresses[0];
			$array_id = $this->search($addresses, 'shipping_address_is_default', 'true');
		}
		else {
			$array_id = '0';
		}

		foreach ($output as $key => $value) {
			if ($key == 'shipping_country' ||
					$key == 'shipping_first_name' ||
					$key == 'shipping_last_name' ||
					$key == 'shipping_company' ||
					$key == 'shipping_address_1' ||
					$key == 'shipping_address_2' ||
					$key == 'shipping_city' ||
					$key == 'shipping_state' ||
					$key == 'shipping_postcode' ){

				$addresses[$array_id][$key] = $value;
			}
		}

		$addresses[$array_id]['shipping_address_is_default'] = 'true';
		update_user_meta($user->ID, 'wc_multiple_shipping_addresses', $addresses);
	}

	/**
	 * Creating the same default shipping for newly created customer
	 *
	 * @since    1.0.0
	 *
	 * @param    integer    $current_user_id
	 */
	public function created_customer_save_shipping_as_default($current_user_id) {
		global $woocommerce;
		if ($current_user_id == 0)
			return;

		$checkout = $woocommerce->checkout->posted;
		$default_address = array();
		if ($checkout['shiptobilling'] == 0) {
			$default_address[0]['shipping_country'] = $checkout['shipping_country'];
			$default_address[0]['shipping_first_name'] = $checkout['shipping_first_name'];
			$default_address[0]['shipping_last_name'] = $checkout['shipping_last_name'];
			$default_address[0]['shipping_company'] = $checkout['shipping_company'];
			$default_address[0]['shipping_address_1'] = $checkout['shipping_address_1'];
			$default_address[0]['shipping_address_2'] = $checkout['shipping_address_2'];
			$default_address[0]['shipping_city'] = $checkout['shipping_city'];
			$default_address[0]['shipping_state'] = $checkout['shipping_state'];
			$default_address[0]['shipping_postcode'] = $checkout['shipping_postcode'];
		} elseif ($checkout['shiptobilling'] == 1) {
			$default_address[0]['shipping_country'] = $checkout['billing_country'];
			$default_address[0]['shipping_first_name'] = $checkout['billing_first_name'];
			$default_address[0]['shipping_last_name'] = $checkout['billing_last_name'];
			$default_address[0]['shipping_company'] = $checkout['billing_company'];
			$default_address[0]['shipping_address_1'] = $checkout['billing_address_1'];
			$default_address[0]['shipping_address_2'] = $checkout['billing_address_2'];
			$default_address[0]['shipping_city'] = $checkout['billing_city'];
			$default_address[0]['shipping_state'] = $checkout['billing_state'];
			$default_address[0]['shipping_postcode'] = $checkout['billing_postcode'];
		}
		$default_address[0]['shipping_address_is_default'] = 'true';
		update_user_meta($current_user_id, 'wc_multiple_shipping_addresses', $default_address);
	}
}
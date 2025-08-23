<?php
/**
 * Plugin Name: Business Proposals
 * Plugin URI: https://github.com/Frenziecodes/proposals
 * Description: Create, manage, and send professional business proposals directly from your WordPress dashboard. Features secure proposal viewing, client management, and status tracking.
 * Version: 1.0.0
 * Author: Lewis Ushindi
 * Author URI: https://github.com/frenziecodes
 * Text Domain: proposals
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Business Proposals is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Business Proposals is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WPBP_VERSION', '1.0.0' );
define( 'WPBP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPBP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPBP_PLUGIN_FILE', __FILE__ );

// Include common global functions.
require_once WPBP_PLUGIN_DIR . 'includes/functions.php';

/**
 * Main WP Business Proposals Class
 *
 * @since 1.0.0
 */
class WP_Business_Proposals {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var WP_Business_Proposals
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Initialize immediately, not just on init hook
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		// Register post type early - this is critical for URL recognition
		add_action( 'init', array( $this, 'register_post_type' ), 0 );
		
		// Initialize other features after post type is registered
		add_action( 'init', array( $this, 'init_features' ), 10 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 * @return WP_Business_Proposals A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize plugin features (after post type registration)
	 *
	 * @since 1.0.0
	 */
	public function init_features() {
		$this->add_hooks();
		$this->add_admin_features();
	}

	/**
	 * Load plugin textdomain
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'proposals',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Register the Proposal custom post type
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Proposals', 'post type general name', 'proposals' ),
			'singular_name'         => _x( 'Proposal', 'post type singular name', 'proposals' ),
			'menu_name'             => _x( 'Proposals', 'admin menu', 'proposals' ),
			'name_admin_bar'        => _x( 'Proposal', 'add new on admin bar', 'proposals' ),
			'add_new'               => _x( 'Add New', 'proposal', 'proposals' ),
			'add_new_item'          => __( 'Add New Proposal', 'proposals' ),
			'new_item'              => __( 'New Proposal', 'proposals' ),
			'edit_item'             => __( 'Edit Proposal', 'proposals' ),
			'view_item'             => __( 'View Proposal', 'proposals' ),
			'all_items'             => __( 'All Proposals', 'proposals' ),
			'search_items'          => __( 'Search Proposals', 'proposals' ),
			'parent_item_colon'     => __( 'Parent Proposals:', 'proposals' ),
			'not_found'             => __( 'No proposals found.', 'proposals' ),
			'not_found_in_trash'    => __( 'No proposals found in Trash.', 'proposals' ),
			'featured_image'        => __( 'Proposal Cover Image', 'proposals' ),
			'set_featured_image'    => __( 'Set cover image', 'proposals' ),
			'remove_featured_image' => __( 'Remove cover image', 'proposals' ),
			'use_featured_image'    => __( 'Use as cover image', 'proposals' ),
			'archives'              => __( 'Proposal archives', 'proposals' ),
			'insert_into_item'      => __( 'Insert into proposal', 'proposals' ),
			'uploaded_to_this_item' => __( 'Uploaded to this proposal', 'proposals' ),
			'filter_items_list'     => __( 'Filter proposals list', 'proposals' ),
			'items_list_navigation' => __( 'Proposals list navigation', 'proposals' ),
			'items_list'            => __( 'Proposals list', 'proposals' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Business proposals for your clients', 'proposals' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 
				'slug' => 'proposal',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 25,
			'menu_icon'           => 'dashicons-media-document',
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
			'show_in_rest'        => true,
			'rest_base'           => 'proposals',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( 'wpbp_proposal', $args );

		// Register proposal status taxonomy.
		$this->register_proposal_status_taxonomy();
	}

	/**
	 * Register proposal status taxonomy
	 *
	 * @since 1.0.0
	 */
	private function register_proposal_status_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Proposal Status', 'taxonomy general name', 'proposals' ),
			'singular_name'              => _x( 'Status', 'taxonomy singular name', 'proposals' ),
			'search_items'               => __( 'Search Statuses', 'proposals' ),
			'popular_items'              => __( 'Popular Statuses', 'proposals' ),
			'all_items'                  => __( 'All Statuses', 'proposals' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Status', 'proposals' ),
			'update_item'                => __( 'Update Status', 'proposals' ),
			'add_new_item'               => __( 'Add New Status', 'proposals' ),
			'new_item_name'              => __( 'New Status Name', 'proposals' ),
			'separate_items_with_commas' => __( 'Separate statuses with commas', 'proposals' ),
			'add_or_remove_items'        => __( 'Add or remove statuses', 'proposals' ),
			'choose_from_most_used'      => __( 'Choose from the most used statuses', 'proposals' ),
			'not_found'                  => __( 'No statuses found.', 'proposals' ),
			'menu_name'                  => __( 'Status', 'proposals' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'proposal-status' ),
			'show_in_rest'          => true,
		);

		register_taxonomy( 'wpbp_proposal_status', array( 'wpbp_proposal' ), $args );
	}

	/**
	 * Add plugin hooks
	 *
	 * @since 1.0.0
	 */
	private function add_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'single_template', array( $this, 'load_proposal_template' ) );
		
		// Add template redirect for better handling
		add_action( 'template_redirect', array( $this, 'handle_proposal_access' ) );
	}

	/**
	 * Handle proposal access and security
	 *
	 * @since 1.0.0
	 */
	public function handle_proposal_access() {
		if ( is_singular( 'wpbp_proposal' ) ) {
			global $post;
			
			// Check if user has permission to view this proposal
			if ( ! $this->can_user_view_proposal( $post->ID ) ) {
				// Show 404 instead of access denied for security
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				return;
			}
		}
	}

	/**
	 * Add admin-specific features
	 *
	 * @since 1.0.0
	 */
	private function add_admin_features() {
		add_action( 'add_meta_boxes', array( $this, 'add_proposal_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_proposal_meta' ) );
		add_filter( 'manage_wpbp_proposal_posts_columns', array( $this, 'add_proposal_columns' ) );
		add_action( 'manage_wpbp_proposal_posts_custom_column', array( $this, 'populate_proposal_columns' ), 10, 2 );
	}

	/**
	 * Check if user can view proposal
	 *
	 * @since 1.0.0
	 * @param int $proposal_id Proposal ID.
	 * @return bool True if user can view proposal.
	 */
	public function can_user_view_proposal( $proposal_id ) {
		// Allow proposal author and administrators.
		if ( current_user_can( 'edit_post', $proposal_id ) || current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check for public access token (for client viewing).
		$access_token = get_post_meta( $proposal_id, '_wpbp_access_token', true );
		$provided_token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

		return ! empty( $access_token ) && ! empty( $provided_token ) && hash_equals( $access_token, $provided_token );
	}

	/**
	 * Get proposal data
	 *
	 * @since 1.0.0
	 * @param int $proposal_id Proposal ID.
	 * @return array Proposal data.
	 */
	public function get_proposal_data( $proposal_id ) {
		$post = get_post( $proposal_id );
		
		// Process content without using the_content filter to avoid recursion
		$content = $post->post_content;
		
		// Apply basic content filters (but not the_content to avoid our own filter)
		$content = wptexturize( $content );
		$content = wpautop( $content );
		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		
		return array(
			'title'        => get_the_title( $proposal_id ),
			'content'      => $content,
			'client_name'  => get_post_meta( $proposal_id, '_wpbp_client_name', true ),
			'client_email' => get_post_meta( $proposal_id, '_wpbp_client_email', true ),
			'amount'       => get_post_meta( $proposal_id, '_wpbp_proposal_amount', true ),
			'status'       => wp_get_post_terms( $proposal_id, 'wpbp_proposal_status' ),
			'date'         => get_the_date( '', $proposal_id ),
		);
	}

	/**
	 * Render proposal template
	 *
	 * @since 1.0.0
	 * @param array $data Proposal data.
	 * @return string Rendered template.
	 */
	public function render_proposal_template( $data ) {
		ob_start();
		?>
		<div class="wpbp-proposal-wrapper">
			<header class="wpbp-proposal-header">
				<h1 class="wpbp-proposal-title"><?php echo esc_html( $data['title'] ); ?></h1>
				<?php if ( ! empty( $data['client_name'] ) ) : ?>
					<p class="wpbp-client-info">
						<?php
						printf(
							/* translators: %s: client name */
							esc_html__( 'Prepared for: %s', 'proposals' ),
							'<strong>' . esc_html( $data['client_name'] ) . '</strong>'
						);
						?>
					</p>
				<?php endif; ?>
				<p class="wpbp-proposal-date"><?php echo esc_html( $data['date'] ); ?></p>
			</header>

			<main class="wpbp-proposal-content">
				<?php echo wp_kses_post( $data['content'] ); ?>
			</main>

			<?php if ( ! empty( $data['amount'] ) ) : ?>
				<footer class="wpbp-proposal-footer">
					<div class="wpbp-proposal-amount">
						<strong>
							<?php
							printf(
								/* translators: %s: proposal amount */
								esc_html__( 'Total: %s', 'proposals' ),
								esc_html( $data['amount'] )
							);
							?>
						</strong>
					</div>
				</footer>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Load custom template for proposals
	 *
	 * @since 1.0.0
	 * @param string $template Template path.
	 * @return string Modified template path.
	 */
	public function load_proposal_template( $template ) {
		if ( is_singular( 'wpbp_proposal' ) ) {
			$custom_template = WPBP_PLUGIN_DIR . 'extensions/templates/single-proposal.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	}

	/**
	 * Enqueue frontend scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts() {
		if ( is_singular( 'wpbp_proposal' ) ) {
			wp_enqueue_style(
				'wpbp-frontend',
				WPBP_PLUGIN_URL . 'assets/css/frontend.css',
				array(),
				WPBP_VERSION
			);
		}
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		$post_type = get_post_type();
		if ( 'wpbp_proposal' === $post_type || 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) {
			wp_enqueue_style(
				'wpbp-admin',
				WPBP_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				WPBP_VERSION
			);

			wp_enqueue_script(
				'wpbp-admin',
				WPBP_PLUGIN_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				WPBP_VERSION,
				true
			);
		}
	}

	/**
	 * Add proposal meta boxes
	 *
	 * @since 1.0.0
	 */
	public function add_proposal_meta_boxes() {
		add_meta_box(
			'wpbp-proposal-details',
			__( 'Proposal Details', 'proposals' ),
			array( $this, 'render_proposal_details_meta_box' ),
			'wpbp_proposal',
			'normal',
			'high'
		);

		add_meta_box(
			'wpbp-client-access',
			__( 'Client Access', 'proposals' ),
			array( $this, 'render_client_access_meta_box' ),
			'wpbp_proposal',
			'side',
			'default'
		);
	}

	/**
	 * Render proposal details meta box
	 *
	 * @since 1.0.0
	 * @param WP_Post $post Current post object.
	 */
	public function render_proposal_details_meta_box( $post ) {
		wp_nonce_field( 'wpbp_save_proposal_meta', 'wpbp_proposal_meta_nonce' );

		$client_name  = get_post_meta( $post->ID, '_wpbp_client_name', true );
		$client_email = get_post_meta( $post->ID, '_wpbp_client_email', true );
		$amount       = get_post_meta( $post->ID, '_wpbp_proposal_amount', true );
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="wpbp_client_name"><?php esc_html_e( 'Client Name', 'proposals' ); ?></label>
				</th>
				<td>
					<input type="text" id="wpbp_client_name" name="wpbp_client_name" value="<?php echo esc_attr( $client_name ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="wpbp_client_email"><?php esc_html_e( 'Client Email', 'proposals' ); ?></label>
				</th>
				<td>
					<input type="email" id="wpbp_client_email" name="wpbp_client_email" value="<?php echo esc_attr( $client_email ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="wpbp_proposal_amount"><?php esc_html_e( 'Proposal Amount', 'proposals' ); ?></label>
				</th>
				<td>
					<input type="text" id="wpbp_proposal_amount" name="wpbp_proposal_amount" value="<?php echo esc_attr( $amount ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'e.g., $5,000 or €3,500', 'proposals' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render client access meta box
	 *
	 * @since 1.0.0
	 * @param WP_Post $post Current post object.
	 */
	public function render_client_access_meta_box( $post ) {
		$access_token = get_post_meta( $post->ID, '_wpbp_access_token', true );
		
		if ( empty( $access_token ) ) {
			$access_token = wp_generate_password( 32, false );
			update_post_meta( $post->ID, '_wpbp_access_token', $access_token );
		}

		$client_url = get_permalink( $post->ID ) . '?token=' . $access_token;
		?>
		<p><strong><?php esc_html_e( 'Share this link with your client:', 'proposals' ); ?></strong></p>
		<input type="text" value="<?php echo esc_url( $client_url ); ?>" readonly class="widefat" onclick="this.select();" />
		<p class="description">
			<?php esc_html_e( 'This secure link allows your client to view the proposal without logging in.', 'proposals' ); ?>
		</p>
		<p>
			<button type="button" class="button" onclick="wpbp_regenerate_token(<?php echo absint( $post->ID ); ?>)">
				<?php esc_html_e( 'Generate New Link', 'proposals' ); ?>
			</button>
		</p>
		<?php
	}

	/**
	 * Save proposal meta data
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 */
	public function save_proposal_meta( $post_id ) {
		// Check if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type.
		if ( 'wpbp_proposal' !== get_post_type( $post_id ) ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['wpbp_proposal_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpbp_proposal_meta_nonce'] ) ), 'wpbp_save_proposal_meta' ) ) {
			return;
		}

		// Save meta fields.
		$fields = array( 'wpbp_client_name', 'wpbp_client_email', 'wpbp_proposal_amount' );
		
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	/**
	 * Add custom columns to proposals list
	 *
	 * @since 1.0.0
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_proposal_columns( $columns ) {
		$new_columns = array();
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			
			if ( 'title' === $key ) {
				$new_columns['client'] = __( 'Client', 'proposals' );
				$new_columns['amount'] = __( 'Amount', 'proposals' );
			}
		}
		
		return $new_columns;
	}

	/**
	 * Populate custom columns
	 *
	 * @since 1.0.0
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 */
	public function populate_proposal_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'client':
				$client_name = get_post_meta( $post_id, '_wpbp_client_name', true );
				echo $client_name ? esc_html( $client_name ) : '—';
				break;
			
			case 'amount':
				$amount = get_post_meta( $post_id, '_wpbp_proposal_amount', true );
				echo $amount ? esc_html( $amount ) : '—';
				break;
		}
	}
}

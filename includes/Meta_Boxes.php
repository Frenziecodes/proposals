<?php
namespace Yoostrap\Yosal;

defined( 'ABSPATH' ) || exit;

/**
 * Handles meta boxes and custom fields
 */
class Meta_Boxes {

	/**
	 * Constructor
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 10, 2 );
	}

	/**
	 * Register meta boxes
	 */
	public static function register_meta_boxes() {
		// Client Information Meta Box for proposals
		add_meta_box(
			'wbp_proposal_client_info',
			__( 'Client Information', 'wp-business-proposals' ),
			array( __CLASS__, 'client_info_meta_box' ),
			'wbp_proposal',
			'side',
			'high'
		);
		
		// Proposal Details Meta Box
		add_meta_box(
			'wbp_proposal_details',
			__( 'Proposal Details', 'wp-business-proposals' ),
			array( __CLASS__, 'proposal_details_meta_box' ),
			'wbp_proposal',
			'normal',
			'high'
		);
		
		// Client Contact Info Meta Box
		add_meta_box(
			'wbp_client_details',
			__( 'Client Details', 'wp-business-proposals' ),
			array( __CLASS__, 'client_details_meta_box' ),
			'wbp_client',
			'normal',
			'high'
		);
	}

	/**
	 * Client information meta box for proposals
	 */
	public static function client_info_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'wbp_save_meta_box_data', 'wbp_meta_box_nonce' );
		
		// Get current value
		$selected_client = get_post_meta( $post->ID, '_wbp_client_id', true );
		
		// Get all clients
		$clients = get_posts( array(
			'post_type'      => 'wbp_client',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		?>
		<p>
			<label for="wbp_client_id"><?php esc_html_e( 'Select Client:', 'wp-business-proposals' ); ?></label>
			<select name="wbp_client_id" id="wbp_client_id" class="widefat">
				<option value=""><?php esc_html_e( '— Select —', 'wp-business-proposals' ); ?></option>
				<?php foreach ( $clients as $client ) : ?>
					<option value="<?php echo esc_attr( $client->ID ); ?>" <?php selected( $selected_client, $client->ID ); ?>>
						<?php echo esc_html( $client->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wbp_client' ) ); ?>" class="button">
				<?php esc_html_e( 'Add New Client', 'wp-business-proposals' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Proposal details meta box
	 */
	public static function proposal_details_meta_box( $post ) {
		// Get current values
		$expiration_date = get_post_meta( $post->ID, '_wbp_expiration_date', true );
		$proposal_value = get_post_meta( $post->ID, '_wbp_proposal_value', true );
		$currency = get_post_meta( $post->ID, '_wbp_currency', true );
		$currency = ! empty( $currency ) ? $currency : 'USD';
		?>
		<p>
			<label for="wbp_expiration_date"><?php esc_html_e( 'Expiration Date:', 'wp-business-proposals' ); ?></label>
			<input type="date" id="wbp_expiration_date" name="wbp_expiration_date" 
				value="<?php echo esc_attr( $expiration_date ); ?>" class="widefat">
		</p>
		<p>
			<label for="wbp_proposal_value"><?php esc_html_e( 'Proposal Value:', 'wp-business-proposals' ); ?></label>
			<input type="number" id="wbp_proposal_value" name="wbp_proposal_value" 
				value="<?php echo esc_attr( $proposal_value ); ?>" step="0.01" min="0" class="widefat">
		</p>
		<p>
			<label for="wbp_currency"><?php esc_html_e( 'Currency:', 'wp-business-proposals' ); ?></label>
			<select name="wbp_currency" id="wbp_currency" class="widefat">
				<option value="USD" <?php selected( $currency, 'USD' ); ?>><?php esc_html_e( 'USD - US Dollar', 'wp-business-proposals' ); ?></option>
				<option value="EUR" <?php selected( $currency, 'EUR' ); ?>><?php esc_html_e( 'EUR - Euro', 'wp-business-proposals' ); ?></option>
				<option value="GBP" <?php selected( $currency, 'GBP' ); ?>><?php esc_html_e( 'GBP - British Pound', 'wp-business-proposals' ); ?></option>
				<!-- Add more currencies as needed -->
			</select>
		</p>
		<?php
	}

	/**
	 * Client details meta box
	 */
	public static function client_details_meta_box( $post ) {
		// Get current values
		$email = get_post_meta( $post->ID, '_wbp_client_email', true );
		$phone = get_post_meta( $post->ID, '_wbp_client_phone', true );
		$company = get_post_meta( $post->ID, '_wbp_client_company', true );
		$address = get_post_meta( $post->ID, '_wbp_client_address', true );
		?>
		<p>
			<label for="wbp_client_email"><?php esc_html_e( 'Email Address:', 'wp-business-proposals' ); ?></label>
			<input type="email" id="wbp_client_email" name="wbp_client_email" 
				value="<?php echo esc_attr( $email ); ?>" class="widefat">
		</p>
		<p>
			<label for="wbp_client_phone"><?php esc_html_e( 'Phone Number:', 'wp-business-proposals' ); ?></label>
			<input type="text" id="wbp_client_phone" name="wbp_client_phone" 
				value="<?php echo esc_attr( $phone ); ?>" class="widefat">
		</p>
		<p>
			<label for="wbp_client_company"><?php esc_html_e( 'Company Name:', 'wp-business-proposals' ); ?></label>
			<input type="text" id="wbp_client_company" name="wbp_client_company" 
				value="<?php echo esc_attr( $company ); ?>" class="widefat">
		</p>
		<p>
			<label for="wbp_client_address"><?php esc_html_e( 'Address:', 'wp-business-proposals' ); ?></label>
			<textarea id="wbp_client_address" name="wbp_client_address" class="widefat" rows="3"><?php echo esc_textarea( $address ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Save meta box data
	 */
	public static function save_meta_boxes( $post_id, $post ) {
		// Check if nonce is set
		if ( ! isset( $_POST['wbp_meta_box_nonce'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['wbp_meta_box_nonce'], 'wbp_save_meta_box_data' ) ) {
			return;
		}

		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions
		if ( 'wbp_proposal' === $post->post_type && ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( 'wbp_client' === $post->post_type && ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save proposal fields
		if ( 'wbp_proposal' === $post->post_type ) {
			// Save client ID
			if ( isset( $_POST['wbp_client_id'] ) ) {
				update_post_meta( $post_id, '_wbp_client_id', sanitize_text_field( $_POST['wbp_client_id'] ) );
			}
			
			// Save expiration date
			if ( isset( $_POST['wbp_expiration_date'] ) ) {
				update_post_meta( $post_id, '_wbp_expiration_date', sanitize_text_field( $_POST['wbp_expiration_date'] ) );
			}
			
			// Save proposal value
			if ( isset( $_POST['wbp_proposal_value'] ) ) {
				update_post_meta( $post_id, '_wbp_proposal_value', sanitize_text_field( $_POST['wbp_proposal_value'] ) );
			}
			
			// Save currency
			if ( isset( $_POST['wbp_currency'] ) ) {
				update_post_meta( $post_id, '_wbp_currency', sanitize_text_field( $_POST['wbp_currency'] ) );
			}
		}
		
		// Save client fields
		if ( 'wbp_client' === $post->post_type ) {
			// Save client email
			if ( isset( $_POST['wbp_client_email'] ) ) {
				update_post_meta( $post_id, '_wbp_client_email', sanitize_email( $_POST['wbp_client_email'] ) );
			}
			
			// Save client phone
			if ( isset( $_POST['wbp_client_phone'] ) ) {
				update_post_meta( $post_id, '_wbp_client_phone', sanitize_text_field( $_POST['wbp_client_phone'] ) );
			}
			
			// Save client company
			if ( isset( $_POST['wbp_client_company'] ) ) {
				update_post_meta( $post_id, '_wbp_client_company', sanitize_text_field( $_POST['wbp_client_company'] ) );
			}
			
			// Save client address
			if ( isset( $_POST['wbp_client_address'] ) ) {
				update_post_meta( $post_id, '_wbp_client_address', sanitize_textarea_field( $_POST['wbp_client_address'] ) );
			}
		}
	}
}

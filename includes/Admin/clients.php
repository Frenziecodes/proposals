<?php
namespace Yoostrap\Yosal\Admin;

defined('ABSPATH') || exit;

/**
 * Client Management Admin Page
 */
class Clients
{
    /**
     * Client being edited
     *
     * @var object
     */
    private static $_client = null;

    /**
     * Output the clients admin page
     *
     * @return void
     */
    public static function output()
    {
        // Check if we're editing a client
        self::_check_edit_client();
        
        // Process form submission
        self::_process_form();
        
        // Add admin styles
        self::_add_admin_styles();
        
        // Display the page
        self::_display_page();
    }
    
    /**
     * Check if we're editing a client
     *
     * @return void
     */
    private static function _check_edit_client()
    {
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $client_id = absint($_GET['edit']);
            $client = get_post($client_id);
            
            if ($client && 'wbp_client' === $client->post_type) {
                self::$_client = $client;
            }
        }
    }
    
    /**
     * Add admin styles
     *
     * @return void
     */
    private static function _add_admin_styles()
    {
        ?>
        <style type="text/css">
            .wp-list-table .column-cb { width: 5%; }
            .wp-list-table .column-title { width: 25%; }
            .wp-list-table .column-email { width: 20%; }
            .wp-list-table .column-phone { width: 15%; }
            .wp-list-table .column-address { width: 35%; }
            
            #col-left { width: 35%; }
            #col-right { width: 65%; }
            
            .form-field input[type="text"],
            .form-field input[type="email"],
            .form-field textarea {
                width: 100%;
            }
            
            .form-field {
                margin-bottom: 1em;
            }
            
            .form-field label {
                display: block;
                margin-bottom: 0.5em;
                font-weight: 600;
            }
            
            .form-field .description {
                margin: 0.25em 0 0.5em;
                color: #666;
            }
        </style>
        <?php
    }
    
    /**
     * Process form submissions
     *
     * @return void
     */
    private static function _process_form()
    {
        // Add/Update Client
        if (isset($_POST['submit_client']) && isset($_POST['_wpnonce'])) {
            // Verify nonce for adding or editing
            $nonce_action = self::$_client ? 'wbp_edit_client_' . self::$_client->ID : 'wbp_add_new_client';
            if (!wp_verify_nonce($_POST['_wpnonce'], $nonce_action)) {
                return;
            }
            
            // Validate required fields
            if (empty($_POST['client_name'])) {
                self::_add_notice('error', __('Client name is required.', 'wp-business-proposals'));
                return;
            }
            
            // Create or update the client
            $client_data = array(
                'post_title'   => sanitize_text_field($_POST['client_name']),
                'post_type'    => 'wbp_client',
                'post_status'  => 'publish',
            );
            
            // If editing, add the ID
            if (self::$_client) {
                $client_data['ID'] = self::$_client->ID;
                $client_id = wp_update_post($client_data);
                $success_message = __('Client updated successfully.', 'wp-business-proposals');
            } else {
                $client_id = wp_insert_post($client_data);
                $success_message = __('Client added successfully.', 'wp-business-proposals');
            }
            
            if (!is_wp_error($client_id)) {
                // Save client meta data
                if (isset($_POST['client_email'])) {
                    update_post_meta($client_id, '_wbp_client_email', sanitize_email($_POST['client_email']));
                }
                
                if (isset($_POST['client_phone'])) {
                    update_post_meta($client_id, '_wbp_client_phone', sanitize_text_field($_POST['client_phone']));
                }
                
                if (isset($_POST['client_address'])) {
                    update_post_meta($client_id, '_wbp_client_address', sanitize_textarea_field($_POST['client_address']));
                }
                
                // Add success notice
                self::_add_notice('success', $success_message);
                
                // Redirect to the main clients page after editing
                if (self::$_client) {
                    wp_redirect(admin_url('admin.php?page=wbp-clients'));
                    exit;
                }
            } else {
                // Add error notice
                self::_add_notice('error', $client_id->get_error_message());
            }
        }
        
        // Delete client(s)
        if (isset($_GET['delete']) && is_numeric($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wbp_delete_client_' . absint($_GET['delete']))) {
            $client_id = absint($_GET['delete']);
            $result = wp_delete_post($client_id, true);
            
            if ($result) {
                self::_add_notice('success', __('Client deleted successfully.', 'wp-business-proposals'));
            } else {
                self::_add_notice('error', __('Error deleting client.', 'wp-business-proposals'));
            }
            
            // Redirect to remove the action from URL
            wp_redirect(admin_url('admin.php?page=wbp-clients'));
            exit;
        }
    }
    
    /**
     * Display admin notices
     *
     * @return void
     */
    private static function _display_notices()
    {
        $notices = get_transient('wbp_admin_notices');
        
        if (!empty($notices)) {
            foreach ($notices as $notice) {
                echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible"><p>' . esc_html($notice['message']) . '</p></div>';
            }
            
            // Clear notices
            delete_transient('wbp_admin_notices');
        }
    }
    
    /**
     * Add admin notice
     *
     * @param  string $type    Notice type (success, error, warning, info)
     * @param  string $message Notice message
     * @return void
     */
    private static function _add_notice($type, $message)
    {
        $notices = get_transient('wbp_admin_notices') ?: array();
        $notices[] = array(
            'type'    => $type,
            'message' => $message,
        );
        
        set_transient('wbp_admin_notices', $notices, 60);
    }
    
    /**
     * Display the clients page
     * 
     * @return void
     */
    private static function _display_page()
    {
        // Get all clients
        $clients = get_posts(
            array(
                'post_type'      => 'wbp_client',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) 
        );
        
        $is_edit_mode = (self::$_client !== null);
        
        // Get client data if in edit mode
        if ($is_edit_mode) {
            $client_name = self::$_client->post_title;
            $client_email = get_post_meta(self::$_client->ID, '_wbp_client_email', true);
            $client_phone = get_post_meta(self::$_client->ID, '_wbp_client_phone', true);
            $client_address = get_post_meta(self::$_client->ID, '_wbp_client_address', true);
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo $is_edit_mode ? esc_html__('Edit Client', 'wp-business-proposals') : esc_html__('Client', 'wp-business-proposals'); ?></h1>
            
            <?php self::_display_notices(); ?>
            
            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h2><?php echo $is_edit_mode ? esc_html__('Edit Client', 'wp-business-proposals') : esc_html__('Add New Client', 'wp-business-proposals'); ?></h2>
                            <form method="post" action="">
                                <?php 
                                if ($is_edit_mode) {
                                    wp_nonce_field('wbp_edit_client_' . self::$_client->ID);
                                } else {
                                    wp_nonce_field('wbp_add_new_client');
                                }
                                ?>
                                
                                <div class="form-field">
                                    <label for="client_name"><?php esc_html_e('Client Name', 'wp-business-proposals'); ?></label>
                                    <input type="text" name="client_name" id="client_name" value="<?php echo $is_edit_mode ? esc_attr($client_name) : ''; ?>" required>
                                </div>
                                
                                <div class="form-field">
                                    <label for="client_email"><?php esc_html_e('Client Email', 'wp-business-proposals'); ?></label>
                                    <input type="email" name="client_email" id="client_email" value="<?php echo $is_edit_mode ? esc_attr($client_email) : ''; ?>">
                                    <p class="description"><?php esc_html_e("Enter client's email.", 'wp-business-proposals'); ?></p>
                                </div>
                                
                                <div class="form-field">
                                    <label for="client_phone"><?php esc_html_e('Phone Number', 'wp-business-proposals'); ?></label>
                                    <input type="text" name="client_phone" id="client_phone" value="<?php echo $is_edit_mode ? esc_attr($client_phone) : ''; ?>">
                                    <p class="description"><?php esc_html_e("Enter client's phone number for your record.", 'wp-business-proposals'); ?></p>
                                </div>
                                
                                <div class="form-field">
                                    <label for="client_address"><?php esc_html_e('Address', 'wp-business-proposals'); ?></label>
                                    <textarea name="client_address" id="client_address" rows="3"><?php echo $is_edit_mode ? esc_textarea($client_address) : ''; ?></textarea>
                                    <p class="description"><?php esc_html_e("Enter client's mailing address for your record.", 'wp-business-proposals'); ?></p>
                                </div>
                                
                                <p class="submit">
                                    <button type="submit" name="submit_client" id="submit_client" class="button button-primary">
                                        <?php echo $is_edit_mode ? esc_html__('Update Client', 'wp-business-proposals') : esc_html__('Add New Client', 'wp-business-proposals'); ?>
                                    </button>
                                    
                                    <?php if ($is_edit_mode) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wbp-clients')); ?>" class="button">
                                        <?php esc_html_e('Cancel', 'wp-business-proposals'); ?>
                                    </a>
                                    <?php endif; ?>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if (!$is_edit_mode) : // Only show client list when not in edit mode ?>
                <div id="col-right">
                    <div class="col-wrap">
                        <table class="widefat fixed striped posts">
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column column-cb check-column">
                                        <input type="checkbox" id="cb-select-all-1">
                                    </th>
                                    <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Client', 'wp-business-proposals'); ?></th>
                                    <th scope="col" class="manage-column column-email"><?php esc_html_e('Email', 'wp-business-proposals'); ?></th>
                                    <th scope="col" class="manage-column column-phone"><?php esc_html_e('Phone', 'wp-business-proposals'); ?></th>
                                    <th scope="col" class="manage-column column-address"><?php esc_html_e('Address', 'wp-business-proposals'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="the-list">
                                <?php if (empty($clients)) : ?>
                                    <tr class="no-items">
                                        <td class="colspanchange" colspan="5"><?php esc_html_e('No clients found.', 'wp-business-proposals'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($clients as $client) : ?>
                                        <?php
                                        $email = get_post_meta($client->ID, '_wbp_client_email', true);
                                        $phone = get_post_meta($client->ID, '_wbp_client_phone', true);
                                        $address = get_post_meta($client->ID, '_wbp_client_address', true);
                                        
                                        // Generate edit and delete URLs
                                        $edit_url = add_query_arg('edit', $client->ID, admin_url('admin.php?page=wbp-clients'));
                                        $delete_url = wp_nonce_url(
                                            add_query_arg('delete', $client->ID, admin_url('admin.php?page=wbp-clients')),
                                            'wbp_delete_client_' . $client->ID
                                        );
                                        ?>
                                        <tr id="client-<?php echo esc_attr($client->ID); ?>">
                                            <th scope="row" class="check-column">
                                                <input type="checkbox" name="delete_clients[]" value="<?php echo esc_attr($client->ID); ?>">
                                            </th>
                                            <td class="title column-title column-primary">
                                                <strong>
                                                    <a href="<?php echo esc_url($edit_url); ?>" class="row-title">
                                                        <?php echo esc_html($client->post_title); ?>
                                                    </a>
                                                </strong>
                                                <div class="row-actions">
                                                    <span class="edit">
                                                        <a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'wp-business-proposals'); ?></a> | 
                                                    </span>
                                                    <span class="trash">
                                                        <a href="<?php echo esc_url($delete_url); ?>" class="submitdelete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this client?', 'wp-business-proposals'); ?>');"><?php esc_html_e('Delete', 'wp-business-proposals'); ?></a>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="email column-email"><?php echo esc_html($email); ?></td>
                                            <td class="phone column-phone"><?php echo esc_html($phone); ?></td>
                                            <td class="address column-address"><?php echo esc_html($address); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="col" class="manage-column column-cb check-column">
                                        <input type="checkbox" id="cb-select-all-2">
                                    </th>
                                    <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Client', 'wp-business-proposals'); ?></th>
                                    <th scope="col" class="manage-column column-email"><?php esc_html_e('Email', 'wp-business-proposals'); ?></th>
                                    <th scope="col" class="manage-column column-phone"><?php esc_html_e('Phone', 'wp-business-proposals'); ?></th>
                                    <th scope="col" class="manage-column column-address"><?php esc_html_e('Address', 'wp-business-proposals'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

Clients::output();

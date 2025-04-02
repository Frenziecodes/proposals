<?php
namespace Yoostrap\Yosal\Admin;

defined('ABSPATH') || exit;

/**
 * Proposal Management Admin Page
 */
class Proposals
{
    /**
     * Output the proposals admin page
     * 
     * @return void
     */
    public static function output()
    {
        // Process any actions (delete, etc.)
        self::process_actions();
        
        // Display any notices
        self::display_notices();
        
        // Display the page
        self::display_page();
    }
    
    /**
     * Process actions such as delete
     * 
     * @return void
     */
    private static function process_actions()
    {
        // Handle delete action
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['proposal']) && is_numeric($_GET['proposal'])) {
            // Verify nonce
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_proposal_' . absint($_GET['proposal']))) {
                self::add_notice('error', __('Security check failed.', 'wp-business-proposals'));
                return;
            }
            
            $proposal_id = absint($_GET['proposal']);
            $result = wp_delete_post($proposal_id, true);
            
            if ($result) {
                self::add_notice('success', __('Proposal deleted successfully.', 'wp-business-proposals'));
            } else {
                self::add_notice('error', __('Error deleting proposal.', 'wp-business-proposals'));
            }
            
            // Redirect to remove the action from URL
            wp_redirect(admin_url('admin.php?page=wbp-proposals'));
            exit;
        }
    }
    
    /**
     * Display admin notices
     * 
     * @return void
     */
    private static function display_notices()
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
    private static function add_notice($type, $message)
    {
        $notices = get_transient('wbp_admin_notices') ?: array();
        $notices[] = array(
            'type'    => $type,
            'message' => $message,
        );
        
        set_transient('wbp_admin_notices', $notices, 60);
    }
    
    /**
     * Display the proposals page
     * 
     * @return void
     */
    private static function display_page()
    {
        // Get all proposals
        $proposals = get_posts(
            array(
            'post_type'      => 'wbp_proposal',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            )
        );
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Proposals', 'wp-business-proposals'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wbp-add-proposal')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'wp-business-proposals'); ?></a>
            <hr class="wp-header-end">
            
            <?php if (empty($proposals)) : ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('No proposals found.', 'wp-business-proposals'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=wbp-add-proposal')); ?>"><?php esc_html_e('Create your first proposal', 'wp-business-proposals'); ?></a></p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped posts">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'wp-business-proposals'); ?></th>
                            <th scope="col" class="manage-column column-client"><?php esc_html_e('Client', 'wp-business-proposals'); ?></th>
                            <th scope="col" class="manage-column column-value"><?php esc_html_e('Value', 'wp-business-proposals'); ?></th>
                            <th scope="col" class="manage-column column-expiration"><?php esc_html_e('Expiration Date', 'wp-business-proposals'); ?></th>
                            <th scope="col" class="manage-column column-date"><?php esc_html_e('Date', 'wp-business-proposals'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proposals as $proposal) : 
                            // Get metadata
                            $client_id = get_post_meta($proposal->ID, '_wbp_client_id', true);
                            $client_name = '';
                            
                            if ($client_id) {
                                $client = get_post($client_id);
                                if ($client && $client->post_type === 'wbp_client') {
                                    $client_name = $client->post_title;
                                }
                            }
                            
                            $value = get_post_meta($proposal->ID, '_wbp_proposal_value', true);
                            $currency = get_post_meta($proposal->ID, '_wbp_currency', true) ?: 'USD';
                            $expiration_date = get_post_meta($proposal->ID, '_wbp_expiration_date', true);
                            
                            // Format value with currency
                            $formatted_value = '';
                            if ($value !== '') {
                                switch ($currency) {
                                case 'USD':
                                    $formatted_value = '$' . number_format((float) $value, 2);
                                    break;
                                case 'EUR':
                                    $formatted_value = '€' . number_format((float) $value, 2);
                                    break;
                                case 'GBP':
                                    $formatted_value = '£' . number_format((float) $value, 2);
                                    break;
                                default:
                                    $formatted_value = $currency . ' ' . number_format((float) $value, 2);
                                }
                            }
                            
                            // Format date
                            $date = date_i18n(get_option('date_format'), strtotime($proposal->post_date));
                            
                            // Format expiration date
                            $formatted_expiration = '';
                            if (!empty($expiration_date)) {
                                $formatted_expiration = date_i18n(get_option('date_format'), strtotime($expiration_date));
                                
                                // Check if expired
                                $is_expired = strtotime($expiration_date) < current_time('timestamp');
                                if ($is_expired) {
                                    $formatted_expiration = '<span class="expired">' . $formatted_expiration . '</span>';
                                }
                            }
                            ?>
                            <tr>
                                <td class="title column-title column-primary">
                                    <strong>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=wbp-add-proposal&id=' . $proposal->ID)); ?>" class="row-title">
                                            <?php echo esc_html($proposal->post_title); ?>
                                        </a>
                                    </strong>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=wbp-add-proposal&id=' . $proposal->ID)); ?>">
                                                <?php esc_html_e('Edit', 'wp-business-proposals'); ?>
                                            </a> | 
                                        </span>
                                        <span class="trash">
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wbp-proposals&action=delete&proposal=' . $proposal->ID), 'delete_proposal_' . $proposal->ID)); ?>" class="submitdelete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this proposal?', 'wp-business-proposals'); ?>')">
                                                <?php esc_html_e('Delete', 'wp-business-proposals'); ?>
                                            </a>
                                        </span>
                                    </div>
                                </td>
                                <td class="client column-client">
                                    <?php echo $client_name ? esc_html($client_name) : '<em>' . esc_html__('None', 'wp-business-proposals') . '</em>'; ?>
                                </td>
                                <td class="value column-value">
                                    <?php echo $formatted_value ? esc_html($formatted_value) : '<em>' . esc_html__('Not set', 'wp-business-proposals') . '</em>'; ?>
                                </td>
                                <td class="expiration column-expiration">
                                    <?php echo $formatted_expiration ? wp_kses_post($formatted_expiration) : '<em>' . esc_html__('No expiration', 'wp-business-proposals') . '</em>'; ?>
                                </td>
                                <td class="date column-date">
                                    <?php echo esc_html($date); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <style type="text/css">
            .wp-list-table .column-title { width: 30%; }
            .wp-list-table .column-client { width: 20%; }
            .wp-list-table .column-value { width: 15%; }
            .wp-list-table .column-expiration { width: 15%; }
            .wp-list-table .column-date { width: 15%; }
            
            .expired {
                color: #dc3545;
                font-weight: bold;
            }
        </style>
        <?php
    }
}

// Output the proposals page
Proposals::output();

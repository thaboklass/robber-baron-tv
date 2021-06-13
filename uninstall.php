<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// delete options from options table
delete_option('robber_baron_tv_connected');
delete_option('robber_baron_tv_email_address');
delete_option('robber_baron_tv_user_id');
?>
<?php defined('ABSPATH') or die('No direct script access!'); ?>

<?php
// delete old log
$last_date = date('Y-m-d', strtotime('-6 week'));
global $wpdb;
//$query = 'DELETE  FROM  '.$GLOBALS['wpdb']->prefix . 'p18a_logs' .' WHERE timestamp   < '.$last_date;
//$res = $wpdb->query($wpdb->query('DELETE  FROM  '.$GLOBALS['wpdb']->prefix . 'p18a_logs' .' WHERE timestamp   < %d',$last_date));
$res = $wpdb->query(
    "DELETE FROM " . $wpdb->prefix . "p18a_logs
   WHERE timestamp < DATE_SUB(CURDATE(),INTERVAL 30 DAY)"
);
if($res>0){
    echo  $res.' records have been deleted from log';
}
?>

<h1>
    <?php echo P18A_PLUGIN_NAME; ?> 
    <span id="p18a_version"><?php echo P18A_VERSION; ?></span>
</h1>

<br />

<div id="p18a_tabs_menu">
    <ul>
        <li>
            <a href="<?php echo admin_url('admin.php?page=' . P18A_PLUGIN_ADMIN_URL); ?>" class="<?php if(is_null($this->get('tab'))) echo 'active'; ?>">
                <?php _e('API Settings', 'p18a'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=' . P18A_PLUGIN_ADMIN_URL . '&tab=transaction-log'); ?>" class="<?php if($this->get('tab') == 'transaction-log') echo 'active'; ?>">
                <?php _e('Transaction log', 'p18a'); ?>
            </a>
        </li>
        <?php if($this->option('application') && $this->option('environment') && $this->option('url')): ?>
        <li>
            <a href="<?php echo admin_url('admin.php?page=' . P18A_PLUGIN_ADMIN_URL . '&tab=test-unit'); ?>" class="<?php if($this->get('tab') == 'test-unit') echo 'active'; ?>">
                <?php _e('API Test unit', 'p18a'); ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

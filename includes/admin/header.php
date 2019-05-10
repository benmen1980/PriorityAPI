<?php defined('ABSPATH') or die('No direct script access!'); ?>

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
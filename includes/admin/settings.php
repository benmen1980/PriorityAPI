<?php defined('ABSPATH') or die('No direct script access!'); ?>

<form id="p18a-settings" name="p18a-settings" method="post" action="<?php echo admin_url('admin.php?page=' . P18A_PLUGIN_ADMIN_URL); ?>">
    <?php wp_nonce_field('save-settings', 'p18a-nonce'); ?>
</form>

<div class="wrap">

    <?php include P18A_ADMIN_DIR . 'header.php'; ?>

    <div class="p18a-page-wrapper">

        <br>
        <table class="p18a">

            <tr>
                <td class="p18a-label">
                    <label for="p18a-version"><?php _e('Priority Version', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-version" type="text" name="p18a-version" form="p18a-settings" value="<?php echo $this->option('priority-version'); ?>">
                </td>
            </tr>
            <tr>
                <td class="p18a-label">
                    <label for="p18a-application"><?php _e('Application', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-application" type="text" name="p18a-application" form="p18a-settings" value="<?php echo $this->option('application'); ?>">
                </td>
            </tr>

            <tr>
                <td class="p18a-label">
                    <label for="p18a-environment"><?php _e('Environment name', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-environment" type="text" name="p18a-environment" form="p18a-settings" value="<?php echo $this->option('environment'); ?>">
                </td>
            </tr>

            <tr>
                <td class="p18a-label">
                    <label for="p18a-language"><?php _e('Language', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-language" type="text" name="p18a-language" form="p18a-settings" value="<?php echo $this->option('language'); ?>">
                </td>
            </tr>

            <tr>
                <td class="p18a-label">
                    <label for="p18a-url"><?php _e('URL', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-url" type="text" name="p18a-url" form="p18a-settings" value="<?php echo $this->option('url'); ?>">
                </td>
            </tr>

            <tr>
                <td class="p18a-label">
                    <label for="p18a-username"><?php _e('Username', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-username" type="text" name="p18a-username" form="p18a-settings" value="<?php echo $this->option('username'); ?>">
                </td>
            </tr>


            <tr>
                <td class="p18a-label">
                    <label for="p18a-password"><?php _e('Password', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-password" type="text" name="p18a-password" form="p18a-settings" value="<?php echo $this->option('password'); ?>">
                </td>
            </tr>
            <tr>
                <td class="p18a-label">
                    <label for="p18a-X-App-Id"><?php _e('X-App-Id', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-X-App-Id" type="text" name="p18a-X-App-Id" form="p18a-settings"  value="<?php echo $this->option('X-App-Id'); ?>">
                </td>
            </tr>


            <tr>
                <td class="p18a-label">
                    <label for="p18a-X-App-Key"><?php _e('X-App-Key', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-X-App-Key" type="text" name="p18a-X-App-Key" form="p18a-settings" value="<?php echo $this->option('X-App-Key'); ?>">
                </td>
            </tr>
            <tr>
                <td class="p18a-label">
                    <label for="p18a-sslverify"><?php _e('SSL verify', 'p18a'); ?></label>
                </td>
                <td>
                    <input id="p18a-sslverify" type="checkbox" name="p18a-sslverify" form="p18a-settings" value="1" <?php if($this->option('sslverify')) echo 'checked'; ?>>
                </td>
            </tr>

        </table>

        <br>

        <input type="submit" class="button-primary" value="<?php _e('Save changes', 'p18a'); ?>" name="p18a-save-settings" form="p18a-settings" />

    </div>
</div>

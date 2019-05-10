<?php defined('ABSPATH') or die('No direct script access!'); ?>

<form id="p18a-api-test" name="p18a-api-test" method="post" action="<?php echo admin_url('admin.php?page=' . P18A_PLUGIN_ADMIN_URL); ?>">

<div class="wrap">

    <?php include P18A_ADMIN_DIR . 'header.php'; ?>

    <div class="p18a-page-wrapper api-test">

        <br><br>

        <label for="p18a-url-addition"><?php _e('URL Addition', 'p18a'); ?></label><br>
        <input id="p18a-url-addition" type="text" name="p18a-url-addition">

        <br><br>

        <label for="p18a-json-request"><?php _e('JSON Request', 'p18a'); ?></label><br>
        <textarea data-json="true" id="p18a-json-request" name="p18a-json-request"></textarea>

        <br><br>

        <label for="p18a-api-action"><?php _e('Action', 'p18a'); ?></label><br>
        <select id="p18a-api-action" name="p18a-api-action">
            <option value="get"><?php _e('Get from API', 'p18a'); ?></option>
            <option value="post"><?php _e('Post to API', 'p18a'); ?></option>
            <option value="patch"><?php _e('Patch API', 'p18a'); ?></option>
            <option value="delete"><?php _e('Delete from API', 'p18a'); ?></option>
        </select>

        <br><br>

        <label for="p18a-json-response">
            <?php _e('JSON Response', 'p18a'); ?>
            <span id="p18a-json-response-hedaer"></span>
        </label><br>
        <textarea id="p18a-json-response"></textarea>

        <br><br>

        <input class="button button-primary p18a-large-button" type="submit" name="submit" value="<?php _e('Send request', 'p18a'); ?>">

    </div>
    
</div>

</form>



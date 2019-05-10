<?php defined('ABSPATH') or die('No direct script access!'); ?>

<div class="wrap">

    <?php include P18A_ADMIN_DIR . 'header.php'; ?>

    <div class="p18a-page">

        <?php

            $list = new PriorityAPI\API_List();
            $list->prepare_items(); 
            $list->display(); 
            
        ?>

    </div>
    
</div>

<div id="p18a-window" style="display:none;">
    <textarea id="p18a-response-window"></textarea><br><br>
    <a href="#" id="p18a-select-all" class="button button-primary"><?php _e('Select all', 'p18a'); ?></a>
</div>
	
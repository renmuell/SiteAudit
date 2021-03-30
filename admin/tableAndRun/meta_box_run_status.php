<?php

function site_audit_run_status_meta_box_hanlder($item)
{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_type'      => $item["settings"]["post_types"]
    ]);

    $num_all = 0;
    foreach ($item["settings"]["devices"] as $device){
        $num_all += (count($device["browser"]) * (empty($device["responsive"]) ? 1 : count($device["responsive"]))) * count($posts);
    }


    ?>
        Progress:
        <progress value="0" max="<?= $num_all ?>">0 %</progress>
        0 / <?= $num_all ?>

        <br>
        <br>

        <?php var_dump( $item["settings"]); ?>

    <?php
}

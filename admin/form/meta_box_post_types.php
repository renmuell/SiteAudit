<?php


function site_audit_posttypes_meta_box_handler($param)
{

    $item = $param["item"];
    $settings = $param["settings"];
    ?>

        <fieldset>

            <?php foreach ( $settings["post_types"]  as $post_type ) : ?>

                <div>
                    <input type="checkbox" id="post_types_<?= $post_type ?>" name="post_types[]" value="<?= $post_type ?>" <?= (in_array($post_type, $item["settings"]["post_types"])) ? "checked=checked" : "" ?>>
                    <label for="post_types_<?= $post_type ?>"><?= $post_type ?></label>
                </div>

            <?php endforeach; ?>

        </fieldset>
    <?php
}


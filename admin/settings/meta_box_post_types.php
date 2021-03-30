<?php


function site_audit_settings_post_types_meta_box_handler ( $settings) {

    $args = array(
        'public'   => true,
        '_builtin' => true
     );
     $output = 'names';
     $operator = 'and';

     $post_types = get_post_types( $args, $output, $operator );



    ?>
    <fieldset>
 
        <?php if ($post_types) : foreach ( $post_types  as $post_type ) : ?>

            <div>
                <input type="checkbox" id="<?= $post_type ?>" name="post_types[]" value="<?= $post_type ?>" <?= (in_array($post_type, $settings["post_types"])) ? "checked=checked" : "" ?>>
                <label for="<?= $post_type ?>"><?= $post_type ?></label>
            </div>

        <?php endforeach; endif; ?>

    </fieldset>
 <?php 
}

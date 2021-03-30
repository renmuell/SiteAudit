<?php


function site_audit_roles_meta_box_handler($param)
{

    $item = $param["item"];
    $settings = $param["settings"];

    ?>

        <fieldset>

            <?php foreach ( $settings["roles"]  as $roles ) : ?>

                <div>
                    <input type="checkbox" id="roles_<?= $roles ?>" name="roles[]" value="<?= $roles ?>" <?= (in_array($roles, $item["settings"]["roles"])) ? "checked=checked" : "" ?>>
                    <label for="roles_<?= $roles ?>"><?= $roles ?></label>
                </div>

            <?php endforeach; ?>

        </fieldset>
    <?php
}
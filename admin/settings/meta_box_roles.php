<?php
function site_audit_settings_roles_meta_box_handler ( $settings) {


    $roles =  [];
    $editable_roles = get_editable_roles();
    foreach ($editable_roles as $role => $details) {
        $sub['role'] = esc_attr($role);
        $sub['name'] = translate_user_role($details['name']);
        $roles[] = $sub;
    }

    ?>
      <fieldset>
     
            <?php if ($roles) : foreach ( $roles  as $role ) : ?>

                <div>
                    <input type="checkbox" id="<?= $role["role"] ?>" name="roles[]" value="<?= $role["role"] ?>" <?= (in_array($role["role"], $settings["roles"])) ? "checked=checked" : "" ?>>
                    <label for="<?= $role["role"] ?>"><?= $role["name"] ?></label>
                </div>

            <?php endforeach; endif; ?>

        </fieldset>
    <?php
}

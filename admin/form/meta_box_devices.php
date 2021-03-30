<?php


function site_audit_device_meta_box_handler($param)
{
    $item = $param["item"];
    $settings = $param["settings"];
    ?>
 
    <style>
        #device_wrapper {
            display:flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: stretch;
        }
        .device{
            flex-grow: 1;
            border:1px solid #ccc;
            
            box-sizing:border-box;
            padding:10px;
        }
    </style>

    <div id="device_wrapper">

        <?php foreach($settings["devices"] as $device) : ?>

            <div class="device">

                <input hidden name="devices_<?= $device["id"] ?>_name" value="<?= $device["name"] ?>"/>
                <input hidden name="devices_<?= $device["id"] ?>_os" value="<?= $device["os"] ?>"/>
        
                <input type="checkbox" id="devices_<?= $device["id"] ?>" name="devices[]" value="<?= $device["id"] ?>" <?= (site_audit_form_is_device_checked($device["id"], $item["settings"]["devices"])) ? "checked=checked" : "" ?>>
                <label for="devices_<?= $device["id"] ?>"><?= $device["name"] ?></label>

                <br>
                <br>
                <b>OS</b>
                <br>
                 <?= $device["os"] ?>
                <br>
                
                <?php if (!empty($device["browser"])) : ?>
                    <br>
                    <b>browser</b>
                    <br>
                    <br>

                    <?php foreach ( $device["browser"]  as $browser ) : ?>
                    
                    <div>
                        <input type="checkbox" id="devices_<?= $device["id"] ?>_browser_<?= $browser ?>" name="devices_<?= $device["id"] ?>_browser[]" value="<?= $browser ?>" <?= (site_audit_form_is_browser_checked($device["id"], $browser, $item["settings"]["devices"])) ? "checked=checked" : "" ?>>
                        <label for="devices_<?= $device["id"] ?>_browser_<?= $browser ?>"><?= $browser ?></label>
                    </div>

                    <?php endforeach; ?>

            

                <?php endif; ?>

                <?php if (!empty($device["responsive"])) : ?>
                    <br>
                    
                    <b>responsive</b>
                    <br>
                    <br>

                    <?php foreach ( $device["responsive"]  as $responsive ) : ?>

                    <div>
                        <input type="checkbox" id="devices_<?= $device["id"] ?>_responsive_<?= $responsive ?>" name="devices_<?= $device["id"] ?>_responsive[]" value="<?= $responsive ?>" <?= (site_audit_form_is_browser_checked($device["id"], $responsive, $item["settings"]["devices"])) ? "checked=checked" : "" ?>>
                        <label for="devices_<?= $device["id"] ?>_responsive_<?= $responsive ?>"><?= $responsive ?></label>
                    </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        </div>
    <?php
}
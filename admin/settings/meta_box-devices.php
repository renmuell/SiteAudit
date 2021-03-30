<?php

function site_audit_settings_devices_meta_box_handler ($settings) {
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


    <input type="hidden" id="deviceMaxNubmer" name="deviceMaxNubmer" readonly value="<?= count($settings["devices"]) ?>"/>

    <script>
        function delete_device(number, event) {
            event.preventDefault();
            event.stopPropagation();
            if (confirm("Delete Device: '" +  document.querySelector('[name="device_'+number+'_name"]').value + "'?")) {
                document.querySelector('[data-number="'+number+'"]').remove();
            }
        
        }
    </script>

    <div id="device_wrapper">

        <?php foreach($settings["devices"] as $device) : ?>

            <?= generateDeviceHtml($device); ?>

        <?php endforeach; ?>

    </div>
    <br>
    <button id="add_device_btn">Add Device</button>

    <script id="device_templaste" type="text/template">
        <?= generateDeviceHtml();  ?>
    </script>

    <script>

        document.querySelector('#add_device_btn').addEventListener("click", function(event){
            event.preventDefault();
            event.stopPropagation();

            var diveces = document.querySelectorAll('#device_wrapper fieldset');
            var maxNubmer = 0;
            if (diveces.length > 0) {
                maxNubmer = Math.max.apply(null, Array.from(diveces).map(function(item){
                return parseInt(item.dataset.number);
            }));
            } 
            document.querySelector('#deviceMaxNubmer').value = maxNubmer + 1;
            document.querySelector('#device_wrapper').innerHTML += document.querySelector('#device_templaste').innerHTML.replaceAll("%%number%%", maxNubmer + 1);
        });

    </script>
<?php
}

function generateDeviceHtml($values = null) {
    $number = "%%number%%";
    if ($values == null) {
        $values = [
            "name" => "",
            "os"   => [],
            "browser"   => [],
            "responsive"   => []
        ];
    } else {
        $number = $values["id"];
    }

    ob_start();
    ?>
        <fieldset class="device" data-number="<?= $number ?>">
            <label>Name</label>
            <br>
            <input type="text" name="device_<?= $number ?>_name" value="<?= $values["name"] ?>"/>
            <br>
            <label>OS</label>
            <br>
            <input type="text" name="device_<?= $number ?>_os" value="<?= $values["os"] ?>"/>
            <br>

            <label>Browser</label>
            <br>
            <textarea rows="5" id="device_<?= $number ?>_browser" name="device_<?= $number ?>_browser"><?= implode("\n", $values["browser"]) ?></textarea>
            <br>

            <label>Responsive</label>
            <br>
            <textarea rows="5" id="device_<?= $number ?>_responsive" name="device_<?= $number ?>_responsive"><?= implode("\n", $values["responsive"]) ?></textarea>
            <br>
            <br>
            <button onclick="delete_device(<?= $number ?>, event)">delete</button>
            
        </fieldset>
    <?php
    return ob_get_clean();
}

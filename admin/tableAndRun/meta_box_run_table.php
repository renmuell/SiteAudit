<?php


function site_audit_run_table_meta_box_hanlder($item)
{

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_type'      => $item["settings"]["post_types"]
    ]);

    ?>

    <style>
        .table-wrapper {
            width:100%;
            overflow:scroll;
        }

        table  {

            border-collapse: collapse;
            border-color:#666;
            border-width:1px;
        }
        table th {
            background:#ccc;
        }

        table thead tr:nth-child(1) th {
            position: -webkit-sticky; /* for Safari */
            position: sticky;
            top: 0;
            z-index: 1;
        }

        table thead tr:nth-child(2) th {
            position: -webkit-sticky; /* for Safari */
            position: sticky;
            top: 21px;
            z-index: 1;
        }

        table thead tr:nth-child(3) th {
            position: -webkit-sticky; /* for Safari */
            position: sticky;
            top:  42px;
            z-index: 1;
        }


        table th:first-child {
            position: -webkit-sticky; /* for Safari */
            position: sticky;
            left: 0;
            z-index: 1;
        }

    </style>

    <div class="table-wrapper">
        <table border="1">
            <thead>
                <tr>
                <th>System</th>
                <?php foreach ($item["settings"]["devices"] as $device) : ?>
                    
                    <th colspan="<?= (count($device["browser"]) * (empty($device["responsive"]) ? 1 : count($device["responsive"]))) ?>" >
                        <?= $device["name"] ?>
                    </th>
                    
                <?php endforeach; ?>
                </tr>
                <tr>
                <th>Browser</th>
                <?php foreach ($item["settings"]["devices"] as $device) : ?>
                    <?php foreach ($device["browser"] as $browser) : ?>
                    <th colspan="<?= count($device["responsive"]) ?>">
                        <?= $browser ?>
                    </th>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tr>
                <tr>
                <th>Responsive</th>
                <?php foreach ($item["settings"]["devices"] as $device) : ?>
                    <?php foreach ($device["browser"] as $browser) : ?>
                        <?php if (empty($device["responsive"])) : ?>
                            <th>-</th>
                        <?php else : ?>
                            <?php foreach ($device["responsive"] as $responsive) : ?>
                            <th>
                                <?= $responsive ?>
                            </th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tr>
            </hhead>
            <tbody>
                <?php foreach ($posts as $post) : ?>
                    <tr>
                        <th>
                            <a href="<?= get_permalink($post->ID) ?>"><?= $post->post_name ?></a>
                        </th>

                        <?php foreach ($item["settings"]["devices"] as $device) : ?>
                            <?php foreach ($device["browser"] as $browser) : ?>
                                <?php if (empty($device["responsive"])) : ?>
                                    <td>
                                        <form>
                                            <input type="hidden" value="<?= $device["id"] ?>">
                                            <input type="hidden" value="<?= $browser ?>">
                                            <input type="hidden" value="<?= $responsive ?>">
                                            <input type="radio" name="result"/><label>ok</label>
                                            <br>
                                            <input type="radio" name="result"/><label>fail</label>
                                            <textarea></textarea>
                                        </form>
                                    </td>
                                <?php else : ?>
                                    <?php foreach ($device["responsive"] as $responsive) : ?>
                                        <td>
                                            <form>
                                                <input type="hidden" value="<?= $device["id"] ?>">
                                                <input type="hidden" value="<?= $browser ?>">
                                                <input type="hidden" value="<?= $responsive ?>">
                                                <input type="radio" name="result"/><label>ok</label>
                                                <br>
                                                <input type="radio" name="result"/><label>fail</label>
                                                <textarea></textarea>
                                            </form>
                                        </td>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

<?php
}
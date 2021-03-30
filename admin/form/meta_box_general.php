<?php


function site_audit_general_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
  
        <td>
        <input id="title" name="title" type="text" style="width: 95%" value="<?php echo esc_attr($item['title'])?>"
                   size="50" class="code" placeholder="<?php _e('Title', 'site-audit')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}
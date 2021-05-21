<?php
/**
 * {@internal type="x-tmpl-mustache" est requis.}
 * @var Pollen\Field\FieldViewLoaderInterface $this
 */
?>
<script <?php echo $this->htmlAttrs($this->get('tmpl.attrs', [])); ?> type="x-tmpl-mustache">
    <table class="widefat">
        <thead>
        <tr>
            <th class="found-radio"><br/></th>
            <th><?php _e('Title'); ?></th>
            <th class="no-break"><?php _e('Type'); ?></th>
            <th class="no-break"><?php _e('Date'); ?></th>
            <th class="no-break"><?php _e('Status'); ?></th>
        </tr>
        </thead>
        <tbody>
        {{#posts}}
        <tr class="found-posts {{alt}}">
            <td class="found-radio">
                <input type="radio" id="found-{{ID}}" name="found_post_id" value="{{ID}}" data-value="{{value}}">
            </td>
            <td>
                <label for="found-{{ID}}">{{post_title}}</label>
            </td>
            <td>{{post_type}}</td>
            <td>{{post_date}}</td>
            <td>{{post_status}}</td>
        </tr>
        {{/posts}}
        </tbody>
    </table>
</script>
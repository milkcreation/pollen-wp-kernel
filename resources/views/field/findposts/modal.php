<?php
/**
 * @var Pollen\Field\FieldViewLoaderInterface $this
 * @var array $post_types
 * @var array $query_args
 */
?>
<div <?php echo $this->htmlAttrs($this->get('modal.attrs', [])); ?>>
    <div class="find-box-head">
        <?php _e('Attach to existing content'); ?>
        <button type="button" data-control="findposts.modal.close">
            <span class="screen-reader-text"><?php _e('Close media attachment panel'); ?></span>
        </button>
    </div>

    <div class="find-box-inside">
        <div class="find-box-search">
            <?php if ($found_action = $this->get('found_action', '')) : ?>
                <?php echo field('hidden', [
                    'name'  => 'found_action',
                    'value' => esc_attr($found_action),
                ]); ?>
            <?php endif; ?>

            <?php if ($query_args = $this->get('query_args', [])) : ?>
                <?php echo field('hidden', [
                    'name'  => 'query_args',
                    'value' => rawurlencode(json_encode($query_args)),
                ]); ?>
            <?php endif; ?>

            <?php echo field('hidden', [
                'name'  => '_ajax_nonce',
                'value' => wp_create_nonce('Findposts'),
            ]); ?>

            <?php echo field('hidden', [
                'name'  => 'affected',
                'value' => '',
            ]); ?>

            <label class="screen-reader-text" for="FieldFindposts-modalSearch--<?php echo $this->get('uniqid'); ?>">
                <?php _e('Search'); ?>
            </label>
            <?php echo field('text', [
                'attrs' => [
                    'id'    => 'FieldFindposts-modalSearch--' . $this->get('uniqid'),
                    'style' => 'vertical-align:middle;',
                    'type'  => 'search',
                ],
                'name'  => 'ps',
                'value' => '',
            ]); ?>

            <?php if ($available_post_types = $this->get('available_post_types', [])) : ?>
                <label class="screen-reader-text" for="FieldFindposts-modalSelect--<?php echo $this->get('uniqid'); ?>">
                    <?php _e('Post type'); ?>
                </label>
                <select id="FieldFindposts-modalSelect--<?php echo $this->get('uniqid'); ?>" name="post_type">
                    <?php foreach ($available_post_types as $post_type => $label) : ?>
                        <option value="<?php echo $post_type; ?>">
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <span class="spinner" data-control="findposts.modal.spinner"></span>

            <?php echo field('button', [
                'attrs'   => [
                    'class'        => 'button button-secondary',
                    'data-control' => 'findposts.modal.search',
                    'type'         => 'submit',
                ],
                'content' => esc_attr__('Search'),
            ]); ?>

            <div class="clear"></div>
        </div>

        <div class="find-posts-response" data-control="findposts.modal.response"></div>
    </div>

    <div class="find-box-buttons">
        <?php echo field('button', [
            'attrs'   => [
                'class'        => 'button button-primary alignright',
                'data-control' => 'findposts.modal.select',
            ],
            'name'    => 'find-posts-submit',
            'content' => __('Select'),
        ]); ?>
        <div class="clear"></div>
    </div>
</div>

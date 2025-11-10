<?php
$counter = isset($params['counter']) ? $params['counter'] : rand(1000, 9999);
?>
<div id="mdt-list-<?php echo esc_attr($counter); ?>" class="mdt-list-wrapper" data-counter="<?php echo esc_attr($counter); ?>">
    <div class="mdt-list-root" data-counter="<?php echo esc_attr($counter); ?>"></div>
</div>

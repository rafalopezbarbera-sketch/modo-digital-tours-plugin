<?php
$counter = isset($params['counter']) ? $params['counter'] : rand(1000, 9999);
?>
<div id="mdt-wrapper-<?php echo esc_attr($counter); ?>" class="mdt-wrapper" data-counter="<?php echo esc_attr($counter); ?>">
    <!-- El JS montará la UI "por steps" dentro del root -->
    <div class="mdt-root" data-counter="<?php echo esc_attr($counter); ?>"></div>
</div>

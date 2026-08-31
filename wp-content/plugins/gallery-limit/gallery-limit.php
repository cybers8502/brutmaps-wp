<?php
/**
 * Plugin Name: Gallery Limit
 * Description: Обмежує кількість зображень у Gutenberg галереї до 5.
 * Version: 1.0
 * Author: Max
 */

add_filter('render_block', 'limit_gallery_images', 10, 2);

function limit_gallery_images($block_content, $block) {
    if ($block['blockName'] !== 'core/gallery') {
        return $block_content;
    }

    // Якщо є атрибути з ID зображень
    $attrs = $block['attrs'];
    if (!isset($attrs['ids']) || !is_array($attrs['ids'])) {
        return $block_content;
    }

    // Якщо ≤5 фото — нічого не змінюємо
    if (count($attrs['ids']) <= 5) {
        return $block_content;
    }

    // Залишаємо лише перші 5 фото
    $limited_ids = array_slice($attrs['ids'], 0, 5);

    // Отримуємо HTML 5-зображень
    $new_block = array_merge($block, ['attrs' => array_merge($attrs, ['ids' => $limited_ids])]);
    $limited_content = render_block($new_block);

    // Додаємо позначку про ще фото
    $rest_count = count($attrs['ids']) - 5;
    $more_text = "<div class='gallery-more'>+ ще $rest_count фото</div>";

    return $limited_content . $more_text;
}

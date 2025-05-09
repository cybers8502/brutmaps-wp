<?php

namespace Brut\Shortcodes;

class PostBannerShortcode {
    public function __construct() {
        add_shortcode('post_banner', [$this, 'render']);
    }

    public function render($atts): string {
        $atts = shortcode_atts([
            'image_id'    => '',
            'class'       => '',
            'alt'         => '',
            'size'        => 'full',
            'link'        => '',
            'link_target' => '_self',
        ], $atts, 'post_banner');

        $image_url = wp_get_attachment_image_url($atts['image_id'], $atts['size']);
        if (!$image_url) return '';

        $img_html = '<picture>
            <img src="' . esc_url($image_url) . '" alt="' . esc_attr($atts['alt']) . '" />
        </picture>';

        if (!empty($atts['link'])) {
            $img_html = '<a href="' . esc_url($atts['link']) . '" target="' . esc_attr($atts['link_target']) . '">' . $img_html . '</a>';
        }

        return '<div data-banner class="' . esc_attr($atts['class']) . '">' . $img_html . '</div>';
    }
}

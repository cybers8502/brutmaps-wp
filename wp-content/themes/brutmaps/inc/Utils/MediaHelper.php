<?php

namespace Brut\Utils;

use Brut\Utils\ContentHelper;

class MediaHelper {
    public static function getSmartImage($image, $authorID) {
        $imageID=$image['ID'];
        $size = wp_get_attachment_image_src($imageID, 'full');
        if (!$size || count($size) < 3) return null;
        return [
            'source' => $size[0],
            'width' => $size[1],
            'height' => $size[2],
            'author' => ContentHelper::getAuthorData($authorID)
        ];
    }

    public static function getImageWithSizes($imageObject)
    {
        $placeholder = defined('PLACEHOLDER') ? PLACEHOLDER : '';
        return [
            'image_full' => $imageObject['url'] ?? $placeholder,
            'image_small' => $imageObject['sizes']['thumbnail'] ?? $placeholder,
            'image_medium' => $imageObject['sizes']['medium'] ?? $placeholder,
        ];
    }
}

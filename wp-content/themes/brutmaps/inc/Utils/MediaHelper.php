<?php
namespace Brut\Utils;
//TODO link this shit
class MediaHelper {
    public static function getSmartImage($imageID, $authorID) {
        $size = wp_get_attachment_image_src($imageID, 'full');
        if (!$size || count($size) < 3) return null;
        return [
            'source' => $size[0],
            'width' => $size[1],
            'height' => $size[2],
            'author' => AuthorHelper::getAuthorData($authorID)
        ];
    }

    public static function getImageWithSizes($imageObject) {
        return [
            'image_full' => $imageObject['url'] ?? PLACEHOLDER,
            'image_small' => $imageObject['sizes']['thumbnail'] ?? PLACEHOLDER,
            'image_medium' => $imageObject['sizes']['medium'] ?? PLACEHOLDER
        ];
    }
}

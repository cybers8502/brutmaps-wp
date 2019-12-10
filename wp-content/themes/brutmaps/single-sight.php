<style type="text/css">
    body {
       background-color: blanchedalmond;
    }

</style>
<?php

$gallery = get_field('gallery', ABOUT);
foreach ($gallery as $item) {
    echo wp_get_attachment_image( $item, 'full' );
    var_dump($item);
}

?>
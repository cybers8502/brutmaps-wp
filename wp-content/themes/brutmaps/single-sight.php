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
var_dump( get_field('capital_and_state') );
var_dump( get_field('established') );
var_dump( get_field('main_image') );
var_dump( get_field('main_content') );
var_dump( get_field('gallery') );
var_dump( get_field('extra_date') );
var_dump( get_field('location') );
?>

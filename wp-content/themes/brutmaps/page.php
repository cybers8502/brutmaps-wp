<?php
get_header();

echo '<div id="root"></div>';

switch (true) {
    case is_checkout():
        while (have_posts()) :
            the_post();
            the_content();
        endwhile;
        break;

    default:
        break;
}

get_footer();

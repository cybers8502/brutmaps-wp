<?php

/*
    Template Name: About Page
*/

get_header();

$title = get_the_title();
$content = get_the_content();

?>

    <!-- site__wrap -->
    <div class="site__wrap">

        <div class="blog-article">

            <article class="blog-article__wrap article">

                <h1><?= $title ?></h1>

                <?php
                the_post();
                the_content();
                ?>

            </article>

        </div>

    </div>
    <!-- /site__wrap -->

<?php get_footer();

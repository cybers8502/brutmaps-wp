<?php
    get_header();

    $title = get_the_title();
    $main_image = get_field('main_image')[url];
    $content = get_field('main_content');
    $location = get_field('location');
    $address = $location['address'];
    $est = get_field('established');

?>

    <!-- site__wrap -->
    <div class="site__wrap">

        <div class="blog-article">

            <article class="blog-article__wrap article">

                <h1><?= $title ?></h1>

                <div class="blog-article__info">
                    <span><?= $address ?></span>
                    <time><?= $est ?></time>
                </div>

                <a href="<?= get_permalink(1) ?>#12/<?= $location['lat'] ?>/<?= $location['lng'] ?>" class="btn btn--color-1"><span>see map</span></a>

                <div class="blog-article__preview">
                    <img src="<?= $main_image ?>" alt="img"/>
                </div>

                <?= $content ?>

                <?php if( have_rows('gallery' ) ): ?>
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <?php
                            while ( have_rows('gallery') ) : the_row();
                                $imgSRC = get_sub_field('gallery_image');
                                $authorID = get_sub_field('gallery_image_author_id');
                                ?>
                                <div class="swiper-slide">
                                    <img src="<?= wp_get_attachment_image_url( $imgSRC, 'full' ) ?>" alt="<?= get_the_title( $authorID ) ?>" />
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <span class="swiper-pagination"></span>
                    </div>
                <?php endif; ?>

            </article>

        </div>

    </div>
    <!-- /site__wrap -->

<?php get_footer();

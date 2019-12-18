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

                <div class="blog-article__rails">
                    <a href="<?= get_home_url(); ?>" class="blog-article__back">
                        <svg viewBox="0 0 35 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M35 9.07104L6.1817e-07 9.07104L7.93016e-07 7.07104L35 7.07104L35 9.07104Z" fill="#DB1313"/>
                            <path d="M7.07108 16.1421L6.1817e-07 9.07104L1.41422 7.65681L8.48529 14.7279L7.07108 16.1421Z" fill="#DB1313"/>
                            <path d="M7.07108 -2.44162e-06L7.93016e-07 7.07104L1.41422 8.48528L8.48529 1.41421L7.07108 -2.44162e-06Z" fill="#DB1313"/>
                        </svg>
                    </a>
                </div>

                <h1><?= $title ?></h1>

                <div class="blog-article__info">
                    <span><?= $address ?></span>
                    <time><?= $est ?></time>
                </div>

                <a href="<?= get_permalink(1) ?>#12/<?= $location['lat'] ?>/<?= $location['lng'] ?>" class="btn btn--color-1"><span>show on the map</span></a>

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

                <h2>Where can you see it</h2>

                <address>
                    <?= $address ?>
                </address>

            </article>

            <div class="map map--single" id="single-map" data-point='[<?= $location['lng'] ?>, <?= $location['lat'] ?>]'></div>

        </div>

    </div>
    <!-- /site__wrap -->

<?php get_footer();

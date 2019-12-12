<?php
    get_header();

    $title = get_the_title();
    $main_image = get_field('main_image')[url];
    $content = get_field('main_content');
    $est = get_field('established');

?>

    <!-- site__aside -->
    <aside class="site__aside">

        <!-- site__header -->
        <header class="site__header">

            <h1 class="logo">
                <img src="<?= DIRECT ?>img/logo_brutmaps.svg" alt="brutmaps"/>
            </h1>

            <p>Global guide to the masterpieces  of brutalist architecture.</p>

        </header>
        <!-- /site__header -->

        <!-- search -->
        <div class="search" id="geocoder">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 8" fill="none">
                    <path d="M0 3.5044H18V4.4956H0V3.5044Z" fill="#2D2D2D"/>
                    <path d="M14.3634 0L18 3.5044L17.2727 4.20529L13.6361 0.700882L14.3634 0Z" />
                    <path d="M14.3634 8L18 4.4956L17.2727 3.7947L13.6361 7.29912L14.3634 8Z" />
                </svg>
            </span>
        </div>
        <!-- /search -->

        <!-- control-view -->
        <div class="control-view">
            Map viewed

            <a href="#" class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="37" height="24" viewBox="0 0 37 24" fill="none">
                    <rect x="13.5" y="0.5" width="23" height="9" stroke="#2D2D2D"/>
                    <rect x="13.5" y="13.5371" width="23" height="9" stroke="#2D2D2D"/>
                    <rect x="0.5" y="0.5" width="9" height="9" stroke="#2D2D2D"/>
                    <rect x="0.5" y="13.5371" width="9" height="9" stroke="#2D2D2D"/>
                </svg>
            </a>

        </div>
        <!-- /control-view -->

        <div class="objects-list">
            <div class="objects-list__header">Recently viewed</div>
            <div class="objects-list__layout" id="feature-listing"></div>
        </div>

        <div class="add-new-obj">
            <span role="button" class="btn btn--color-2"><span>contribute a brutalist building</span></span>
        </div>

    </aside>
    <!-- /site__aside -->

    <!-- site__wrap -->
    <div class="site__wrap">

        <div class="blog-article">

            <article class="blog-article__wrap article">

                <h1><?= $title ?></h1>

                <div class="blog-article__info">
                    <span>Mexico-city, Mexico</span>
                    <time><?= $est ?></time>
                </div>

                <a href="#" class="btn btn--color-1"><span>show on the map</span></a>

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
                                    <img src="<?= $imgSRC ?>" alt="<?= get_the_title( $authorID ) ?>" />
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <span class="swiper-pagination"></span>
                    </div>
                <?php endif; ?>

                <h2>Where can you see it</h2>

                <address>
                    <p>Mexico-city, Mexico</p>
                    <p>Mexican street 78</p>
                    <p>to the right from 7eleven</p>
                </address>

                <div class="blog-article__map map" id="map" data-map='{"point":[40.7532886, -73.97542709999999]}'></div>

            </article>

        </div>

    </div>
    <!-- /site__wrap -->

<?php get_footer();
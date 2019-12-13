<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">

    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="address=no">

    <link rel="apple-touch-icon" sizes="57x57" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= get_stylesheet_directory_uri(); ?>/assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <title>Brutmaps | Interactive map with brutalist objects | official open in October 2019</title>

    <?php wp_head() ?>

    <link rel='stylesheet' href='https://api.tiles.mapbox.com/mapbox-gl-js/v1.5.0/mapbox-gl.css' />
    <link rel="stylesheet" href="<?= DIRECT ?>css/swiper.min.css">
    <link rel="stylesheet" href="<?= DIRECT ?>css/common.css">

</head>
<body data-action="<?= admin_url( 'admin-ajax.php' );?>">

<!-- site -->
<div class="site">

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

<!--        <div class="add-new-obj">-->
<!--            <span role="button" class="btn btn--color-2"><span>contribute a brutalist building</span></span>-->
<!--        </div>-->

    </aside>
    <!-- /site__aside -->
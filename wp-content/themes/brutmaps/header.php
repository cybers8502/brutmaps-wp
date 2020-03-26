<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">

    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="address=no">

    <meta name="description" content="What is brutalism? Join our global community. Brutalism — one of the most rarely found architectural styles in the world. Join our global community. Check out our portfolio and blog.">
    <meta name="keywords" content="brutmaps, brutalism, brutalistarchitecture, brutalist architecture, architectureporn, modernism">

    <link rel="canonical" href="https://brutmaps.com/">

    <meta property="og:locale" content="en_US" />
    <meta property="og:locale:alternate" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Brutmaps | Interactive map with brutalist objects" />
    <meta property="og:description" content="What is brutalism? Join our global community. Brutalism — one of the most rarely found architectural styles in the world." />
    <meta property="og:url" content="https://brutmaps.com/" />
    <meta property="og:site_name" content="brutmaps" />
    <meta property="og:image" content="https://brutmaps.designstudio.ag/wp-content/themes/brutmaps/assets/img/brutmaps.jpg" />
    <meta property="og:image:secure_url" content="https://brutmaps.designstudio.ag/wp-content/themes/brutmaps/assets/img/brutmaps.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="627" />
    <meta property="og:image:alt" content="brutmaps.ag" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="Join our global community. Check out our portfolio and blog." />
    <meta name="twitter:title" content="Brutmaps | Interactive map with brutalist objects" />
    <meta name="twitter:image" content="https://brutmaps.designstudio.ag/wp-content/themes/brutmaps/assets/img/brutmaps.jpg" />

    <meta name="google-site-verification" content="HBnn1N3y4HNw0tpUZe5QhPK040cPYdvLYtdMoVZf4_4" />
    <meta name="msvalidate.01" content="919FB0116B97158137D6FFC414803F41" />
    <meta name="yandex-verification" content="ba79fc04a5c3300e" />

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

    <?php wp_head() ?>

    <?php if ( is_front_page() ): ?>
        <title><?= get_bloginfo( 'name' ) ?> | <?= get_bloginfo( 'description' ) ?></title>
    <?php else:  ?>
        <title><?php wp_title(''); ?> | <?= get_bloginfo( 'name' ) ?></title>
    <?php endif; ?>

</head>
<body data-action="<?= admin_url( 'admin-ajax.php' );?>" data-url="<?= get_home_url() ?>">

<!-- site -->
<div class="site">
    <?php var_dump(getArchitects()); ?>
    <!-- site__aside -->
    <aside class="site__aside">

        <!-- site__header -->
        <header class="site__header">

            <?php if( is_front_page() ): ?>

                <h1 class="logo">
                    <img src="<?= DIRECT ?>img/logo_brutmaps.svg" alt="brutmaps"/>
                </h1>

            <?php else: ?>

                <a href="<?= get_home_url() ?>" class="logo">
                    <img src="<?= DIRECT ?>img/logo_brutmaps.svg" alt="brutmaps"/>
                </a>

            <?php endif; ?>

            <p>Global guide to the masterpieces  of brutalist architecture.</p>

        </header>
        <!-- /site__header -->

        <?php if ( !is_page_template( 'pages/article-page.php' ) ): ?>
        <!-- search -->
        <?php  get_template_part('components/content', 'search'); ?>
        <!-- /search -->

        <!-- control-view -->
        <?php  get_template_part('components/content', 'control-view'); ?>
        <!-- /control-view -->

        <!-- control-view -->
        <?php  get_template_part('components/content', 'objects-list'); ?>
        <!-- /control-view -->

<!--        <div class="add-new-obj">-->
<!--            <span role="button" class="btn btn--color-2"><span>contribute a brutalist building</span></span>-->
<!--        </div>-->

        <?php endif; ?>

    </aside>
    <!-- /site__aside -->

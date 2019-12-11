<?php
    get_header();
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

            <a href="#" class="blog-article__back">
                <svg viewBox="0 0 35 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M35 9.07104L6.1817e-07 9.07104L7.93016e-07 7.07104L35 7.07104L35 9.07104Z" fill="#DB1313"/>
                    <path d="M7.07108 16.1421L6.1817e-07 9.07104L1.41422 7.65681L8.48529 14.7279L7.07108 16.1421Z" fill="#DB1313"/>
                    <path d="M7.07108 -2.44162e-06L7.93016e-07 7.07104L1.41422 8.48528L8.48529 1.41421L7.07108 -2.44162e-06Z" fill="#DB1313"/>
                </svg>
            </a>

            <article class="blog-article__wrap article">

                <h1>Paseo De Las Palmas</h1>

                <div class="blog-article__info">
                    <span>Mexico-city, Mexico</span>
                    <time>1975</time>
                </div>

                <a href="#" class="btn btn--color-1"><span>show on the map</span></a>

                <!-- blog-article__text -->
                <div class="blog-article__text">

                    <div class="blog-article__preview">
                        <img src="pic/img-001.jpg" alt="img"/>
                    </div>

                    <h3>History</h3>

                    <p>This building has a nine-story tower for offices and a two-story plinth for commercial premises. Each floor is horizontally displaced in order to generate its unmistakable staggered profile. Despite its apparent instability, the rigid-frame structure transmits loads vertically throughout the whole height of the building. Each level is formed by a solid parapet clad in dark aluminum above which there runs a perimeter ribbon window. The plinth is clad with a polished red granite surface. Labeling it an example of a “counter-trend” in the context of Mexican modernity and of Sordo Madaleno’s own career, Alberto González Pozo points out that the building “demonstrates that abandoning­ orthogonal order is not a prerequisite for achieving provocative forms.” Due to its avant-garde shape, this building is nowadays one of the architect’s most famous buildings</p>

                    <h3>Current State</h3>

                    <p>Despite its apparent instability, the rigid-frame structure transmits loads vertically throughout the whole height of the building. Each level is formed by a solid parapet clad in dark aluminum above which there runs a perimeter ribbon window. The plinth is clad with a polished red granite surface. Labeling it an example of a “counter-trend”.</p>

                    <blockquote>
                        <p>It was the first buiding I have ever seen in my life</p>
                        <p>Jeniffer Lopez have said</p>
                    </blockquote>

                    <h3>Current State</h3>

                    <p>Despite its apparent instability, the rigid-frame structure transmits loads vertically throughout the whole height of the building. Each level is formed by a solid parapet clad in dark aluminum above which there runs a perimeter ribbon window. The plinth is clad with a polished red granite surface. Labeling it an example of a “counter-trend”:</p>

                    <ul>
                        <li>rigid-frame structure</li>
                        <li>ribbon window.</li>
                        <li>a perimeter</li>
                    </ul>

                    <h3>Current State</h3>

                    <p>Despite its apparent instability, the rigid-frame structure transmits loads vertically throughout the whole height of the building. Each level is formed by a solid parapet clad in dark aluminum above which there runs a perimeter ribbon window. The plinth is clad with a polished red granite surface. Labeling it an example of a “counter-trend”:</p>

                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img src="pic/img-002.jpg" alt="img"/>
                            </div>
                            <div class="swiper-slide">
                                <img src="pic/img-002.jpg" alt="img"/>
                            </div>
                            <div class="swiper-slide">
                                <img src="pic/img-002.jpg" alt="img"/>
                            </div>
                            <div class="swiper-slide">
                                <img src="pic/img-002.jpg" alt="img"/>
                            </div>
                            <div class="swiper-slide">
                                <img src="pic/img-002.jpg" alt="img"/>
                            </div>
                        </div>
                        <span class="swiper-pagination"></span>
                    </div>

                    <h3>Current State</h3>

                    <p>Despite its apparent instability, the rigid-frame structure transmits loads vertically throughout the whole height of the building. Each level is formed by a solid parapet clad in dark aluminum above which there runs a perimeter ribbon window. The plinth is clad with a polished red granite surface. Labeling it an example of a “counter-trend”:</p>

                    <img src="pic/img-004.jpg" alt="img"/>

                    <h2>Where can you see it</h2>

                    <address>
                        <p>Mexico-city, Mexico</p>
                        <p>Mexican street 78</p>
                        <p>to the right from 7eleven</p>
                    </address>

                    <div class="blog-article__map map" id="map" data-map='{"point":[40.7532886, -73.97542709999999]}'></div>

                </div>
                <!-- /blog-article__text -->

            </article>

        </div>

    </div>
    <!-- /site__wrap -->

<?php get_footer();
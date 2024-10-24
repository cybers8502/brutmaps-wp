<?php

    /*
        Template Name: Support Page
    */

    get_header();

    $title = get_the_title();
    $content = get_the_content();

?>

    <!-- site__wrap -->
    <div class="site__wrap">

        <div class="support">

            <div class="article">

                <h1><?= $title ?></h1>

                <?php
                    the_post();
                    the_content();
                ?>

            </div>

            <!-- support -->
            <form class="support__form form js-send-request" novalidate>

                <input type="text" name="s-name" placeholder="Your name" required />
                <input type="email" name="s-email" placeholder="Your email" required />
                <textarea name="s-message" placeholder="Message" required ></textarea>

                <div class="support__message"></div>

                <button type="submit" class="btn btn--color-1"><span>Send</span></button>


                <div class="support__loader">
                    <div class="loader"><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/></div>
                </div>

            </form>
            <!-- /support -->

        </div>

    </div>
    <!-- /site__wrap -->

<?php get_footer();

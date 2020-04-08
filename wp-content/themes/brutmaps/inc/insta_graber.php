<?php

// Add Custom Elements To Admin Panel
class II_Admin {

    public function __construct() {
        add_action('admin_init', array($this, 'wp_db_backup_admin_init'));
        add_filter('views_edit-sight', array($this, 'my_filter') );
        add_action('admin_enqueue_scripts', array($this, 'my_enqueue') );
        add_action('admin_menu', array($this, 'register_my_custom_submenu_page') );
    }

    function my_filter($views){
        $views['import'] = '<a href="'. site_url() .'/wp-admin/edit.php?post_type=sight&page=instagramm-import-post" id="instagram-importer">Import</a>';
        return $views;
    }

    function my_enqueue($hook) {
        wp_enqueue_script('admin_script', get_template_directory_uri().'/assets/js/admin.min.js?v='. $hook);
        wp_enqueue_style('admin_style', get_template_directory_uri().'/assets/css/admin-page.css');
    }

    function register_my_custom_submenu_page() {
        add_submenu_page(
            'edit.php?post_type=sight',
            'Import from Instagram',
            'Import',
            'manage_options',
            'instagramm-import-post',
            array($this, 'my_custom_submenu_page_callback' ) );
    }

    function my_custom_submenu_page_callback() { ?>
        <div class="wrap insta-graber">

            <h1 class="wp-heading-inline"><?= get_admin_page_title() ?></h1>
            <a href="<?=  site_url() ?>/wp-admin/edit.php?post_type=sight&page=instagramm-import-post" class="page-title-action">New Import</a>
            <hr class="wp-header-end">

            <form action="<?=  site_url() ?>/wp-admin/edit.php?post_type=sight&page=instagramm-import-post" method="post" class="insta-graber__wrap" id="insta-graber__form">

                <label class="insta-graber__title">
                    <input type="text" name="post_title" size="30" id="title" placeholder="Add Title" autocomplete="off"/>
                </label>

                <label class="insta-graber__fildset">
                    <span class="insta-graber__label">Link on Instagram Post <i>*</i></span>
                    <input type="text" name="url" placeholder="https://" autocomplete="off" required />
                </label>

                <div style="margin-top: 30px">
                    <button id="insta-graber__import-btn" type="submit" class="button button-primary button-large">Publish</button>
                </div>

            </form>

            <?php

            if ( isset( $_GET['page'] ) && $_GET['page'] == 'instagramm-import-post' && isset( $_POST['url'] ) ) {

                $data = $this->wp_insta_import_get_data($_POST['url'] );
                $result = $this->wp_insta_create_new_post( $data );

                echo '<p><a href="http://localhost:8888/brutmaps/wp-admin/post.php?post='. $result .'&action=edit">Edit Object</a></p>';

                if ( $data['status'] !== 'success' ){
                    echo $data;
                }

            }
            ?>

        </div>
    <?php }

    function wp_insta_import_get_data( $url ) {

        require_once ( TEMPLATEPATH .'/insta_graber/autoload.php' );;

        $instagram = new \InstagramScraper\Instagram();
        $media = $instagram->getMediaByUrl( $url );
        $account = $media->getOwner();
        $data = array();

        function printMediaInfo( \InstagramScraper\Model\Media $media ) {

            $arr = array();

            $arr['id'] = $media->getId();
            $arr['type_media'] = $media->getType();
            $arr['short_code'] = $media->getShortCode();
            $arr['link'] = $media->getImageHighResolutionUrl();

            return $arr;
        }

        $data['user'] = $account->getUsername();
        $data['fullName'] = $account->getFullName();

        $data['cover'] = printMediaInfo( $media );
        $data['cover']['caption'] = $media->getCaption();

        if ( $media->getSidecarMedias() ):
            foreach ($media->getSidecarMedias() as $sidecarMedia) {
                $data['slider'][] = printMediaInfo( $sidecarMedia );
            }
        endif;

        $data['status'] = 'success';

        return $data;

    }

    function wp_insta_create_new_post( $data ){

        $title = $_POST['post_title'];
        if ( !$title )
            $title = 'New Insta Post';

        $cover = $data['cover'];
        $description = $cover['caption'];
        $short_code = $cover['short_code'];
        $authorID = $this->wp_insta_create_update_author( $data );
        $imagesID = [];

        // create post
        $args = [
            'post_title'    => $title,
            'post_type'     => 'sight',
            'post_status'   => 'pending'
        ];

        $sightID = wp_insert_post($args);

        $main_pic = $this->wp_insta_upload( $cover['link'], $title, $sightID );
        $contributor = $this->wp_insta_create_update_contributor( $sightID );

        $index = 0;

        foreach ( $data['slider'] as $image) {

            if ( $index == 0 ){
                $index++;
                continue;
            }

            $imagesID[] = $this->wp_insta_upload( $image['link'], $title, $sightID );

        }

        // fill up post
        if (!is_null($sightID)) {
            update_field('main_content', $description, $sightID);
            update_field('main_image', $main_pic, $sightID);
            update_field('main_image_author_id', $authorID, $sightID);
            update_field('source', $_POST['url'], $sightID);
            update_field('contributor', $contributor, $sightID);

            foreach ( $imagesID as $imageID) {
                $row = array(
                    'gallery_image' => $imageID,
                    'gallery_image_author_id' => $authorID
                );
                add_row('gallery', $row, $sightID);
            }

        }

        return $sightID;

    }

    function wp_insta_upload( $url, $name, $id ){

        $tmp = download_url($url);
        $title = str_replace( ' ', '-', $name ) .'-'. time();

        $file_array = [
            'name'     => $title .'.jpg',
            'tmp_name' => $tmp,
            'error'    => 0,
            'size'     => filesize($tmp)
        ];

        $id = media_handle_sideload( $file_array, $id, $name );

        if( is_wp_error( $id ) ) {
            @unlink($file_array['tmp_name']);
            return $id->get_error_messages();
        }

        @unlink( $tmp );

        return $id;

    }

    function wp_insta_create_update_author( $data ) {

        $user = '@'. $data['user'];
        $fullName = $data['fullName'];
        $link = 'https://www.instagram.com/'. $data['user'];


        $args = array(
            'numberposts'   => 1,
            'post_type'		=> 'authors',
            'fields'        => 'ids',
            'post_status'   => array('publish', 'pending', 'draft'),
            'meta_key'		=> 'instagram',
            'meta_value'	=> $user
        );

        $author = get_posts($args);

        if ( count( $author ) > 0 ) {

            $authorID = $author[0];

        } else {

            $args = [
                'post_title'    => $fullName ? $fullName : $user,
                'post_type'     => 'authors',
                'post_status'   => 'publish'
            ];

            $authorID = wp_insert_post($args);

            if ( is_int($authorID) && $authorID > 0) {
                update_field('link', $link, $authorID);
                update_field('instagram', $user, $authorID);
            }

        }

        $authorID = intval( $authorID );


        return $authorID;

    }

    function wp_insta_create_update_contributor( $sightID ) {

        $user = wp_get_current_user();
        $email = $user->user_email;

        $args = array(
            'numberposts'   => 1,
            'post_type'		=> 'contributor',
            'fields'        => 'ids',
            'post_status'   => array('publish', 'pending', 'draft'),
            'meta_key'		=> 'email',
            'meta_value'	=> $email
        );

        $contributor = get_posts($args);

        if (count($contributor) > 0) {
            $contributorID = $contributor[0];

            $linkedSights = get_field('linked_sights', $contributorID);

            if (is_null($linkedSights)) {
                $linkedSights = [];
            }

            $linkedSights[] = $sightID;
        } else {
            $args = [
                'post_title'    => $email,
                'post_type'     => 'contributor',
                'post_status'   => 'publish'
            ];
            $contributorID = wp_insert_post($args);
            $linkedSights = [$sightID];

            update_field('email', $email, $contributorID);

        }

        $contributorID = intval($contributorID);

        if (is_int($contributorID) && $contributorID > 0) {
            update_field('linked_sights', $linkedSights, $contributorID);
        }

        return $contributorID;

    }

}

return new II_Admin();

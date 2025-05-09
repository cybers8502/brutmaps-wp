<?php

require_once get_template_directory() . '/inc/ThemeSetupService.php';

require_once get_template_directory() . '/inc/Admin/AcfConfigService.php';

require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ArchitectACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/AuthorsACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ContributorACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ProductACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/SightsACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ThemeSetupOptionsACFFieldsManager.php';

require_once get_template_directory() . '/inc/Admin/PostTypes/ArchitectPostTypeRegistrar.php';
require_once get_template_directory() . '/inc/Admin/PostTypes/AuthorsPostTypeRegistrar.php';
require_once get_template_directory() . '/inc/Admin/PostTypes/ContributorPostTypeRegistrar.php';
require_once get_template_directory() . '/inc/Admin/PostTypes/SightsPostTypeRegistrar.php';

require_once get_template_directory() . '/inc/Admin/AdminMenuManager.php';

require_once get_template_directory() . '/inc/Ajax/Controllers/AuthorsController.php';
require_once get_template_directory() . '/inc/Ajax/Controllers/CartController.php';

require_once get_template_directory() . '/inc/Assets/AssetManager.php';

require_once get_template_directory() . '/inc/Rest/ApiRouter.php';

require_once get_template_directory() . '/inc/Rest/Controllers/AuthController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/FavoritesController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/PasswordController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/PostsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/ProductsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/SightsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/UserProfileController.php';

require_once get_template_directory() . '/inc/Routing/PermalinkService.php';

require_once get_template_directory() . '/inc/Security/CorsService.php';
require_once get_template_directory() . '/inc/Security/JwtService.php';

require_once get_template_directory() . '/inc/Services/MailchimpService.php';
require_once get_template_directory() . '/inc/Services/MediaUploadService.php';
require_once get_template_directory() . '/inc/Services/RateLimiter.php';
require_once get_template_directory() . '/inc/Services/UserDeletionService.php';
require_once get_template_directory() . '/inc/Services/UserService.php';

require_once get_template_directory() . '/inc/Shortcodes/PostBannerShortcode.php';

require_once get_template_directory() . '/inc/Utils/ContentHelper.php';
require_once get_template_directory() . '/inc/Utils/MediaHelper.php';
require_once get_template_directory() . '/inc/Utils/PostHelper.php';
require_once get_template_directory() . '/inc/Utils/RequestSanitizer.php';
require_once get_template_directory() . '/inc/Utils/ResponseHelper.php';
require_once get_template_directory() . '/inc/Utils/UserMetaHelper.php';
require_once get_template_directory() . '/inc/Utils/ValidatorHelper.php';

(new \Brut\ThemeSetupService());

(new \Brut\Rest\ApiRouter());

(new \Brut\Assets\AssetManager());

(new \Brut\Security\CorsService());

(new \Brut\Routing\PermalinkService())

(new \Brut\Shortcodes\PostBannerShortcode());

(new \Brut\Ajax\Controllers\AuthorsController());
(new \Brut\Ajax\Controllers\CartController());

add_action('plugins_loaded', function () {
    (new \Brut\Admin\AdminMenuManager());

    (new \Brut\Admin\AcfConfigService())->boot();

    (new \Brut\Admin\PostTypes\ArchitectPostTypeRegistrar())->boot();
    (new \Brut\Admin\PostTypes\AuthorsPostTypeRegistrar())->boot();
    (new \Brut\Admin\PostTypes\ContributorPostTypeRegistrar())->boot();
    (new \Brut\Admin\PostTypes\SightsPostTypeRegistrar())->boot();

    (new \Brut\Admin\ACFFieldsManager\ArchitectACFFieldsManager())->boot();
    (new \Brut\Admin\ACFFieldsManager\AuthorsACFFieldsManager())->boot();
    (new \Brut\Admin\ACFFieldsManager\ContributorACFFieldsManager())->boot();
    (new \Brut\Admin\ACFFieldsManager\ProductACFFieldsManager())->boot();
    (new \Brut\Admin\ACFFieldsManager\SightsACFFieldsManager())->boot();
    (new \Brut\Admin\ACFFieldsManager\ThemeSetupOptionsACFFieldsManager())->boot();
});

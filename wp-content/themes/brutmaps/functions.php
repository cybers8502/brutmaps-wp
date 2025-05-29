<?php

require_once get_template_directory() . '/inc/App.php';

require_once get_template_directory() . '/inc/Admin/AcfConfigService.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldConfig.php';
require_once get_template_directory() . '/inc/Admin/ThemeSetupService.php';

require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ArchitectACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/AuthorsACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ContributorACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ProductACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/SightsACFFieldsManager.php';
require_once get_template_directory() . '/inc/Admin/ACFFieldsManager/ThemeSetupOptionsACFFieldsManager.php';

require_once get_template_directory() . '/inc/Admin/Dashboard/PopularArchitectsWidget.php';

require_once get_template_directory() . '/inc/Admin/Filters/SightAdminFiltersService.php';

require_once get_template_directory() . '/inc/Admin/PostTypes/ArchitectPostTypeRegistrar.php';
require_once get_template_directory() . '/inc/Admin/PostTypes/AuthorsPostTypeRegistrar.php';
require_once get_template_directory() . '/inc/Admin/PostTypes/ContributorPostTypeRegistrar.php';
require_once get_template_directory() . '/inc/Admin/PostTypes/SightsPostTypeRegistrar.php';

require_once get_template_directory() . '/inc/Admin/AdminMenuManager.php';

require_once get_template_directory() . '/inc/Ajax/Controllers/CartController.php';

require_once get_template_directory() . '/inc/Assets/AssetManager.php';

require_once get_template_directory() . '/inc/Rest/ApiRouter.php';

require_once get_template_directory() . '/inc/Rest/Controllers/ArchitectsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/AuthController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/FavoritesController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/PasswordController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/PostsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/ProductsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/ObjectsController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/TaxonomyController.php';
require_once get_template_directory() . '/inc/Rest/Controllers/UserProfileController.php';

require_once get_template_directory() . '/inc/Routing/PermalinkService.php';

require_once get_template_directory() . '/inc/Security/CorsService.php';
require_once get_template_directory() . '/inc/Security/JwtService.php';

require_once get_template_directory() . '/inc/Services/ArchitectStatsService.php';
require_once get_template_directory() . '/inc/Services/CacheService.php';
require_once get_template_directory() . '/inc/Services/MailchimpService.php';
require_once get_template_directory() . '/inc/Services/MediaUploadService.php';
require_once get_template_directory() . '/inc/Services/RateLimiter.php';
require_once get_template_directory() . '/inc/Services/TaxonomyService.php';
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

(new \Brut\App())->boot();

if (defined('WP_CLI') && WP_CLI) {
    require_once get_template_directory() . '/inc/Console/ClearCacheCommand.php';

    \Brut\Console\ClearCacheCommand::register();
}


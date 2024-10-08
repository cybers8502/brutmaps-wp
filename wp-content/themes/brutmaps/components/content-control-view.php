<?php if( is_front_page() ): ?>
    <div class="control-view js-control-view">
        <span>Map view</span>

        <span class="control-view__btn">
            <img src="<?= DIRECT ?>img/icon-list-view.svg" alt="view"/>
        </span>

    </div>
<?php else:
    $location = get_field('location');
?>
    <div class="control-view">
        <span>Map view</span>

        <a href="<?= get_home_url() ?>#12/<?= $location['lat'] ?>/<?= $location['lng'] ?>" class="control-view__btn">
            <img src="<?= DIRECT ?>img/icon-map-view.svg" alt="view"/>
        </a>

    </div>
<?php endif; ?>

<?php if( get_the_ID() == 1 ): ?>
    <div class="control-view js-control-view">
        <span>Map view</span>

        <a href="#" class="control-view__btn">
            <img src="<?= DIRECT ?>img/icon-list-view.svg" alt="view"/>
        </a>

    </div>
<?php else: ?>
    <div class="control-view">
        <span>Map view</span>

        <a href="<?= get_permalink( 1 ) ?>" class="control-view__btn">
            <img src="<?= DIRECT ?>img/icon-map-view.svg" alt="view"/>
        </a>

    </div>
<?php endif; ?>
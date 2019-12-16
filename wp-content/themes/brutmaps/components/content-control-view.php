<div class="control-view">
    Map viewed

    <?php if( get_the_ID() == 1 ): ?>

        <a href="#" class="control-view__btn js-view-btn">
            <img src="<?= DIRECT ?>img/icon-list-view.svg" alt="view"/>
        </a>

    <?php else: ?>

        <a href="<?= get_permalink( 1 ) ?>" class="control-view__btn">
            <img src="<?= DIRECT ?>img/icon-map-view.svg" alt="view"/>
        </a>

    <?php endif; ?>

</div>
<div class="objects-list is-loading">

    <?php if( get_the_ID() == 1 ): ?>

        <div class="objects-list__layout" id="js-feature-listing"></div>

    <?php else: ?>

        <div class="objects-list__header">Nearest objects</div>
        <div class="objects-list__layout" id="js-nearest-objects"></div>

    <?php endif; ?>

    <div class="loader"><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/></div>

</div>
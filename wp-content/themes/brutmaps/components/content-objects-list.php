<div class="objects-list">

    <?php if( get_the_ID() == 1 ): ?>

        <div class="objects-list__layout" id="feature-listing"></div>

    <?php else: ?>

        <div class="objects-list__header">Nearest objects</div>
        <div class="objects-list__layout" id="nearest-objects"></div>

    <?php endif; ?>

</div>
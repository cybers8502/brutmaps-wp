<?php if( get_the_ID() == 1 ): ?>

    <div class="search" id="geocoder">
            <span class="objects-list__btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 8" fill="none">
                    <path d="M0 3.5044H18V4.4956H0V3.5044Z" fill="#2D2D2D"/>
                    <path d="M14.3634 0L18 3.5044L17.2727 4.20529L13.6361 0.700882L14.3634 0Z" />
                    <path d="M14.3634 8L18 4.4956L17.2727 3.7947L13.6361 7.29912L14.3634 8Z" />
                </svg>
            </span>
    </div>

<?php else: ?>

    <div class="search" id="js-single-geocoder">
            <span class="objects-list__btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 8" fill="none">
                    <path d="M0 3.5044H18V4.4956H0V3.5044Z" fill="#2D2D2D"/>
                    <path d="M14.3634 0L18 3.5044L17.2727 4.20529L13.6361 0.700882L14.3634 0Z" />
                    <path d="M14.3634 8L18 4.4956L17.2727 3.7947L13.6361 7.29912L14.3634 8Z" />
                </svg>
            </span>
    </div>

<?php endif; ?>
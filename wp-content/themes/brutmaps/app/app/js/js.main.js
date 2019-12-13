( function(){

    let _articleGallery;
    let _articleMap;

    ( _articleGallery = document.querySelector( '.blog-article .swiper-container' ) ) && InitArticleSlider();
    ( _articleMap = document.querySelector( '#single-map' ) ) && InitSingleArticle();

    function InitArticleSlider() {

        let _swiper = new Swiper( _articleGallery, {
            slidesPerView: 1,
            spaceBetween: 21,
            threshold: 10,
            pagination: {
                el: '.swiper-pagination',
                type: 'custom',
            },
            breakpoints: {
                768: {
                    spaceBetween: 10
                }
            }
        } );

    }

    function InitSingleArticle() {

        let _object = JSON.parse( _articleMap.dataset.point );

        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        let _mapFrame = new mapboxgl.Map( {
            container: 'single-map',
            zoom: 9,
            hash: false,
            style: 'mapbox://styles/cybers8502/cjpb47lj66ufh2spadk5auttp',
            center: _object
        } );

        var marker = document.createElement( 'div' );
        marker.className = 'map__marker';

        new mapboxgl.Marker( marker )
            .setLngLat( _object )
            .addTo( _mapFrame );

        console.log( marker )

    };

} )();
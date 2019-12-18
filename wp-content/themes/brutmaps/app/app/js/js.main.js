( function(){

    let _articleGallery;
    let _articleMap;
    let _controlViewBtn;
    let _nearest0bjects;
    let _singleGeocoder;

    if ( _articleGallery = document.querySelector( '.blog-article .swiper-container' ) )
        InitArticleSlider();

    if ( _articleMap = document.querySelector( '#single-map' ) )
        InitSingleArticleMap();

    if ( _controlViewBtn = document.querySelector( '.js-view-btn' ) )
        ControlListView();

    if ( _nearest0bjects = document.querySelector( '#nearest-objects' ) )
        Nearest0bjects();

    function ControlListView() {

        let tableViewStatus = false;
        let objTable = document.querySelector( '.objects-list' );
        let btnImg = _controlViewBtn.querySelector( 'img' );
        let btnImgSrc = btnImg.src;
        let btnImgSrcArr = btnImgSrc.split('/');

        _controlViewBtn.addEventListener( 'click', (e) => {
            e.preventDefault();
            _changeView();
        } );

        function _changeView() {

            if ( tableViewStatus ){

                objTable.classList.remove( 'is-show' );
                btnImgSrcArr[ btnImgSrcArr.length - 1 ] = 'icon-list-view.svg';
                btnImg.src = btnImgSrcArr.join( '/' );
                tableViewStatus = false;

            } else {

                objTable.classList.add( 'is-show' );
                btnImgSrcArr[ btnImgSrcArr.length - 1 ] = 'icon-map-view.svg';
                btnImg.src = btnImgSrcArr.join( '/' );
                tableViewStatus = true;

            }

        }


    }

    function InitArticleSlider() {

        let slide = _articleGallery.querySelectorAll( '.swiper-slide' );

        if( slide.length === 1 )
            return;

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

    function InitSingleArticleMap() {

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

    }

    function Nearest0bjects() {

        let data = localStorage.getItem( 'brutList' );

        if ( !data )
            return;

        _nearest0bjects.innerHTML = data;

    }

} )();
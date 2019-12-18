let articleGallery;
let articleMap;
let controlViewBtn;
let nearest0bjects;

if ( articleGallery = document.querySelector( '.blog-article .swiper-container' ) )
    InitArticleSlider();

if ( articleMap = document.querySelector( '#single-map' ) )
    InitSingleArticleMap();

if ( controlViewBtn = document.querySelector( '.js-view-btn' ) )
    ControlListView();

if ( nearest0bjects = document.querySelector( '#nearest-objects' ) )
    Nearest0bjects();

function BehaviorSearchForm( e, obj ) {

    var _btn = obj.querySelector( '.objects-list__btn' );

    e.on( 'loading', () => {
        _btn.style.setProperty( 'opacity', 0 );
    } );

    e.on( 'results', () => {
        _btn.style.setProperty( 'opacity', 1 );
    } );

}

function ControlListView() {

    let _tableViewStatus = false;
    let _objTable = document.querySelector( '.objects-list' );
    let _btnImg = controlViewBtn.querySelector( 'img' );
    let _btnImgSrc = _btnImg.src;
    let _btnImgSrcArr = _btnImgSrc.split('/');

    controlViewBtn.addEventListener( 'click', (e) => {
        e.preventDefault();
        _changeView();
    } );

    function _changeView() {

        if ( _tableViewStatus ){

            _objTable.classList.remove( 'is-show' );
            _btnImgSrcArr[ _btnImgSrcArr.length - 1 ] = 'icon-list-view.svg';
            _btnImg.src = _btnImgSrcArr.join( '/' );
            _tableViewStatus = false;

        } else {

            _objTable.classList.add( 'is-show' );
            _btnImgSrcArr[ _btnImgSrcArr.length - 1 ] = 'icon-map-view.svg';
            _btnImg.src = _btnImgSrcArr.join( '/' );
            _tableViewStatus = true;

        }

    }

}

function InitArticleSlider() {

    let _slide = articleGallery.querySelectorAll( '.swiper-slide' );

    if( _slide.length === 1 )
        return;

    let _swiper = new Swiper( articleGallery, {
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

    let _object = JSON.parse( articleMap.dataset.point );

    mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

    let _mapFrame = new mapboxgl.Map( {
        container: 'single-map',
        zoom: 9,
        hash: false,
        style: 'mapbox://styles/cybers8502/cjpb47lj66ufh2spadk5auttp',
        center: _object
    } );

    var _marker = document.createElement( 'div' );
    _marker.className = 'map__marker';

    new mapboxgl.Marker( _marker )
        .setLngLat( _object )
        .addTo( _mapFrame );

    var _geocoderWrap = document.getElementById( 'js-single-geocoder' );

    var _geocoder = new MapboxGeocoder( {
        accessToken: mapboxgl.accessToken,
        mapboxgl: mapboxgl,
        placeholder: "Type address",
        marker: false,
        flyTo: false,
        setLanguage: 'en-GB'
    } );

    BehaviorSearchForm( _geocoder, _geocoderWrap );

    _geocoderWrap.appendChild( _geocoder.onAdd( _mapFrame ) );

    _geocoder.on( 'result', (e) => {
        var homeURL = document.body.dataset.url;
        localStorage.setItem( 'searchGeo', e.result.place_name );
        window.location.replace( `${homeURL}#12/${e.result.center[1]}/${e.result.center[0]}` );
    } );

}

function Nearest0bjects() {

    let _data = localStorage.getItem( 'brutList' );

    if ( !_data )
        return;

    nearest0bjects.innerHTML = _data;

}
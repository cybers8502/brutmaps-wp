let articleGallery;
let articleMap;
let controlViewBtn;
let nearest0bjects;
let blogBackRails;

if ( articleGallery = document.querySelector( '.blog-article .swiper-container' ) )
    InitArticleSlider();

if ( articleMap = document.querySelector( '#single-map' ) )
    InitSingleArticleMap();

if ( controlViewBtn = document.querySelector( '.js-view-btn' ) )
    ControlListView();

if ( nearest0bjects = document.querySelector( '#js-nearest-objects' ) )
    Nearest0bjects();

if ( blogBackRails = document.querySelector( '.blog-article__rails' ) )
    BlogBackRails();

function BehaviorSearchForm( e, obj ) {

    var _btn = obj.querySelector( '.objects-list__btn' );

    e.on( 'loading', () => {
        _btn.style.setProperty( 'opacity', 0 );
    } );

    e.on( 'results', () => {
        _btn.style.setProperty( 'opacity', 1 );
    } );

}

function BlogBackRails() {

    let _backBtn = blogBackRails.querySelector( '.blog-article__back' );
    let _btnHeight = _backBtn.getBoundingClientRect().height;

    window.addEventListener( 'scroll', _onScroll );
    window.addEventListener( 'resize', () => {
        _btnHeight = _backBtn.getBoundingClientRect().height;
        _onScroll();
    } );

    function _onScroll() {
        if ( blogBackRails.getBoundingClientRect().top - 40 <= 0 ){
            _backBtn.style.position ='fixed';
            _backBtn.style.top = '40px';
            _backBtn.style.left = blogBackRails.getBoundingClientRect().left +'px';

            if ( blogBackRails.getBoundingClientRect().bottom <= _btnHeight + 40 ){
                _backBtn.style.position ='absolute';
                _backBtn.style.top = 'auto';
                _backBtn.style.bottom = '0px';
                _backBtn.style.left = '0px';
            } else {
                _backBtn.style.bottom = 'auto';
            }

        } else {
            _backBtn.removeAttribute( 'style' )
        }

    }

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
    let _slidePaginarion = articleGallery.querySelector( '.swiper-pagination' );

    if( _slide.length === 1 )
        return;

    let carriageWrap;
    let carriageStep = _slidePaginarion.getBoundingClientRect().width / _slide.length;

    let _swiper = new Swiper( articleGallery, {
        slidesPerView: 1,
        spaceBetween: 21,
        threshold: 10,
        scrollbar: {
            el: _slidePaginarion,
            draggable: true
        },
        breakpoints: {
            768: {
                spaceBetween: 10
            }
        },
        mousewheel: {
            invert: true,
            releaseOnEdges: true
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
        sessionStorage.setItem( 'searchGeo', e.result.place_name );
        window.location.replace( `${homeURL}#12/${e.result.center[1]}/${e.result.center[0]}` );
    } );

}

function Nearest0bjects() {

    let _data = localStorage.getItem( 'brutList' );

    if ( !_data )
        return;

    nearest0bjects.innerHTML = _data;

    document.querySelector( '.objects-list' ).classList.remove( 'is-loading' );

}
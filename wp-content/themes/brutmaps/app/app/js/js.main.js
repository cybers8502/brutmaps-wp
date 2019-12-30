let articleGallery;
let articleMap;
let controlViewBtn;
let nearest0bjects;
let blogBackRails;
let formValidation;

if ( articleGallery = document.querySelector( '.blog-article .swiper-container' ) )
    InitArticleSlider();

if ( articleMap = document.querySelector( '#single-map' ) )
    InitSingleArticleMap();

if ( controlView = document.querySelector( '.js-control-view' ) )
    ControlListView();

if ( nearest0bjects = document.querySelector( '#js-nearest-objects' ) )
    Nearest0bjects();

if ( blogBackRails = document.querySelector( '.blog-article__rails' ) )
    BlogBackRails();

if ( formValidation = document.querySelector( '.js-send-request' ) )
    Validation( formValidation );

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

    let _controlViewBtn = controlView.querySelector( '.control-view__btn' );
    let _tableViewStatus = false;
    let _objTable = document.querySelector( '.objects-list' );
    let _inscriptionWrap = controlView.querySelector( 'span' );
    let _btnImg = _controlViewBtn.querySelector( 'img' );
    let _btnImgSrc = _btnImg.src;
    let _btnImgSrcArr = _btnImgSrc.split('/');

    _controlViewBtn.addEventListener( 'click', (e) => {
        e.preventDefault();
        _changeView();
    } );

    function _changeView() {

        if ( _tableViewStatus ){

            _inscriptionWrap.innerHTML = 'Map view';
            _objTable.classList.remove( 'is-show' );
            _btnImgSrcArr[ _btnImgSrcArr.length - 1 ] = 'icon-list-view.svg';
            _btnImg.src = _btnImgSrcArr.join( '/' );
            _tableViewStatus = false;

        } else {

            _inscriptionWrap.innerHTML = 'List view';
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

    var scrollWrap = document.createElement('sapn');
    scrollWrap.className = 'swiper-pagination';

    articleGallery.appendChild( scrollWrap );

    let _slidePaginarion = articleGallery.querySelector( '.swiper-pagination' );

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
        placeholder: "Type address / location",
        marker: false,
        flyTo: false,
        setLanguage: 'en-GB'
    } );

    BehaviorSearchForm( _geocoder, _geocoderWrap );

    _geocoderWrap.appendChild( _geocoder.onAdd( _mapFrame ) );

    _geocoder.on( 'result', (e) => {
        var homeURL = document.body.dataset.url;
        sessionStorage.setItem( 'searchGeo', e.result.place_name );
        window.location.href = `${homeURL}#12/${e.result.center[1]}/${e.result.center[0]}`;
    } );

}

function Nearest0bjects() {

    let _data = localStorage.getItem( 'brutList' );
    let _ps = null;

    if ( !_data )
        return;

    nearest0bjects.innerHTML = _data;

    document.querySelector( '.objects-list' ).classList.remove( 'is-loading' );

    let isNew = nearest0bjects.querySelectorAll( '.is-new' );

    isNew.forEach( item => item.classList.remove( 'is-new' ) );

    _initScroll();

    function _initScroll() {

        let _listWrap = document.querySelector( '.objects-list__layout' );
        let _listScrollWrap = _listWrap.querySelector( '.objects-list__scroll' );

        if( nearest0bjects.offsetHeight > _listWrap.offsetHeight && _ps == null ){

            // _listWrap.classList.add( 'is-scroll in-top-list' );

            _ps = new PerfectScrollbar( _listScrollWrap, {
                suppressScrollX: true
            } );

        } else if ( _listWrap.offsetHeight >= nearest0bjects.offsetHeight && _ps !== null ) {

            _ps.destroy();
            _listWrap.classList.remove( 'is-scroll' );
            _ps = null;

        } else if ( _ps !== null ) {
            _ps.update();
        }

    }

}

function SendData () {

    let xhr = new XMLHttpRequest();
    let formData = new FormData( formValidation );
    let action = document.querySelector( 'body' ).dataset.action;

    formData.append( 'action', 'send_support' );

    xhr.open( 'POST', action, true );
    xhr.send( formData );

    xhr.onreadystatechange = function() {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if ( xhr.response == 'success' ) {
                formValidation.querySelector( '.support__message' ).textContent = 'Thank you. We’ll reach out to you shortly.';
                formValidation.classList.remove( 'is-loading' );
            }
        }
    };
}

function Validation () {

    let inputs = formValidation.querySelectorAll( '[required]' );

    notTouched();

    for ( let item of inputs ){
        item.addEventListener( 'keypress', () => {
            notTouched();
            item.classList.remove( 'not-valid' );
        } );
        item.addEventListener( 'focusout', () => {
            notTouched();
            item.classList.remove( 'not-valid' );
        } );
    }

    formValidation.addEventListener( 'submit', ( e ) => {
        validForm( e );
    } );

    function validForm( e ) {

        formValidation.classList.add( 'is-loading' );

        for ( let item of inputs ){

            if ( item.classList.contains( 'not-touched' ) || item.value === '' ){
                item.classList.add( 'not-valid' );

                e.preventDefault();
            } else {
                item.classList.remove( 'not-touched' );
                item.classList.remove( 'not-valid' );

                e.preventDefault();
            }

            let type = item.getAttribute( 'type' );

            if( type === 'email' && item.value !== '' ){
                if( !validateEmail( item.value ) ){
                    item.classList.add( 'not-valid' );
                    e.preventDefault();
                }
            }

            formValidation.classList.remove( 'is-loading' );

        }

        if ( formValidation.querySelectorAll( '.not-valid' ).length === 0 ) {
            SendData();
        }

    }

    function validateEmail ( email ) {
        let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test( email );
    }

    function notTouched() {

        for ( let item of inputs ){

            if ( item.value === '' ){
                item.classList.add( 'not-touched' );
            } else {
                item.classList.remove( 'not-touched' );
            }

        }

    }

}
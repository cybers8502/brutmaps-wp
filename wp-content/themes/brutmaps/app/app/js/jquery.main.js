( function(){

    $( function () {

        "use strict";

        $( '.blog-article .swiper-container' ).each(function(){
            new InitArticleSlider( $(this) );
        } );

        $.each( $( '.blog-article__back' ), function() {
            new FixedArticleBackButton($(this));
        } );

    } );

    var FixedArticleBackButton = function( obj ) {

        //private properties
        var _obj = obj,
            _site = $( '.site' ),
            _siteHead = _site.find( '.site__header' ),
            _contentWrap = _site.find( '.blog-article__wrap' ),
            _window = $( window ),
            _objOffset = _obj.offset(),
            _isFixed = false;

        //private methods
        var _onEvent = function() {

                _window.on( 'scroll', function ( ) {
                    if ( _window.outerWidth() >= 992 ){
                        _checkScroll();
                    }
                } );

            },
            _checkScroll = function(){

                if ( _isFixed ){
                    return false;
                }

                var contentWrapHeight = _contentWrap.outerHeight(),
                    siteHeadHeight = _siteHead.outerHeight(),
                    screenTop = _window.scrollTop() + siteHeadHeight + 30;

                if ( screenTop >= _objOffset.top ){

                    _obj.css( {
                        'position': 'fixed',
                        'top': _siteHead.outerHeight() + 30,
                        'left': _objOffset.left,
                        'bottom': 'auto',
                        'right': 'auto'
                    } );

                    if ( contentWrapHeight + _contentWrap.offset().top <= _obj.offset().top + _obj.outerHeight() ){
                        _obj.removeAttr( 'style' );
                        _obj.css( {
                            'position': 'absolute',
                            'top': contentWrapHeight - _obj.outerHeight() +'px'
                        } );
                    }

                } else if ( screenTop <= _objOffset.top ) {
                    _obj.removeAttr( 'style' );
                }

            },
            _checkCondition = function() {

                if ( _obj.outerHeight() + 100 < _contentWrap.outerHeight() ) {
                    _onEvent();
                    _checkScroll();
                }

            },
            _init = function() {
                _checkCondition();

                _window.on( 'resize', function () {
                    _objOffset = _obj.offset();
                    _checkCondition();
                } );

            };

        //public properties

        //public methods

        _init();
    };

    var InitArticleSlider = function( obj ){

        //private properties
        var _swiperSlider = obj,
            _swiper = null;

        //private methods
        var _initSlider = function() {

                _swiper = new Swiper( _swiperSlider, {
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

            },
            _construct = function() {
                _initSlider();
            };

        //public properties

        //public methods

        _construct();

    };

} )();
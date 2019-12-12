( function(){

    let _articleGallery;

    ( _articleGallery = document.querySelector( '.blog-article .swiper-container' ) ) && InitArticleSlider();

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

    };

} )();
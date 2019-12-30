( function(){

    let mapWrap;

    if ( mapWrap = document.querySelector( '#common-map' ) )
        InitMapBox();

    function InitMapBox() {

        let _mapFrame;
        let _listWrap = document.querySelector( '.objects-list' );
        let _listingEl = document.getElementById( 'js-feature-listing' );
        let _initObjListTimeOut = null;
        let _IDVisiblePopup = null;
        let _hoveredStateId = null;
        let _timer;
        let _timerClosePopup;
        let _timerClosePopupDuration = 1000;
        let _ps = null;
        let _imgPin = 'https://brutmaps.com/wp-content/themes/brutmaps/assets/img/icon-pin.jpg';
        let _device = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent );

        if ( location.host == 'localhost:8888' )
            _imgPin = 'http://localhost:8888/brutmaps-wp/wp-content/themes/brutmaps/assets/img/icon-pin.jpg';

        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        function _createPopUp( currentFeature ) {

            var popup = new mapboxgl.Popup( { closeOnClick: false, closeButton: false } )
                .setLngLat( currentFeature.geometry.coordinates )
                .setHTML(`<a class="mapboxgl-popup-picture" href="${currentFeature.properties.link}">
                    <div class="loader"><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/></div>
                    <img src="${currentFeature.properties.images}" alt="${currentFeature.properties.title}"/></a>
                    <a href="${currentFeature.properties.link}" class="mapboxgl-popup-text"><div><p>${currentFeature.properties.title}</p></div></a>`)
                .addTo(_mapFrame);

            _cutText( document.querySelector('.mapboxgl-popup p'), 61 );

            var popUps = document.getElementsByClassName('mapboxgl-popup');

            if ( popUps[0] ){

                popUps[0].addEventListener( 'mouseover', function () {

                    if ( _timerClosePopup !== null )
                        clearTimeout( _timerClosePopup );

                } );

                popUps[0].addEventListener( 'mouseleave', function () {

                    _timerClosePopup = setTimeout( () => {
                        _removePopups();
                        clearTimeout( _timerClosePopup );
                        _timerClosePopup = null;
                    }, _timerClosePopupDuration );

                } );

            }

        }

        function _createMarkersData( data ) {

            let cluster_data = {},
                features = [];

            data.forEach( function( marker ) {

                features.push( {
                    "type": "Feature",
                    "geometry": {
                        "type": "Point",
                        "coordinates": [ marker.coordinates.long, marker.coordinates.lat, 0.0 ]
                    },
                    "properties": {
                        "id": + marker.id,
                        "title": marker.title,
                        "address": marker.address,
                        "year": marker.year,
                        "images": marker.images.image_small,
                        "link": marker.link
                    }
                } );

            } );

            cluster_data.features = features;

            return cluster_data;

        }

        function _cutText( obj, maxH ) {

            var text = obj.textContent,
                clone = obj.cloneNode(true);

            clone.style.position = 'absolute';
            clone.style.visibility = 'hidden';
            clone.style.maxHeight = 'none';

            obj.parentNode.insertBefore( clone, obj.nextSibling );

            var l = text.length - 1;


            for (; l >= 0 && clone.offsetHeight > maxH; --l ) {
                clone.textContent = text.substring( 0, l ) +'...';
            }

            obj.textContent = clone.textContent;
            clone.remove();

        }

        function _initGeocoder() {

            var searchWrap = document.getElementById( 'geocoder' );
            var _timer, _input;

            var geocoder = new MapboxGeocoder( {
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                placeholder: "Type address / location",
                marker: false,
                setLanguage: 'en-GB'
            } );

            BehaviorSearchForm( geocoder, searchWrap );

            searchWrap.appendChild( geocoder.onAdd( _mapFrame ) );

            geocoder.on( 'result', () => {
                searchWrap.querySelector( 'input' ).blur();
            } );

            _timer = setTimeout( function () {

                _input = searchWrap.querySelector( 'input' );

                if(_input)
                    _behaviorSearchInput();

            }, 500 );

            function _behaviorSearchInput() {
                clearTimeout( _timer );

                var data = sessionStorage.getItem( 'searchGeo' );

                if ( data ){
                    _input.value = data;
                    sessionStorage.removeItem( 'searchGeo' );
                }

            }

        }

        function _initMap( data ) {

            let defaultData = data.settings.default_center;

            _mapFrame = new mapboxgl.Map( {
                container: 'common-map',
                zoom: defaultData.zoom || 9,
                hash: true,
                style: 'mapbox://styles/mapbox/dark-v10',
                center: [ defaultData.coordinates.long, defaultData.coordinates.lat]
            } );

            _mapFrame.on( 'load', function() {

                _mapFrame.addSource( 'earthquakes', {
                    type: "geojson",
                    data: _createMarkersData( data.sights ),
                    cluster: true,
                    clusterRadius: 40,
                    clusterMaxZoom: 11
                } );

                _mapFrame.loadImage( _imgPin, function(error, image) {
                    if (error) throw error;

                    _mapFrame.addImage('point', image);

                    _mapFrame.addLayer( {
                        id: "brut-obj",
                        type: "symbol",
                        source: "earthquakes",
                        filter: ["!", ["has", "point_count"]],
                        'layout': {
                            'icon-image': 'point',
                            'icon-size': .2
                        }
                    } );

                    _mapFrame.addLayer( {
                        id: 'clusters',
                        type: 'circle',
                        source: 'earthquakes',
                        filter: ['has', 'point_count'],
                        paint: {
                            'circle-radius': 15,
                            'circle-color': [
                                'case',
                                ['boolean', ['feature-state', 'hover'], false],
                                '#DFDDD8',
                                '#EAE9E6'
                            ]
                        }
                    } );

                    _mapFrame.addLayer( {
                        id: 'cluster-count',
                        type: 'symbol',
                        source: 'earthquakes',
                        filter: ['has', 'point_count'],
                        layout: {
                            'text-field': '{point_count_abbreviated}',
                            'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                            'text-size': 12
                        }
                    } );

                    _onEvents();

                } );

            } );

            _initGeocoder();

        }

        function _initMarkerPopup(e) {

            var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });

            if ( !features[0] || _IDVisiblePopup === features[0].properties.id )
                return;

            _removePopups();

            _IDVisiblePopup = features[0].properties.id;

            _createPopUp( features[0] );

        }

        function _getUniqueFeatures( array, comparatorProperty ) {
            var existingFeatureKeys = {};

            var uniqueFeatures = array.filter(function( el ) {
                if (existingFeatureKeys[el.properties[comparatorProperty]]) {
                    return false;
                } else {
                    existingFeatureKeys[el.properties[comparatorProperty]] = true;
                    return true;
                }
            });

            return uniqueFeatures;
        }

        function _onEvents() {

            _mapFrame.on( 'data', function () {
                clearTimeout( _initObjListTimeOut );
                _listWrap.classList.add( 'is-loading' );
                _initObjListTimeOut = setTimeout( () => {
                    _renderListings( _getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ) , 'id') )
                }, 1000 );
            } );

            _mapFrame.on( 'click', function(e) {

                var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });

                if ( features.length === 0 ){
                    _removePopups();
                    return;
                }

                if ( !_device )
                    window.location.href = features[0].properties.link;

            } );

            _mapFrame.on( 'zoom', function () {
                _removePopups();
            } );

            _mapFrame.on( 'moveend', function () {
                clearTimeout( _initObjListTimeOut );
                _listWrap.classList.add( 'is-loading' );
                _initObjListTimeOut = setTimeout( () => {
                    _renderListings( _getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ) , 'id') )
                }, 1000 );

            } );

            _mapFrame.on( 'click', 'clusters', function(e) {

                var features = _mapFrame.queryRenderedFeatures( e.point, { layers: ['clusters'] } );

                var clusterId = features[0].properties.cluster_id;

                _mapFrame.getSource('earthquakes').getClusterExpansionZoom(
                    clusterId,
                    function(err, zoom) {
                        if (err) return;

                        _mapFrame.easeTo({
                            center: features[0].geometry.coordinates,
                            zoom: zoom
                        });
                    }
                );

            });

            _mapFrame.on( 'click', 'brut-obj', function(e) {

                if ( !_device )
                    return false;

                _removePopups();

                _mapFrame.flyTo( {
                    center: [
                        e.lngLat.lng,
                        e.lngLat.lat
                    ],
                    curve: 0,
                    essential: true,
                    maxDuration: 500
                } );

                var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });

                if ( !features[0] || _IDVisiblePopup === features[0].properties.id )
                    return;

                _IDVisiblePopup = features[0].properties.id;

                let _deviceInitPopupDuration;

                _deviceInitPopupDuration = setTimeout( () => {
                    _createPopUp( features[0] );
                    clearTimeout( _deviceInitPopupDuration );
                }, 500 );

            });

            if ( _device )
                return;

            _mapFrame.on( 'mouseenter', 'clusters', function(e) {
                _mapFrame.getCanvas().style.cursor = 'pointer';

                if ( e.features.length > 0) {
                    if (_hoveredStateId) {
                        _mapFrame.setFeatureState(
                            { source: 'earthquakes', id: _hoveredStateId },
                            { hover: false }
                        );
                    }
                    _hoveredStateId = e.features[0].id;
                    _mapFrame.setFeatureState(
                        { source: 'earthquakes', id: _hoveredStateId },
                        { hover: true }
                    );
                }

            });

            _mapFrame.on( 'mouseleave', 'clusters', function() {
                _mapFrame.getCanvas().style.cursor = '';

                if (_hoveredStateId) {
                    _mapFrame.setFeatureState(
                        { source: 'earthquakes', id: _hoveredStateId },
                        { hover: false }
                    );
                }
                _hoveredStateId = null;

            });

            _mapFrame.on( 'mouseenter', 'brut-obj', function(e) {
                _mapFrame.getCanvas().style.cursor = 'pointer';

                _initMarkerPopup(e);

                if ( _timerClosePopup !== null )
                    clearTimeout( _timerClosePopup );

            });

            _mapFrame.on( 'mouseleave', 'brut-obj', function() {
                _mapFrame.getCanvas().style.cursor = '';

                _timerClosePopup = setTimeout( () => {
                    _removePopups();
                    clearTimeout( _timerClosePopup );
                    _timerClosePopup = null;
                }, _timerClosePopupDuration );

            });

        }

        function _renderListings( features ) {

            _listingEl.innerHTML = '';

            if (features.length) {
                features.forEach( function( feature ) {

                    var prop = feature.properties;

                    var item = document.createElement('a');
                    item.className = 'objects-list__item is-new';
                    item.href = prop.link;
                    item.innerHTML = '<div class="objects-list__picture">' +
                        '<div class="loader"><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/></div>'+
                        '<img src="'+ prop.images +'" alt="'+ prop.title +'"/></div>' +
                        '<div class="objects-list__info"><address>' +
                        '<h3>'+ prop.title +'</h3><p>'+ prop.address +'</p>' +
                        '</address><p><strong>'+ prop.year +'</strong></p></div>';

                    item.addEventListener( 'mouseover', function () {

                        if ( _IDVisiblePopup === feature.properties.id )
                            return;

                        _removePopups();

                        _IDVisiblePopup = feature.properties.id;

                        _createPopUp( feature );

                    } );

                    item.addEventListener( 'mouseleave', function () {
                        _removePopups();
                    } );

                    _listingEl.appendChild( item );

                    _cutText( item.querySelector( 'h3' ), 46 );
                    _cutText( item.querySelector( 'p' ), 43 );

                } );
            } else {
                var empty = document.createElement('p');
                empty.textContent = 'Drag or zoom the map to see results';
                _listingEl.appendChild(empty);
            }

            _initScroll();

            let isNew = _listingEl.querySelectorAll( '.is-new' ),
                count = 0;

            isNew.forEach( ( item ) => {
                _showListObjItem( item, count );
                count++;
            } );

            localStorage.setItem( 'brutList', _listingEl.outerHTML );
            _listWrap.classList.remove( 'is-loading' );

            clearTimeout( _initObjListTimeOut );

        }

        function _removePopups() {

            var popUps = document.getElementsByClassName('mapboxgl-popup');

            if ( popUps[0] ){
                popUps[0].remove();
            }

            _IDVisiblePopup = null;

        }

        function _sendRequest() {

            let action = document.querySelector( 'body' ).dataset.action;
            let formData = new FormData();

            formData.append( 'action', 'get_sight' );

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    _initMap( JSON.parse( xhr.response ).data.data );
                }
            };
            xhr.open('POST', action, true);
            xhr.send( formData );

        }

        function _showListObjItem( obj, i ) {
            setTimeout( () => {
                obj.classList.remove( 'is-new' );
            }, 80 * i )
        }

        function _initScroll() {

            let _listWrap = document.querySelector( '.objects-list__layout' );
            let _listScrollWrap = _listWrap.querySelector( '.objects-list__scroll' );

            if( _listingEl.offsetHeight > _listWrap.offsetHeight && _ps == null ){

                // _listWrap.classList.add( 'is-scroll in-top-list' );

                _ps = new PerfectScrollbar( _listScrollWrap, {
                    suppressScrollX: true
                } );

            } else if ( _listWrap.offsetHeight >= _listingEl.offsetHeight && _ps !== null ) {

                _ps.destroy();
                _listWrap.classList.remove( 'is-scroll' );
                _ps = null;

            } else if ( _ps !== null ) {
                _ps.update();
            }

        }

        _sendRequest();

    }

} )();
( function(){

    let mapWrap;

    if ( mapWrap = document.querySelector( '#common-map' ) )
        InitMapBox();

    function InitMapBox() {

        let _mapFrame;
        let _listWrap = document.querySelector( '.objects-list' );
        let _listingEl = document.getElementById( 'js-feature-listing' );
        let _timer;

        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        function _createPopUp( currentFeature ) {

            _removePopups();

            var popup = new mapboxgl.Popup( { closeOnClick: false, closeButton: false, anchor: 'bottom-right' } )
                .setLngLat( currentFeature.geometry.coordinates )
                .setHTML(`<a class="mapboxgl-popup-picture" href="${currentFeature.properties.link}">
                    <div class="loader"><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/></div>
                    <img src="${currentFeature.properties.images}" alt="${currentFeature.properties.title}"/></a>
                    <a href="${currentFeature.properties.link}" class="mapboxgl-popup-text"><div><p>${currentFeature.properties.title}</p></div></a>`)
                .addTo(_mapFrame);

            _cutText( document.querySelector('.mapboxgl-popup p'), 61 );

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
                        "id": 'id_'+ marker.id,
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

                _mapFrame.addLayer( {
                    id: "brut-obj",
                    type: "circle",
                    source: "earthquakes",
                    filter: ["!", ["has", "point_count"]],
                    paint: {
                        'circle-radius': 5,
                        "circle-color": '#EAE9E6'
                    }
                } );

                _mapFrame.addLayer({
                    id: 'clusters',
                    type: 'circle',
                    source: 'earthquakes',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-radius': 15,
                        'circle-color': '#EAE9E6',
                    }
                });

                _mapFrame.addLayer({
                    id: 'cluster-count',
                    type: 'symbol',
                    source: 'earthquakes',
                    filter: ['has', 'point_count'],
                    layout: {
                        'text-field': '{point_count_abbreviated}',
                        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                        'text-size': 12
                    }
                });

                _onEvents();

            } );

            _initGeocoder();

        }

        function _initMarkerPopup(e) {

            var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });

            if ( features.length )
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
                _renderListings( _getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ), 'id') );
            } );

            _mapFrame.on( 'click', function(e) {
                _removePopups();
                _initMarkerPopup(e);
            } );

            _mapFrame.on('click', 'clusters', function(e) {

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

            _mapFrame.on( 'move', function () {
                _removePopups();
            } );

            _mapFrame.on( 'moveend', function () {
                _renderListings( _getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ) , 'id') );
            } );

        }

        function _renderListings( features ) {

            _listWrap.classList.add( 'is-loading' );

            _listingEl.innerHTML = '';

            if (features.length) {
                features.forEach( function( feature ) {

                    var prop = feature.properties;

                    var item = document.createElement('a');
                    item.className = 'objects-list__item';
                    item.href = prop.link;
                    item.innerHTML = '<div class="objects-list__picture">' +
                        '<div class="loader"><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/><hr/></div>'+
                        '<img src="'+ prop.images +'" alt="'+ prop.title +'"/></div>' +
                        '<div class="objects-list__info"><address>' +
                        '<h3>'+ prop.title +'</h3><p>'+ prop.address +'</p>' +
                        '</address><p><strong>'+ prop.year +'</strong></p></div>';

                    item.addEventListener( 'mouseover', function () {
                        _createPopUp( feature );
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

            localStorage.setItem( 'brutList', _listingEl.outerHTML );
            _listWrap.classList.remove( 'is-loading' );

        }

        function _removePopups() {

            var popUps = document.getElementsByClassName('mapboxgl-popup');

            if ( popUps[0] ){
                popUps[0].remove();
            }

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

        _sendRequest();

    }

} )();
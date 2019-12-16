( function(){

    let _mapWrap;

    ( _mapWrap = document.querySelector( '#common-map' ) ) && InitMapBox();

    function InitMapBox() {

        let _mapFrame;
        let _device = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent );

        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        function _drawClusters() {

            var clustersArr = {};
            var clustersOnScreen = {};
            let timer = null;

            function _updateClusters() {

                var curClustersArr = {};
                var features = _mapFrame.querySourceFeatures( 'earthquakes' );

                for (var i = 0; i < features.length; i++) {

                    var coords = features[i].geometry.coordinates;
                    var props = features[i].properties;

                    if ( !props.cluster )
                        continue;

                    var id = props.cluster_id;

                    var clusterMarker = clustersArr[id];

                    if (!clusterMarker) {
                        var el = document.createElement('div');
                        el.className = 'map__cluster';
                        el.dataset.id = id;
                        el.dataset.coordinates = coords;
                        el.innerHTML = props.point_count;
                        clusterMarker = clustersArr[id] = new mapboxgl.Marker({element: el}).setLngLat(coords);
                    }

                    curClustersArr[id] = clusterMarker;

                    if (!clustersOnScreen[id])
                        clusterMarker.addTo(_mapFrame);

                }

                for (id in clustersOnScreen) {
                    if (!curClustersArr[id])
                        clustersOnScreen[id].remove();
                }

                clustersOnScreen = curClustersArr;

                _setClustersEvent();

            }

            _mapFrame.on( 'data', function (e) {
                if (e.sourceId !== 'earthquakes' || !e.isSourceLoaded) return;

                _mapFrame.on( 'move', _updateClusters );
                _mapFrame.on( 'moveend', () => {
                    _updateClusters();

                    timer = setTimeout( function () {
                        _updateClusters();
                        clearTimeout( timer );
                    }, 300 );

                } );

                _updateClusters();

            } );

        }

        function _drawMarkers() {

            var markersArr = {};
            var markersOnScreen = {};

            function _updateMarkers() {

                var curMarkersArr = {};
                var features = _mapFrame.querySourceFeatures( 'earthquakes' );

                for (var i = 0; i < features.length; i++) {

                    var coords = features[i].geometry.coordinates;
                    var props = features[i].properties;

                    if ( props.cluster )
                        continue;

                    var id = props.id;

                    var clusterMarker = markersArr[id];

                    if (!clusterMarker) {
                        var el = document.createElement('div');
                        el.className = `map__marker ${id}`;
                        el.dataset.id = id;
                        el.dataset.properties = JSON.stringify( { 'geometry': { 'coordinates': coords }, 'properties': props } );
                        clusterMarker = markersArr[id] = new mapboxgl.Marker({element: el}).setLngLat(coords);
                    }

                    curMarkersArr[id] = clusterMarker;

                    if (!markersOnScreen[id])
                        clusterMarker.addTo(_mapFrame);

                }

                for (id in markersOnScreen) {
                    if (!curMarkersArr[id])
                        markersOnScreen[id].remove();
                }

                markersOnScreen = curMarkersArr;

            }

            _mapFrame.on( 'data', function (e) {
                if (e.sourceId !== 'earthquakes' || !e.isSourceLoaded) return;

                _mapFrame.on( 'move', _updateMarkers );
                _mapFrame.on( 'moveend', _updateMarkers );

                _updateMarkers();

            } );

        }

        function _createPopUp( currentFeature ) {

            _removePopups();

            var popup = new mapboxgl.Popup( { closeOnClick: false } )
                .setLngLat( currentFeature.geometry.coordinates )
                .setHTML(`<a  class="mapboxgl-popup-picture" href="${currentFeature.properties.link}">
                    <img src="${currentFeature.properties.images}" alt="${currentFeature.properties.title}"/></a>
                    <a href="${currentFeature.properties.link}" class="mapboxgl-popup-text"><div><p>${currentFeature.properties.address}</p></div></a>`)
                .addTo(_mapFrame);

            document.querySelector( `.${currentFeature.properties.id}` ).classList.add( 'is-active' );

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

            var geocoder = new MapboxGeocoder( {
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                placeholder: "Type address",
                marker: false
            } );

            document.getElementById( 'geocoder' ).appendChild( geocoder.onAdd( _mapFrame ) );

            geocoder.setLanguage = () => {
                return 'en-GB'
            };

        }

        function _initMap( data ) {

            let defaultData = data.settings.default_center;

            _mapFrame = new mapboxgl.Map( {
                container: 'common-map',
                zoom: defaultData.zoom || 9,
                hash: true,
                style: 'mapbox://styles/cybers8502/cjpb47lj66ufh2spadk5auttp',
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
                        'circle-radius': 10,
                        "circle-color": "transparent"
                    }
                } );


                _drawClusters();
                _drawMarkers();
                _onEvents();

            } );

            _initGeocoder();

        }

        function _initMarkerPopup(e) {

            var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });

            if (features.length) {
                _createPopUp( features[0] );
            }

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

            _mapFrame.on( 'move', function () {
                _removePopups();
            } );

            _mapFrame.on( 'moveend', function () {
                _renderListings( _getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ) , 'id') );
            } );

        }

        function _renderListings( features ) {

            var listingEl = document.getElementById( 'feature-listing' );

            listingEl.innerHTML = '';

            if (features.length) {
                features.forEach( function( feature ) {

                    var prop = feature.properties;

                    var item = document.createElement('a');
                    item.className = 'objects-list__item';
                    item.href = prop.link;
                    item.innerHTML = '<div class="objects-list__picture">' +
                        '<img src="'+ prop.images +'" alt="'+ prop.title +'"/></div>' +
                        '<div class="objects-list__info"><address>' +
                        '<h3>'+ prop.title +'</h3><p>'+ prop.address +'</p>' +
                        '</address><p><strong>'+ prop.year +'</strong></p></div>';

                    item.addEventListener( 'mouseover', function () {
                        _createPopUp( feature );
                    } );

                    listingEl.appendChild( item );

                    _cutText( item.querySelector( 'h3' ), 46 );
                    _cutText( item.querySelector( 'p' ), 43 );

                } );

            } else {
                var empty = document.createElement('p');
                empty.textContent = 'Drag or zoom the map to results';
                listingEl.appendChild(empty);
            }

            localStorage.setItem( 'brutList', listingEl.outerHTML );

        }

        function _removePopups() {

            var popUps = document.getElementsByClassName('mapboxgl-popup');

            if ( popUps[0] ){
                popUps[0].remove();
                document.querySelector( '.is-active' ).classList.remove( 'is-active' );
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

        function _setClustersEvent() {

            var mapCluster = document.querySelectorAll( '.map__cluster' );

            mapCluster.forEach ( function ( mapCluster ){

                mapCluster.addEventListener( 'click', function (e) {

                    var curClustr = this;
                    var clusterId = +( curClustr.dataset.id );

                    _mapFrame.getSource('earthquakes').getClusterExpansionZoom(clusterId, function ( err, zoom ) {
                        if (err)
                            return;

                        if ( _device ){

                            _mapFrame.flyTo({
                                center: JSON.parse("[" + curClustr.dataset.coordinates + "]"),
                                zoom: zoom + .1
                            });

                        } else {

                            _mapFrame.jumpTo({
                                center: JSON.parse("[" + curClustr.dataset.coordinates + "]"),
                                zoom: zoom + .1
                            });

                        }


                    });

                });

            } );

        }

        _sendRequest();

    }

} )();
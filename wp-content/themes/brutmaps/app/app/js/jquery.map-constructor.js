( function(){

    let _mapWrap;

    ( _mapWrap = document.querySelector( '#map' ) ) && InitMapBox();

    function InitMapBox() {

        let _mapFrame;
        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        function _drawMarkers() {

            var clustersArr = {};
            var clustersOnScreen = {};

            function _updateMarkers() {

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

                _mapFrame.on( 'move', _updateMarkers );
                _mapFrame.on( 'moveend', _updateMarkers );

                _updateMarkers();

            } );

        }

        function _createPopUp( currentFeature ) {

            _removePopups();

            var popup = new mapboxgl.Popup( { closeOnClick: false } )
                .setLngLat( currentFeature.geometry.coordinates )
                .setHTML(`<h3>${currentFeature.properties.title}</h3>(${currentFeature.properties.address})`)
                .addTo(_mapFrame);
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
                        "id": marker.id,
                        "title": marker.title,
                        "address": marker.address,
                        "year": marker.year,
                        "images": marker.images.image_small
                    }
                } );

            } );

            cluster_data.features = features;

            return cluster_data;

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
                container: 'map',
                zoom: defaultData.zoom || .2,
                style: 'mapbox://styles/cybers8502/cjpb47lj66ufh2spadk5auttp',
                center: [ defaultData.coordinates.long, defaultData.coordinates.lat]
            } );

            _mapFrame.on( 'load', function() {

                _mapFrame.addSource( 'earthquakes', {
                    type: "geojson",
                    data: _createMarkersData( data.sights ),
                    cluster: true,
                    clusterRadius: 40,
                    clusterMaxZoom: 14
                } );

                _mapFrame.addLayer({
                    id: "clusters",
                    type: "circle",
                    source: "earthquakes",
                    filter: ["has", "point_count"]
                });

                _mapFrame.addLayer( {
                    id: "brut-obj",
                    type: "circle",
                    source: "earthquakes",
                    filter: ["!", ["has", "point_count"]]
                } );

                _drawMarkers();
                _onEvents();

            } );

            _initGeocoder();

        }

        function _initMarkerPopup(e) {

            var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });

            if (features.length) {
                var clickedPoint = features[0];
                _createPopUp( clickedPoint );
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

                    var item = document.createElement('div');
                    item.className = 'objects-list__item';
                    item.innerHTML = '<div class="objects-list__picture"><img src="'+ prop.images +'" alt="'+ prop.title +'"/></div><div class="objects-list__info"><address><h3>'+ prop.title +'</h3><p>'+ prop.address +'</p></address><p><strong>'+ prop.year +'</strong></p></div>';

                    item.addEventListener( 'mouseover', function () {
                        _createPopUp( feature );
                    } );

                    listingEl.appendChild( item );
                } );
            } else {
                var empty = document.createElement('p');
                empty.textContent = 'Drag the map to results';
                listingEl.appendChild(empty);
            }
        }

        function _removePopups() {

            var popUps = document.getElementsByClassName('mapboxgl-popup');

            if ( popUps[0] ) popUps[0].remove();

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
                    var clusterId = +( this.dataset.id );

                    _mapFrame.getSource('earthquakes').getClusterExpansionZoom(clusterId, function ( err, zoom ) {
                        if (err)
                            return;

                        _mapFrame.flyTo({
                            center: JSON.parse("[" + curClustr.dataset.coordinates + "]"),
                            zoom: zoom + .1
                        });

                    });

                });

            } );

        }

        _sendRequest();

    }

} )();



// var geojson = {
//     type: 'FeatureCollection',
//     features: [ {
//         type: 'Feature',
//         geometry: {
//             type: 'Point',
//             coordinates: [data[0].coordinates.long, data[0].coordinates.lat]
//         },
//         properties: {
//             title: 'Mapbox',
//             description: 'Washington, D.C.'
//         }
//     }]
// };
//
// features.forEach(function( marker ) {
//
//     var el = document.createElement('div');
//     el.className = 'marker';
//
//     new mapboxgl.Marker(el)
//         .setLngLat(marker.geometry.coordinates)
//         .addTo( _mapFrame );
// });

// add clusrers
// _mapFrame.addLayer({
//     id: "clusters",
//     type: "circle",
//     source: "earthquakes",
//     filter: ["has", "point_count"]
// });

// _mapFrame.addLayer({
//     id: "cluster-count",
//     type: "symbol",
//     source: "earthquakes",
//     filter: ["has", "point_count"],
//     layout: {
//         "text-field": "{point_count_abbreviated}",
//         "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
//         "text-size": 12
//     }
// });


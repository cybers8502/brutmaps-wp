( function(){

    let _mapWrap;

    ( _mapWrap = document.querySelector( '#map' ) ) && InitMapbox();

    function InitMapbox() {

        let _mapFrame;
        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        function _construct() {
            _initGeocoder();
        }

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

                _mapFrame.on('click', function(e) {
                    // Query all the rendered points in the view
                    var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['brut-obj'] });
                    if (features.length) {
                        var clickedPoint = features[0];
                        // 2. Close all other popups and display popup for clicked store
                        createPopUp(clickedPoint);
                        // 3. Highlight listing in sidebar (and remove highlight for all other listings)
                        var activeItem = document.getElementsByClassName('active');
                        if (activeItem[0]) {
                            activeItem[0].classList.remove('active');
                        }
                        // Find the index of the store.features that corresponds to the clickedPoint that fired the event listener
                        // var selectedFeature = clickedPoint.properties.address;
                        //
                        // for (var i = 0; i < stores.features.length; i++) {
                        //     if (stores.features[i].properties.address === selectedFeature) {
                        //         selectedFeatureIndex = i;
                        //     }
                        // }
                        // // Select the correct list item using the found index and add the active class
                        // var listing = document.getElementById('listing-' + selectedFeatureIndex);
                        // listing.classList.add('active');
                    }
                } );

                function createPopUp(currentFeature) {
                    var popUps = document.getElementsByClassName('mapboxgl-popup');
                    // Check if there is already a popup on the map and if so, remove it
                    if (popUps[0]) popUps[0].remove();

                    var popup = new mapboxgl.Popup({ closeOnClick: false })
                        .setLngLat(currentFeature.geometry.coordinates)
                        .setHTML('<h3>Sweetgreen</h3>' +
                            '<h4>222</h4>')
                        .addTo(_mapFrame);
                }

            }

            _mapFrame.on( 'data', function (e) {
                if (e.sourceId !== 'earthquakes' || !e.isSourceLoaded) return;

                _mapFrame.on('move', _updateMarkers);
                _mapFrame.on('moveend', function () {
                    _updateMarkers();

                    console.log( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ) );

                    renderListings( getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ) , 'id') );

                });

                _updateMarkers();

                var listingEl = document.getElementById( 'feature-listing' );

                renderListings( getUniqueFeatures( _mapFrame.queryRenderedFeatures( { layers: ['brut-obj'] } ), 'id') );

                var popup = new mapboxgl.Popup();

                function renderListings( features ) {
                    // Clear any existing listings
                    listingEl.innerHTML = '';

                    // console.log(features.length);

                    if (features.length) {
                        features.forEach(function(feature) {
                            var prop = feature.properties;
                            var item = document.createElement('div');
                            item.className = 'objects-list__item';
                            item.innerHTML = '<div class="objects-list__picture"><img src="'+ prop.images +'" alt="'+ prop.title +'"/></div><div class="objects-list__info"><address><h3>'+ prop.title +'</h3><p>'+ prop.address +'</p></address><p><strong>'+ prop.year +'</strong></p></div>';

                            item.addEventListener('mouseover', function() {
                                // Highlight corresponding feature on the map
                                popup.setLngLat(feature.geometry.coordinates)
                                    .setText(feature.properties.title + ' (' + feature.properties.address + ')')
                                    .addTo( _mapFrame );
                            });

                            listingEl.appendChild(item);
                        });

                    } else {
                        var empty = document.createElement('p');
                        empty.textContent = 'Drag the map to populate results';
                        listingEl.appendChild(empty);

                        // remove features filter
                        // _mapFrame.setFilter('earthquakes', "!", ['has', 'point_count'] );

                    }
                }

                function getUniqueFeatures(array, comparatorProperty) {
                    var existingFeatureKeys = {};

                    // console.log(array);

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

            });

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

                _mapFrame.addSource( "earthquakes", {
                    type: "geojson",
                    data: _createMarkersData( data.sights ),
                    cluster: true,
                    clusterRadius: 40,
                    clusterMaxZoom: 14
                } );

                _mapFrame.addLayer( {
                    id: "brut-obj",
                    type: "circle",
                    source: "earthquakes",
                    filter: ["!", ["has", "point_count"]]
                } );

                _drawMarkers();

            } );

            _initGeocoder();

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

// var mapCluster = document.querySelectorAll( '.map__cluster' );
//
// for ( id in mapCluster ){
//
//
//     mapCluster[id].addEventListener( 'click', function (e) {
//         console.log('dd');
//         var features = _mapFrame.queryRenderedFeatures(e.point, { layers: ['clusters'] });
//         var clusterId = features[0].properties.cluster_id;
//         _mapFrame.getSource('earthquakes').getClusterExpansionZoom(clusterId, function (err, zoom) {
//             if (err)
//                 return;
//
//             _mapFrame.easeTo({
//                 center: features[0].geometry.coordinates,
//                 zoom: zoom
//             });
//         });
//     });
// }
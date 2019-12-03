( function(){

    let _mapWrap, _mapFrame;

    ( _mapWrap = document.querySelector( '#map' ) ) && SendRequest();

    function SendRequest() {

        let action = document.querySelector( 'body' ).dataset.action;
        let formData = new FormData();

        formData.append( 'action', 'get_sight' );

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                InitMap( JSON.parse( xhr.response ).data.data );
            }
        };
        xhr.open('POST', action, true);
        xhr.send( formData );

    }

    function InitMap( data ) {

        let cluster_data = {},
            features = [];

        // Draw map
        mapboxgl.accessToken = 'pk.eyJ1IjoiY3liZXJzODUwMiIsImEiOiJjanBiM3I5ancyMHB5M3FuNGg0M2Rub25pIn0.UMgICyxLhWOZ2S4lb2cIJQ';

        _mapFrame = new mapboxgl.Map({
            container: 'map',
            zoom: .2,
            style: 'mapbox://styles/cybers8502/cjpb47lj66ufh2spadk5auttp',
            center: [data.settings.default_center.coordinates.long, data.settings.default_center.coordinates.lat],
        } );

        data.sights.forEach(function( marker ) {

            features.push( {
                "type": "Feature",
                "geometry": {
                    "type": "Point",
                    "coordinates": [ marker.coordinates.long, marker.coordinates.lat, 0.0 ]
                }
            } );

        });

        cluster_data.features = features;

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

        // create clusters
        _mapFrame.on('load', function() {

            _mapFrame.addSource( "earthquakes", {
                type: "geojson",
                data: cluster_data,
                cluster: true,
                clusterRadius: 40,
                clusterMaxZoom: 14
            });

            // add clusrers
            // _mapFrame.addLayer({
            //     id: "clusters",
            //     type: "circle",
            //     source: "earthquakes",
            //     filter: ["has", "point_count"]
            // });
            //
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

            // add markers
            _mapFrame.addLayer({
                id: "unclustered-point",
                type: "circle",
                source: "earthquakes",
                filter: ["!", ["has", "point_count"]]
            });

            // objects for caching and keeping track of HTML marker objects (for performance)
            var clustersArr = {};
            var clustersOnScreen = {};

            function updateMarkers() {
                var curClustersArr = {};
                var features = _mapFrame.querySourceFeatures('earthquakes');

                // for every cluster on the screen, create an HTML marker for it (if we didn't yet),
                // and add it to the map if it's not there already
                for (var i = 0; i < features.length; i++) {

                    var coords = features[i].geometry.coordinates;
                    var props = features[i].properties;

                    if ( props.cluster ) {

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

                    } else {

                        // var id = props.cluster_id;
                        //
                        // var clusterMarker = clustersArr[id];
                        //
                        // console.log( coords )
                        //
                        // if (!clusterMarker) {
                        //     var el = document.createElement('div');
                        //     el.className = 'marker';
                        //     el.dataset.id = features[i].id;
                        //     clusterMarker = clustersArr[id] = new mapboxgl.Marker({element: el}).setLngLat(coords);
                        // }
                        //
                        // curClustersArr[id] = clusterMarker;
                        //
                        // if (!clustersOnScreen[id])
                        //     clusterMarker.addTo(_mapFrame);

                    }

                }

                // for every marker we've added previously, remove those that are no longer visible
                for (id in clustersOnScreen) {
                    if (!curClustersArr[id])
                        clustersOnScreen[id].remove();
                }

                clustersOnScreen = curClustersArr;

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

            }

            _mapFrame.on('data', function (e) {
                if (e.sourceId !== 'earthquakes' || !e.isSourceLoaded) return;

                _mapFrame.on('move', updateMarkers);
                _mapFrame.on('moveend', updateMarkers);

                updateMarkers();

            });

        });

    }

} )();
<?php
$is_standalone = (basename($_SERVER['PHP_SELF'] ?? '') === 'maps.php');
if ($is_standalone) {
    require_once 'includes/connection.php';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Peta Geospatial Zon &amp; Sub-Zon — Green Management System (DBKU)</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo filemtime(__DIR__ . '/assets/css/modern-dashboard.css'); ?>">
    <style>
        .map-standalone-wrapper {
            height: calc(100vh - 240px);
            min-height: 520px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            background: #e2e8f0;
        }
        #map { width: 100%; height: 100%; min-height: 450px; }
    </style>
</head>
<body>
    <div class="container-scroller">
        <?php include 'partials/navbar.php'; ?>
        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php'; ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="gms-hero-banner gms-fade-in mb-4" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Sistem Maklumat Geografi (GIS)</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Peta Sempadan Zon &amp; Sub-Zon</h1>
                                <p class="gms-hero-subtitle">Visualisasi satelit interaktif dan lapisan KMZ bagi kawasan pentadbiran DBKU</p>
                            </div>
                        </div>
                    </div>
                    <div class="map-standalone-wrapper">
                        <div id="map"></div>
                    </div>
                </div>
                <?php include 'partials/footer.php'; ?>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div id="map" style="width: 100%; height: 50vh;"></div>
<?php } ?>

<script>
    function initMap() {
        var mapEl = document.getElementById('map');
        if (!mapEl) return;
        var map = new google.maps.Map(mapEl, {
            center: {
                lat: 1.585367,
                lng: 110.334509
            },
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.HYBRID
        });

        // Lapisan GeoJSON Moden (Menggantikan KmlLayer yang telah ditamatkan)
        map.data.loadGeoJson('assets/data/zon_dbku.geojson', null, function(features) {
            if (features && features.length > 0) {
                var bounds = new google.maps.LatLngBounds();
                map.data.forEach(function(feature) {
                    processGeometry(feature.getGeometry(), bounds.extend, bounds);
                });
                map.fitBounds(bounds);
            }
        });

        function processGeometry(geometry, callback, thisArg) {
            if (geometry instanceof google.maps.LatLng) {
                callback.call(thisArg, geometry);
            } else if (geometry instanceof google.maps.Data.Point) {
                callback.call(thisArg, geometry.get());
            } else {
                geometry.getArray().forEach(function(g) {
                    processGeometry(g, callback, thisArg);
                });
            }
        }

        map.data.setStyle(function(feature) {
            return {
                fillColor: '#10b981',
                fillOpacity: 0.28,
                strokeColor: '#059669',
                strokeWeight: 2,
                clickable: true
            };
        });

        var infoWindow = new google.maps.InfoWindow();
        map.data.addListener('click', function(event) {
            var name = event.feature.getProperty('name') || 'Zon DBKU';
            var desc = event.feature.getProperty('description') || '';
            var content = '<div style="font-family:inherit; padding:6px 10px; max-width:280px;">' +
                          '<div style="font-weight:700; color:#065f46; font-size:14px; margin-bottom:4px;">' + name + '</div>' +
                          (desc ? '<div style="font-size:12px; color:#475569;">' + desc + '</div>' : '') +
                          '</div>';
            infoWindow.setContent(content);
            infoWindow.setPosition(event.latLng);
            infoWindow.open(map);
        });

        map.data.addListener('mouseover', function(event) {
            map.data.overrideStyle(event.feature, {
                fillColor: '#34d399',
                fillOpacity: 0.45,
                strokeWeight: 3
            });
        });

        map.data.addListener('mouseout', function(event) {
            map.data.revertStyle();
        });
    }
</script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAM9KnXl4SMmy68M2syalpDyMfZeNItPjk&loading=async&callback=initMap"></script>
<?php if ($is_standalone): ?>
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
</body>
</html>
<?php endif; ?>

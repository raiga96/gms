<div id="map" style="width: 100%; height: 50vh;"></div>

<script>
    function initMap() {

        var map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: 1.585367,
                lng: 110.334509
            },
            zoom: 17,
            mapTypeId: google.maps.MapTypeId.HYBRID
        });

        var kmzLayer = new google.maps.KmlLayer({
            url: 'https://jts.sarawak.gov.my/dbku/kmz/zon_dbku_baru.kmz',
            map: map,
            zoom: 8,
            preserveViewport: false,
        });
    }
</script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAM9KnXl4SMmy68M2syalpDyMfZeNItPjk&callback=initMap"></script>
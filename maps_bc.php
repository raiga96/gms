<div id="map" style="width:100%; height:50vh;"></div>

<script>
    var map;
    var kmzLayers = [];

    var kmzFiles = [{
            name: "Gov District B Jln Diplomatik",
            url: "https://drive.google.com/uc?export=download&id=1zO4Enm_BKeOOqBmAAzzDIfZc1FBFj9uW"
        },
        {
            name: "Darul Hana 3",
            url: "https://drive.google.com/uc?export=download&id=1zLGK7BGSEmaz4eOUY37TTGp7FYgBQzHh"
        },
        {
            name: "Santubong, Bako and Buntal B Simpang SK SG BUDA",
            url: "https://drive.google.com/uc?export=download&id=1x_gqijEsNMVrcIkGJCIopL_XpOQJP-bL"
        },
        {
            name: "Sg Berdaun bsr",
            url: "https://drive.google.com/uc?export=download&id=1xQ_RMvfAPncIRzAeXomKIz-gGElRd5Zv"
        },
        {
            name: "Petra Jaya B",
            url: "https://drive.google.com/uc?export=download&id=1uzpj2pOrDRWuiBGOkSarvjIHQmX6X5jH"
        },
        {
            name: "Zone Darul Hana",
            url: "https://drive.google.com/uc?export=download&id=1vNnJliT7immCQZp-v0i4gpYTahO2_prs"
        },
        {
            name: "Petra Jaya Gov District A",
            url: "https://drive.google.com/uc?export=download&id=1uoWG_Y4OZn4-Die9iHa09ktxhmnnNMHa"
        },
        {
            name: "Gov District B Kasuma",
            url: "https://drive.google.com/uc?export=download&id=1taWS0pw5PvL9wc3og3qrs683tyNdw10D"
        },
        {
            name: "Zone PJ C Tmn desa guru",
            url: "https://drive.google.com/uc?export=download&id=1tPlpd727n6m_8zTzJ1AprKu6lbG5mLUH"
        },
        {
            name: "Zone Demak Laut & Sejingkat A 2",
            url: "https://drive.google.com/uc?export=download&id=1svmA2Evg_r3mwG58XT4d6fyQDdnvAuJQ"
        },
        {
            name: "Demak Laut & Sejingkat B",
            url: "https://drive.google.com/uc?export=download&id=1q6EiO1PX2e_Z4sW4F8pXtT5lS015oOuK"
        },
        {
            name: "Gov District B Jln Tun Abdul Rahman Yaakub",
            url: "https://drive.google.com/uc?export=download&id=1puunuGF6W7mWqdCK6yX9HJK3AHheu0Hb"
        },
        {
            name: "Taman Sri Permai 2",
            url: "https://drive.google.com/uc?export=download&id=1p-C4Q8IJfLt2dEcSiB7sQOIHtbWxzKtt"
        },
        {
            name: "OUTER CBD WEST E",
            url: "https://drive.google.com/uc?export=download&id=1mx_gvog16BotEfe4dqnsEdO1HYK2rl4u"
        },
        {
            name: "Jln Ceria",
            url: "https://drive.google.com/uc?export=download&id=1lRizd4x-AJjWMAMcYTstpnDY-Ng4XMrm"
        },
        {
            name: "Bwh Jambatan Santubong 2",
            url: "https://drive.google.com/uc?export=download&id=1l-5m_sicu66ONdGIELybolaF9cMdURrL"
        },
        {
            name: "Riveria",
            url: "https://drive.google.com/uc?export=download&id=1efkL40GMKlvmFk8CQT4qxMYFkX-5TBhK"
        },
        {
            name: "zone samariang(bbs)",
            url: "https://drive.google.com/uc?export=download&id=1hxbw4J28pWoYxSXmYE60wJv3jWsrC1Ar"
        },
        {
            name: "Isthmus",
            url: "https://drive.google.com/uc?export=download&id=1eboQTAqqebSikVH7H-Lrl5HYwTDj979w"
        },
        {
            name: "Parit RBT SESCO",
            url: "https://drive.google.com/uc?export=download&id=1dou7wWG2-IWImA9hjGCCOiP02KXVE6Bq"
        },
        {
            name: "zone samariang(taman puteri)",
            url: "https://drive.google.com/uc?export=download&id=1dPlR7YhhldiX8xLWxWQ7VrZOkenoHhqJ"
        },
        {
            name: "Parit RBT MAHKAMAH",
            url: "https://drive.google.com/uc?export=download&id=1ZJ7nvcW5GOf-vtFg2rCDlzymy-pQpjDJ"
        },
        {
            name: "zone samariang(family park)",
            url: "https://drive.google.com/uc?export=download&id=1ZA09BXoxFtItmZ7W8SuBclWV9sfTYG7U"
        },
        {
            name: "Tmn Sri Permai 1",
            url: "https://drive.google.com/uc?export=download&id=1Z2PxbuMyyQNLG-vX6tv5E2mprXMIe3V7"
        },
        {
            name: "Petra Jaya Government District C",
            url: "https://drive.google.com/uc?export=download&id=1YtrEuhADArU12OK0Ald12r0ZC0PGbI6-"
        },
        {
            name: "Gov District B Tmn Farhanas",
            url: "https://drive.google.com/uc?export=download&id=1W8oRHrTcQBvYVlesTyTZZrPmqQ2NhnEu"
        },
        {
            name: "Zone Darul Hana Attribute",
            url: "https://drive.google.com/uc?export=download&id=1UrYrXLn-YuZH-Saw9cI1qAIv9PpNibaj"
        },
        {
            name: "Gov District B Perumahan siol kandis",
            url: "https://drive.google.com/uc?export=download&id=1RPWSGRwtpavQmeVTAuyzIPJTm0NjEkpZ"
        },
        {
            name: "OUTER CBD WEST A",
            url: "https://drive.google.com/uc?export=download&id=1Qzybg8LyBRdemyewt7bo3o5AruZmd9Bu"
        },
        {
            name: "Satok B",
            url: "https://drive.google.com/uc?export=download&id=1QhJTCet4iJnJmm_VkM3nOe8i36mnEQPN"
        },
        {
            name: "Zone Demak Laut and Sejingkat A",
            url: "https://drive.google.com/uc?export=download&id=1MgnavQbLsxx0riGx2OWj9NoZRr2GcgBK"
        },
        {
            name: "Zone PJ C Tmn Sukma",
            url: "https://drive.google.com/uc?export=download&id=1MCGoJRDAqoF0Zw2u2pZa38VABvvWa5xA"
        },
        {
            name: "Zone Petra Jaya C Attribute",
            url: "https://drive.google.com/uc?export=download&id=1LrXEAulQHHhX0Nx9r3_ssTu1y1qbxbaF"
        },
        {
            name: "Bwh Jambatan Santubong",
            url: "https://drive.google.com/uc?export=download&id=1LKyW2OFlwzvPlErkaqrDxIDdUYvak7x-"
        },
        {
            name: "OUTER CBD WEST D",
            url: "https://drive.google.com/uc?export=download&id=1Ky_aP_EK51rjbZG1A3WG50RViczTl7Dy"
        },
        {
            name: "OUTER CBD WEST B",
            url: "https://drive.google.com/uc?export=download&id=1I2boDDCOPkTENwLxfRiAL-P0nG2r0vIT"
        },
        {
            name: "Darul Hana Jln Besar",
            url: "https://drive.google.com/uc?export=download&id=1HIX-YceN_O5ANt-riqdixrKBQSr9E6Tx"
        },
        {
            name: "Sri Damai",
            url: "https://drive.google.com/uc?export=download&id=1Bp0tLHwnNZ1N4s1LB6-PoMVf6tVpp9xG"
        },
        {
            name: "Jln Masuk Centexs",
            url: "https://drive.google.com/uc?export=download&id=1F6mfn9XoV2H_7XImsvrv5CLFdyfbndIW"
        },
        {
            name: "Petra Jaya A",
            url: "https://drive.google.com/uc?export=download&id=1AQIGFQl2vk6gAtz5-uKs1W6Wiz1KhUWF"
        },
        {
            name: "Zone Demak Laut & Sejingkat A Attribute",
            url: "https://drive.google.com/uc?export=download&id=17jEDwSpiVY1OZKxDk8AluhwQgiTacmHT"
        },
        {
            name: "Satok A",
            url: "https://drive.google.com/uc?export=download&id=16j8X10gCuMal1ELzMQhW0OELR_Y4Kopd"
        },
        {
            name: "Kampung Rampangi Attrribute",
            url: "https://drive.google.com/uc?export=download&id=15bhYrS-HMm_GM-nFpjJa0DyY3wSIIrjf"
        },
        {
            name: "Sri Mutiara",
            url: "https://drive.google.com/uc?export=download&id=14W6cb4mlTKxqxBZXMrAO_n1dDyO3kIi7"
        },
        {
            name: "Santubong, Bako and Buntal A Attribute",
            url: "https://drive.google.com/uc?export=download&id=116gWsuCuPZFneMn4MUJbhBeXSbVZVnmE"
        },
        {
            name: "PPR Petra Jaya 1",
            url: "https://drive.google.com/uc?export=download&id=1-m8YxM4l3muxitoMxVM4S_N00797fZaU"
        },
        {
            name: "Santubong, Bako & Buntal B Attribute",
            url: "https://drive.google.com/uc?export=download&id=1-VoR1-jz96gvpKU3VOa2HpHEqDOdErf2"
        },
        {
            name: "CBD West C",
            url: "https://drive.google.com/uc?export=download&id=1-SFuQ3bNTkSKZQPViz-xqgKkXUTnioWS"
        },
        {
            name: "Parit RBT Normah",
            url: "https://drive.google.com/uc?export=download&id=1-NrsqwEG3rwM-54_pm7BZJCfDwiNBmte"
        }
    ];


    function initMap() {

        map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: 1.585367,
                lng: 110.334509
            },
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.HYBRID
        });

        kmzFiles.forEach(function(item, index) {

            // 🔴 IMPORTANT: map must be set here
            var layer = new google.maps.KmlLayer({
                url: item.url,
                map: map,
                preserveViewport: true
            });

            kmzLayers.push(layer);

            // checkbox
            var cb = document.createElement("input");
            cb.type = "checkbox";
            cb.checked = true;

            cb.onchange = function() {
                layer.setMap(this.checked ? map : null);
            };

            var label = document.createElement("label");
            label.style.display = "block";
            label.appendChild(cb);
            label.append(" " + item.name);

            document.getElementById("controls").appendChild(label);
        });
    }
</script>
<script>
    kmzFiles.forEach(function(item) {
        var cb = document.createElement("input");
        cb.type = "checkbox";
        cb.checked = true;
        cb.onchange = function() {
            layer.setMap(this.checked ? map : null);
        };

        var label = document.createElement("label");
        label.style.display = "flex"; // horizontal dalam label
        label.style.alignItems = "center";
        label.style.gap = "5px"; // jarak antara checkbox & text
        label.appendChild(cb);
        label.append(item.name);

        document.getElementById("controls").appendChild(label);
    });
</script>


<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAM9KnXl4SMmy68M2syalpDyMfZeNItPjk&callback=initMap"></script>
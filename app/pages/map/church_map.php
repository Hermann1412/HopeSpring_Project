<?php

include("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['mybook_userid']);
$USER = $user_data;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Church Map | HopeSpring</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
</head>
<body>

<?php include("app/partials/header.php"); ?>

<div class="page-wrapper" style="max-width:1080px;">
    <section class="card map-page-card">
        <div class="map-head">
            <h2>Nearby Churches</h2>
            <p>Showing all nearby churches within a 20 km radius.</p>
            <div class="map-tools">
                <input type="text" id="churchSearchInput" class="form-control" placeholder="Search nearby church by name...">
                <div class="map-tools-place">
                    <input type="text" id="placeSearchInput" class="form-control" placeholder="Search another place (city, area, address)...">
                    <button type="button" id="placeSearchBtn" class="btn btn-outline btn-sm">Search Place</button>
                </div>
            </div>
        </div>

        <div id="churchMap" class="church-map"></div>

        <div class="map-status" id="mapStatus">Requesting your location...</div>
        <div class="church-reco" id="churchReco"></div>
        <div class="church-list" id="churchList"></div>
    </section>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function () {
    const statusEl = document.getElementById("mapStatus");
    const listEl = document.getElementById("churchList");
    const recoEl = document.getElementById("churchReco");
    const searchInput = document.getElementById("churchSearchInput");
    const placeInput = document.getElementById("placeSearchInput");
    const placeBtn = document.getElementById("placeSearchBtn");

    let allChurches = [];
    let markerLayer = L.layerGroup();
    let originLat = null;
    let originLon = null;
    let userMarker = null;
    let placeMarker = null;

    const map = L.map("churchMap").setView([0, 0], 2);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);
    markerLayer.addTo(map);

    function setStatus(text, type) {
        statusEl.textContent = text;
        statusEl.className = "map-status" + (type ? " " + type : "");
    }

    function addChurchItem(church) {
        const name = church.name || "Unnamed Church";
        const item = document.createElement("div");
        item.className = "church-item";
        item.innerHTML = "<strong>" + name + "</strong><span>" + church.distanceLabel + " away</span>";
        item.addEventListener("click", function () {
            map.setView([church.lat, church.lon], 16);
            church.marker.openPopup();
        });
        listEl.appendChild(item);
    }

    function setRecommendations(items) {
        if (!items || items.length === 0) {
            recoEl.innerHTML = "";
            return;
        }

        const picks = items.slice(0, 3);
        let html = '<div class="church-reco-title">Recommended nearby</div><div class="church-reco-row">';
        picks.forEach(church => {
            html += '<button type="button" class="church-reco-item" data-lat="' + church.lat + '" data-lon="' + church.lon + '">'
                + '<strong>' + church.name + '</strong>'
                + '<span>' + church.distanceLabel + ' away</span>'
                + '</button>';
        });
        html += '</div>';
        recoEl.innerHTML = html;

        recoEl.querySelectorAll('.church-reco-item').forEach(btn => {
            btn.addEventListener('click', function () {
                const lat = parseFloat(btn.getAttribute('data-lat'));
                const lon = parseFloat(btn.getAttribute('data-lon'));
                const match = allChurches.find(c => c.lat === lat && c.lon === lon);
                map.setView([lat, lon], 16);
                if (match && match.marker) {
                    match.marker.openPopup();
                }
            });
        });
    }

    function haversineKm(lat1, lon1, lat2, lon2) {
        const toRad = deg => deg * Math.PI / 180;
        const R = 6371;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function renderChurches(filtered) {
        listEl.innerHTML = "";
        markerLayer.clearLayers();

        if (!filtered || filtered.length === 0) {
            setRecommendations([]);
            setStatus("No churches match that search nearby.", "warn");
            return;
        }

        const bounds = [];
        filtered.forEach(church => {
            const marker = L.marker([church.lat, church.lon]).addTo(markerLayer);
            marker.bindPopup("<strong>" + church.name + "</strong><br>Christian Church");
            church.marker = marker;
            bounds.push([church.lat, church.lon]);
            addChurchItem(church);
        });

        setRecommendations(filtered);
        setStatus("Found " + filtered.length + " churches nearby.", "ok");
        map.fitBounds(L.latLngBounds(bounds).pad(0.2));
    }

    function buildOverpassQuery(radius, lat, lon) {
        return `[out:json][timeout:30];\n(
                    node[\"amenity\"=\"place_of_worship\"][\"religion\"=\"christian\"](around:${radius},${lat},${lon});
                    way[\"amenity\"=\"place_of_worship\"][\"religion\"=\"christian\"](around:${radius},${lat},${lon});
                    relation[\"amenity\"=\"place_of_worship\"][\"religion\"=\"christian\"](around:${radius},${lat},${lon});
        );\nout center;`;
    }

    async function fetchFromOverpassWithFallback(query) {
        const endpoints = [
            "https://overpass-api.de/api/interpreter",
            "https://overpass.kumi.systems/api/interpreter",
            "https://lz4.overpass-api.de/api/interpreter"
        ];

        let lastError = "Unknown network error";

        for (const endpoint of endpoints) {
            try {
                const resp = await fetch(endpoint, { method: "POST", body: query });
                if (!resp.ok) {
                    lastError = "HTTP " + resp.status + " from " + endpoint;
                    continue;
                }
                return await resp.json();
            } catch (err) {
                lastError = (err && err.message) ? err.message : lastError;
            }
        }

        throw new Error(lastError);
    }

    async function fetchChurches(lat, lon) {
        const radius = 20000;
        listEl.innerHTML = "";
        setRecommendations([]);

        try {
            const query = buildOverpassQuery(radius, lat, lon);
            const data = await fetchFromOverpassWithFallback(query);

            allChurches = [];
            (data.elements || []).forEach(el => {
                const tags = el.tags || {};
                const churchLat = el.lat || (el.center && el.center.lat);
                const churchLon = el.lon || (el.center && el.center.lon);
                if (!churchLat || !churchLon) return;

                const name = tags.name || "Unnamed Church";

                const dist = haversineKm(originLat, originLon, churchLat, churchLon);
                allChurches.push({
                    name: name,
                    lat: churchLat,
                    lon: churchLon,
                    distance: dist,
                    distanceLabel: (dist < 1) ? Math.round(dist * 1000) + " m" : dist.toFixed(1) + " km"
                });
            });

            allChurches.sort((a, b) => a.distance - b.distance);

            if (allChurches.length > 0) {
                renderChurches(allChurches);
                return;
            }

            setStatus("No churches found within 20 km.", "warn");
        } catch (err) {
            const msg = err && err.message ? err.message : "Unknown network error";
            setStatus("Could not fetch church data right now: " + msg, "err");
        }
    }

    searchInput.addEventListener("input", function () {
        const term = searchInput.value.trim().toLowerCase();
        if (!term) {
            renderChurches(allChurches);
            return;
        }

        const filtered = allChurches.filter(church => church.name.toLowerCase().includes(term));
        renderChurches(filtered);
    });

    function searchPlaceAndRefresh() {
        const q = placeInput.value.trim();
        if (!q) {
            return;
        }

        setStatus("Searching place: " + q + " ...", "");

        fetch("https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" + encodeURIComponent(q))
            .then(resp => resp.json())
            .then(results => {
                if (!Array.isArray(results) || results.length === 0) {
                    setStatus("Place not found. Try a city or neighborhood name.", "warn");
                    return;
                }

                const lat = parseFloat(results[0].lat);
                const lon = parseFloat(results[0].lon);

                if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
                    setStatus("Could not resolve that place location.", "warn");
                    return;
                }

                originLat = lat;
                originLon = lon;

                if (placeMarker) {
                    map.removeLayer(placeMarker);
                }
                placeMarker = L.marker([lat, lon]).addTo(map).bindPopup("Search area: " + q).openPopup();
                map.setView([lat, lon], 13);

                fetchChurches(lat, lon);
            })
            .catch((err) => {
                const msg = err && err.message ? err.message : "Unknown network error";
                setStatus("Could not search that place: " + msg, "err");
            });
    }

    placeBtn.addEventListener("click", searchPlaceAndRefresh);
    placeInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            searchPlaceAndRefresh();
        }
    });

    if (!navigator.geolocation) {
        setStatus("Geolocation is not supported in this browser.", "err");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        pos => {
            originLat = pos.coords.latitude;
            originLon = pos.coords.longitude;

            map.setView([originLat, originLon], 13);
            userMarker = L.marker([originLat, originLon]).addTo(map).bindPopup("You are here").openPopup();
            fetchChurches(originLat, originLon);
        },
        () => {
            setStatus("Location permission denied. Enable location to find nearby churches.", "err");
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
})();
</script>

</body>
</html>

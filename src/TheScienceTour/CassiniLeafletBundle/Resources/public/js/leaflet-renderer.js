const   NO_GEOLOCATION = 1,
        DEF_LATITUDE = 48,
        DEF_LONGITUDE = 2,
        DEF_ZOOM = 10;

var LST = L.Class.include({
    // La liste des marqueurs associés à une carte
    markers: [],

    /**
     * Supprime tous les marqueurs de position d'une carte donnée
     *
     * @returns {Object}
     */
    unMarkAll: function() {
        while (this.markers.length > 0) {
            marker = this.markers.shift();
            map.removeLayer(marker);
        }
        return this;
    },

    /**
     *  Affiche une liste de marqueurs de positions sur une carte
     *
     * @param {Array} listPositions
     * @returns {L.Map}
     */
    mark: function(listPositions) {
        for (let point of listPositions) {
            console.log(point);
            this.markers.push(
                L.marker([point.latitude, point.longitude])
                    .addTo(this)
                    .bindPopup(point.title)
            )
        }

        return this;
    }
});

var mapBoxes = {};

/**
 * Donne la position géolocalisée de l'ordinateur accédant au site,
 * sous forme de promesse qui échoue si l'ordinateur met trop de temps à répondre oun'a pas accès à une API GPS
 *
 * @returns {Promise}
 */
function gpsPicker() {
    return new Promise(function (resolve, reject) {
        console.log('GPS');
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(
                resolve,
                reject,
                {timeout: 10000}
            );
        } else {
            // alert('Pas de géolocalisation');
            reject({"lat": DEF_LATITUDE, "long": DEF_LONGITUDE, "reason": NO_GEOLOCATION});
        }
    });
}

/**
 * Initialise une carte avec une liste de  marqueurs de position
 *
 * @param {Array} position
 * @param {Object} whichMap
 */
function mapBuild([position, whichMap]) {
    console.log(whichMap);
    console.log(position);
    var latitude  = position.coords.latitude;
    var longitude = position.coords.longitude;
    whichMap.setView([latitude, longitude], DEF_ZOOM);
    mark(whichMap, 'projects', 'in-progress', latitude, longitude)
}

/**
 * Rafraîchit les marqueurs de position affichés sur une carte en fonction d'une liste de critères :
 * tyoe de contenu, sélection temporelle, latitude & longitude de l'utilisateur, restriction à l'environnement proche de l'utilisateur
 *
 * @param {Array} [position, options]
 */
function refreshMarkers([position, options]) {
    let contentPattern = options.data('content-pattern');
    let selection = optins.data('selection');
    let aroundOnly = options.data('around');
    let target = options.data('target');
    mark(target, contentPattern, selection, latitude, longitude, aroundOnly);
}

/**
 * Affiche sur la carte les marqueurs de positions correspondant à tous les contenus sélectionnés
 *
 * @param {Object} map              Une carte Leaflet
 * @param {string} contentPattern   Type de contenu à afficher
 * @param {string} selection        Critère de sélection du contenu
 * @param {float} latitude          Latitude de l'ordinateur du visiteur
 * @param {float} longitude         Longitude de l'ordinateur du visiteur
 * @param {bool} around             Restreindre les marqueurs à l'environnement proche du visiteur
 */
function mark(map, contentPattern, selection, latitude, longitude, around = false) {
    var url = `/st0/web/app_dev.php/${contentPattern}/json/${selection}/${latitude}/${longitude}`;
    console.log(url);
    $.getJSON(url)
        .done(function(listPositions) {
            console.log(listPositions);
            map.unMarkAll().mark(listPositions);
            /*
            for (let point of listPositions) {
                console.log(point);
                map.markers.push(
                    L.marker([point.latitude, point.longitude])
                    .addTo(map)
                    .bindPopup(point.title)
                )
            }
            */
        })
        .fail(function(error) {
            console.log(error)
        })
}

function geoError(reason) {
    console.log("Erreur de géolocalisation");
    console.log(reason);
    //aroundMeMap.setView([DEF_LATITUDE, DEF_LONGITUDE], DEF_ZOOM);
}

function mapRender() {
    let id = $(this).prop('id');
    console.log(id);
    let position = $(this).data('position');
    console.log(position);
    mapBoxes[id]= L.map(id);
    mapBoxes[id].markers = [];
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>  ',
        maxZoom: 28
    }).addTo(mapBoxes[id]);

    if (position == 'default') {
        mapBoxes[id].setView([DEF_LATITUDE, DEF_LONGITUDE], DEF_ZOOM);
        mark(mapBoxes[id], 'projects', 'in-progress', DEF_LATITUDE, DEF_LONGITUDE)
    } else {
        Promise
            .all([gpsPicker(), Promise.resolve(mapBoxes[id])])
            .then(mapBuild)
            .catch(geoError);

    }

}


$(document).ready(function() {
    $('.markerRenderable').on('click', function (event) {
        Promise
            .all(gpsPicker(), Promise.resolve($this))
            .then(refreshMarkers)
            .catch(geoError);
    })
})

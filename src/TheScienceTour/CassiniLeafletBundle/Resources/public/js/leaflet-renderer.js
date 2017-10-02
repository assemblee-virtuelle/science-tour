const DEF_LATITUDE = 48.7,
      DEF_LONGITUDE = 2.4,
      DEF_ZOOM = 10;

var mapBoxes = {};

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

function mapBuild([position, whichMap]) {
    console.log(whichMap);
    var latitude  = position.coords.latitude;
    var longitude = position.coords.longitude;
    whichMap.setView([latitude, longitude], DEF_ZOOM);
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
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>  ',
        maxZoom: 28
    }).addTo(mapBoxes[id]);

    if (position == 'default') {
        mapBoxes[id].setView([DEF_LATITUDE, DEF_LONGITUDE], DEF_ZOOM);
    } else {
        Promise
            .all([gpsPicker(), Promise.resolve(mapBoxes[id])])
            .then(mapBuild)
            .catch(geoError)
    }

}
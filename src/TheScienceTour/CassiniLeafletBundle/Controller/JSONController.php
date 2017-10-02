<?php
/**
 * Created by PhpStorm.
 * User: ferdydurke
 * Date: 26/09/2017
 * Time: 19:39
 */

namespace TheScienceTour\CassiniLeafletBundle\Controller;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\ProviderAggregator;
use Http\Adapter\Guzzle6\Client;


class JSONController extends Controller
{

    private function markerCreator($lat, $long, $label, $key): string
    {
        return "var marker{$key} = L.marker([{$lat}, {$long}]).addTo(map);
    marker{$key}.bindPopup(\"{$label}\");";
    }

    public function markersAction(Request $request)
    {
        $geocoder = new ProviderAggregator();
        // Create a chain of providers.
        $chain = new Chain(
            array(
                new Nominatim($adapter, "http://nominatim.openstreetmap.org/search"),
                new FreeGeoIp($adapter, "https://freegeoip.net/json/%s")
            )
        );

        $geocoder->registerProvider($chain);

        $locations = [];

        $mapdata = $marker_group = [];

        foreach ($locations as $key => $value) {
            // Try to geocode.
            try {
                $geocode = $geocoder->geocodeQuery(GeocodeQuery::create($value['address']));
                $coordinates = $geocode->first()->getCoordinates();
                $longitude = $coordinates->getLongitude();
                $latitude = $coordinates->getLatitude();
                // Create map data array
                $mapdata[] = markerCreator($latitude, $longitude, $value['title'], $key);
                // Marker grouping array
                $marker_group[] = "marker{$key}";
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
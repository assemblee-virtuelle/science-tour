<?php
namespace TheScienceTour\MapBundle\Helper;

use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\Provider\GeoPlugin\GeoPlugin;
use Geocoder\Provider\HostIp\HostIp;
use Geocoder\ProviderAggregator;



/**
 *
 * @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
 *
 */
class MapHelper {

	/**
	 * Geocode IP or street addresses
	 * @param String $address
	 * @throws Exception
	 * @return Geocode
	 */
	public function getGeocode($address) {
		// For local testing set your public IP
		if ($address == '127.0.0.1') $address = '82.229.75.195';

		//$buzz    = new \Buzz\Browser(new \Buzz\Client\Curl());
		//$adapter = new \Geocoder\HttpAdapter\BuzzHttpAdapter($buzz);

		//$adapter = new \Geocoder\HttpAdapter\SocketHttpAdapter();
		$config = [
    'timeout' => 2.0,
    'verify' => false,
];
	  $guzzle = new GuzzleClient($config);

	  $adapter  = new Client($guzzle);
		//$adapter = new TSTSocketHttpAdapter();

		$chain    = new Chain(array(
			    new FreeGeoIp($adapter),
				new GeoPlugin($adapter),
				new HostIp($adapter),
			    new GoogleMaps($adapter, 'fr_FR', 'France', true)
		));

		$aggregator = new ProviderAggregator();
		$aggregator->registerProvider($chain);
		//$geocoder = new \Geocoder\Geocoder();
		//$geocoder->registerProvider($chain);

		try {
			  $query = GeocodeQuery::create($address);
		    $geocode = $aggregator->geocodeQuery(GeocodeQuery::create($address));
		} catch (\Geocoder\Exception\ChainNoResultException $e) {
		    error_log($e->getMessage());
		} catch (Exception $e) {
		    error_log($e->getMessage());
		    throw $e;
		}

	    return $geocode;
	}

}

<?php
namespace TheScienceTour\MapBundle\Helper;

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
		$adapter = new TSTSocketHttpAdapter();
		
		$chain    = new \Geocoder\Provider\ChainProvider(array(
			    new \Geocoder\Provider\FreeGeoIpProvider($adapter),
				new \Geocoder\Provider\GeoPluginProvider($adapter),
				new \Geocoder\Provider\HostIpProvider($adapter),
			    new \Geocoder\Provider\GoogleMapsProvider($adapter, 'fr_FR', 'France', true)
		));
		$geocoder = new \Geocoder\Geocoder();
		$geocoder->registerProvider($chain);
		$geocode = $geocoder->geocode('');
		
		try {
		    $geocode = $geocoder->geocode($address);
		} catch (\Geocoder\Exception\ChainNoResultException $e) {
		    error_log($e->getMessage());
		} catch (Exception $e) {
		    error_log($e->getMessage());
		    throw $e;
		}
		
	    return $geocode;
	}

}

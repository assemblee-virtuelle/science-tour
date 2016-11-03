<?php
namespace TheScienceTour\MapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ivory\GoogleMap\Overlays\Animation;
use Ivory\GoogleMap\Overlays\Circle;
use Ivory\GoogleMap\Overlays\Marker;
use Ivory\GoogleMap\Controls\ControlPosition;
use Ivory\GoogleMap\Controls\ZoomControl;
use Ivory\GoogleMap\Controls\ZoomControlStyle;
use Ivory\GoogleMap\Events\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Ivory\GoogleMap\Base\Point;
use Gaufrette\Adapter\file_exists;
use Ivory\GoogleMap\Overlays\InfoWindow;
use Ivory\GoogleMap\Events\MouseEvent;
use Symfony\Bridge\Monolog\Logger;
use Ivory\GoogleMap\Base\Bound;
use Symfony\Component\CssSelector\XPath\Translator;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
 *
 */
class MapController extends Controller {
	
	public function mapAction($documentList = array(), $route = array(), $menus = array(), $centerCoordinates = array(), $defaultMap = false) {
		$map = $this->_addMarkers($documentList, $centerCoordinates, true);
		
		return $this->render('TheScienceTourMapBundle::map.html.twig', array(
				'map' => $map,
				'route' => $route,
				'menus' => $menus,
				'defaultMap' => $defaultMap
		));
	}

	public function asideMapAction($documentList,
			$zoomMax = 5,
			$sizes = array('width' => '204px', 'height' => '153px'),
			$htmlContainerId = 'aside-map-canvas', $centerCoordinates = array()) {
		
		if (!($documentList instanceof \Countable)) {
			$documentList = new ArrayCollection(array($documentList));
		}

		$map = $this->_addMarkers($documentList, $centerCoordinates, false);
		
		$map->setHtmlContainerId($htmlContainerId);
		
		$map->setMapOptions(array(
				'mapTypeId'					=> 'roadmap',
				'disableDefaultUI'			=> true,
				'disableDoubleClickZoom'	=> true,
		));
		
		$map->setStylesheetOptions($sizes);
		
		$zoomControl = new ZoomControl();
		
		// Add your zoom control to the map
		$map->setZoomControl($zoomControl);
		$map->setZoomControl(ControlPosition::TOP_RIGHT, ZoomControlStyle::DEFAULT_);			
	
		return $this->render('TheScienceTourMapBundle::asideMap.html.twig', array(
				'map' => $map,
				'zoomMax' => $zoomMax
		));
	}
	
	private function _addMarkers($documentList, $centerCoordinates, $showInfoWindow) {
		// + Google Map and markers
		// + --------------------------------------------------
		$map = $this->get('ivory_google_map.map');
		$map->setLibraries(array('places'));
		
		// Is there a document or a center to place on the map ?
		if ($documentList->count() || sizeof($centerCoordinates)) {
			$map->setAutoZoom(true);
			$bound = null;
		
			if (sizeof($centerCoordinates)) {
				// Requests the ivory google map circle service
				$circle = $this->get('ivory_google_map.circle');

				$circle = new Circle();
				
				// Configure your circle options
				$circle->setPrefixJavascriptVariable('circle_');
				$circle->setCenter($centerCoordinates['latitude'], $centerCoordinates['longitude'], true);
				$circle->setRadius(50000); // In meters
				
				$circle->setOptions(array(
						'strokeColor'	=> '#4d3965',
						'strokeOpacity'	=> 0.8,
						'strokeWeight'	=> 1,
						'fillColor'		=> '#805fa9',
						'fillOpacity'	=> 0.2,
						'clickable'		=> false,
				));
				
				// Add your circle to the map
				$map->addCircle($circle);
				
				$bound = new Bound();
				$bound->extend($circle);
				
				$marker = new Marker();
				$marker->setPrefixJavascriptVariable('marker_');
				$marker->setPosition($centerCoordinates['latitude'], $centerCoordinates['longitude'], true);
				$marker->setAnimation(Animation::DROP);
				$marker->setOptions(array(
						'clickable' => false,
						'flat'      => true,
				));
				$marker->setIcon($this->container->getParameter('root_url')."/img/markers/my-position.png");
				$marker->getIcon()->setAnchor(new Point(9.5,9.5));
				
				$map->addMarker($marker);
			}
			
			foreach ($documentList as $document) {
				$documentCoordinates = $document->getCoordinates();
				if ($documentCoordinates) {
					$markerUrl = $this->_getMarkerUrl($document, $this->container->getParameter('root_url'));
					$markerSize = $this->_getMarkerSize($markerUrl);
					
					$marker = new Marker();
					$marker->setIcon($markerUrl);
					$marker->getIcon()->setAnchor(new Point(round(intval($markerSize[0]) / 2), intval($markerSize[1]))); // H center, V bottom
					$marker->setPrefixJavascriptVariable('marker_');
					$marker->setPosition($documentCoordinates->getLatitude(), $documentCoordinates->getLongitude(), true);
					$marker->setAnimation(Animation::DROP);
					$marker->setOptions(array(
							'clickable' => true,
							'flat'      => true,
					));
					
					$map->addMarker($marker);

					if ($showInfoWindow) {
						$content = '<div class="map-infoWindow">';
						
						if (method_exists($document, 'getTools')) {
							// Projects
							$content .= '
								<a href="'.$this->get('router')->generate('tst_project', array('id' => $document->getId())).'" >
									<h2>'.$document->getTitle().'</h2>
									<p>
										<strong>'.$document->getDuration().'</strong> '.$this->get('translator')->transChoice($document->getDurationUnit(), $document->getDuration()).'
										<span><i class="icon-beaker"></i>'.$document->getTotalResNb().'</span>
									</p>
									<div class="progress_bar">
										<div style="width: 35%;">
										</div>
									</div>
								</a>
							';
						} else {
							// Events
							$content .= '
								<a href="'.$this->get('router')->generate('tst_event', array('id' => $document->getId())).'" >
									<h2>'.$document->getTitle().'</h2>
									<p class="txtright darkGrey">'.$document->getStartDate()->format('d/m/Y H:i').' - '.$document->getEndDate()->format('d/m/Y H:i').'</p>
									<p>'.$document->getPlace().'</p>
								</a>
							';
						}
						
						$content .= '</div>';
						
						$infoWindow = new InfoWindow();
						$infoWindow->setContent($content);
						$infoWindow->setAutoClose(true);
						$marker->setInfoWindow($infoWindow);
					} else {
						if (method_exists($document, 'getTools')) {
							$url = $this->get('router')->generate('tst_project', array('id' => $document->getId()));
						} else {
							$url = $this->get('router')->generate('tst_event', array('id' => $document->getId()));
						}
						$event = new Event();
						$event->setInstance($marker->getJavascriptVariable());
						$event->setEventName('click');
						$event->setHandle('function(){window.location = "' . $url . '";}');
						$event->setCapture(true);
						$map->getEventManager()->addDomEvent($event);
					}
				}
			}
			$map->getMarkerCluster()->setOption('styles', array(array('url' => $this->container->getParameter('root_url').'/img/markers/cluster.png', 'width' => 24, 'height' => 24, 'textColor' => '#FFFFFF', 'textSize' => 10)));
			$map->getMarkerCluster()->setOption('gridSize', 35);
			$map->getMarkerCluster()->setOption('maxZoom', 18);
			
			if ($bound instanceof Bound) {
				$map->setBound($bound);
			}
		}
		
		return $map;
	}
	
	private function _getMarkerUrl($document, $baseurl) {
		$markerUrl = $baseurl . "/img/markers/default.png";
			
		if ($document instanceof \TheScienceTour\EventBundle\Document\Event) {
			if ($label = $document->getLabel()) {
				if ($label->GetLabelType() && $label->getLabelType()->getMarker()) {
					$markerMedia = $document->getLabel()->getLabelType()->getMarker();
					if ($markerMedia) {
						$provider = $this->container->get($markerMedia->getProviderName());
						$format = $provider->getFormatName($markerMedia,
								$document->getLabel()->getLabelType()->getMarkerFormat());
						$markerUrl = $provider->generatePublicUrl($markerMedia, $format);
					}
				}
					
				if ($bgColor = $label->getBackgroundColor()) {
					if (preg_match("/^#[0-9a-fA-F]{6}$/", $bgColor)) {
						$markerExt = strrchr($markerUrl, '.');
						$markerName = basename($markerUrl, $markerExt);
						$hexa = substr($bgColor, 1);
						if (!file_exists($this->get('kernel')->getRootDir(). "/../web/img/markers/generated/".$markerName."-".$hexa.".png")) {
							if ($this->_colorizeMarker($markerUrl, $hexa)) {
								$markerUrl = $baseurl . "/img/markers/generated/".$markerName."-".$hexa.".png";
							}
						} else {
							$markerUrl = $baseurl . "/img/markers/generated/".$markerName."-".$hexa.".png";
						}
					}
				}
			}
		}
		
		return $markerUrl;
	}
	
	private function  _getMarkerSize($markerUrl) {
		$markerPath = $this->_getMarkerPath($markerUrl);
		$markerSize = (file_exists($markerPath) && filesize($markerPath)) ? getimagesize($markerPath) : array(32,32);
		
		return $markerSize;
	}
	
	private function _getMarkerPath($markerUrl) {
		$markerPath = strstr($markerUrl, '/img/markers/') ? strstr($markerUrl, '/img/markers/') : strstr($markerUrl, '/uploads/media/marker/');
		
		return $this->get('kernel')->getRootDir(). "/../web" . $markerPath;
	}
	
	private function _colorizeMarker($markerUrl, $hexa) {
		$markerRoot = $this->get('kernel')->getRootDir(). "/../web/img/markers/";
		
		$file = $this->_getMarkerPath($markerUrl);
		$fileExt = strrchr($file, '.');
		$fileName = basename($file, $fileExt);
		
		$hexa_R = substr($hexa,0,2);
		$hexa_G = substr($hexa,2,2);
		$hexa_B = substr($hexa,4,2);
		$rgb = array(hexdec($hexa_R),hexdec($hexa_G),hexdec($hexa_B));
		
		$imContent = file_get_contents($file);
		if (!$imContent) {
			$im = imagecreatefrompng($markerRoot . 'default.png');
			$fileName = 'default';
		} else {
			$im = imagecreatefromstring($imContent);
		
			if (!$im) {
				$im = imagecreatefrompng($markerRoot . 'default.png');
				$fileName = 'default';
			}
		}
		
		imagefilter($im, IMG_FILTER_COLORIZE, $rgb[0], $rgb[1], $rgb[2]);
		imagesavealpha($im, true);
		
		$response = imagepng($im, $markerRoot . "generated/".$fileName."-".$hexa.".png");
		imagedestroy($im);
	
		return $response;
	}
	
}

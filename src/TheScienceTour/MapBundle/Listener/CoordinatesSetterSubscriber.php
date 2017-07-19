<?php
namespace TheScienceTour\MapBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use TheScienceTour\MapBundle\Document\Coordinates;
use TheScienceTour\EventBundle\Document\Event;
use TheScienceTour\ProjectBundle\Document\Project;
use TheScienceTour\MapBundle\Helper\MapHelper;

/**
 *
 * @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
 *
 */
class CoordinatesSetterSubscriber implements EventSubscriber {
	protected $mapHelper;
	protected $logger;

	public function __construct(MapHelper $mapHelper) {
		$this->mapHelper = $mapHelper;
	}

	public function getSubscribedEvents()
	{
		return array(
				'prePersist',
				'preUpdate'
		);
	}

	public function prePersist(LifecycleEventArgs $args) {
		$document = $this->_setCoordinates($args);
	}

	/**
	 * If you modify a document in the preUpdate event
	 * you must call recomputeSingleDocumentChangeSet for the modified document
	 * in order for the changes to be persisted.
	 * To learn more see {@link https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/events.html?highlight=event#preupdate}
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function preUpdate(LifecycleEventArgs $args) {
		$document = $this->_setCoordinates($args);
        $dm = $args->getDocumentManager();
        $class = $dm->getClassMetaData(get_class($document));
        $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $document);
	}

	/**
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function _setCoordinates(LifecycleEventArgs $args)
	{
		$document = $args->getDocument();

		// We only want to act on some "Event" or published "Project" document
		if ($document instanceof Event || ($document instanceof Project && $document->getStatus() != 0)) {
			// When updating an event or a project and setting a new place
			// the coordinates must be unset for them to be updated here
			// Examples :
			// - In TheScienceTour\EventBundle\Admin\EventAdmin#preUpdate()
			if (!$document->getCoordinates()) {
				try {
					$geocode = $this->mapHelper->getGeocode($document->getPlace());
				} catch (Exception $e) {
					$session = $this->get('session');
					$session->getFlashBag()->add('notice', $e->getMessage());
				}

				$coordinates = new Coordinates();
				$coordinates->setLatitude($geocode->getLatitude());
				$coordinates->setLongitude($geocode->getLongitude());
				$document->setCoordinates($coordinates);
			}
		}

		return $document;
	}
}

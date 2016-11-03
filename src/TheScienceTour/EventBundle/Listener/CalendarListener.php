<?php

namespace TheScienceTour\EventBundle\Listener;

use ADesigns\CalendarBundle\Event\CalendarEvent;
use ADesigns\CalendarBundle\Entity\EventEntity;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * 
 * @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
 *
 */
class CalendarListener {
    private $dm;
    private $router;

    public function __construct(DocumentManager $documentManager, Router $router)
    {
        $this->dm = $documentManager;
        $this->router = $router;
    }
    
	public function loadEvents(CalendarEvent $calendarEvent)
	{
		$startDate = $calendarEvent->getStartDatetime();
		$endDate = $calendarEvent->getEndDatetime();
	
		// load events starting or ending between calendar's startDate and endDate
		$qb = $this->dm->createQueryBuilder('TheScienceTourEventBundle:Event');
		$qb->addOr($qb->expr()->field('startDate')->range($startDate, $endDate));
		$qb->addOr($qb->expr()->field('endDate')->range($startDate, $endDate));
		$sciencetourEvents = $qb->getQuery()->execute();
		
		// An array to store distinct days where an event occurs
		$eventDays = array();
		$interval = \DateInterval::createFromDateString('1 day');
	
		foreach($sciencetourEvents as $sciencetourEvent) {
			// Set period start and end
			$start = max($startDate, $sciencetourEvent->getStartDate());
			$start->setTime(0, 0, 0);
			$end = min($endDate, $sciencetourEvent->getEndDate());
			$end->setTime(23, 59, 59);
			$days = new \DatePeriod($start, $interval, $end);
			
			// For each distinct day create an all day event
			foreach ($days as $day) {
				if (!in_array($day, $eventDays)) {
					$eventEntity = new EventEntity('', $day, null, true);
			
					//optional calendar event settings
					$eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
					$eventEntity->setUrl($this->router
						->generate('tst_agenda_day', array('date' => $day->format('Y-m-d')))); // url to send user to when event label is clicked
// 					$eventEntity->setBgColor('#FF0000'); //set the background color of the event's label
// 					$eventEntity->setFgColor('#FFFFFF'); //set the foreground color of the event's label
// 					$eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels
			
					// Add the event to the CalendarEvent for displaying on the calendar
					$calendarEvent->addEvent($eventEntity);
					
					// Add the day in our event days array
					$eventDays[] = $day;
				}
			}
		}
	}
}

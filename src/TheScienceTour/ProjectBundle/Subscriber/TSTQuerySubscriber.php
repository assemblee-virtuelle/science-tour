<?php

namespace TheScienceTour\ProjectBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Doctrine\ODM\MongoDB\Query\Query;

class TSTQuerySubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof Query) {
            // items
            $type = $event->target->getType();
            if ($type !== Query::TYPE_FIND && $type !== Query::TYPE_GEO_LOCATION) {
                throw new \UnexpectedValueException('ODM query must be a FIND or GEO_LOCATION type query');
            }
            static $reflectionProperty;
            if (is_null($reflectionProperty)) {
                $reflectionClass = new \ReflectionClass('Doctrine\MongoDB\Query\Query');
                $reflectionProperty = $reflectionClass->getProperty('query');
                $reflectionProperty->setAccessible(true);
            }
            $queryOptions = $reflectionProperty->getValue($event->target);

            $queryOptions['limit'] = $event->getLimit();
            $queryOptions['skip'] = $event->getOffset();

            $resultQuery = clone $event->target;
            $reflectionProperty->setValue($resultQuery, $queryOptions);
            $cursor = $resultQuery->execute();

            // set the count from the cursor
            $event->count = $cursor->count();

            $event->items = array();
            // iterator_to_array for GridFS results in 1 item
            foreach ($cursor as $item) {
                $event->items[] = $item;
            }
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1)
        );
    }
}

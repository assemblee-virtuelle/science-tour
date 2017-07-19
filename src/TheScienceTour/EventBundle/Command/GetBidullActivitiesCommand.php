<?php
namespace TheScienceTour\EventBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TheScienceTour\EventBundle\Document\LabelType;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use TheScienceTour\EventBundle\Document\Label;
use TheScienceTour\EventBundle\Document\Event;
use TheScienceTour\MapBundle\Document\Coordinates;

class GetBidullActivitiesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
		->setName('tst:event:get-bidull-activities')
		->setDescription('Récupère les activités de La Bidull (camions, festivals, thémathèquess) et les enregistre en tant qu’événements dans l’agenda')
		->addOption('type', null, InputOption::VALUE_REQUIRED, 'Cette option définie le type : camion ou fest ou thema ou inserm')
		->addOption('date', null, InputOption::VALUE_REQUIRED, 'Si cette option est définie, seules les activités se terminant après cette date seront récupérées')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$url = "http://camions.lesciencetour.org/engine.php?rub=act&type=";
		
		$url .= ($input->getOption('type')) ? $input->getOption('type') : 'camion';
		
		if ($input->getOption('date')) {
			$url .= '&date=' . $input->getOption('date');
		}
		
		$json = file_get_contents($url);
		$createdLabelTypes = 0;
		$createdLabels = 0;
		$createdEvents = 0;
		$updatedEvents = 0;
		
		if ($object = json_decode($json)) {
			if ($data = $object->data) {
				foreach ($data as $regionName => $departments) {
					foreach ($departments as $departmentName => $activities) {
						foreach ($activities as $activity) {
							$labelType = $this->addLabelType($activity->type, $createdLabelTypes);
							$label = $this->addLabel($activity->camion, $labelType, $createdLabels);
							$event = $this->addEvent($activity, $label, $createdEvents, $updatedEvents);
						}
					}
				}
			}
		}
		
		$output->writeln('Created:');
		$output->writeln('- <fg=green>' . $createdLabelTypes . '</fg=green> LabelType');
		$output->writeln('- <fg=green>' . $createdLabels . '</fg=green> Label');
		$output->writeln('- <fg=green>' . $createdEvents . '</fg=green> Event');
		$output->writeln('Updated:');
		$output->writeln('- <fg=green>' . $updatedEvents . '</fg=green> Event');
	}
	
	/**
	 * Create a LabelType for the given slug if it doesn't already exist
	 * @param unknown $slug
	 * @param unknown $counter
	 * @return \TheScienceTour\EventBundle\Document\LabelType
	 */
	private function addLabelType($slug, &$counter) {
		$slug = strtolower($slug);
		
		$labelType = $this->getContainer()->get('doctrine_mongodb')
			->getRepository('TheScienceTourEventBundle:LabelType')
			->findOneBySlug($slug);
		
		// Slug not found? Ok, let's create the new LabelType then
		if (!$labelType) {
			$labelType = new LabelType();
			$labelType->setName(ucfirst($slug));
			$labelType->setSlug($slug);
			
			$dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
			$dm->persist($labelType);
			$dm->flush();
			
			$counter++;
		}
		
		return $labelType;
	}
	
	/**
	 * Create or update a Label
	 * @param unknown $title
	 * @param unknown $labelType
	 * @param unknown $counter
	 * @return \TheScienceTour\EventBundle\Document\Label
	 */
	private function addLabel($title, $labelType, &$counter) {
		$title = ucwords(strtolower($title));
		
		$labels = $this->getContainer()->get('doctrine_mongodb')
			->getRepository('TheScienceTourEventBundle:Label')
			->findByTitle($title);
		$label = null;
		foreach ($labels as $lb) {
			if ($lb->getLabelType() && $lb->getLabelType() == $labelType) {
				$label = $lb;
				break;
			}
		}
		
		// Title not found? Ok, let's create the new Label then
		if (!$label) {
			$label = new Label();
			$label->setTitle($title);
			$label->setLabelType($labelType);
			
			$dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
			$dm->persist($label);
			$dm->flush();
			
			$counter++;
		}
		
		return $label;
	}
	
	private function addEvent($activity, $label, &$createdCounter, &$updatedCounter) {
		$event = $this->getContainer()->get('doctrine_mongodb')
			->getRepository('TheScienceTourEventBundle:Event')
			->findOneByBidullActivityId(intval($activity->id));
		
		if (!$event) {
			$event = new Event();
			$event->setBidullActivityId(intval($activity->id));
			$createdCounter++;
		} else {
			$updatedCounter++;
		}
		
		$event->setTitle(str_replace("_", " ", $activity->titre));
		$event->setDescription($activity->desc);
		$event->setStartDate(new \DateTime($activity->date_debut . 't000000'));
		$event->setEndDate(new \DateTime($activity->date_fin . 't235959'));
		
		$place = ($activity->adresse) ? $activity->adresse : '';
		if ($activity->cp || $activity->ville) {
			if ($place) $place .= ',';
			if ($activity->cp) $place .= ' ' . $activity->cp;
			if ($activity->ville) $place .= ' ' . $activity->ville;
		}
		$event->setPlace($place);
		
		
		$event->setLabel($label);
		$event->setFrontPage(false);
		
		$coord = explode(",", $activity->coord);
		$coordinates = new Coordinates();
		$coordinates->setLatitude($coord[0]);
		$coordinates->setLongitude($coord[1]);
		$event->setCoordinates($coordinates);
			
		$dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
		$dm->persist($event);
		$dm->flush();
		
		return $event;
	}
}
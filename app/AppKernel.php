<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),

	        new JMS\AopBundle\JMSAopBundle(),
	        new JMS\DiExtraBundle\JMSDiExtraBundle($this),
     	    new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
	        new JMS\SerializerBundle\JMSSerializerBundle(),
      	    new FOS\UserBundle\FOSUserBundle(),
      	    new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
	        new FOS\RestBundle\FOSRestBundle(),
      	    new ADesigns\CalendarBundle\ADesignsCalendarBundle(),
      	    new Knp\Bundle\MenuBundle\KnpMenuBundle(),
	        new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
//      	    new Sonata\jQueryBundle\SonatajQueryBundle(),
      	    new Sonata\DoctrineMongoDBAdminBundle\SonataDoctrineMongoDBAdminBundle(),
      	    new Sonata\AdminBundle\SonataAdminBundle(),
      	    new Sonata\MediaBundle\SonataMediaBundle(),
      	    new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
      	    new Sonata\IntlBundle\SonataIntlBundle(),
      	    new Ivory\GoogleMapBundle\IvoryGoogleMapBundle(),
      	    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
      	    new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
      	    new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
      	    new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),

	        new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),

            new TheScienceTour\CassiniLeafletBundle\TheScienceTourCassiniLeafletBundle(),
            new TheScienceTour\ChallengeBundle\TheScienceTourChallengeBundle(),
            new TheScienceTour\ContentPatternBundle\TheScienceTourContentPatternBundle(),
            new TheScienceTour\DocumentBundle\TheScienceTourDocumentBundle(),
            new TheScienceTour\EventBundle\TheScienceTourEventBundle(),
            new TheScienceTour\MainBundle\TheScienceTourMainBundle(),
            new TheScienceTour\MapBundle\TheScienceTourMapBundle(),
            new TheScienceTour\MediaBundle\TheScienceTourMediaBundle(),
            new TheScienceTour\MessageBundle\TheScienceTourMessageBundle(),
            new TheScienceTour\ProjectBundle\TheScienceTourProjectBundle(),
            new TheScienceTour\UserBundle\TheScienceTourUserBundle(),
            new TheScienceTour\PeerBundle\PeerBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}

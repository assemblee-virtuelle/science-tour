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
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
        	new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
        	new FOS\UserBundle\FOSUserBundle(),
        	new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        	new ADesigns\CalendarBundle\ADesignsCalendarBundle(),
        	new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        	new Sonata\BlockBundle\SonataBlockBundle(),
        	new Sonata\jQueryBundle\SonatajQueryBundle(),
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
        	new FOS\ElasticaBundle\FOSElasticaBundle(),
        	new TheScienceTour\MainBundle\TheScienceTourMainBundle(),
            new TheScienceTour\UserBundle\TheScienceTourUserBundle(),
        	new TheScienceTour\MediaBundle\TheScienceTourMediaBundle(),
            new TheScienceTour\ProjectBundle\TheScienceTourProjectBundle(),
            new TheScienceTour\EventBundle\TheScienceTourEventBundle(),
            new TheScienceTour\MapBundle\TheScienceTourMapBundle(),
            new TheScienceTour\MessageBundle\TheScienceTourMessageBundle(),
            new TheScienceTour\ChallengeBundle\TheScienceTourChallengeBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
       $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}

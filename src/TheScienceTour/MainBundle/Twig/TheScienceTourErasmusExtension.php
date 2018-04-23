<?php

namespace TheScienceTour\MainBundle\Twig;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class TheScienceTourErasmusExtension
 *
 * Extension Twig permettant de discriminer les données à afficher selon que l'on est surle site du Science Tour
 * ou sur le site YCFC
 * @todo Extension destinée à devenir obsolète avec la personnalisation de la plate-forme
 *
 * @package TheScienceTour\MainBundle\Twig
 */
class TheScienceTourErasmusExtension extends \Twig_Extension
{

    /**
     * La session PHP (par le biais du composant Session de Symfony)
     *
     * @var Session
     */
    private $session;

    /**
     * Constructeur de TheScienceTourErasmusExtension.
     *
     * La classe étant un service, la valeur du paramètre $session est donnée dans le fichier de configuration
     *
     * @param Session $session
     */
    function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Déclaration des nouvelles fonctions disponibles dans les squelettes Twig
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isErasmus', array($this, 'isErasmusFunction')),
        );
    }

    /**
     * Label de l'extension
     *
     * @return string
     */
    public function getName()
    {
        return 'erasmus';
    }

    /**
     * Interroge le tableau de session PHP pour déterminer quel est le site interrogé (Science Tour ou YCFC)
     *
     * @return mixed
     */
    public function isErasmusFunction()
    {
        return $this->session->get('isErasmus', false);
    }
}

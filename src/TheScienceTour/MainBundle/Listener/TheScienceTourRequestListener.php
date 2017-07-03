<?php
namespace TheScienceTour\MainBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
*
* @author glouton aka Charles Rozier <charles.rozier@web2com.fr> <charles@guide2com.fr>
*
*/
class TheScienceTourRequestListener {

    const ACCCEPT_LANGUAGE_PATTERN = "/([a-z]{2,3})(-[A-Z]{2,3})?(;q=(\d)(\.\d+)?)?/";

    private $router;
    private $erasmusDomains;

    public function __construct(Router $router, $erasmusDomains, $languages) {
        $this->router         = $router;
        $this->erasmusDomains = $erasmusDomains;
        $this->languages      = $languages;
    }

    /**
    * onKernelRequest Fonction de rappel liée à l'événement kernel.request
    *
    * @param  GetResponseEvent $event [description]
    * @return void
    */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        // Première exécution : isErasmus est null
        static $isErasmus = null;

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $event->getRequest();
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        // Une langue a-t-elle été choisie par l'utilisateur
        $chosenLanguage = $session->get('chosenLanguage');

        // Traitement spécfique de la page d'accueil
        if (!($event->getRequest()->getPathInfo() == '/')) {
            $session->set('chosenLanguage', $request->getLocale());
        } else {
            $setLanguage = $chosenLanguage;
            if (!in_array($setLanguage, $this->languages)) {
                // Search into given accepted languages.
                $acceptedLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                foreach ($acceptedLanguages as $lng) {
                    preg_match_all(self::ACCCEPT_LANGUAGE_PATTERN, $lng, $paramsLanguage);
                    // Langue : en, fr, etc.
                    $principale = $paramsLanguage[1][0];
                    // Variante régionale (optionnelle) : -FR, -CH, -CA, etc.
                    if ($paramsLanguage[3][0] !== '') {
                        $regionale = $paramsLanguage[3][0];
                    }
                    // Coefficient de priorité (optionnel) : 0.1, 0.9, etc.
                    if ($paramsLanguage[5][0] !== '') {
                        $poids = (float)($paramsLanguage[5][0].$lng[6][0]);
                    }

                    if (in_array($principale, $this->languages)) {
                        $setLanguage = $principale;
                    }
                }
            }
            if (!in_array($setLanguage, $this->languages)) {
                $setLanguage = array_keys($this->languages)[0];
            }
            // Build redirect response.
            $event->setResponse(
                new RedirectResponse(
                    $event->getRequest()->getBaseUrl().'/'.$setLanguage
                )
            );

            $session->set('chosenLanguage', $setLanguage);
            // No more work needed.
            return;
        }

        // We are on the Erasmus website.
        if ($isErasmus === null) {
            if (in_array($request->getHttpHost(), $this->erasmusDomains)) {
                // Save for further usage.
                $session->set('isErasmus', true);
                $isErasmus = true;
            }
            $isErasmus = false;
        }
    }
}

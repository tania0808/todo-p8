<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ErrorController extends AbstractController
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function showException(\Throwable $exception): Response
    {
        $statusCode = $exception->getStatusCode() ?? 500;

        $message = '';
        $details = '';

        switch ($statusCode) {
            case 403:
                $message = 'Accès refusé.';
                $details = 'Désolé, vous n\'avez pas la permission d\'accéder à cette page.';

                break;

            case 404:
                $message = 'Page non trouvée.';
                $details = 'Désolé, la page que vous recherchez est introuvable.';

                break;

            case 500:
                $message = 'Erreur interne du serveur.';
                $details = 'Désolé, une erreur s\'est produite.';

                break;

            default:
                $message = 'Une erreur s\'est produite.';
                $details = 'Désolé, une erreur s\'est produite. Veuillez patienter quelques minutes et réessayer.';
        }

        return new Response(
            $this->twig->render('bundles/TwigBundle/Exception/error.html.twig', ['exception' => $exception, 'status' => $statusCode, 'message' => $message, 'details' => $details]),
            $statusCode
        );
    }
}

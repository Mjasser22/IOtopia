<?php

namespace App\Controller;

use App\Form\AnimalHealthFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\TransportExceptionInterface;

class AnimalHealthController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    #[Route('/animal-health', name: 'animal_health')]
    public function index(Request $request): Response
    {
        // Configuration des choix
        $animalChoices = [
            'Dog' => 'Dog',
            'Cat' => 'Cat',
            'Cow' => 'Cow',
            'Sheep' => 'Sheep',
            'Horse' => 'Horse'
        ];

        $symptomChoices = [
            'Fever' => 'Fever',
            'Diarrhea' => 'Diarrhea',
            'Vomiting' => 'Vomiting',
            'Weight loss' => 'Weight loss',
            'Lethargy' => 'Lethargy',
            'Coughing' => 'Coughing',
            'Nasal Discharge' => 'Nasal discharge',
            'Loss of Appetite' => 'Loss of appetite',
            'Dehydration' => 'Dehydration'
        ];

        // Création du formulaire
        $form = $this->createForm(AnimalHealthFormType::class, null, [
            'animals' => $animalChoices,
            'symptoms' => $symptomChoices
        ]);

        $form->handleRequest($request);
        $prediction = null;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // 1. Préparation des données
                $formData = $form->getData();
                
                // 2. Appel à l'API Flask
                $response = $this->httpClient->request(
                    'POST', 
                    'http://localhost:5000/predict',
                    [
                        'headers' => ['Content-Type' => 'application/json'],
                        'json' => $this->formatRequestData($formData),
                        'timeout' => 10
                    ]
                );

                // 3. Traitement de la réponse
                if (200 === $response->getStatusCode()) {
                    $prediction = $this->processApiResponse($response->toArray());
                } else {
                    $error = 'API Error: HTTP ' . $response->getStatusCode();
                }

            } catch (TransportExceptionInterface $e) {
                $error = 'Network Error: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'Processing Error: ' . $e->getMessage();
            }
        }

        return $this->render('animal_health/index.html.twig', [
            'form' => $form->createView(),
            'prediction' => $prediction,
            'error' => $error
        ]);
    }

    private function formatRequestData(array $formData): array
    {
        return [
            'AnimalName' => $formData['animalType'],
            'symptoms1' => $formData['symptoms'][0] ?? 'None',
            'symptoms2' => $formData['symptoms'][1] ?? 'None',
            'symptoms3' => $formData['symptoms'][2] ?? 'None',
            'symptoms4' => $formData['symptoms'][3] ?? 'None',
            'symptoms5' => $formData['symptoms'][4] ?? 'None'
        ];
    }

    private function processApiResponse(array $apiResponse): array
    {
        return [
            'prediction' => $apiResponse['prediction'] ?? 'Unknown',
            'confidence' => ($apiResponse['confidence'] ?? 0) * 100,
            'features' => array_filter(
                $apiResponse['features_used'] ?? [],
                fn($f) => str_starts_with($f, 'symptoms')
            )
        ];
    }
}
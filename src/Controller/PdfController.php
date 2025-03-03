<?php  
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Animal;

class PdfController extends AbstractController
{
    
 #[ Route("/generate-pdf/{id}", name:"generate_pdf") ] 

    public function generatePdf(Animal $animal): Response
    {
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf
        $dompdf = new Dompdf($pdfOptions);

        // Render the pdf/animal_table.html.twig template with the animal data
        $html = $this->renderView('pdf/animal_table.html.twig', [
            'animal' => $animal,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to the browser
        $output = $dompdf->output();
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="animal_details.pdf"');

        return $response;
    }
}
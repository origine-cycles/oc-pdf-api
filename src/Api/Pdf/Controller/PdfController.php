<?php
namespace App\Api\Pdf\Controller;

use App\Api\Common\Request\PayloadValidation;
use App\Api\Pdf\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class PdfController extends AbstractController
{

    private PayloadValidation $payloadValidation;
    private LoggerInterface $logger;
    private PdfService $pdfService;

    public function __construct(
        PayloadValidation $payloadValidation,
        LoggerInterface $logger,
        PdfService $pdfService
    ){
        $this->payloadValidation = $payloadValidation;
        $this->logger = $logger;
        $this->pdfService = $pdfService;
    }

    public function generate(Request $request): JsonResponse
    {

        $this->logger->info('Route PdfController::generate called');

        // Parse payload
        $requestPayload = $this->payloadValidation->validate($request, 'PdfGenerateRequestPayload');

        if($requestPayload instanceof JsonResponse){
            return $requestPayload;
        }

        return $this->pdfService->generate($requestPayload);
        
    }

    public function saveDocument(Request $request): JsonResponse
    {
        
        $this->logger->info('Route PdfController::saveDocument called');

        // Parse payload
        $requestPayload = $this->payloadValidation->validate($request, 'SaveDocumentRequestPayload');

        if($requestPayload instanceof JsonResponse){
            return $requestPayload;
        }

        return $this->pdfService->saveDocument($requestPayload);

    }

    public function getDocument(Request $request): JsonResponse
    {
        
        $this->logger->info('Route PdfController::getDocument called');

        // Parse payload
        $requestPayload = $this->payloadValidation->validate($request, 'GetDocumentRequestPayload');

        if($requestPayload instanceof JsonResponse){
            return $requestPayload;
        }

        return $this->pdfService->getDocument($requestPayload);

    }

}

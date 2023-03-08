<?php
namespace App\Api\Pdf\Service;

use App\Api\Common\Request\Schema\GetDocumentRequestPayload;
use App\Api\Common\Request\Schema\PdfGenerateRequestPayload;
use App\Api\Common\Request\Schema\SaveDocumentRequestPayload;
use App\Entity\I18n;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;

class PdfService extends AbstractController
{

    public Dompdf $dompdf;
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {

        $this->doctrine = $doctrine;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->setDefaultFont('Open Sans');
        $this->dompdf = new Dompdf($options);
        
    }

    public function generate(PdfGenerateRequestPayload $requestPayload): JsonResponse
    {

        $variableInContent = [];

        foreach($requestPayload->templateContent as $key => $content){
            $variableInContent[$key] = $content;
        }

        $i18nRepository = $this->doctrine->getRepository(I18n::class);
        $lang = ($requestPayload->language == 'fr' ? 1 : 2);
        $variableInContent['txt'] = $i18nRepository->findContenuLang($lang, 'pdf');

        $this->dompdf->loadHtml($this->renderView($requestPayload->templateName . '.html.twig', $variableInContent));

        // (Optional) Setup the paper size and orientation
        $this->dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $this->dompdf->render();

        // Base64 encode document as a string, pretty usefull to send it by json
        $outputPdf = base64_encode($this->dompdf->output());

        $saved = null;
        if(!empty($requestPayload->savePath)){
            $saveRequest = new SaveDocumentRequestPayload();
            $saveRequest->base64Document = $outputPdf;
            $saveRequest->path = $requestPayload->savePath;
            $saveRequest->filename = (!empty($variableInContent['document_name']) ? $variableInContent['document_name'] : $variableInContent['document_title']);
            $saved = $this->saveDocument($saveRequest);
        }

		$data = array('success' => true, 'data' => array('pdf' => $outputPdf, 'saved' => $saved), 'status' => Response::HTTP_OK);
		return new JsonResponse($data, Response::HTTP_OK);

    }

    public function saveDocument(SaveDocumentRequestPayload $requestPayload){

        // Add slash
        if(substr($requestPayload->path, -1, 1) != '/'){
			$requestPayload->path .= '/';
		}

		if(empty($requestPayload->overwrite) && file_exists($requestPayload->path.$requestPayload->filename)){
			return null;
		}

		return file_put_contents($requestPayload->path.$requestPayload->filename, base64_decode($requestPayload->base64Document));

    }

    public function getDocument(GetDocumentRequestPayload $requestPayload){

        $cleSSL = '581ug2y3c5yd155r2eevvaysy5ktvbcqa3cgv29ywx2otx6l2b7obw71shlejnvjhyp3qosddksw9o91q7fsucjdkuit953gtjaayse24wp3cpusm1b84lbe6ky5fscy';
	    $ivSSL = 'o6jx8sk2pt8d155u';
	    $methodSSL = 'aes-256-cbc';

        $requestPayload->path = openssl_decrypt($requestPayload->path, $methodSSL, $cleSSL, 0, $ivSSL);
        $requestPayload->filename = openssl_decrypt($requestPayload->filename, $methodSSL, $cleSSL, 0, $ivSSL);

        // Add slash
        if(substr($requestPayload->path, -1, 1) != '/'){
			$requestPayload->path .= '/';
		}

        $document = null;
        $status = Response::HTTP_NOT_FOUND;

        if(file_exists($requestPayload->path.$requestPayload->filename)){
            $document = base64_encode(file_get_contents($requestPayload->path.$requestPayload->filename));
            $status = Response::HTTP_OK;
        }

        $data = array('success' => true, 'data' => array('pdf' => $document), 'status' => $status);
		return new JsonResponse($data, $status);
        
    }

}
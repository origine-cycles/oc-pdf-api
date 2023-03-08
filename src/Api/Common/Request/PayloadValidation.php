<?php
// src/Api/Common/Request/PayloadValidator.php
# Automatic validator of payloads for controllers, based on ./payloads files

namespace App\Api\Common\Request;

use App\Api\Common\Util\Utils;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayloadValidation {

    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private $schemaFolder = 'App\Api\Common\Request\Schema\\';
    private $resourceFolder = 'src/Api/Common/Request/Resources';

    public function __construct(
        SerializerInterface $serializer, 
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function validate(Request $request, string $classname)
    {

        // If request type is $_POST, params are in a json in getContent()
        // Else it's just a string, and we get all the params with query->all()  
        $requestContent = Utils::isJson($request->getContent()) ? $request->getContent() : json_encode($request->query->all());

        try {
            
            $class = new ReflectionClass($this->schemaFolder . $classname);

            $requestPayload = $this->serializer->deserialize(
                $requestContent,
                $class->getName(),
                'json'
            );

            $errors = $this->validator->validate($requestPayload);

            if ($errors->count() > 0) {
                return new JsonResponse(
                    $errors,
                    Response::HTTP_BAD_REQUEST,
                    [],
                    true
                );
            }

            if(!isset($requestPayload->templateName)){
                return $requestPayload;
            }

            $finder = new Finder();
            $template = $requestPayload->templateName . '.html.twig';
            $finder->in('../templates')->name($template);
            
            if(!$finder->hasResults()){
                $requestPayload->templateName = 'basic-template';
            }
            
                            
            // Second validation for specific templates if exist, and if no previous errors
            $finder = new Finder();
            $resourceValidation = $requestPayload->resourceName . '.json';
            $finder->in(__DIR__ . '/Resources')->name($resourceValidation);
            
            if(!$finder->hasResults()){
                $error = 'Error, tried to generate PDF with unknown template : '. $requestPayload->resourceName;
                $this->logger->error($error);
                $data = array('success' => false, 'data' => $error, 'status' => Response::HTTP_NOT_FOUND);
        
                return new JsonResponse($data, $data['status']);
            }

            $templateContent = $this->validateWithJsonTemplate($finder, $requestPayload);

            if(is_array($templateContent) && isset($templateContent[0]) && $templateContent[0] === false){
                if(is_array($templateContent[1])){
                    $templateContent[1] = json_encode($templateContent[1]);
                }
                // $templateContent[1] Contains undefined/empty required value
                $data = array('success' => false, 'data' => 'Content missing : ' . $templateContent[1], 'status' => Response::HTTP_EXPECTATION_FAILED);
        
                return new JsonResponse($data, $data['status']);
            }

            $requestPayload->templateContent = $templateContent;
            unset($requestPayload->content);

            return $requestPayload;

        } catch (MissingConstructorArgumentsException $e) {
            return new JsonResponse(
                $e,
                Response::HTTP_BAD_REQUEST
            );
        }
        
    }


    private function validateWithJsonTemplate(Finder $templateValidationFiles, $requestPayload) 
    {

        $templateContent = array();
        foreach ($templateValidationFiles as $file) { // Should only have 1 file
            $jsonValidation = $file->getContents();
            $validationTemplate = json_decode($jsonValidation); // Get json file content

            if($validationTemplate->resourceName !== $requestPayload->resourceName){
                return array(false, "Resource does not match");
            }

            // Check the content required in Json against the request sended
            $checkedContent = $this->checkContentInJson($validationTemplate->content, $requestPayload->content);

            $templateContent = array_merge($checkedContent, $templateContent);
        }

        return $templateContent;

    }

    private function checkContentInJson($validationTemplateContent, $requestContent)
    {
        
        $contentValidated = array();
        
        foreach($validationTemplateContent as $templateKeyValidation => $templateValueValidation){
            // Sub-array case
            if(!empty($templateKeyValidation) && !is_numeric($templateKeyValidation)){
                if(!empty($requestContent[$templateKeyValidation])){
                    // Recursive
                    $contentValidated[$templateKeyValidation] = $this->checkContentInJson($templateValueValidation, $requestContent[$templateKeyValidation]);
                    if(isset($contentValidated[$templateKeyValidation][0]) && $contentValidated[$templateKeyValidation][0] === false){
                        return $contentValidated[$templateKeyValidation];
                    }
                } else {
                    // Sub array looks missing in requestContent
                    return [false, $templateKeyValidation];
                }
            } else {

                // Here we need to check full request content against 1 templateValidation key
                if(isset($requestContent[0])){
                    foreach($requestContent as $requestKey => $contentKeyValue){
                        $check = $this->checkContentRequired($templateValueValidation, $contentKeyValue);
                        if($check === false){
                            return [false, $templateValueValidation[1]];
                        } else {
                            $contentValidated[$requestKey][key($check)] = current($check);
                        }
                    }
                } else {
                    $check = $this->checkContentRequired($templateValueValidation, $requestContent);
                    if($check === false){
                        return [false, $templateValueValidation];
                    } else {
                        $contentValidated[key($check)] = current($check);
                    }
                }
            }

        }
        return $contentValidated;

    }

    private function checkContentRequired($contentRequired, $paramsKeysValues)
    {

        $contentRetrieved = array();

        if(is_object($contentRequired[1])){
            /*  Case :
                ["firstname", {"path": "prenom", "optional": true],
            */
            
            // So we need a path info at least
            if(empty($contentRequired[1]->path)){
                return false;
            }

            $found = false;
            foreach($paramsKeysValues as $paramKey => $paramValue){

                if($contentRequired[1]->path !== $paramKey){
                    continue;
                }

                if(
                    !isset($paramValue)
                    && (empty($contentRequired[1]->optional) || $contentRequired[1]->optional != true) // If optional not defined, it's considered mandatory
                ){
                    return false;
                }

                $found = true;
                $contentRetrieved[$contentRequired[0]] = $paramValue;
            }

            // If not found but parameter is optional, we create it blank to avoid falsy return
            if(!$found && $contentRequired[1]->optional){
                $contentRetrieved[$contentRequired[0]] = null;
            }
  
        } else {
            /*  Case :
                ["firstname", "prenom"],
            */
            foreach($paramsKeysValues as $paramKey => $paramValue){

                if($contentRequired[1] !== $paramKey){
                    continue;
                }

                if(!isset($paramValue)){
                    return false;
                }

                $contentRetrieved[$contentRequired[0]] = $paramValue;
            }
        }

        if(empty($contentRetrieved)){
            return false;
        }

        return $contentRetrieved;
    }

}
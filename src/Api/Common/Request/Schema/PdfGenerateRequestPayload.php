<?php 
// src/Api/Common/Request/Schema/PdfGenerateRequestPayload.php
namespace App\Api\Common\Request\Schema;

use Symfony\Component\Validator\Constraints as Assert;

class PdfGenerateRequestPayload
{

    const LANGUAGE_CODES = ['fr', 'en', 'nl'];

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Choice(choices=PdfGenerateRequestPayload::LANGUAGE_CODES)
     */
    public string $language;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
    */
    public string $resourceName;

    public string $templateName;

    public string $savePath;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
    */
    public $content = [];


    public $templateContent;


}
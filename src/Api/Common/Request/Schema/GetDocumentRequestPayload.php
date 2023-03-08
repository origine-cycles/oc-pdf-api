<?php 
// src/Api/Common/Request/Schema/GetDocumentRequestPayload.php
namespace App\Api\Common\Request\Schema;

use Symfony\Component\Validator\Constraints as Assert;

class GetDocumentRequestPayload
{
    
    /**
     * @Assert\NotNull
     * @Assert\NotBlank
    */
    public string $path;
    
    /**
     * @Assert\NotNull
     * @Assert\NotBlank
    */
    public string $filename;


}
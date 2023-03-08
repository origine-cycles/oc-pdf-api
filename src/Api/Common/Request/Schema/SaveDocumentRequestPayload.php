<?php 
// src/Api/Common/Request/Schema/SaveDocumentRequestPayload.php
namespace App\Api\Common\Request\Schema;

use Symfony\Component\Validator\Constraints as Assert;

class SaveDocumentRequestPayload
{

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
    */
    public string $base64Document;
    
    
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

    public int $overwrite;


}
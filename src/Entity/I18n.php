<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * I18n
 *
 * @ORM\Table(name="i18n", indexes={@ORM\Index(name="id_lang", columns={"id_lang"})})
 * @ORM\Entity(repositoryClass="App\Repository\I18nRepository")
 */
class I18n
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="page", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $page;

    /**
     * @var string|null
     *
     * @ORM\Column(name="valeur", type="text", length=65535, nullable=true)
     */
    private $valeur;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="valeurDateAFaire", type="datetime", nullable=true)
     */
    private $valeurdateafaire;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="valeurDateFait", type="datetime", nullable=true)
     */
    private $valeurdatefait;

    /**
     * @var \Langue
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Langue")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_lang", referencedColumnName="id")
     * })
     */
    private $idLang;


    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage($name)
    {
        $this->page = $name;
    }


    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(?string $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getValeurdateafaire(): ?\DateTimeInterface
    {
        return $this->valeurdateafaire;
    }

    public function setValeurdateafaire(?\DateTimeInterface $valeurdateafaire): self
    {
        $this->valeurdateafaire = $valeurdateafaire;

        return $this;
    }

    public function getValeurdatefait(): ?\DateTimeInterface
    {
        return $this->valeurdatefait;
    }

    public function setValeurdatefait(?\DateTimeInterface $valeurdatefait): self
    {
        $this->valeurdatefait = $valeurdatefait;

        return $this;
    }

    public function getIdLang(): ?Langue
    {
        return $this->idLang;
    }

    public function setIdLang(?Langue $idLang): self
    {
        $this->idLang = $idLang;

        return $this;
    }


}

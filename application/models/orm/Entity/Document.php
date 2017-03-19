<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 *
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass="Entity\DocumentRepository")
 */
class Document
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="lpath", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $lpath;

    /**
     * @var integer
     *
     * @ORM\Column(name="storage", type="smallint", precision=2, scale=0, nullable=false, unique=false)
     */
    private $storage = 1;

    /**
     * @var \Entity\Request
     *
     * @ORM\ManyToOne(targetEntity="Entity\Request", inversedBy="documents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="request_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $belongingRequest;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Document
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Document
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lpath
     *
     * @param string $lpath
     * @return Document
     */
    public function setLpath($lpath)
    {
        $this->lpath = $lpath;

        return $this;
    }

    /**
     * Get lpath
     *
     * @return string
     */
    public function getLpath()
    {
        return $this->lpath;
    }

    /**
     * Set storage
     *
     * @param integer $storage
     * @return Document
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage
     *
     * @return integer
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set belongingRequest
     *
     * @param \Entity\Request $belongingRequest
     * @return Document
     */
    public function setBelongingRequest(\Entity\Request $belongingRequest)
    {
        $this->belongingRequest = $belongingRequest;

        return $this;
    }

    /**
     * Get belongingRequest
     *
     * @return \Entity\Request
     */
    public function getBelongingRequest()
    {
        return $this->belongingRequest;
    }
}

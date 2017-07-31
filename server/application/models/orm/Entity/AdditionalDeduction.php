<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdditionalDeduction
 *
 * @ORM\Table(name="additional_deduction")
 * @ORM\Entity(repositoryClass="Entity\AdditionalDeductionRepository")
 */
class AdditionalDeduction
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
     * @var integer
     *
     * @ORM\Column(name="concept", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $concept;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=0, scale=0, nullable=false, unique=false)
     */
    private $amount;

    /**
     * @var \Entity\Request
     *
     * @ORM\ManyToOne(targetEntity="Entity\Request", inversedBy="additionalDeductions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="request_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $request;


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
     * Set concept
     *
     * @param integer $concept
     * @return AdditionalDeduction
     */
    public function setConcept($concept)
    {
        $this->concept = $concept;

        return $this;
    }

    /**
     * Get concept
     *
     * @return integer
     */
    public function getConcept()
    {
        return $this->concept;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return AdditionalDeduction
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
     * Set amount
     *
     * @param float $amount
     * @return AdditionalDeduction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set request
     *
     * @param \Entity\Request $request
     * @return AdditionalDeduction
     */
    public function setRequest(\Entity\Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return \Entity\Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}

<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Request
 *
 * @ORM\Table(name="request")
 * @ORM\Entity(repositoryClass="Entity\RequestRepository")
 */
class Request
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
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", precision=0, scale=0, nullable=false, unique=false)
     */
    private $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $comment;

    /**
     * @var integer
     *
     * @ORM\Column(name="reunion", type="integer", precision=0, scale=0, nullable=true, unique=false)
     */
    private $reunion;

    /**
     * @var integer
     *
     * @ORM\Column(name="requested_amount", type="float", precision=0, scale=0, nullable=false, unique=false)
     */
    private $requestedAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="approved_amount", type="float", precision=0, scale=0, nullable=true, unique=false)
     */
    private $approvedAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", precision=2, scale=0, nullable=false, unique=false)
     */
    private $status;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\Document", mappedBy="belongingRequest")
     */
    private $documents;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\History", mappedBy="origin")
     */
    private $history;

    /**
     * @var \Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Entity\User", inversedBy="requests")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $userOwner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->history = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Request
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Request
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
    /**
     * Set reunion
     *
     * @param integer $reunion
     * @return Request
     */
    public function setReunion($reunion)
    {
        $this->reunion = $reunion;

        return $this;
    }

    /**
     * Get reunion
     *
     * @return integer
     */
    public function getReunion()
    {
        return $this->reunion;
    }

    /**
     * Set requestedAmount
     *
     * @param integer $requestedAmount
     * @return Request
     */
    public function setRequestedAmount($requestedAmount)
    {
        $this->requestedAmount = $requestedAmount;

        return $this;
    }

    /**
     * Get requestedAmount
     *
     * @return integer
     */
    public function getRequestedAmount()
    {
        return $this->requestedAmount;
    }

    /**
     * Set approvedAmount
     *
     * @param integer $approvedAmount
     * @return Request
     */
    public function setApprovedAmount($approvedAmount)
    {
        $this->approvedAmount = $approvedAmount;

        return $this;
    }

    /**
     * Get approvedAmount
     *
     * @return integer
     */
    public function getApprovedAmount()
    {
        return $this->approvedAmount;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Request
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
    * Get status converted into text
    * @return string
    */
    public function getStatusByText()
    {
        $status = $this->status;
        return ($status == 1 ? "Recibida" : ($status == 2 ? "Aprobada" : "Rechazada"));
    }
    /**
    * Set status converted into text
    * @param string $status
    * @return Request
    */
    public function setStatusByText($status)
    {
        $this->status = ($status == "Recibida" ? 1 : ($status == "Aprobada" ? 2 : 3));
        return $this;
    }

    /**
     * Add documents
     *
     * @param \Entity\Document $documents
     * @return Request
     */
    public function addDocument(\Entity\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \Entity\Document $documents
     */
    public function removeDocument(\Entity\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add history
     *
     * @param \Entity\History $history
     * @return Request
     */
    public function addHistory(\Entity\History $history)
    {
        $this->history[] = $history;

        return $this;
    }

    /**
     * Remove history
     *
     * @param \Entity\History $history
     */
    public function removeHistory(\Entity\History $history)
    {
        $this->history->removeElement($history);
    }

    /**
     * Get history
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Set userOwner
     *
     * @param \Entity\User $userOwner
     * @return Request
     */
    public function setUserOwner(\Entity\User $userOwner)
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    /**
     * Get userOwner
     *
     * @return \Entity\User
     */
    public function getUserOwner()
    {
        return $this->userOwner;
    }
}

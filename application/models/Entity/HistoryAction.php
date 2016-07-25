<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoryAction
 *
 * @ORM\Table(name="history_action")
 * @ORM\Entity(repositoryClass="Entity\HistoryActionRepository")
 */
class HistoryAction
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
     * @ORM\Column(name="sumary", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $summary;
    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $detail;

    /**
     * @var \Entity\History
     *
     * @ORM\ManyToOne(targetEntity="Entity\History", inversedBy="actions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="history_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $belongingHistory;


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
     * Set summary
     *
     * @param string $summary
     * @return HistoryAction
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set detail
     *
     * @param string $detail
     * @return HistoryAction
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    
        return $this;
    }

    /**
     * Get detail
     *
     * @return string 
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set belongingHistory
     *
     * @param \Entity\History $belongingHistory
     * @return HistoryAction
     */
    public function setBelongingHistory(\Entity\History $belongingHistory)
    {
        $this->belongingHistory = $belongingHistory;
    
        return $this;
    }

    /**
     * Get belongingHistory
     *
     * @return \Entity\History 
     */
    public function getBelongingHistory()
    {
        return $this->belongingHistory;
    }
}
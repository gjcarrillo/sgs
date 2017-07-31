<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History
 *
 * @ORM\Table(name="history")
 * @ORM\Entity(repositoryClass="Entity\HistoryRepository")
 */
class History
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
     * @ORM\Column(name="date", type="datetime", precision=0, scale=0, nullable=false, unique=false)
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="title", type="smallint", precision=2, scale=0, nullable=false, unique=false)
     */
    private $title;

    /**
     * @var \Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Entity\User", inversedBy="history")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_responsible", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $userResponsible;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\HistoryAction", mappedBy="belongingHistory")
     */
    private $actions;

    /**
     * @var \Entity\Request
     *
     * @ORM\ManyToOne(targetEntity="Entity\Request", inversedBy="history")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="request_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $origin;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date
     *
     * @param \DateTime $date
     * @return History
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set title
     *
     * @param integer $title
     * @return History
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return integer
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set userResponsible
     *
     * @param \Entity\User $userResponsible
     * @return History
     */
    public function setUserResponsible(\Entity\User $userResponsible)
    {
        $this->userResponsible = $userResponsible;

        return $this;
    }

    /**
     * Get userResponsible
     *
     * @return \Entity\User
     */
    public function getUserResponsible()
    {
        return $this->userResponsible;
    }

    /**
     * Add actions
     *
     * @param \Entity\HistoryAction $actions
     * @return History
     */
    public function addAction(\Entity\HistoryAction $actions)
    {
        $this->actions[] = $actions;

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \Entity\HistoryAction $actions
     */
    public function removeAction(\Entity\HistoryAction $actions)
    {
        $this->actions->removeElement($actions);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Set origin
     *
     * @param \Entity\Request $origin
     * @return History
     */
    public function setOrigin(\Entity\Request $origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return \Entity\Request
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}

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
     * @var string
     *
     * @ORM\Column(name="user_responsable", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $userResponsable;

    /**
     * @var integer
     *
     * @ORM\Column(name="title", type="smallint", precision=2, scale=0, nullable=false, unique=false)
     */
    private $title;


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
     * Set userResponsable
     *
     * @param string $userResponsable
     * @return History
     */
    public function setUserResponsable($userResponsable)
    {
        $this->userResponsable = $userResponsable;

        return $this;
    }

    /**
     * Get userResponsable
     *
     * @return string
     */
    public function getUserResponsable()
    {
        return $this->userResponsable;
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

    /**
     * Set title
     *
     * @param string $title
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Get title converted into text
    * @return string
    */
    public function getTitleByText()
    {
        $title = $this->title;
        return ($title == 1 ? "Creación" : ($title == 2 ? "Adición" : ($title == 3 ? "Modificación" : ($title == 4 ? "Cierre" : ($title == 5 ? "Eliminación" : "Reporte")))));
    }

    /**
    * Set title converted into text
    * @param string $title
    * @return Request
    */
    public function setTitleByText($title)
    {
        $this->title = ($title == "Creación" ? 1 : ($title == "Modificación" ? 2 : ($title == "Adición" ? 3 : ($title == "Cierre" ? 4 : ($title == "Eliminación" ? 5 : 6)))));
        return $this;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 29/07/2017
 * Time: 16:30
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
 * @ORM\Table(name="message")
 */
class Message
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_form_id")
     */
    private $from;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_to_id")
     */
    private $to;

    /**
     * @var string
     * @ORM\Column(type="text", length=8192, nullable=true)
     */
    private $message;

    /**
     * @var string
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    /**
     * @var boolean
     * @ORM\Column(name="is_read", type="boolean")
     */
    private $read;

    /**
     * Message constructor.
     * @param User $form
     * @param User $to
     * @param string $message
     * @param string $createdAt
     */
    public function __construct(User $from, User $to, $message)
    {
        $this->from = $from;
        $this->to = $to;
        $this->message = $message;
        $this->createdAt = new \DateTime();
        $this->read = false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param User $from
     * @return Message
     */
    public function setFrom(User $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return User
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param User $to
     * @return Message
     */
    public function setTo(User $to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return str_replace("\n", "<br>", preg_replace("#(spotify\:[a-zA-Z0-9\:]+)#", '
        <iframe src="https://open.spotify.com/embed?uri=$1" width="250" height="80" frameborder="0" allowtransparency="true"></iframe>
        ', strip_tags($this->message)));
    }

    /**
     * @param mixed $message
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     * @return Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}
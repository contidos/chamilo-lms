<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Entity\LtiProvider;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class License.
 *
 * @package Chamilo\PluginBundle\Entity\LtiProvider
 *
 * @ORM\Table(name="plugin_lti_provider_licenses")
 * @ORM\Entity()
 */
class License
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="client_id", type="string")
     */
    private $clientId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expedition_date", type="datetime")
     */
    private $expeditionDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="expired", type="boolean", options={"default": false})
     */
    private $expired = false;

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set id.
     */
    public function setId(int $id): License
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get user ID.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set user ID.
     */
    public function setUserId(int $userId): License
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get client ID.
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Set client ID.
     */
    public function setClientId(string $clientId): License
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get expedition date.
     */
    public function getExpeditionDate(): \DateTime
    {
        return $this->expeditionDate;
    }

    /**
     * Set expedition date.
     */
    public function setExpeditionDate(\DateTime $expeditionDate): License
    {
        $this->expeditionDate = $expeditionDate;

        return $this;
    }

    /**
     * Get expired status.
     */
    public function isExpired(): bool
    {
        return $this->expired;
    }

    /**
     * Set expired status.
     */
    public function setExpired(bool $expired): License
    {
        $this->expired = $expired;

        return $this;
    }
} 
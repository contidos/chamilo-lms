<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Entity\LtiProvider;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Platform.
 *
 * @package Chamilo\PluginBundle\Entity\LtiProvider
 *
 * @ORM\Table(name="plugin_lti_provider_platform")
 * @ORM\Entity()
 */
class Platform
{
    /**
     * @var string
     *
     * @ORM\Column(name="issuer", type="text")
     */
    public $issuer;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text")
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="kid", type="string")
     */
    private $kid;
    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="text")
     */
    private $clientId;
    /**
     * @var string
     *
     * @ORM\Column(name="auth_login_url", type="text")
     */
    private $authLoginUrl;
    /**
     * @var string
     *
     * @ORM\Column(name="auth_token_url", type="text")
     */
    private $authTokenUrl;
    /**
     * @var string
     *
     * @ORM\Column(name="key_set_url", type="text")
     */
    private $keySetUrl;
    /**
     * @var string
     *
     * @ORM\Column(name="deployment_id", type="text")
     */
    private $deploymentId;
    /**
     * @var string
     *
     * @ORM\Column(name="tool_provider", type="text")
     */
    private $toolProvider;
    /**
     * @var string
     *
     * @ORM\Column(name="total_licenses", type="integer")
     */
    private $totalLicenses;
    /**
     * @var string
     *
     * @ORM\Column(name="available_licenses", type="integer")
     */
    private $availableLicenses;

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
    public function setId(int $id): Platform
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Get name.
     */
    public function getName(): string
    {
        if (empty($this->name) || null === $this->name) {
            return "";
        }
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getToolProvider()
    {
        return $this->toolProvider;
    }

    /**
     * @param string $toolProvider
     */
    public function setToolProvider(?string $toolProvider): void
    {
        $this->toolProvider = $toolProvider;
    }

    /**
     * Get key id.
     */
    public function getKid()
    {
        return $this->kid;
    }

    /**
     * Set key id.
     */
    public function setKid(string $kid): Platform
    {
        $this->kid = $kid;

        return $this;
    }

    /**
     * Get Issuer.
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Set issuer.
     */
    public function setIssuer(string $issuer): Platform
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * Get client ID.
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set client ID.
     */
    public function setClientId(string $clientId): Platform
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get auth login URL.
     */
    public function getAuthLoginUrl()
    {
        return $this->authLoginUrl;
    }

    /**
     * Set auth login URL.
     */
    public function setAuthLoginUrl(string $authLoginUrl): Platform
    {
        $this->authLoginUrl = $authLoginUrl;

        return $this;
    }

    /**
     * Get auth token URL.
     */
    public function getAuthTokenUrl()
    {
        return $this->authTokenUrl;
    }

    /**
     * Set auth token URL.
     */
    public function setAuthTokenUrl(string $authTokenUrl): Platform
    {
        $this->authTokenUrl = $authTokenUrl;

        return $this;
    }

    /**
     * Get key set URL.
     */
    public function getKeySetUrl()
    {
        return $this->keySetUrl;
    }

    /**
     * Set key set URL.
     */
    public function setKeySetUrl(string $keySetUrl): Platform
    {
        $this->keySetUrl = $keySetUrl;

        return $this;
    }

    /**
     * Get Deployment ID.
     */
    public function getDeploymentId()
    {
        return $this->deploymentId;
    }

    /**
     * Set Deployment ID.
     */
    public function setDeploymentId(string $deploymentId): Platform
    {
        $this->deploymentId = $deploymentId;

        return $this;
    }

    /**
     * Get total licienses.
     */
    public function getTotalLicenses()
    {
        if (empty($this->totalLicenses) || null === $this->totalLicenses) {
            return 0;
        }        
        return $this->totalLicenses;
    }

    /**
     * Set total licenses.
     */
    public function setTotalLicenses(int $totalLicenses): Platform
    {
        $this->totalLicenses = $totalLicenses;

        return $this;
    }

    /**
     * Get available licenses.
     */
    public function getAvailableLicenses()
    {
        if (empty($this->totalLicenses) || null === $this->totalLicenses || $this->totalLicenses === 0) {
            return 9999;
        }   
        return $this->availableLicenses;
    }

    /**
     * Set available licenses.
     */
    public function setAvailableLicenses(int $availableLicenses): Platform
    {
        $this->availableLicenses = $availableLicenses;

        return $this;
    }
}

<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;

/**
 * @link http://apidocs.mailchimp.com/api/2.0/lists/member-info.php
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mailchimp_member"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-user"
 *      }
 *  }
 * )
 */
class Member implements OriginAwareInterface, FirstNameInterface, LastNameInterface
{
    /**#@+
     * @const string Status of member
     */
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_CLEANED = 'cleaned';
    /**#@-*/

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="origin_id", type="string", length=32, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $originId;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $channel;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * The subscription status for this email address, either pending, subscribed, unsubscribed, or cleaned
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    protected $company;

    /**
     * The rating of the subscriber. This will be 1 - 5
     *
     * @var integer
     *
     * @ORM\Column(name="member_rating", type="smallint", nullable=true)
     */
    protected $memberRating;

    /**
     * The date+time the opt-in completed.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="optedin_at", type="datetime", nullable=true)
     */
    protected $optedInAt;

    /**
     * IP Address this address opted in from.
     *
     * @var string
     *
     * @ORM\Column(name="optedin_ip", type="string", length=20, nullable=true)
     */
    protected $optedInIpAddress;

    /**
     * The date+time the confirm completed.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="confirmed_at", type="datetime", nullable=true)
     */
    protected $confirmedAt;

    /**
     * IP Address this address confirmed from.
     *
     * @var string
     *
     * @ORM\Column(name="confirmed_ip", type="string", length=16, nullable=true)
     */
    protected $confirmedIpAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=64, nullable=true)
     */
    protected $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=64, nullable=true)
     */
    protected $longitude;

    /**
     * GMT offset
     *
     * @var string
     *
     * @ORM\Column(name="gmt_offset", type="string", length=16, nullable=true)
     */
    protected $gmtOffset;

    /**
     * GMT offset during daylight savings (if DST not observered, will be same as gmtoff)
     *
     * @var string
     *
     * @ORM\Column(name="dst_offset", type="string", length=16, nullable=true)
     */
    protected $dstOffset;

    /**
     * The timezone we've place them in
     *
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=40, nullable=true)
     */
    protected $timezone;

    /**
     * 2 digit ISO-3166 country code
     *
     * @var string
     *
     * @ORM\Column(name="cc", type="string", length=2, nullable=true)
     */
    protected $cc;

    /**
     * Generally state, province, or similar
     *
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    protected $region;

    /**
     * The last time this record was changed. If the record is old enough, this may be blank.
     *
     * @var \DateTime
     *
     * @ORM\Column(name="last_changed_at", type="datetime", nullable=true)
     */
    protected $lastChangedAt;

    /**
     * The Member id used in our web app, allows you to create a link directly to it
     *
     * @var integer
     *
     * @ORM\Column(name="leid", type="integer", nullable=true)
     */
    protected $leid;

    /**
     * The unique id for an email address (not list related) - the email "id" returned from listMemberInfo,
     * Webhooks, Campaigns, etc.
     *
     * @var string
     *
     * @ORM\Column(name="euid", type="string", length=255, nullable=true)
     */
    protected $euid;

    /**
     * Local created date time
     *
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * Local updated date time
     *
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param string $originId
     * @return Member
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return Member
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param string $cc
     * @return Member
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return Member
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @param \DateTime $confirmedAt
     * @return Member
     */
    public function setConfirmedAt(\DateTime $confirmedAt = null)
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmedIpAddress()
    {
        return $this->confirmedIpAddress;
    }

    /**
     * @param string $confirmedIpAddress
     * @return Member
     */
    public function setConfirmedIpAddress($confirmedIpAddress)
    {
        $this->confirmedIpAddress = $confirmedIpAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getDstOffset()
    {
        return $this->dstOffset;
    }

    /**
     * @param string $dstOffset
     * @return Member
     */
    public function setDstOffset($dstOffset)
    {
        $this->dstOffset = $dstOffset;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Member
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEuid()
    {
        return $this->euid;
    }

    /**
     * @param string $euid
     * @return Member
     */
    public function setEuid($euid)
    {
        $this->euid = $euid;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Member
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getGmtOffset()
    {
        return $this->gmtOffset;
    }

    /**
     * @param string $gmtOffset
     * @return Member
     */
    public function setGmtOffset($gmtOffset)
    {
        $this->gmtOffset = $gmtOffset;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastChangedAt()
    {
        return $this->lastChangedAt;
    }

    /**
     * @param \DateTime $lastChangedAt
     * @return Member
     */
    public function setLastChangedAt(\DateTime $lastChangedAt = null)
    {
        $this->lastChangedAt = $lastChangedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Member
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return Member
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeid()
    {
        return $this->leid;
    }

    /**
     * @param int $leid
     * @return Member
     */
    public function setLeid($leid)
    {
        $this->leid = $leid;

        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return Member
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return int
     */
    public function getMemberRating()
    {
        return $this->memberRating;
    }

    /**
     * @param int $memberRating
     * @return Member
     */
    public function setMemberRating($memberRating)
    {
        $this->memberRating = $memberRating;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOptedInAt()
    {
        return $this->optedInAt;
    }

    /**
     * @param \DateTime $optedInAt
     * @return Member
     */
    public function setOptedInAt(\DateTime $optedInAt = null)
    {
        $this->optedInAt = $optedInAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptedInIpAddress()
    {
        return $this->optedInIpAddress;
    }

    /**
     * @param string $optedInIpAddress
     * @return Member
     */
    public function setOptedInIpAddress($optedInIpAddress)
    {
        $this->optedInIpAddress = $optedInIpAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return Member
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Member
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return Member
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Member
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return Member
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        if (!$this->updatedAt) {
            $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}

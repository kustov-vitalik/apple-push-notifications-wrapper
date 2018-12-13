<?php
/**
 * Created by IntelliJ IDEA.
 * User: vitaly
 * Date: 13/12/2018
 * Time: 12:43
 */

namespace VitalyKustov;

/**
 * Class Message
 * @package VitalyKustov
 */
class Message
{
    /**
     * @var string[]
     */
    private $deviceTokens = [];

    /**
     * @var string
     */
    private $text;

    /**
     * @var array
     */
    private $customData;

    /**
     * @return string[]
     */
    public function getDeviceTokens(): array
    {
        return $this->deviceTokens;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getCustomData(): array
    {
        return $this->customData;
    }

    /**
     * @param string $text
     * @return Message
     */
    public function setText(string $text): Message
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param array $customData
     * @return Message
     */
    public function setCustomData(array $customData): Message
    {
        $this->customData = $customData;
        return $this;
    }

    /**
     * @param string $token
     * @return Message
     */
    public function addDeviceToken(string $token): Message
    {
        $this->deviceTokens[] = $token;
        return $this;
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: vitaly
 * Date: 13/12/2018
 * Time: 14:06
 */

namespace VitalyKustov;

use Apple\ApnPush\Certificate\Certificate;
use Apple\ApnPush\Exception\CertificateFileNotFoundException;
use Apple\ApnPush\Model\DeviceToken;
use Apple\ApnPush\Model\Notification;
use Apple\ApnPush\Model\Payload;
use Apple\ApnPush\Model\Receiver;
use Apple\ApnPush\Protocol\Http\Authenticator\CertificateAuthenticator;
use Apple\ApnPush\Sender\Builder\Http20Builder;
use Apple\ApnPush\Sender\SenderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ApplePushNotificationSender
 * @package VitalyKustov
 */
class ApplePushNotificationSender
{
    /**
     * @var string
     */
    private $applicationBundleId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $prodCertPath;

    /**
     * @var string
     */
    private $prodCertPass;

    /**
     * @var string
     */
    private $sandboxCertPath;

    /**
     * @var string
     */
    private $sandboxCertPass;

    /**
     * ApplePushNotificationSender constructor.
     * @param string $applicationBundleId
     * @param string $prodCertPath
     * @param string $prodCertPass
     * @param string $sandboxCertPath
     * @param string $sandboxCertPass
     * @param LoggerInterface $logger
     */
    public function __construct(string $applicationBundleId, string $prodCertPath, string $prodCertPass, string $sandboxCertPath, string $sandboxCertPass, LoggerInterface $logger)
    {
        $this->applicationBundleId = $applicationBundleId;
        $this->prodCertPath = $prodCertPath;
        $this->prodCertPass = $prodCertPass;
        $this->sandboxCertPath = $sandboxCertPath;
        $this->sandboxCertPass = $sandboxCertPass;
        $this->logger = $logger;
    }

    /**
     * @param Message $message
     * @throws ApplePushNotificationSenderException
     */
    public function send(Message $message): void
    {
        if (!\count($message->getDeviceTokens())) {
            return;
        }
        try {
            /** @var SenderInterface[] $senders */
            $senders = [
                'prod' => $this->getSender(false),
                'sandbox' => $this->getSender(true),
            ];

            foreach ($senders as $env => $sender) {

                $payload = Payload::createWithBody($message->getText());
                foreach ($message->getCustomData() as $k => $customDatum) {
                    $payload = $payload->withCustomData($k, $customDatum);
                }

                $notification = new Notification($payload);

                foreach ($message->getDeviceTokens() as $deviceToken) {
                    try {
                        $sender->send(new Receiver(new DeviceToken($deviceToken), $this->applicationBundleId), $notification);
                    } catch (\Throwable $e) {
                        $this->logger->warning(sprintf('[%s] Failed to send push: %s', $env, $e->getMessage()));
                    }
                }
            }

        } catch (CertificateFileNotFoundException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            throw new ApplePushNotificationSenderException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param bool $sandbox
     * @return SenderInterface
     * @throws CertificateFileNotFoundException
     */
    private function getSender(bool $sandbox): SenderInterface
    {
        return $sandbox
            ? (new Http20Builder(new CertificateAuthenticator(new Certificate($this->sandboxCertPath, $this->sandboxCertPass))))->build()
            : (new Http20Builder(new CertificateAuthenticator(new Certificate($this->prodCertPath, $this->prodCertPass))))->build();
    }

}
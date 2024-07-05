<?php
namespace Kleisli\FusionMailer\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Fusion\View\FusionView;

use Psr\Http\Message\UploadedFileInterface;
use Sitegeist\Neos\SymfonyMailer\Factories\MailerFactory;
use Sitegeist\Neos\SymfonyMailer\Factories\MailFactory;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @Flow\Scope("singleton")
 */
class FusionMailer
{
    #[Flow\Inject]
    protected MailerFactory $mailerFactory;

    #[Flow\Inject]
    protected MailFactory $mailFactory;

    protected FusionView $fusionView;


    public function __construct(?ControllerContext $controllerContext = null, ?string $packageKey = null){

        if($controllerContext == null && $packageKey == null){
            throw new \InvalidArgumentException('Either $controllerContext or $packageKey must be set when constructing a FusionMailer', 1717618798);
        }

        $this->fusionView = new FusionView();
        if($controllerContext != null) {
            $this->fusionView->setControllerContext($controllerContext);
        }
        if($packageKey != null) {
            $this->fusionView->setPackageKey($packageKey);
        }
        $this->fusionView->setFusionPathPatterns(['resource://Neos.Fusion/Private/Fusion/Root.fusion', 'resource://@package/Private/Emails']);
    }

    /**
     * @param string $subject
     * @param Address[]|Address|string $to
     * @param Address|string $from
     * @param string|null $textFusionPath e.g. "Vendor/Package/email/textMail"
     * @param string|null $htmlFusionPath e.g. "Vendor/Package/email/htmlMail"
     * @param Address[]|Address|string|null $replyTo
     * @param Address[]|Address|string|null $cc
     * @param Address[]|Address|string|null $bcc
     * @param array<PersistentResource|UploadedFileInterface|array{'name'?:string, 'content'?:string, 'type'?:string}|string>|null $attachments
     * @return void
     *
     * @throws TransportExceptionInterface
     */
    public function sendFusionMail(string $subject,
                                   array|Address|string $to,
                                   Address|string $from,
                                   string $textFusionPath = null,
                                   string $htmlFusionPath = null,
                                   array $variables = [],
                                   array|Address|string $replyTo = null,
                                   array|Address|string $cc = null,
                                   array|Address|string $bcc = null,
                                   array $attachments = null)
    {
        $this->fusionView->assignMultiple($variables);

        $html = null;
        if($htmlFusionPath){
            $this->fusionView->setFusionPath($htmlFusionPath);
            $html = $this->fusionView->render() ?? null;
        }

        $text = null;
        if($textFusionPath) {
            $this->fusionView->setFusionPath($textFusionPath);
            $text = $this->fusionView->render() ?? null;
        }

        $mail = $this->mailFactory->createMail(
            $subject,
            $to,
            $from,
            $text,
            $html,
            $replyTo,
            $cc,
            $bcc,
            $attachments
        );
        $mailer = $this->mailerFactory->createMailer();
        $mailer->send($mail);
    }
}


<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

use PhoneBurner\SaltLite\Framework\Domain\Email\EmailAddress;

class Email implements MailableMessage
{
    /**
     * @var array<EmailAddress>
     */
    private array $to = [];

    /**
     * @var array<EmailAddress>
     */
    private array $cc = [];

    /**
     * @var array<EmailAddress>
     */
    private array $bcc = [];

    /**
     * @var array<EmailAddress>
     */
    private array $from = [];

    /**
     * @var array<EmailAddress>
     */
    private array $reply_to = [];

    private MessageBody|null $body = null;

    /**
     * @var array<Attachment>
     */
    private array $attachments = [];

    public function __construct(
        private readonly string $subject,
        private readonly Priority $priority = Priority::Normal,
    ) {
    }

    #[\Override]
    public function getSubject(): string
    {
        return $this->subject;
    }

    #[\Override]
    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function addTo(EmailAddress ...$addresses): static
    {
        return $this->mapAddresses($this->to, $addresses);
    }

    /**
     * @return array<EmailAddress>
     */
    #[\Override]
    public function getTo(): array
    {
        return $this->to;
    }

    public function addCc(EmailAddress ...$addresses): static
    {
        return $this->mapAddresses($this->cc, $addresses);
    }

    /**
     * @return array<EmailAddress>
     */
    #[\Override]
    public function getCc(): array
    {
        return $this->cc;
    }

    public function addBcc(EmailAddress ...$addresses): static
    {
        return $this->mapAddresses($this->bcc, $addresses);
    }

    /**
     * @return array<EmailAddress>
     */
    #[\Override]
    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function addFrom(EmailAddress ...$addresses): static
    {
        return $this->mapAddresses($this->from, $addresses);
    }

    /**
     * @return array<EmailAddress>
     */
    #[\Override]
    public function getFrom(): array
    {
        return $this->from;
    }

    public function addReplyTo(EmailAddress ...$addresses): static
    {
        return $this->mapAddresses($this->reply_to, $addresses);
    }

    /**
     * @return array<EmailAddress>
     */
    #[\Override]
    public function getReplyTo(): array
    {
        return $this->reply_to;
    }

    public function setTextBody(
        MessageBodyPart|string $body,
        string $charset = MessageBodyPart::DEFAULT_CHARSET,
    ): static {
        return $this->setBody(new MessageBody(
            $this->body?->html,
            $body instanceof MessageBodyPart ? $body : new MessageBodyPart($body, $charset),
        ));
    }

    public function setHtmlBody(
        MessageBodyPart|string $body,
        string $charset = MessageBodyPart::DEFAULT_CHARSET,
    ): static {
        return $this->setBody(new MessageBody(
            $body instanceof MessageBodyPart ? $body : new MessageBodyPart($body, $charset),
            $this->body?->text,
        ));
    }

    public function setBody(MessageBody $body): static
    {
        $this->body = $body;

        return $this;
    }

    #[\Override]
    public function getBody(): MessageBody|null
    {
        return $this->body;
    }

    public function attach(Attachment $attachment): static
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * @return array<Attachment>
     */
    #[\Override]
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    private function mapAddresses(array &$property, array $addresses): static
    {
        foreach ($addresses as $address) {
            $property[$address->address] = $address;
        }

        return $this;
    }
}

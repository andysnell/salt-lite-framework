# Mailer

The Mailer component is a wrapper around the Symfony Mailer library. At a high
level, it provides a simple interface for sending emails in the form of objects
implementing the `\PhoneBurner\SaltLite\Framework\Mailer\Mailable` interface through the
`\PhoneBurner\SaltLite\Framework\Mailer\Mailer` interface.

Two interfaces extend the `\PhoneBurner\SaltLite\Framework\Mailer\Mailable` interface:

- `\PhoneBurner\SaltLite\Framework\Mailer\MailableNotification` - for "simple" notification type emails, where just the
  recipient, subject, and message body are needed, using the global default "from" address as the sender.
- `\PhoneBurner\SaltLite\Framework\Mailer\MailableMessage` - for more complex emails, where the sender, recipient,
  subject, and message body are all specified, along with additional headers like "CC", "BCC", and "Reply-To". This
  interface also allows for attaching/embedding content into the email.

The generic `\PhoneBurner\SaltLite\Framework\Mailer\Email` class can be used for the majority of use cases. It is a
mutable object with a fluent interface.

```php
public function sendMessage(\PhoneBurner\SaltLite\Framework\Mailer\Mailer $mailer): void
{
    $email = (new \PhoneBurner\SaltLite\Framework\Mailer\Email($subject))
        ->addTo(new EmailAddress($recipient))
        ->setTextBody("Hello, World!")
        ->attach(Attachment::fromPath(\PhoneBurner\SaltLite\Framework\APP_ROOT . '/storage/doc.pdf'));
    
    $mailer->send($email);
}
```

## Configuration

The application looks for the configuration for this component in the "config/mailer.php" file.

By default, the Mailer component uses the "smtp" transport, which is configured in the "config/mailer.php" file. The default
transport can be changed to SendGrid, which requires the API key to be added as an environment variable.

Also by default, the mailer is configured to send all messages asynchronously. This can be changed by setting the
'async' configuration option to `false`.
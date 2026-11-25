<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Resend'in HTTPS API'sine (port 443) giden mail taşıyıcısı.
 * Sunucu SMTP portlarını (25/465/587) dışarı engellediği ve ek composer
 * paketi istemediğimiz için, Laravel'in Http istemcisiyle doğrudan gönderir.
 */
class ResendHttpTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = array_filter([
            'from' => $this->tek($email->getFrom()),
            'to' => $this->liste($email->getTo()),
            'cc' => $this->liste($email->getCc()),
            'bcc' => $this->liste($email->getBcc()),
            'reply_to' => $this->liste($email->getReplyTo()),
            'subject' => $email->getSubject() ?? '',
            'html' => $email->getHtmlBody(),
            'text' => $email->getTextBody(),
        ], fn ($v) => $v !== null && $v !== []);

        Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(20)
            ->post('https://api.resend.com/emails', $payload)
            ->throw();
    }

    /** @param  Address[]  $adresler */
    private function tek(array $adresler): ?string
    {
        return isset($adresler[0]) ? $this->bicim($adresler[0]) : null;
    }

    /** @param  Address[]  $adresler @return string[] */
    private function liste(array $adresler): array
    {
        return array_map(fn (Address $a) => $this->bicim($a), $adresler);
    }

    private function bicim(Address $a): string
    {
        return $a->getName() !== '' ? $a->getName().' <'.$a->getAddress().'>' : $a->getAddress();
    }

    public function __toString(): string
    {
        return 'resend-http';
    }
}

<?php

// Sends mail via the ZeptoMail HTTP API (port 443) instead of SMTP,
// because cloud servers (Forge / DigitalOcean) block outbound SMTP ports.

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class ZeptoMailTransport extends AbstractTransport
{
    public function __construct(
        protected string $token,
        protected string $endpoint = 'https://api.zeptomail.com/v1.1/email',
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $from = $email->getFrom()[0] ?? null;

        $payload = array_filter([
            'from' => $from ? $this->address($from) : null,
            'to' => $this->recipients($email->getTo()),
            'cc' => $this->recipients($email->getCc()),
            'bcc' => $this->recipients($email->getBcc()),
            'reply_to' => array_map(fn (Address $a) => $this->address($a), $email->getReplyTo()),
            'subject' => $email->getSubject() ?? '',
            'htmlbody' => $email->getHtmlBody(),
            'textbody' => $email->getTextBody(),
        ], fn ($v) => $v !== null && $v !== []);

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-enczapikey '.$this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(20)->post($this->endpoint, $payload);

        if ($response->failed()) {
            throw new \RuntimeException('ZeptoMail API error ('.$response->status().'): '.$response->body());
        }
    }

    /** @param  array<int,Address>  $addresses */
    protected function recipients(array $addresses): array
    {
        return array_map(fn (Address $a) => ['email_address' => $this->address($a)], $addresses);
    }

    /** @return array<string,string> */
    protected function address(Address $a): array
    {
        $out = ['address' => $a->getAddress()];
        if ($a->getName() !== '') {
            $out['name'] = $a->getName();
        }

        return $out;
    }

    public function __toString(): string
    {
        return 'zeptomail';
    }
}

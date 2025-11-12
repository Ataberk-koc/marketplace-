<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Netgsm SMS API Entegrasyon Servisi
 * API Dokümantasyonu: https://www.netgsm.com.tr/dokuman/
 */
class NetgsmService
{
    protected $apiUrl;
    protected $username;
    protected $password;
    protected $header;

    public function __construct()
    {
        $this->apiUrl = config('services.netgsm.api_url', 'https://api.netgsm.com.tr');
        $this->username = config('services.netgsm.username');
        $this->password = config('services.netgsm.password');
        $this->header = config('services.netgsm.header', ''); // SMS başlığı
    }

    /**
     * Tek bir kullanıcıya SMS gönderir
     * 
     * @param string $phone Telefon numarası (5xxxxxxxxx formatında)
     * @param string $message SMS mesajı
     * @return array
     */
    public function sendSms($phone, $message)
    {
        return $this->sendBulkSms([$phone], $message);
    }

    /**
     * Toplu SMS gönderir
     * 
     * @param array $phones Telefon numaraları dizisi
     * @param string $message SMS mesajı
     * @return array
     */
    public function sendBulkSms(array $phones, $message)
    {
        try {
            // Telefon numaralarını temizle (başındaki 0'ı kaldır, sadece rakamları al)
            $cleanedPhones = array_map(function ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                return ltrim($phone, '0');
            }, $phones);

            // XML formatında istek hazırla
            $xml = $this->prepareXmlRequest($cleanedPhones, $message);

            // API'ye istek gönder
            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
            ])->send('POST', "{$this->apiUrl}/sms/send/xml", [
                'body' => $xml
            ]);

            $responseBody = $response->body();

            // Yanıt kodlarını kontrol et
            if ($response->successful()) {
                $result = $this->parseResponse($responseBody);
                
                if ($result['success']) {
                    Log::info('Netgsm SMS başarıyla gönderildi', [
                        'phones' => $cleanedPhones,
                        'message' => $message,
                        'response' => $responseBody
                    ]);
                } else {
                    Log::warning('Netgsm SMS gönderilemedi', [
                        'phones' => $cleanedPhones,
                        'message' => $message,
                        'response' => $responseBody,
                        'error' => $result['message']
                    ]);
                }

                return $result;
            }

            Log::error('Netgsm SMS API hatası', [
                'phones' => $cleanedPhones,
                'response' => $responseBody
            ]);

            return [
                'success' => false,
                'message' => 'SMS gönderilemedi',
                'error' => $responseBody
            ];

        } catch (\Exception $e) {
            Log::error('Netgsm SMS exception', [
                'phones' => $phones,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sipariş oluşturulduğunda müşteriye bilgilendirme SMS'i gönderir
     * 
     * @param \App\Models\Order $order
     * @return array
     */
    public function sendOrderConfirmationSms($order)
    {
        $message = "Sayın {$order->user->name}, {$order->order_number} nolu siparişiniz alınmıştır. Toplam tutar: {$order->total} TL. Teşekkür ederiz.";
        
        return $this->sendSms($order->user->phone, $message);
    }

    /**
     * Sipariş durumu değiştiğinde müşteriye bilgilendirme SMS'i gönderir
     * 
     * @param \App\Models\Order $order
     * @param string $newStatus
     * @return array
     */
    public function sendOrderStatusSms($order, $newStatus)
    {
        $statusMessages = [
            'processing' => 'hazırlanıyor',
            'shipped' => 'kargoya verildi',
            'delivered' => 'teslim edildi',
            'cancelled' => 'iptal edildi',
        ];

        $statusText = $statusMessages[$newStatus] ?? $newStatus;
        $message = "Sayın {$order->user->name}, {$order->order_number} nolu siparişiniz {$statusText}.";
        
        return $this->sendSms($order->user->phone, $message);
    }

    /**
     * XML formatında istek hazırlar
     */
    protected function prepareXmlRequest(array $phones, $message)
    {
        $phonesXml = '';
        foreach ($phones as $phone) {
            $phonesXml .= "<no>{$phone}</no>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mainbody>
    <header>
        <company dil="TR">Netgsm</company>
        <usercode>{$this->username}</usercode>
        <password>{$this->password}</password>
        <type>1:n</type>
        <msgheader>{$this->header}</msgheader>
    </header>
    <body>
        <msg><![CDATA[{$message}]]></msg>
        {$phonesXml}
    </body>
</mainbody>
XML;
    }

    /**
     * API yanıtını parse eder
     * 
     * Dönüş kodları:
     * 00 veya 01-999999999: Başarılı (Gönderim ID'si)
     * 20: Mesaj metninde hata
     * 30: Geçersiz kullanıcı adı, şifre veya kullanıcı pasif
     * 40: Mesaj başlığı (header) sistemde yok
     * 50: Abone hesabında kredi yok
     * 60: Mesaj gönderim limitini aştınız
     * 70: Hatalı sorgu
     */
    protected function parseResponse($response)
    {
        $response = trim($response);

        // Başarılı yanıt (00 veya bulkID)
        if ($response === '00' || is_numeric($response)) {
            return [
                'success' => true,
                'message' => 'SMS başarıyla gönderildi',
                'bulk_id' => $response
            ];
        }

        // Hata kodları
        $errorMessages = [
            '20' => 'Mesaj metninde hata var',
            '30' => 'Geçersiz kullanıcı adı veya şifre',
            '40' => 'Mesaj başlığı sistemde kayıtlı değil',
            '50' => 'Yetersiz bakiye',
            '60' => 'SMS gönderim limiti aşıldı',
            '70' => 'Hatalı sorgu formatı',
        ];

        $message = $errorMessages[$response] ?? "Bilinmeyen hata: {$response}";

        return [
            'success' => false,
            'message' => $message,
            'error_code' => $response
        ];
    }

    /**
     * Bakiye sorgulama
     * 
     * @return array
     */
    public function getBalance()
    {
        try {
            $response = Http::get("{$this->apiUrl}/balance/list/xml", [
                'usercode' => $this->username,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $balance = trim($response->body());
                
                return [
                    'success' => true,
                    'balance' => $balance,
                    'message' => "Kalan bakiye: {$balance} SMS"
                ];
            }

            return [
                'success' => false,
                'message' => 'Bakiye sorgulanamadı',
            ];

        } catch (\Exception $e) {
            Log::error('Netgsm balance check exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }
}

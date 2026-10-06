<?php

namespace App\Services\Storage;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class CloudflareR2Service
{
    protected string $region = 'auto';

    protected string $service = 's3';

    /**
     * Check if R2 has required configuration set in DB settings or .env
     */
    public function isConfigured(
        ?string $accountId = null,
        ?string $accessKey = null,
        ?string $secretKey = null,
        ?string $bucket = null
    ): bool {
        $acc = $accountId ?? Setting::get('r2_account_id', env('CLOUDFLARE_R2_ACCOUNT_ID', ''));
        $key = $accessKey ?? Setting::get('r2_access_key_id', env('CLOUDFLARE_R2_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', '')));
        $sec = $secretKey ?? Setting::get('r2_secret_access_key', env('CLOUDFLARE_R2_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY', '')));
        $bkt = $bucket ?? Setting::get('r2_bucket', env('CLOUDFLARE_R2_BUCKET', env('AWS_BUCKET', '')));

        return ! empty($acc) && ! empty($key) && ! empty($sec) && ! empty($bkt);
    }

    /**
     * Get active R2 credentials from DB or fallback to environment variables
     *
     * @return array{account_id: string, access_key_id: string, secret_access_key: string, bucket: string, public_url: string}
     */
    public function getCredentials(): array
    {
        return [
            'account_id' => (string) Setting::get('r2_account_id', env('CLOUDFLARE_R2_ACCOUNT_ID', '')),
            'access_key_id' => (string) Setting::get('r2_access_key_id', env('CLOUDFLARE_R2_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', ''))),
            'secret_access_key' => (string) Setting::get('r2_secret_access_key', env('CLOUDFLARE_R2_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY', ''))),
            'bucket' => (string) Setting::get('r2_bucket', env('CLOUDFLARE_R2_BUCKET', env('AWS_BUCKET', ''))),
            'public_url' => (string) Setting::get('r2_url', env('CLOUDFLARE_R2_URL', '')),
        ];
    }

    /**
     * Upload an object into Cloudflare R2 bucket using S3 SigV4 REST API
     *
     * @param  string  $key  Relative path in bucket (e.g. "torrents/cleanmymac.torrent")
     * @param  string  $data  Binary file content
     * @param  string  $mimeType  MIME type
     * @return string|null Public URL if uploaded, or null on failure
     */
    public function putObject(string $key, string $data, string $mimeType = 'application/octet-stream'): ?string
    {
        $creds = $this->getCredentials();
        if (! $this->isConfigured($creds['account_id'], $creds['access_key_id'], $creds['secret_access_key'], $creds['bucket'])) {
            Log::warning('CloudflareR2Service: Missing R2 credentials for upload.');

            return null;
        }

        $cleanKey = ltrim($key, '/');
        $bucket = $creds['bucket'];
        $accountId = $creds['account_id'];
        $host = "{$accountId}.r2.cloudflarestorage.com";
        $endpoint = "https://{$host}";

        $canonicalUri = "/{$bucket}/{$cleanKey}";
        $url = "{$endpoint}{$canonicalUri}";

        $payloadHash = hash('sha256', $data);
        $now = gmdate('Ymd\THis\Z');
        $dateOnly = gmdate('Ymd');

        $headers = [
            'content-length' => (string) strlen($data),
            'content-type' => $mimeType,
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $now,
        ];
        ksort($headers);

        $canonicalHeaders = '';
        $signedHeadersList = [];
        foreach ($headers as $hKey => $hVal) {
            $canonicalHeaders .= strtolower($hKey).':'.trim((string) $hVal)."\n";
            $signedHeadersList[] = strtolower($hKey);
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = "PUT\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $scope = "{$dateOnly}/{$this->region}/{$this->service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256', $canonicalRequest);

        $signingKey = $this->deriveSigningKey($creds['secret_access_key'], $dateOnly, $this->region, $this->service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$creds['access_key_id']}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $requestHeaders = [
            "Host: {$host}",
            "Content-Type: {$mimeType}",
            'Content-Length: '.strlen($data),
            "x-amz-date: {$now}",
            "x-amz-content-sha256: {$payloadHash}",
            "Authorization: {$authHeader}",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $this->buildPublicUrl($cleanKey, $creds);
        }

        Log::error("CloudflareR2Service: Upload failed [HTTP {$httpCode}]: {$response} | cURL: {$curlError}");

        return null;
    }

    /**
     * Delete an object from Cloudflare R2 bucket
     */
    public function deleteObject(string $key): bool
    {
        $creds = $this->getCredentials();
        if (! $this->isConfigured($creds['account_id'], $creds['access_key_id'], $creds['secret_access_key'], $creds['bucket'])) {
            return false;
        }

        $cleanKey = ltrim($key, '/');
        $bucket = $creds['bucket'];
        $accountId = $creds['account_id'];
        $host = "{$accountId}.r2.cloudflarestorage.com";
        $endpoint = "https://{$host}";

        $canonicalUri = "/{$bucket}/{$cleanKey}";
        $url = "{$endpoint}{$canonicalUri}";

        $payloadHash = hash('sha256', '');
        $now = gmdate('Ymd\THis\Z');
        $dateOnly = gmdate('Ymd');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $now,
        ];
        ksort($headers);

        $canonicalHeaders = '';
        $signedHeadersList = [];
        foreach ($headers as $hKey => $hVal) {
            $canonicalHeaders .= strtolower($hKey).':'.trim((string) $hVal)."\n";
            $signedHeadersList[] = strtolower($hKey);
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = "DELETE\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $scope = "{$dateOnly}/{$this->region}/{$this->service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256', $canonicalRequest);

        $signingKey = $this->deriveSigningKey($creds['secret_access_key'], $dateOnly, $this->region, $this->service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$creds['access_key_id']}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $requestHeaders = [
            "Host: {$host}",
            "x-amz-date: {$now}",
            "x-amz-content-sha256: {$payloadHash}",
            "Authorization: {$authHeader}",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Test connection to Cloudflare R2 bucket with explicit or stored credentials
     *
     * @return array{success: bool, message: string, http_code?: int}
     */
    public function testConnection(
        ?string $accountId = null,
        ?string $accessKey = null,
        ?string $secretKey = null,
        ?string $bucket = null
    ): array {
        $creds = $this->getCredentials();
        $accountId = trim($accountId ?: $creds['account_id']);
        $accessKey = trim($accessKey ?: $creds['access_key_id']);
        $secretKey = trim($secretKey ?: $creds['secret_access_key']);
        $bucket = trim($bucket ?: $creds['bucket']);

        if (empty($accountId) || empty($accessKey) || empty($secretKey) || empty($bucket)) {
            return [
                'success' => false,
                'message' => 'Por favor completa todos los campos requeridos: Account ID, Access Key ID, Secret Access Key y Bucket.',
            ];
        }

        $testKey = '_test_ping_'.time().'.txt';
        $testPayload = 'Cloudflare R2 probe ping from HaxMac portal: '.now()->toIso8601String();

        $host = "{$accountId}.r2.cloudflarestorage.com";
        $endpoint = "https://{$host}";
        $canonicalUri = "/{$bucket}/{$testKey}";
        $url = "{$endpoint}{$canonicalUri}";

        $payloadHash = hash('sha256', $testPayload);
        $now = gmdate('Ymd\THis\Z');
        $dateOnly = gmdate('Ymd');

        $headers = [
            'content-length' => (string) strlen($testPayload),
            'content-type' => 'text/plain',
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $now,
        ];
        ksort($headers);

        $canonicalHeaders = '';
        $signedHeadersList = [];
        foreach ($headers as $hKey => $hVal) {
            $canonicalHeaders .= strtolower($hKey).':'.trim((string) $hVal)."\n";
            $signedHeadersList[] = strtolower($hKey);
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = "PUT\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $scope = "{$dateOnly}/{$this->region}/{$this->service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256', $canonicalRequest);

        $signingKey = $this->deriveSigningKey($secretKey, $dateOnly, $this->region, $this->service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $requestHeaders = [
            "Host: {$host}",
            'Content-Type: text/plain',
            'Content-Length: '.strlen($testPayload),
            "x-amz-date: {$now}",
            "x-amz-content-sha256: {$payloadHash}",
            "Authorization: {$authHeader}",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $testPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            // Delete the test probe cleanly
            $this->deleteObjectWithCreds($testKey, $accountId, $accessKey, $secretKey, $bucket);

            return [
                'success' => true,
                'http_code' => $httpCode,
                'message' => "¡Conexión exitosa a Cloudflare R2! El bucket '{$bucket}' tiene permisos de escritura y lectura correctos.",
            ];
        }

        $errorMessage = match ($httpCode) {
            403 => "Error 403 (Acceso Denegado): Verifica que tu 'Access Key ID' y 'Secret Access Key' sean válidos y tengan permisos de 'Object Read & Write'.",
            404 => "Error 404 (No Encontrado): El bucket '{$bucket}' no existe en la cuenta de Cloudflare '{$accountId}'.",
            0 => "Error de red/conexión: {$curlError}. Verifica que tu servidor tenga acceso a internet.",
            default => "Error Cloudflare R2 [Código {$httpCode}]: ".strip_tags(substr((string) $response, 0, 200)),
        };

        return [
            'success' => false,
            'http_code' => $httpCode,
            'message' => $errorMessage,
        ];
    }

    /**
     * Delete object with explicit credentials
     */
    protected function deleteObjectWithCreds(
        string $key,
        string $accountId,
        string $accessKey,
        string $secretKey,
        string $bucket
    ): void {
        $cleanKey = ltrim($key, '/');
        $host = "{$accountId}.r2.cloudflarestorage.com";
        $url = "https://{$host}/{$bucket}/{$cleanKey}";

        $payloadHash = hash('sha256', '');
        $now = gmdate('Ymd\THis\Z');
        $dateOnly = gmdate('Ymd');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $now,
        ];
        ksort($headers);

        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$now}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = "DELETE\n/{$bucket}/{$cleanKey}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $scope = "{$dateOnly}/{$this->region}/{$this->service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256', $canonicalRequest);

        $signingKey = $this->deriveSigningKey($secretKey, $dateOnly, $this->region, $this->service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Host: {$host}",
            "x-amz-date: {$now}",
            "x-amz-content-sha256: {$payloadHash}",
            "Authorization: {$authHeader}",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Build public access URL for stored file
     */
    public function buildPublicUrl(string $cleanKey, array $creds): string
    {
        if (! empty($creds['public_url'])) {
            return rtrim($creds['public_url'], '/').'/'.$cleanKey;
        }

        return "https://{$creds['account_id']}.r2.cloudflarestorage.com/{$creds['bucket']}/{$cleanKey}";
    }

    /**
     * Derive AWS SigV4 signing key
     */
    protected function deriveSigningKey(string $secretKey, string $date, string $region, string $service): string
    {
        $kDate = hash_hmac('sha256', $date, 'AWS4'.$secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}

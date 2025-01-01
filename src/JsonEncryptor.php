<?php
namespace Almhdy\JsonShelter;

use RuntimeException;
use InvalidArgumentException;

class JsonEncryptor
{
    private string $encryptionMethod;
    private string $secretKey;
    private string $secretIv;

    public function __construct(string $secretKey, string $secretIv)
    {
        $this->encryptionMethod = "AES-256-CBC";
        $this->secretKey = hash("sha256", $secretKey, true); // Use binary output
        $this->secretIv = substr(hash("sha256", $secretIv, true), 0, 16); // Use binary output
    }

    public function encrypt(mixed $data): string
    {
        $jsonData = json_encode($data, JSON_THROW_ON_ERROR);

        $encryptedData = openssl_encrypt(
            $jsonData,
            $this->encryptionMethod,
            $this->secretKey,
            OPENSSL_RAW_DATA,
            $this->secretIv
        );

        if ($encryptedData === false) {
            throw new RuntimeException("Encryption failed: " . openssl_error_string());
        }

        return base64_encode($encryptedData);
    }

    public function decrypt(string $data): mixed
    {
        $decodedData = base64_decode($data, true);

        if ($decodedData === false) {
            throw new InvalidArgumentException("Invalid Base64 data provided for decryption.");
        }

        $decryptedData = openssl_decrypt(
            $decodedData,
            $this->encryptionMethod,
            $this->secretKey,
            OPENSSL_RAW_DATA,
            $this->secretIv
        );

        if ($decryptedData === false) {
            throw new RuntimeException("Decryption failed: " . openssl_error_string());
        }

        return json_decode($decryptedData, true, 512, JSON_THROW_ON_ERROR);
    }

    // Additional methods for handling large datasets

    public function encryptStream($inputStream, $outputStream): void
    {
        $iv = $this->secretIv;
        fwrite($outputStream, $iv);

        while (($buffer = fread($inputStream, 8192)) !== false) {
            $encryptedData = openssl_encrypt(
                $buffer,
                $this->encryptionMethod,
                $this->secretKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encryptedData === false) {
                throw new RuntimeException("Streaming encryption failed: " . openssl_error_string());
            }

            fwrite($outputStream, $encryptedData);
        }

        if (!feof($inputStream)) {
            throw new RuntimeException("Error reading input stream.");
        }
    }

    public function decryptStream($inputStream, $outputStream): void
    {
        $iv = fread($inputStream, 16);

        if (strlen($iv) !== 16) {
            throw new RuntimeException("Invalid IV length.");
        }

        while (($buffer = fread($inputStream, 8192)) !== false) {
            $decryptedData = openssl_decrypt(
                $buffer,
                $this->encryptionMethod,
                $this->secretKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decryptedData === false) {
                throw new RuntimeException("Streaming decryption failed: " . openssl_error_string());
            }

            fwrite($outputStream, $decryptedData);
        }

        if (!feof($inputStream)) {
            throw new RuntimeException("Error reading input stream.");
        }
    }
}
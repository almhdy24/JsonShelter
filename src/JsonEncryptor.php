<?php
namespace Almhdy\JsonShelter;

class JsonEncryptor
{
  private string $encryptionMethod;
  private string $secretKey;
  private string $secretIv;

  public function __construct(string $secretKey, string $secretIv)
  {
    $this->encryptionMethod = "AES-256-CBC"; // Fixed method
    $this->secretKey = hash("sha256", $secretKey, false); // Secure the key
    $this->secretIv = substr(hash("sha256", $secretIv, false), 0, 16); // Get the IV
  }

  public function encrypt(mixed $data): string
  {
    $jsonData = json_encode($data);

    if ($jsonData === false) {
      throw new \RuntimeException(
        "Failed to JSON encode data: " . json_last_error_msg()
      );
    }

    $encryptedData = openssl_encrypt(
      $jsonData,
      $this->encryptionMethod,
      $this->secretKey,
      0,
      $this->secretIv
    );

    if ($encryptedData === false) {
      throw new \RuntimeException(
        "Encryption failed: " . openssl_error_string()
      );
    }

    return base64_encode($encryptedData); // Base64 encode for storage
  }

  public function decrypt(string $data): mixed
  {
    $decodedData = base64_decode($data, true);

    if ($decodedData === false) {
      throw new \InvalidArgumentException(
        "Invalid Base64 data provided for decryption."
      );
    }

    $decryptedData = openssl_decrypt(
      $decodedData,
      $this->encryptionMethod,
      $this->secretKey,
      0,
      $this->secretIv
    );

    if ($decryptedData === false) {
      throw new \RuntimeException(
        "Decryption failed: " . openssl_error_string()
      );
    }

    $result = json_decode($decryptedData, true);

    if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException(
        "Failed to JSON decode data: " . json_last_error_msg()
      );
    }

    return $result; // Convert back to array
  }
}

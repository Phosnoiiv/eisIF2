<?php
namespace EverISay\SIF\ML\Common\Proprietary;

interface NetworkHelperInterface {
    public function encryptRequestBody(string $data): string;
    public function signRequest(string $jsonData, bool $isPost, int $userId, int $timestamp): string;
    public function decryptResponseBody(string $data): string;
}

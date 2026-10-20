<?php
function generateKey() {
    return random_bytes(32);
}

function encrypt($plaintext, $key) {
    if (empty($plaintext) || !is_string($plaintext)) {
        throw new Exception('Plaintext harus berupa string tidak kosong');
    }
    if (strlen($key) !== 32) {
        throw new Exception('Kunci harus 32 byte untuk AES-256');
    }
    $iv = random_bytes(12);
    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    if ($ciphertext === false) {
        throw new Exception('Enkripsi gagal');
    }
    return base64_encode($iv . $tag . $ciphertext);
}

function decrypt($encryptedData, $key) {
    if (!is_string($encryptedData) || empty($encryptedData)) {
        throw new Exception('Data terenkripsi harus berupa string tidak kosong');
    }
    if (strlen($key) !== 32) {
        throw new Exception('Kunci harus 32 byte untuk AES-256');
    }
    $data = base64_decode($encryptedData, true);
    if ($data === false) {
        throw new Exception('Data base64 tidak valid');
    }
    if (strlen($data) < 28) {
        throw new Exception('Data terenkripsi terlalu pendek');
    }
    $iv = substr($data, 0, 12);
    $tag = substr($data, 12, 16);
    $ciphertext = substr($data, 28);
    if (strlen($iv) !== 12) {
        throw new Exception('IV harus 12 byte');
    }
    if (strlen($tag) !== 16) {
        throw new Exception('Tag autentikasi harus 16 byte');
    }
    if (empty($ciphertext)) {
        throw new Exception('Ciphertext tidak boleh kosong');
    }
    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    if ($plaintext === false) {
        throw new Exception('Dekripsi gagal: Data mungkin telah dimanipulasi');
    }
    return $plaintext;
}
?>
<?php
// TOTP (RFC 6238) helpers — Google Authenticator compatible.

function base32_encode_bin(string $bin): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $out = '';
    $bits = '';
    for ($i = 0; $i < strlen($bin); $i++) {
        $bits .= str_pad(decbin(ord($bin[$i])), 8, '0', STR_PAD_LEFT);
    }
    for ($i = 0; $i < strlen($bits); $i += 5) {
        $chunk = substr($bits, $i, 5);
        $out .= $alphabet[bindec(str_pad($chunk, 5, '0'))];
    }
    return $out; // 20 bytes = exactly 32 base32 chars — no padding to strip
}

function totp_secret_generate(): string
{
    return base32_encode_bin(random_bytes(20));
}

function totp_verify(string $secret, string $code, int $window = 1): bool
{
    if (!preg_match('/^\d{6}$/', $code)) return false;
    $secret = strtoupper(preg_replace('/\s+/', '', $secret));
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($secret) as $c) {
        $v = strpos($alphabet, $c);
        if ($v === false) return false;
        $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
    }
    $key = '';
    for ($i = 0; $i < strlen($bits); $i += 8) {
        $key .= chr(bindec(str_pad(substr($bits, $i, 8), 8, '0')));
    }
    if ($key === '') return false;

    $time = (int) floor(time() / 30);
    for ($i = -$window; $i <= $window; $i++) {
        $ts = pack('N*', 0) . pack('N*', $time + $i);
        $hash = hash_hmac('sha1', $ts, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $val = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);
        $otp = str_pad((string) ($val % 1000000), 6, '0', STR_PAD_LEFT);
        if (hash_equals($otp, $code)) return true;
    }
    return false;
}

function totp_uri(string $secret, string $account, string $issuer = 'Apple Art'): string
{
    $label = rawurlencode($issuer) . ':' . rawurlencode($account);
    return 'otpauth://totp/' . $label . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30';
}

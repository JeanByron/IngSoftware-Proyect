<?php

namespace App\Services\TwoFactor;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * RNF-19: Segundo Factor de Autenticación por TOTP (RFC 6238), implementado en
 * PHP puro (sin servicio ni paquete externo — coherente con la autonomía del
 * proyecto, RNF-14). Compatible con Google Authenticator, Authy, etc.
 */
class TwoFactor
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // base32 (RFC 4648)
    private const PERIOD    = 30;   // segundos por código
    private const DIGITS    = 6;

    /** Genera un secreto base32 nuevo (160 bits). */
    public static function generateSecret(int $length = 32): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Verifica un código contra el secreto, con tolerancia de ±1 periodo para
     * absorber pequeñas diferencias de reloj y el cambio de ventana.
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $slice = (int) floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::codeAt($secret, $slice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /** Código TOTP actual (útil para pruebas y para el reloj del servidor). */
    public static function currentCode(string $secret): string
    {
        return self::codeAt($secret, (int) floor(time() / self::PERIOD));
    }

    /** URI otpauth:// para el QR que lee la app autenticadora. */
    public static function otpauthUri(string $secret, string $account): string
    {
        $issuer = rawurlencode((string) config('comercio.nombre', 'MesaQR'));
        $label  = $issuer.':'.rawurlencode($account);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&period=".self::PERIOD.'&digits='.self::DIGITS;
    }

    /** QR (SVG) de la URI otpauth, reutilizando bacon-qr-code (sin ext-gd). */
    public static function qrSvg(string $uri): string
    {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($uri);
    }

    /** Calcula el código de 6 dígitos para un periodo dado (HMAC-SHA1). */
    private static function codeAt(string $secret, int $slice): string
    {
        $key    = self::base32Decode($secret);
        $binary = hash_hmac('sha1', pack('N*', 0).pack('N*', $slice), $key, true);

        $offset = ord($binary[strlen($binary) - 1]) & 0x0f;
        $value  = (
            ((ord($binary[$offset]) & 0x7f) << 24) |
            ((ord($binary[$offset + 1]) & 0xff) << 16) |
            ((ord($binary[$offset + 2]) & 0xff) << 8) |
            (ord($binary[$offset + 3]) & 0xff)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $value, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Decodifica base32 a binario. */
    private static function base32Decode(string $secret): string
    {
        $secret = rtrim(strtoupper($secret), '=');
        $bits   = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos(self::ALPHABET, $char);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $binary .= chr(bindec($byte));
            }
        }

        return $binary;
    }
}

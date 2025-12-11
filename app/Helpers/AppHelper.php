<?php

namespace App\Helpers;

use JsonException;
use App\Models\Vendor;
use GuzzleHttp\Client;
use App\Models\MobileOtp;
use App\Models\VendorUser;
use Illuminate\Support\Carbon;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AppHelper
{
    public static function sendOtp($params, $user = null): int
    {
        $exists =
            MobileOtp::query()
            ->where('otp_type', $params['otp_type'])
            ->where('otp_value', $params['otp_value'])
            ->where('action', $params['action'])
            ->where('expires_at', '>', Carbon::now());
        if ($user) {
            $exists->where('vendor_user_id', $user->id);
        }
        $exists = $exists->first();
        if (!$exists) {
            $code = 1234;
            if (app()->environment('production')) {
                $code = rand(1000, 9999);
            }
            $created_at = Carbon::now();
            $expires_at = $created_at->addMinutes(config('settings.verification_expiry'));
            $data = [
                'otp_type' => $params['otp_type'],
                'otp_value' => $params['otp_value'],
                'action' => $params['action'],
                'verification_code' => $code,
                'expires_at' => $expires_at,
                'created_at' => $created_at,
            ];
            if ($user) {
                $data['vendor_user_id'] = $user->id;
            }
            MobileOTP::query()->create($data);
        } else {
            $expires_at = $exists->expires_at;
        }

        $remaining = 0;
        if (Carbon::now()->lt(Carbon::parse($expires_at))) {
            $expiry_ts = Carbon::createFromFormat('Y-m-d H:i:s', $expires_at)->getTimestamp();
            $remaining = $expiry_ts - Carbon::now()->getTimestamp();
        }
        return $remaining;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public static function sendSMS($mobile, $message)
    {
        $data = array(
            'userName' => config('sms.username'),
            'apiKey' => config('sms.api_key'),
            'userSender' => config('sms.user_sender'),
            'msgEncoding' => 'UTF8',
            'numbers' => $mobile,
            'msg' => $message,
            'timeToSend' => 'now'
        );

        $headers['Content-Type'] = 'application/json';
        $response = (new Client(['http_errors' => false]))->request('POST', config('sms.base_url') . '/gw/sendsms.php', [
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
            'headers' => $headers,
        ]);

        try {
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error($e->getMessage());
            return null;
        }
    }


    static function checkRateLimit(string $key, int $maxAttempts = 3, int $decaySeconds = 60): ?JsonResponse
    {
        $limiter = app(RateLimiter::class);
        if ($limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $limiter->availableIn($key);
            return response()->json([
                'message' => 'The given data was invalid.',
                'data' => [
                    'seconds' => $seconds,
                    'message' => [
                        trans('auth.throttle', [
                            'seconds' => $seconds,
                            'minutes' => ceil($seconds / 60),
                        ]),
                    ],
                ],
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        $limiter->hit($key, $decaySeconds);
        return null;
    }

    static function uploadFiles(string $path, $file): string
    {
        if ($file) {
            $path = $file->store($path, 'public');
        }
        return $path;
    }

    static function getVendorId(): int
    {
        /** @var VendorUser $user */
        $user = auth()->user();

        if (!$user?->vendor_id) {
            throw new UnauthorizedHttpException('Unauthorized.');
        }

        $vendorUuid = request()->header('vendor-id');

        if (empty($vendorUuid)) {
            return $user->vendor_id;
        }

        $vendor = Vendor::query()
            ->select('id')
            ->where(['uuid' => $vendorUuid, 'parent_id' => $user->vendor_id])
            ->firstOrFail();

        return $vendor->id;
    }
    public static function hslToHex($h, $s, $l)
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        if ($h < 60) {
            [$r, $g, $b] = [$c, $x, 0];
        } elseif ($h < 120) {
            [$r, $g, $b] = [$x, $c, 0];
        } elseif ($h < 180) {
            [$r, $g, $b] = [0, $c, $x];
        } elseif ($h < 240) {
            [$r, $g, $b] = [0, $x, $c];
        } elseif ($h < 300) {
            [$r, $g, $b] = [$x, 0, $c];
        } else {
            [$r, $g, $b] = [$c, 0, $x];
        }

        $r = ($r + $m) * 255;
        $g = ($g + $m) * 255;
        $b = ($b + $m) * 255;

        return sprintf("#%02x%02x%02x", round($r), round($g), round($b));
    }
    public static function colorToHex($color)
    {
        $colors = [
            'black' => '#000000',
            'white' => '#ffffff',
            'red'   => '#ff0000',
            'green' => '#008000',
            'blue'  => '#0000ff',
            'yellow' => '#ffff00',
            'cyan'  => '#00ffff',
            'magenta' => '#ff00ff',
            'gray'  => '#808080',
            'silver' => '#c0c0c0',
            'maroon' => '#800000',
            'olive' => '#808000',
            'purple' => '#800080',
            'teal'  => '#008080',
            'navy'  => '#000080',
            'aqua'  => '#00ffff',
            'fuchsia' => '#ff00ff',
            'lime'  => '#00ff00',
            'orange' => '#ffa500',
            'pink'  => '#ffc0cb',
            'violet' => '#ee82ee',
            'indigo' => '#4b0082',
            'blueviolet' => '#8a2be2',
            'coral' => '#ff7f50',
            'chocolate' => '#d2691e',
            'olivedrab' => '#6b8e23',
            // add more if needed
        ];

        $color = strtolower(trim($color));
        return $colors[$color] ?? null;
    }
    public static function rgbToHex($rgb)
    {
        if (preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/i', $rgb, $matches)) {
            return sprintf("#%02x%02x%02x", $matches[1], $matches[2], $matches[3]);
        }
        return null;
    }

    public static function colorStringToHex($color)
    {
        $color = trim(strtolower($color));

        if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color)) {
            return $color;
        }

        $named = self::colorToHex($color); // self:: بدل ما تنادي عادي
        if ($named) return $named;

        $rgb = self::rgbToHex($color);
        if ($rgb) return $rgb;

        if (preg_match('/hsl\((\d+),\s*(\d+)%?,\s*(\d+)%?\)/i', $color, $m)) {
            return self::hslToHex($m[1], $m[2], $m[3]);
        }

        return null;
    }
}

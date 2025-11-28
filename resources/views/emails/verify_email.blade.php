<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your OTP Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
<div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <h2 style="color: #333333;">Hello {{ $name ?? 'User' }},</h2>
    <p style="font-size: 16px; color: #555555;">Your One-Time Password (OTP) is:</p>
    <p style="font-size: 30px; color: #007bff; font-weight: bold; text-align: center;">{{ $otp }}</p>
    <p style="font-size: 14px; color: #999999; margin-top: 30px;">
        This OTP will expire in {{ $expiration ?? '5' }} minutes. Please do not share it with anyone.
    </p>
    <p style="font-size: 14px; color: #555555;">Thanks,<br>The {{ config('app.name') }} Team</p>
</div>
</body>
</html>

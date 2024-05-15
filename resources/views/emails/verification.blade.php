
    <h1>Verify Your Email Address</h1>
<p>Hi </p>
<p>Thank you for registering on our platform. To complete your registration, please verify your email address by entering the following code:</p>
<p><b>{{ $code }}</b></p>
<p>Alternatively, you can click the link below to verify your email:</p>
 <a href="{{ $link }}">{{ $code }}</a>
<p>This verification code will expire in 3 minutes.</p>
<p>If you didn't request this verification code, you can safely ignore it.</p>
<br>
<p>Best regards,</p>
<p>{{ config('app.name') }}</p>

    </form>

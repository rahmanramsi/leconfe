@use('SimpleSoftwareIO\QrCode\Facades\QrCode')
<div>
    <div class="flex justify-center">
        {!! 
            QrCode::format('png')
                ->size(600)
                ->generate($attendanceRedirectUrl) 
        !!}
    </div>
    <div class="text-center mt-2">
        <span class="text-red-500 inline-block">*</span>
        <p class="inline-block">
            Please scan this QR Code to confirm your attendance.
        </p>
    </div>
</div>
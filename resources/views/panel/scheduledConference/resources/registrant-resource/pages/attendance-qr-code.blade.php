@use('SimpleSoftwareIO\QrCode\Facades\QrCode')
<div>
    <div class="flex justify-center">
        {!! 
            QrCode::format('svg')
                ->size($QrCodeImageSize)
                ->generate($attendanceRedirectUrl) 
        !!}
    </div>
    <div class="text-center mt-2 text-sm">
        <span class="text-red-500">*</span> {{ $QrCodeFooterText }}
    </div>
</div>
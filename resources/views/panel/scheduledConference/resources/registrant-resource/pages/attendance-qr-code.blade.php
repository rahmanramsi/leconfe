@use('SimpleSoftwareIO\QrCode\Facades\QrCode')
<div>
    {!! QrCode::format('svg')->size(1000)->generate($attendanceRedirectUrl) !!}
</div>
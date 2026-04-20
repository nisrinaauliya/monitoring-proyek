<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #af2027; padding: 24px; text-align: center;">
            <h2 style="color: white; margin: 0; font-size: 18px;">Reminder Revisi PPSMB</h2>
        </div>
        <div style="padding: 30px; color: #333;">
            <p>Yth. <strong>{{ $ppsmb->user->name }}</strong>,</p>
            <p>Ini adalah pengingat bahwa PPSMB Anda sudah memasuki hari ke-<strong>{{ $hariKe }}</strong> dalam status <strong>Revisi User</strong> dan belum direvisi.</p>

            <div style="background: #f9f9f9; border-left: 4px solid #af2027; padding: 16px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 4px 0; font-size: 14px;"><strong>Nama Project:</strong> {{ $ppsmb->nama_project }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Model Aplikasi:</strong> {{ $ppsmb->model_aplikasi }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Departemen:</strong> {{ $ppsmb->dept }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Status:</strong> {{ $ppsmb->status }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Dikembalikan pada:</strong> {{ \Carbon\Carbon::parse($ppsmb->revisi_at)->format('d M Y H:i') }}</p>
            </div>

            <div style="background: #fff3cd; border-left: 4px solid #f0ad4e; padding: 16px; margin: 20px 0; border-radius: 4px; font-size: 14px;">
                Jika tidak direvisi dalam <strong>{{ 30 - $hariKe }} hari</strong> ke depan, PPSMB Anda akan otomatis direject oleh sistem.
            </div>

            <p>Segera login ke sistem dan lakukan revisi PPSMB Anda.</p>
            <p>Terima kasih.</p>
        </div>
        <div style="background: #f5f5f5; padding: 16px; text-align: center; font-size: 12px; color: #999;">
            <p>© {{ date('Y') }} Sistem Helpdesk - Wahana Artha Group</p>
        </div>
    </div>
</body>
</html>
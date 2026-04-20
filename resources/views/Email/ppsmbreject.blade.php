<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #af2027; padding: 24px; text-align: center;">
            <h2 style="color: white; margin: 0; font-size: 18px;">PPSMB Otomatis Direject</h2>
        </div>
        <div style="padding: 30px; color: #333;">
            <p>Yth. <strong>{{ $ppsmb->user->name }}</strong>,</p>
            <p>Kami memberitahukan bahwa PPSMB Anda telah <strong>otomatis direject</strong> oleh sistem karena tidak direvisi dalam 30 hari.</p>

            <div style="background: #f9f9f9; border-left: 4px solid #af2027; padding: 16px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 4px 0; font-size: 14px;"><strong>Nama Project:</strong> {{ $ppsmb->nama_project }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Model Aplikasi:</strong> {{ $ppsmb->model_aplikasi }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Departemen:</strong> {{ $ppsmb->dept }}</p>
                <p style="margin: 4px 0; font-size: 14px;"><strong>Dikembalikan pada:</strong> {{ \Carbon\Carbon::parse($ppsmb->revisi_at)->format('d M Y H:i') }}</p>
            </div>

            <div style="background: #f8d7da; border-left: 4px solid #af2027; padding: 16px; margin: 20px 0; border-radius: 4px; font-size: 14px; color: #721c24;">
                PPSMB ini telah direject secara otomatis karena melewati batas waktu revisi 30 hari. Jika ingin melanjutkan, silahkan ajukan PPSMB baru.
            </div>

            <p>Terima kasih.</p>
        </div>
        <div style="background: #f5f5f5; padding: 16px; text-align: center; font-size: 12px; color: #999;">
            <p>© {{ date('Y') }} Sistem Helpdesk - Wahana Artha Group</p>
        </div>
    </div>
</body>
</html>
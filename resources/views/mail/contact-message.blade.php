<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #1e293b;">
    <h2 style="margin-bottom: 4px;">Pesan baru dari landing page Kasiro</h2>
    <p style="margin: 0 0 16px; color: #64748b;">Dikirim {{ $contactMessage->created_at->format('d M Y H:i') }}</p>

    <p><strong>Nama:</strong> {{ $contactMessage->name }}</p>
    <p><strong>Email:</strong> {{ $contactMessage->email }}</p>
    <p><strong>Pesan:</strong></p>
    <p style="white-space: pre-line;">{{ $contactMessage->message }}</p>
</body>
</html>

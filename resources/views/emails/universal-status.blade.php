<!DOCTYPE html>
<html lang="fr" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title }}</title>
    <style>
        @import url('https://googleapis.com');

        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #fafafa;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        .wrapper {
            background-color: #fafafa;
            padding: 48px 16px;
        }

        .container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 12px 24px rgba(0, 0, 0, 0.03);
            border: 1px solid #4f46e5;
        }

        .header {
            padding: 36px 40px 10px 40px;
            text-align: left;
        }

        .logo-text {
            color: #4f46e5;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .content {
            padding: 24px 40px 40px 40px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }

        .message {
            font-size: 15px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 28px;
            letter-spacing: -0.1px;
        }

        .status-approved {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .status-rejected {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .comment-box {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            padding: 20px;
            border-radius: 12px;
            margin: 28px 0;
            color: #334155;
            font-size: 14.5px;
            line-height: 1.5;
        }

        .comment-title {
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .comment-text {
            margin: 0;
            font-style: italic;
            color: #1e293b;
        }

        .btn-wrapper {
            margin-top: 32px;
            text-align: left;
        }

        .btn {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff !important;
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .divider {
            height: 1px;
            background-color: #f1f5f9;
            margin: 40px 0 24px 0;
        }

        .footer {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            text-align: left;
        }

        .footer p {
            margin: 0 0 8px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <p class="logo-text">{{ config('app.name') }}</p>
            </div>

            <!-- Content -->
            <div class="content">
                <h2 class="greeting">Bonjour {{ $notifiableName }},</h2>
                <p class="message">{{ $messageContent }}</p>

                <!-- Badge de Statut SaaS -->
                @if(in_array(strtolower($status), ['validé', 'approuvé', 'approved', 'approuve']))
                    <div class="status-badge status-approved">✓ Approuvé</div>
                @else
                    <div class="status-badge status-rejected">✕ Rejeté</div>
                @endif

                <!-- Section Commentaire Pro -->
                @if($comment)
                    <div class="comment-box">
                        <div class="comment-title">Note du superviseur</div>
                        <p class="comment-text">« {{ $comment }} »</p>
                    </div>
                @endif

                <!-- Bouton d'action Minimaliste -->
                <div class="btn-wrapper">
                    <a href="{{ $routeUrl }}" class="btn">Ouvrir l'élément gddd →</a>
                </div>

                <div class="divider"></div>

                <!-- Footer -->
                <div class="footer">
                    <p>Ceci est une notification automatique générée par l'application {{ config('app.name') }}.</p>
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

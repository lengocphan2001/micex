<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác nhận đổi mật khẩu quỹ</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
        }
        .header {
            background: linear-gradient(135deg, #2d59ff 0%, #2448d1 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px dashed #2d59ff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 32px;
            font-weight: 700;
            color: #2d59ff;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-text {
            font-size: 13px;
            color: #856404;
            margin: 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer-text {
            font-size: 12px;
            color: #999;
            margin: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2d59ff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Micex</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                <strong>Xin chào{{ $userName ? ' ' . $userName : '' }},</strong>
            </div>
            
            <div class="message">
                Bạn đã yêu cầu đổi mật khẩu quỹ. Vui lòng sử dụng mã xác nhận bên dưới để hoàn tất thao tác.
            </div>
            
            <div class="code-box">
                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Mã xác nhận của bạn:</div>
                <div class="code">{{ $verificationCode }}</div>
            </div>
            
            <div class="warning">
                <p class="warning-text">
                    <strong>⚠️ Lưu ý:</strong> Mã xác nhận này chỉ có hiệu lực trong <strong>1 phút</strong>. 
                    Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này và đảm bảo tài khoản của bạn được bảo mật.
                </p>
            </div>
            
            <div class="message">
                Nếu bạn gặp vấn đề, vui lòng liên hệ với bộ phận hỗ trợ của chúng tôi.
            </div>
        </div>
        
        <div class="footer">
            <p class="footer-text">
                © {{ date('Y') }} Micex. Tất cả quyền được bảo lưu.<br>
                Email này được gửi tự động, vui lòng không trả lời email này.
            </p>
        </div>
    </div>
</body>
</html>


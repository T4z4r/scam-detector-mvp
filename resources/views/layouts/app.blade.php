<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TZ/KE Scam Detector')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            text-align: center;
        }

        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .nav-links {
            margin-top: 20px;
        }

        .nav-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: #667eea;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                max-width: 95%;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .container {
                max-width: 95%;
                padding: 15px;
            }

            .header {
                padding: 20px;
            }

            .main-content {
                padding: 20px;
            }

            .nav-links a {
                display: inline-block;
                margin: 5px;
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }

            .header {
                padding: 15px;
            }

            .main-content {
                padding: 15px;
            }
        }

        @yield('styles')
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🚨 TZ/KE Scam Detector</h1>
            <p>Protect yourself from fraudulent SMS and messages</p>
            <nav class="nav-links">
                <a href="/">Home</a>
                <a href="/scam-check">Check Message</a>
                <a href="/training">Train Model</a>
                <a href="https://www.cck.go.ke/" target="_blank">Report Scams</a>
            </nav>
        </header>

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>

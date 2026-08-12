<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Apple Art</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
            height: 100vh;
            display: flex;
            overflow: hidden;
            position: relative;
        }
        /* Premium Background Detail: Animated Glowing Orbs */
        body::before, body::after, .bg-orb-3 {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 0;
            animation: float 20s infinite alternate ease-in-out;
            pointer-events: none;
        }
        body::before {
            width: 800px; height: 800px;
            background: rgba(10, 132, 255, 0.15); /* Apple Blue */
            top: -200px; left: -200px;
        }
        body::after {
            width: 700px; height: 700px;
            background: rgba(94, 92, 230, 0.12); /* Apple Purple */
            bottom: -100px; right: 20%;
            animation-delay: -7s;
            animation-duration: 25s;
        }
        .bg-orb-3 {
            width: 600px; height: 600px;
            background: rgba(48, 209, 88, 0.1); /* Apple Green */
            top: 20%; right: -100px;
            animation-delay: -14s;
            animation-duration: 30s;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 80px) scale(1.05); }
            100% { transform: translate(-50px, 40px) scale(0.95); }
        }

        .split-left {
            flex: 1;
            background: transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 40px;
            z-index: 1;
        }
        .brand-logo-massive {
            width: 480px;
            height: 480px;
            object-fit: contain;
            filter: drop-shadow(0 15px 35px rgba(0,0,0,0.06));
        }
        .brand-title {
            color: #1e293b;
            font-size: 42px;
            font-weight: 700;
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 12px;
        }
        .brand-subtitle {
            color: #64748b;
            font-size: 18px;
            text-align: center;
            max-width: 500px;
            line-height: 1.6;
        }
        .split-right {
            flex: 0 0 520px;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 80px;
            box-shadow: -10px 0 40px rgba(0,0,0,0.04);
            z-index: 10;
            border-left: 1px solid rgba(255, 255, 255, 0.4);
        }
        .login-header {
            margin-bottom: 48px;
        }
        .login-header h2 {
            font-weight: 700;
            font-size: 32px;
            color: #1e293b;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .login-header p {
            color: #64748b;
            font-size: 16px;
        }
        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 16px;
            border: 1px solid #cbd5e1;
            background: #fff;
            margin-bottom: 24px;
            transition: all 0.2s;
        }
        .form-control:focus {
            background: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .btn-primary {
            background: #0f172a;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.2s;
            margin-top: 12px;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
        }
        #login-error {
            display: none;
            color: #ef4444;
            font-size: 14px;
            margin-bottom: 24px;
            background: #fef2f2;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #fee2e2;
            font-weight: 500;
        }
        
        @media (max-width: 992px) {
            body {
                flex-direction: column;
                overflow: auto;
            }
            .split-left {
                flex: none;
                padding: 60px 20px;
                min-height: 50vh;
            }
            .brand-logo-massive {
                width: 180px;
                height: 180px;
            }
            .brand-title {
                font-size: 32px;
            }
            .split-right {
                flex: none;
                width: 100%;
                padding: 40px 24px;
                box-shadow: none;
                border-radius: 32px 32px 0 0;
                margin-top: -32px;
                min-height: 60vh;
                border-left: none;
            }
        }
    </style>
</head>
<body>
<div class="bg-orb-3"></div>

<div class="split-left">
    <img src="aalogo.png" alt="Apple Art Logo" class="brand-logo-massive">
</div>

<div class="split-right">
    <div class="login-header">
        <h2>Apple Art</h2>
        <p>Student Management System</p>
    </div>
    
    <div id="login-error"></div>
    
    <form id="login-form">
        <div>
            <label class="form-label">Email Address</label>
            <input type="email" id="email" class="form-control" placeholder="admin@appleart.com" required>
        </div>
        
        <div>
            <label class="form-label">Password</label>
            <input type="password" id="password" class="form-control" placeholder="••••••••" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Sign In to Portal</button>
    </form>
    
    <div style="text-align: center; margin-top: 40px; font-size: 12px; color: #94a3b8; font-weight: 500;">
        &copy; <?php echo date('Y'); ?> Apple Art. All rights reserved.<br>
        Developed by Kyaw Zin Hein
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const btn = e.target.querySelector('button');
    const err = document.getElementById('login-error');
    
    btn.disabled = true;
    btn.innerHTML = 'Authenticating...';
    err.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        
        const res = await fetch('api/login.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        
        if (result.status === 'success') {
            window.location.reload();
        } else {
            err.innerText = result.message || 'Login failed';
            err.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = 'Sign In to Portal';
        }
    } catch (error) {
        err.innerText = 'Network error occurred';
        err.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = 'Sign In to Portal';
    }
});
</script>

</body>
</html>

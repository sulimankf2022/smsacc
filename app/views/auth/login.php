<?php require_once __DIR__ . '/../../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #1e2a3a 0%, #2d4263 100%); min-height: 100vh; display:flex; align-items:center; }
        .login-card { border-radius:16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .login-logo { width:64px; height:64px; background:#4f9cf9; border-radius:16px; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:2rem; color:white; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4 col-sm-8">
            <div class="card login-card border-0">
                <div class="card-body p-5">
                    <div class="login-logo"><i class="bi bi-reception-4"></i></div>
                    <h4 class="text-center fw-bold mb-1"><?php echo APP_NAME; ?></h4>
                    <p class="text-center text-muted small mb-4">SMS Traffic Finance Manager</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>/index.php?page=login">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" required autocomplete="username">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </form>
                    <p class="text-center text-muted small mt-4 mb-0">Demo: admin / admin123</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

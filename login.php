<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'includes/db.php';

$error = "";

// Show flash messages from redirects (e.g. community.php auth check)
if (!empty($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password, membership_type, role, premium_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $membership_status = $user['membership_type'];
            $paid_types = ['premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
            if (in_array($user['membership_type'], $paid_types) && $user['premium_expiry']) {
                $expiry = new DateTime($user['premium_expiry']);
                $now = new DateTime();
                if ($now > $expiry) {
                    $uid = $user['id'];
                    $conn->query("UPDATE users SET membership_type='free', premium_expiry=NULL WHERE id=$uid");
                    $membership_status = 'free';
                }
            }
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_type'] = $membership_status;
            $_SESSION['role']      = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
/* ════════════════════════════════════════
   LOGIN PAGE — PREMIUM DARK
   ════════════════════════════════════════ */
@keyframes lgFadeUp  { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }
@keyframes lgOrb     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.15) translate(14px,-10px)} }
@keyframes lgShimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes lgSpin    { to{transform:rotate(360deg)} }

.lg-page {
    min-height: 100vh;
    background: #020617;
    display: flex; align-items: center; justify-content: center;
    padding: 40px 20px;
    position: relative; overflow: hidden;
}

/* Background grid */
.lg-grid {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(212,175,55,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,0.04) 1px, transparent 1px);
    background-size: 60px 60px;
}

/* Orbs */
.lg-orb {
    position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px);
}
.lg-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
    top: -150px; right: -100px;
    animation: lgOrb 8s ease-in-out infinite;
}
.lg-orb-2 {
    width: 350px; height: 350px;
    background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
    bottom: -80px; left: -80px;
    animation: lgOrb 10s ease-in-out infinite reverse;
}

/* Card */
.lg-card {
    position: relative; z-index: 2;
    width: 100%; max-width: 440px;
    background: rgba(10,22,40,0.85);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 24px;
    padding: 48px 40px;
    backdrop-filter: blur(20px);
    box-shadow: 0 40px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(212,175,55,0.06);
    animation: lgFadeUp 0.6s ease both;
}

/* Logo / brand */
.lg-brand {
    text-align: center; margin-bottom: 32px;
}
.lg-logo-ring {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-radius: 18px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: #020617;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(212,175,55,0.35);
}
.lg-brand h2 {
    font-size: 1.7rem; font-weight: 900; color: #fff; margin: 0 0 6px;
}
.lg-brand p { color: #64748b; font-size: 0.9rem; margin: 0; }

/* Error */
.lg-error {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: #fca5a5;
    padding: 12px 16px; border-radius: 12px; margin-bottom: 24px;
    font-size: 0.87rem; display: flex; align-items: center; gap: 8px;
}

/* Fields */
.lg-field { margin-bottom: 20px; }
.lg-label {
    display: block; font-size: 0.75rem; font-weight: 800;
    color: #64748b; letter-spacing: 1.5px; text-transform: uppercase;
    margin-bottom: 8px;
}
.lg-input {
    width: 100%; padding: 13px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; color: #e2e8f0;
    font-size: 0.95rem; outline: none;
    transition: border-color 0.25s, box-shadow 0.25s;
    box-sizing: border-box;
}
.lg-input:focus {
    border-color: rgba(212,175,55,0.5);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
}
.lg-input::placeholder { color: #334155; }

/* Password wrapper */
.lg-pwd-wrap { position: relative; }
.lg-pwd-toggle {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    color: #475569; cursor: pointer; font-size: 0.9rem; transition: color 0.2s;
    background: none; border: none; padding: 0;
}
.lg-pwd-toggle:hover { color: #d4af37; }

/* Forgot row */
.lg-row {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px;
}
.lg-forgot { font-size: 0.8rem; color: #475569; text-decoration: none; transition: color 0.2s; }
.lg-forgot:hover { color: #d4af37; }

/* Submit button */
.lg-submit {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    border: none; border-radius: 12px;
    color: #020617; font-size: 1rem; font-weight: 800;
    cursor: pointer; letter-spacing: 0.5px;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    box-shadow: 0 6px 24px rgba(212,175,55,0.35);
    margin-top: 8px;
}
.lg-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 36px rgba(212,175,55,0.5);
}
.lg-submit:active { transform: scale(0.98); }

/* Divider */
.lg-divider {
    display: flex; align-items: center; gap: 12px; margin: 24px 0;
}
.lg-divider span {
    flex: 1; height: 1px;
    background: rgba(255,255,255,0.07);
}
.lg-divider em { font-size: 0.75rem; color: #334155; font-style: normal; }

/* Footer link */
.lg-footer {
    text-align: center; font-size: 0.88rem; color: #475569; margin-top: 4px;
}
.lg-footer a { color: #d4af37; font-weight: 700; text-decoration: none; }
.lg-footer a:hover { color: #facc15; }
</style>

<div class="lg-page">
    <div class="lg-grid"></div>
    <div class="lg-orb lg-orb-1"></div>
    <div class="lg-orb lg-orb-2"></div>

    <div class="lg-card">
        <div class="lg-brand">
            <div class="lg-logo-ring"><i class="fas fa-chart-line"></i></div>
            <h2>Welcome Back</h2>
            <p>Sign in to your Wise Invest account</p>
        </div>

        <?php if ($error): ?>
        <div class="lg-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="lg-field">
                <label class="lg-label">Email Address</label>
                <input type="email" name="email" class="lg-input" placeholder="name@email.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="lg-field">
                <div class="lg-row">
                    <label class="lg-label" style="margin:0;">Password</label>
                    <a href="#" class="lg-forgot">Forgot password?</a>
                </div>
                <div class="lg-pwd-wrap">
                    <input type="password" name="password" id="lgPwd" class="lg-input" placeholder="••••••••" required style="padding-right:44px;">
                    <button type="button" class="lg-pwd-toggle" onclick="togglePwd()">
                        <i class="fas fa-eye" id="lgEye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="lg-submit">
                <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i> Access Account
            </button>
        </form>

        <div class="lg-divider"><span></span><em>or</em><span></span></div>

        <div class="lg-footer">
            New to Wise Invest? <a href="signup.php">Create a free account</a>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    var inp = document.getElementById('lgPwd');
    var eye = document.getElementById('lgEye');
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        eye.className = 'fas fa-eye';
    }
}
</script>

<?php include 'includes/footer.php'; ?>

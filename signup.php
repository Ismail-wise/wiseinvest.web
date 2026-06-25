<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname         = $conn->real_escape_string(trim($_POST['fullname']));
    $email            = $conn->real_escape_string(trim($_POST['email']));
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $membership = 'free';
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, membership_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $fullname, $email, $hashed_password, $role, $membership);
            if ($stmt->execute()) {
                echo "<script>alert('Account created successfully! Please login.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
/* ════════════════════════════════════════
   SIGNUP PAGE — PREMIUM DARK
   ════════════════════════════════════════ */
@keyframes sgFadeUp { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }
@keyframes sgOrb    { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.12) translate(14px,-10px)} }

.sg-page {
    min-height: 100vh;
    background: #020617;
    display: flex; align-items: center; justify-content: center;
    padding: 40px 20px;
    position: relative; overflow: hidden;
}
.sg-grid {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(212,175,55,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,0.04) 1px, transparent 1px);
    background-size: 60px 60px;
}
.sg-orb {
    position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px);
}
.sg-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(212,175,55,0.11) 0%, transparent 70%);
    top: -150px; left: -100px;
    animation: sgOrb 8s ease-in-out infinite;
}
.sg-orb-2 {
    width: 350px; height: 350px;
    background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
    bottom: -80px; right: -80px;
    animation: sgOrb 11s ease-in-out infinite reverse;
}

.sg-card {
    position: relative; z-index: 2;
    width: 100%; max-width: 480px;
    background: rgba(10,22,40,0.85);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 24px;
    padding: 48px 40px;
    backdrop-filter: blur(20px);
    box-shadow: 0 40px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(212,175,55,0.06);
    animation: sgFadeUp 0.6s ease both;
}

.sg-brand { text-align: center; margin-bottom: 30px; }
.sg-logo-ring {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-radius: 18px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: #020617;
    margin-bottom: 14px;
    box-shadow: 0 8px 24px rgba(212,175,55,0.35);
}
.sg-brand h2 { font-size: 1.65rem; font-weight: 900; color: #fff; margin: 0 0 5px; }
.sg-brand p  { color: #64748b; font-size: 0.88rem; margin: 0; }

/* Steps indicator */
.sg-steps {
    display: flex; align-items: center; justify-content: center; gap: 0;
    margin-bottom: 28px;
}
.sg-step-dot {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 800;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #475569;
}
.sg-step-dot.active {
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-color: transparent; color: #020617;
}
.sg-step-dot.done {
    background: rgba(212,175,55,0.15);
    border-color: rgba(212,175,55,0.3);
    color: #d4af37;
}
.sg-step-line {
    flex: 1; max-width: 50px; height: 1px;
    background: rgba(255,255,255,0.07);
    margin: 0 8px;
}

.sg-error {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: #fca5a5;
    padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;
    font-size: 0.87rem; display: flex; align-items: center; gap: 8px;
}

.sg-field { margin-bottom: 18px; }
.sg-label {
    display: block; font-size: 0.74rem; font-weight: 800;
    color: #64748b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 7px;
}
.sg-input {
    width: 100%; padding: 13px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; color: #e2e8f0;
    font-size: 0.95rem; outline: none;
    transition: border-color 0.25s, box-shadow 0.25s;
    box-sizing: border-box;
}
.sg-input:focus {
    border-color: rgba(212,175,55,0.5);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
}
.sg-input::placeholder { color: #334155; }

.sg-pwd-wrap { position: relative; }
.sg-pwd-toggle {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    color: #475569; cursor: pointer; font-size: 0.9rem;
    background: none; border: none; padding: 0; transition: color 0.2s;
}
.sg-pwd-toggle:hover { color: #d4af37; }

/* Password strength */
.sg-strength-bar {
    height: 3px; border-radius: 3px; margin-top: 7px;
    background: rgba(255,255,255,0.06);
    overflow: hidden;
}
.sg-strength-fill {
    height: 100%; width: 0%; border-radius: 3px;
    transition: width 0.3s, background 0.3s;
}

/* Perks */
.sg-perks {
    display: flex; flex-direction: column; gap: 6px;
    margin-bottom: 20px;
    padding: 14px 16px;
    background: rgba(212,175,55,0.05);
    border: 1px solid rgba(212,175,55,0.12);
    border-radius: 12px;
}
.sg-perk {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.82rem; color: #94a3b8;
}
.sg-perk i { color: #d4af37; font-size: 0.78rem; }

.sg-submit {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    border: none; border-radius: 12px;
    color: #020617; font-size: 1rem; font-weight: 800;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    box-shadow: 0 6px 24px rgba(212,175,55,0.35);
}
.sg-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 36px rgba(212,175,55,0.5);
}

.sg-divider {
    display: flex; align-items: center; gap: 12px; margin: 20px 0;
}
.sg-divider span { flex: 1; height: 1px; background: rgba(255,255,255,0.07); }
.sg-divider em { font-size: 0.75rem; color: #334155; font-style: normal; }

.sg-footer {
    text-align: center; font-size: 0.88rem; color: #475569; margin-top: 4px;
}
.sg-footer a { color: #d4af37; font-weight: 700; text-decoration: none; }
.sg-footer a:hover { color: #facc15; }

.sg-terms {
    font-size: 0.75rem; color: #475569; text-align: center; margin-top: 14px;
}
.sg-terms a { color: #64748b; }
</style>

<div class="sg-page">
    <div class="sg-grid"></div>
    <div class="sg-orb sg-orb-1"></div>
    <div class="sg-orb sg-orb-2"></div>

    <div class="sg-card">
        <div class="sg-brand">
            <div class="sg-logo-ring"><i class="fas fa-seedling"></i></div>
            <h2>Join Wise Invest</h2>
            <p>Start your journey to financial freedom</p>
        </div>

        <div class="sg-steps">
            <div class="sg-step-dot active">1</div>
            <div class="sg-step-line"></div>
            <div class="sg-step-dot">2</div>
            <div class="sg-step-line"></div>
            <div class="sg-step-dot">3</div>
        </div>

        <?php if ($error): ?>
        <div class="sg-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="sg-perks">
            <div class="sg-perk"><i class="fas fa-check-circle"></i> Free access to articles & videos</div>
            <div class="sg-perk"><i class="fas fa-check-circle"></i> Portfolio & calculator tools</div>
            <div class="sg-perk"><i class="fas fa-check-circle"></i> Upgrade to Premium anytime</div>
        </div>

        <form method="POST" action="" id="sgForm">
            <div class="sg-field">
                <label class="sg-label">Full Name</label>
                <input type="text" name="fullname" class="sg-input" placeholder="Your full name" required
                       value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="sg-field">
                <label class="sg-label">Email Address</label>
                <input type="email" name="email" class="sg-input" placeholder="name@email.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="sg-field">
                <label class="sg-label">Password</label>
                <div class="sg-pwd-wrap">
                    <input type="password" name="password" id="sgPwd1" class="sg-input"
                           placeholder="Min. 6 characters" required style="padding-right:44px;"
                           oninput="checkStrength(this.value)">
                    <button type="button" class="sg-pwd-toggle" onclick="togglePwd('sgPwd1','sgEye1')">
                        <i class="fas fa-eye" id="sgEye1"></i>
                    </button>
                </div>
                <div class="sg-strength-bar"><div class="sg-strength-fill" id="sgStrength"></div></div>
            </div>

            <div class="sg-field">
                <label class="sg-label">Confirm Password</label>
                <div class="sg-pwd-wrap">
                    <input type="password" name="confirm_password" id="sgPwd2" class="sg-input"
                           placeholder="Repeat password" required style="padding-right:44px;">
                    <button type="button" class="sg-pwd-toggle" onclick="togglePwd('sgPwd2','sgEye2')">
                        <i class="fas fa-eye" id="sgEye2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="sg-submit">
                <i class="fas fa-user-plus" style="margin-right:8px;"></i> Create Account
            </button>
        </form>

        <div class="sg-divider"><span></span><em>already a member?</em><span></span></div>

        <div class="sg-footer">
            <a href="login.php">Sign in to your account</a>
        </div>
    </div>
</div>

<script>
function togglePwd(id, eyeId) {
    var inp = document.getElementById(id);
    var eye = document.getElementById(eyeId);
    if (inp.type === 'password') {
        inp.type = 'text'; eye.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password'; eye.className = 'fas fa-eye';
    }
}

function checkStrength(val) {
    var fill = document.getElementById('sgStrength');
    var strength = 0;
    if (val.length >= 6) strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    var colors = ['', '#ef4444','#f97316','#eab308','#22c55e','#10b981'];
    var widths = ['0%','20%','40%','60%','80%','100%'];
    fill.style.width   = widths[strength];
    fill.style.background = colors[strength];
}
</script>

<?php include 'includes/footer.php'; ?>

<style>
/* ════════════════════════════════════════════════════════════
   FOOTER — PREMIUM REDESIGN
   ════════════════════════════════════════════════════════════ */

.site-footer {
    background: #04090f;
    color: #94a3b8;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    font-size: 14px;
    position: relative;
    overflow: hidden;
}

/* Decorative top border */
.site-footer::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(212,175,55,0.3) 15%,
        #d4af37 35%,
        #facc15 50%,
        #d4af37 65%,
        rgba(212,175,55,0.3) 85%,
        transparent 100%);
}

/* Subtle radial glow at top-center */
.site-footer::after {
    content: '';
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 700px; height: 300px;
    background: radial-gradient(ellipse, rgba(212,175,55,0.06) 0%, transparent 70%);
    pointer-events: none;
}

/* ── Pre-footer CTA strip ──────────────────────────────────── */
.footer-cta-strip {
    background: linear-gradient(135deg, #0d1b2e 0%, #0a1628 50%, #0d1b2e 100%);
    border-bottom: 1px solid rgba(212,175,55,0.1);
    padding: 36px 32px;
    text-align: center;
    position: relative;
}
.footer-cta-strip::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4af37' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.footer-cta-inner { position: relative; max-width: 580px; margin: 0 auto; }
.footer-cta-strip h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 6px;
    letter-spacing: -0.2px;
}
.footer-cta-strip p {
    color: #64748b;
    font-size: 13.5px;
    margin-bottom: 20px;
}
.footer-cta-form {
    display: flex;
    max-width: 420px;
    margin: 0 auto;
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 50px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: rgba(255,255,255,0.03);
}
.footer-cta-form:focus-within {
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
}
.footer-cta-form input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 12px 20px;
    color: #f1f5f9;
    font-size: 13px;
    font-family: inherit;
}
.footer-cta-form input::placeholder { color: #475569; }
.footer-cta-form button {
    background: linear-gradient(135deg, #d4af37 0%, #facc15 100%);
    border: none;
    padding: 0 22px;
    cursor: pointer;
    color: #020617;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
    transition: opacity 0.2s, transform 0.2s;
    font-family: inherit;
}
.footer-cta-form button:hover { opacity: 0.9; transform: scale(1.02); }

/* ── Main top grid ─────────────────────────────────────────── */
.footer-top {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 56px 48px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 32px 52px;
    position: relative;
    z-index: 1;
}

/* ── Brand column ──────────────────────────────────────────── */
.f-brand {}

.f-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    text-decoration: none;
}
.f-logo-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(212,175,55,0.15) 0%, rgba(212,175,55,0.05) 100%);
    border: 1px solid rgba(212,175,55,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    color: #d4af37;
    flex-shrink: 0;
}
.f-logo-text {
    font-size: 1.3rem;
    font-weight: 800;
    color: #f1f5f9;
    letter-spacing: -0.4px;
}
.f-logo-text span { color: #d4af37; }

.f-tagline {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(212,175,55,0.6);
    margin-bottom: 14px;
}

.f-desc {
    line-height: 1.8;
    margin-bottom: 24px;
    max-width: 280px;
    color: #64748b;
    font-size: 13.5px;
}

/* Stats row */
.f-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 26px;
}
.f-stat {
    display: flex;
    flex-direction: column;
}
.f-stat-num {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: -0.5px;
}
.f-stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #475569;
    margin-top: 1px;
}

.f-divider-mini {
    width: 1px;
    background: rgba(255,255,255,0.07);
    align-self: stretch;
}

/* Social buttons */
.f-socials {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.f-social-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 13.5px;
    transition: color 0.2s, background 0.2s, border-color 0.2s, transform 0.2s, box-shadow 0.2s;
}
.f-social-btn:hover {
    color: #020617;
    background: #d4af37;
    border-color: #d4af37;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(212,175,55,0.35);
}

/* ── Column headings ───────────────────────────────────────── */
.f-head {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.f-head::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, rgba(212,175,55,0.25), transparent);
}

/* ── Link lists ────────────────────────────────────────────── */
.f-links ul {
    list-style: none;
    padding: 0; margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.f-links ul li a {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    text-decoration: none;
    padding: 6px 0;
    font-size: 13.5px;
    transition: color 0.2s, padding-left 0.2s;
    border-radius: 4px;
}
.f-links ul li a .f-link-dot {
    width: 4px; height: 4px;
    border-radius: 50%;
    background: #1e293b;
    flex-shrink: 0;
    transition: background 0.2s, transform 0.2s;
}
.f-links ul li a:hover {
    color: #f1f5f9;
    padding-left: 6px;
}
.f-links ul li a:hover .f-link-dot {
    background: #d4af37;
    transform: scale(1.4);
}

/* ── Contact column ────────────────────────────────────────── */
.f-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}
.f-icon-wrap {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.15);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.f-icon {
    color: #d4af37;
    font-size: 12px;
}
.f-text span {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #334155;
    margin-bottom: 2px;
}
.f-text p {
    color: #cbd5e1;
    font-size: 13.5px;
    margin: 0;
}

/* ── Quote strip ───────────────────────────────────────────── */
.quote-slider-container {
    background: linear-gradient(90deg, #030c1a 0%, #040f1f 50%, #030c1a 100%);
    border-top: 1px solid rgba(255,255,255,0.04);
    border-bottom: 1px solid rgba(255,255,255,0.04);
    overflow: hidden;
    padding: 0;
    white-space: nowrap;
    position: relative;
}
.quote-slider-container::before,
.quote-slider-container::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0;
    width: 100px;
    z-index: 2;
    pointer-events: none;
}
.quote-slider-container::before {
    left: 0;
    background: linear-gradient(to right, #030c1a, transparent);
}
.quote-slider-container::after {
    right: 0;
    background: linear-gradient(to left, #030c1a, transparent);
}
.quote-slide-track {
    display: flex;
    align-items: center;
    height: 40px;
    animation: footer-scroll 45s linear infinite;
}
.quote-slide {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    color: #334155;
    padding: 0 60px;
    white-space: nowrap;
    flex-shrink: 0;
}
.quote-slide i {
    color: rgba(212,175,55,0.4);
    font-size: 11px;
}
.quote-slide strong { color: rgba(212,175,55,0.6); font-style: normal; }
@keyframes footer-scroll {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}

/* ── Bottom bar ────────────────────────────────────────────── */
.footer-bottom-wrap {
    border-top: 1px solid rgba(255,255,255,0.05);
    background: rgba(0,0,0,0.2);
}
.footer-bottom {
    padding: 20px 32px;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
}
.footer-bottom-left {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #334155;
}
.footer-bottom-left a { color: #d4af37; text-decoration: none; transition: color 0.2s; }
.footer-bottom-left a:hover { color: #facc15; }

.footer-bottom-links {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.footer-bottom-links a {
    font-size: 11.5px;
    color: #334155;
    text-decoration: none;
    transition: color 0.2s;
}
.footer-bottom-links a:hover { color: #94a3b8; }

/* Back to top */
.back-to-top {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.2);
    color: #d4af37;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s, border-color 0.2s, transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
}
.back-to-top:hover {
    background: rgba(212,175,55,0.18);
    border-color: #d4af37;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(212,175,55,0.25);
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .footer-top { grid-template-columns: 1fr 1fr; gap: 40px 36px; }
}
@media (max-width: 640px) {
    .footer-top { grid-template-columns: 1fr; padding: 40px 20px 36px; gap: 36px; }
    .footer-bottom { padding: 16px 20px; justify-content: center; text-align: center; flex-direction: column; gap: 12px; }
    .footer-cta-strip { padding: 28px 20px; }
    .f-stats { gap: 14px; }
    .footer-bottom-links { justify-content: center; gap: 14px; }
}

/* ── Floating widget ───────────────────────────────────────── */
.floating-widget {
    position: fixed;
    bottom: 28px;
    right: 28px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 9000;
}
.float-btn {
    position: relative;
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.4);
    transition: transform .2s, box-shadow .2s;
}
.float-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.5); }
.btn-login-float { background: #1e293b; color: #f1f5f9; }
.btn-livechat    { background: #d4af37; color: #000; }
.btn-whatsapp    { background: #25d366; color: #fff; }
.float-tooltip {
    position: absolute;
    right: calc(100% + 10px); top: 50%;
    transform: translateY(-50%);
    background: #0f172a;
    color: #f1f5f9;
    font-size: 11.5px;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity .2s;
    border: 1px solid #1e293b;
}
.float-tooltip::after {
    content: '';
    position: absolute;
    left: 100%; top: 50%;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-left-color: #1e293b;
}
.float-btn:hover .float-tooltip { opacity: 1; }

/* ── Chat box ──────────────────────────────────────────────── */
#visitor-chat-box {
    position: fixed;
    bottom: 100px; right: 30px;
    width: 350px; height: 450px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 30px rgba(0,0,0,.2);
    z-index: 9999;
    border: 1px solid #e2e8f0;
    flex-direction: column;
    display: none;
}
.vc-header {
    background: #0f172a;
    padding: 15px;
    border-radius: 12px 12px 0 0;
    color: white;
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 700;
}
.vc-header i.fa-headset { color: #d4af37; margin-right: 8px; }
.vc-close { background: none; border: none; color: white; cursor: pointer; font-size: 14px; }
#v-msg-area {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f8fafc;
    display: flex; flex-direction: column; gap: 10px;
    font-size: 13px;
}
.chat-welcome { text-align: center; color: #94a3b8; font-size: 13px; }
.chat-bubble-user {
    align-self: flex-end;
    background: #0f172a; color: white;
    padding: 8px 12px;
    border-radius: 10px 10px 0 10px;
    max-width: 80%; font-size: 13px;
}
.chat-bubble-agent {
    align-self: flex-start;
    background: #e2e8f0; color: #334155;
    padding: 8px 12px;
    border-radius: 10px 10px 10px 0;
    max-width: 80%; font-size: 13px;
}
#v-chat-form {
    padding: 10px;
    border-top: 1px solid #e2e8f0;
    display: flex; gap: 6px;
}
#v-input {
    flex: 1;
    border: 1px solid #e2e8f0;
    border-radius: 6px; outline: none;
    padding: 10px; font-size: 13px; font-family: inherit;
}
#v-input:focus { border-color: #d4af37; }
#v-chat-form button[type=submit] {
    background: #d4af37; border: none;
    width: 40px; border-radius: 6px;
    cursor: pointer; color: #000;
    transition: background .2s;
}
#v-chat-form button[type=submit]:hover { background: #b8942e; }
</style>

<footer class="site-footer">

    <!-- Newsletter CTA strip -->
    <div class="footer-cta-strip">
        <div class="footer-cta-inner">
            <h3>Stay Ahead of the Market</h3>
            <p>Get weekly insights, stock picks, and investment strategies delivered to your inbox.</p>
            <form class="footer-cta-form" onsubmit="return false;">
                <input type="email" placeholder="Enter your email address...">
                <button type="submit"><i class="fas fa-paper-plane"></i>&nbsp; Subscribe</button>
            </form>
        </div>
    </div>

    <div class="footer-top">

        <!-- Brand -->
        <div class="f-brand">
            <a href="index.php" class="f-logo">
                <div class="f-logo-icon"><i class="fas fa-chart-line"></i></div>
                <span class="f-logo-text">Wise<span>Invest</span></span>
            </a>
            <p class="f-tagline">Your Global Investing Gateway</p>
            <p class="f-desc">Empowering global investors with professional-grade tools, education, and community insights to make smarter financial decisions.</p>

            <div class="f-stats">
                <div class="f-stat">
                    <span class="f-stat-num">10K+</span>
                    <span class="f-stat-label">Members</span>
                </div>
                <div class="f-divider-mini"></div>
                <div class="f-stat">
                    <span class="f-stat-num">500+</span>
                    <span class="f-stat-label">Articles</span>
                </div>
                <div class="f-divider-mini"></div>
                <div class="f-stat">
                    <span class="f-stat-num">50+</span>
                    <span class="f-stat-label">Courses</span>
                </div>
            </div>

            <div class="f-socials">
                <a href="#" class="f-social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="f-social-btn" title="Twitter/X"><i class="fab fa-twitter"></i></a>
                <a href="#" class="f-social-btn" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="f-social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://wa.me/660933396350" target="_blank" rel="noopener" class="f-social-btn" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="f-links">
            <h3 class="f-head">Company</h3>
            <ul>
                <li><a href="index.php"><span class="f-link-dot"></span>Home</a></li>
                <li><a href="about.php"><span class="f-link-dot"></span>About Us</a></li>
                <li><a href="services.php"><span class="f-link-dot"></span>Services</a></li>
                <li><a href="community.php"><span class="f-link-dot"></span>Community</a></li>
                <li><a href="contact.php"><span class="f-link-dot"></span>Contact Us</a></li>
                <li><a href="vacancy.php"><span class="f-link-dot"></span>Careers</a></li>
            </ul>
        </div>

        <!-- Learning -->
        <div class="f-links">
            <h3 class="f-head">Learning</h3>
            <ul>
                <li><a href="classes.php"><span class="f-link-dot"></span>Master Classes</a></li>
                <li><a href="videos.php"><span class="f-link-dot"></span>Video Library</a></li>
                <li><a href="articles.php"><span class="f-link-dot"></span>Market News</a></li>
                <li><a href="calculators.php"><span class="f-link-dot"></span>Calculators</a></li>
                <li><a href="glossary.php"><span class="f-link-dot"></span>Glossary</a></li>
                <li><a href="guides.php"><span class="f-link-dot"></span>Guides</a></li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="f-contact">
            <h3 class="f-head">Contact</h3>

            <div class="f-contact-item">
                <div class="f-icon-wrap"><i class="fas fa-map-marker-alt f-icon"></i></div>
                <div class="f-text">
                    <span>Office</span>
                    <p>Chiang Mai, Thailand</p>
                </div>
            </div>

            <div class="f-contact-item">
                <div class="f-icon-wrap"><i class="fas fa-envelope f-icon"></i></div>
                <div class="f-text">
                    <span>Email</span>
                    <p>support@wiseinvest.com</p>
                </div>
            </div>

            <div class="f-contact-item">
                <div class="f-icon-wrap"><i class="fab fa-whatsapp f-icon"></i></div>
                <div class="f-text">
                    <span>WhatsApp</span>
                    <p>+66 09 3339 6350</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Quotes ticker -->
    <div class="quote-slider-container" id="quote-slider">
        <div class="quote-slide-track">
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Price is what you pay. Value is what you get." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Risk comes from not knowing what you're doing." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "In investing, what is comfortable is rarely profitable." &mdash; <strong>Robert Arnott</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "The stock market is a device to transfer money from the impatient to the patient." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "An investment in knowledge pays the best interest." &mdash; <strong>Benjamin Franklin</strong></span>
            <!-- Duplicate for seamless loop -->
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Price is what you pay. Value is what you get." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Risk comes from not knowing what you're doing." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "In investing, what is comfortable is rarely profitable." &mdash; <strong>Robert Arnott</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "The stock market is a device to transfer money from the impatient to the patient." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "An investment in knowledge pays the best interest." &mdash; <strong>Benjamin Franklin</strong></span>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="footer-bottom-wrap">
        <div class="footer-bottom">
            <div class="footer-bottom-left">
                <i class="fas fa-shield-alt" style="color:rgba(212,175,55,0.4);font-size:11px;"></i>
                &copy; <?php echo date('Y'); ?> <a href="index.php">Wise Invest</a>. All Rights Reserved.
            </div>
            <nav class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
                <a href="contact.php">Support</a>
            </nav>
            <a href="#" class="back-to-top" title="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">
                <i class="fas fa-arrow-up"></i>
            </a>
        </div>
    </div>

</footer>

<!-- Floating widget -->
<div class="floating-widget">
    <a href="login.php" class="float-btn btn-login-float">
        <i class="fas fa-user"></i>
        <span class="float-tooltip">Log In</span>
    </a>
    <button onclick="toggleChatBox()" class="float-btn btn-livechat">
        <i class="fas fa-comments"></i>
        <span class="float-tooltip">Live Support</span>
    </button>
    <a href="https://wa.me/660933396350" target="_blank" rel="noopener" class="float-btn btn-whatsapp">
        <i class="fab fa-whatsapp"></i>
        <span class="float-tooltip">WhatsApp Us</span>
    </a>
</div>

<!-- Chat box -->
<div id="visitor-chat-box">
    <div class="vc-header">
        <span><i class="fas fa-headset"></i> Support</span>
        <button class="vc-close" onclick="toggleChatBox()"><i class="fas fa-times"></i></button>
    </div>
    <div id="v-msg-area">
        <div class="chat-welcome">Welcome! How can we help you today?</div>
    </div>
    <form id="v-chat-form">
        <input type="text" id="v-input" placeholder="Type..." autocomplete="off">
        <button type="submit"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>

<?php if (basename($_SERVER['PHP_SELF']) !== 'calculators.php'): ?>
<script src="<?php echo $root; ?>/main.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

<script>
function toggleChatBox() {
    const box = document.getElementById('visitor-chat-box');
    const open = box.style.display !== 'flex';
    box.style.display = open ? 'flex' : 'none';
    if (open) fetchVisitorMessages();
}

function fetchVisitorMessages() {
    fetch('support_api.php?action=fetch')
        .then(r => r.json())
        .then(msgs => {
            const area = document.getElementById('v-msg-area');
            if (!msgs.length) return;
            area.innerHTML = '';
            msgs.forEach(m => {
                const d = document.createElement('div');
                d.className = m.sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-agent';
                d.textContent = m.msg;
                area.appendChild(d);
            });
            area.scrollTop = area.scrollHeight;
        })
        .catch(() => {});
}

document.getElementById('v-chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const inp = document.getElementById('v-input');
    const txt = inp.value.trim();
    if (!txt) return;
    const fd = new FormData();
    fd.append('message', txt);
    fetch('support_api.php?action=send', { method: 'POST', body: fd })
        .then(() => { inp.value = ''; fetchVisitorMessages(); })
        .catch(() => {});
});

setInterval(() => {
    if (document.getElementById('visitor-chat-box').style.display === 'flex') fetchVisitorMessages();
}, 3000);
</script>

</body>
</html>

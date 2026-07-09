<?php
require_once 'db_config.php';

// Initialize variables
$error = '';
$success = '';

// Handle form submission and Razorpay Order Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        $error = "All fields are required.";
    } else {
        // Fetch Razorpay Keys from DB
        $keyId = getSetting('razorpay_key', $conn) ?: RAZORPAY_KEY_ID;
        $keySecret = getSetting('razorpay_secret', $conn) ?: RAZORPAY_KEY_SECRET;
        
        // Amount in paise (900 INR = 90000 paise)
        $amount = 90000; 
        $receipt = 'msg_' . time();
        
        // Create Razorpay Order via cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => $receipt,
            'payment_capture' => 1
        ]));
        curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $order = json_decode($response, true);
        curl_close($ch);
        
        if (isset($order['id'])) {
            $razorpay_order_id = $order['id'];
            
            // Generate a legacy_id for the user
            $legacy_id = 'MTK' . strtoupper(substr(md5(time()), 0, 6));
            
            // Insert User into DB (Pending state)
            $stmt = $conn->prepare("INSERT INTO users (legacy_id, name, email, mobile, address, payment_status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sssss", $legacy_id, $name, $email, $phone, $address);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Insert Payment Record
                $stmt_pay = $conn->prepare("INSERT INTO payments (user_id, razorpay_order_id, amount, status) VALUES (?, ?, ?, 'created')");
                $pay_amount = 900.00;
                $stmt_pay->bind_param("isd", $user_id, $razorpay_order_id, $pay_amount);
                $stmt_pay->execute();
                
                // Success - Prepare data for frontend
                $checkout_data = [
                    'key' => $keyId,
                    'amount' => $amount,
                    'order_id' => $razorpay_order_id,
                    'name' => 'MAATKA Premium',
                    'description' => 'Digital Membership Onboarding',
                    'prefill' => [
                        'name' => $name,
                        'email' => $email,
                        'contact' => $phone
                    ],
                    'notes' => [
                        'user_id' => $user_id
                    ]
                ];
                $show_checkout = true;
            } else {
                $error = "Error saving data: " . $conn->error;
            }
        } else {
            $error = "Failed to initiate payment. Please try again later. " . ($order['error']['description'] ?? '');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join MAATKA | Elite Membership Onboarding</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --black-primary: #0a0a0a;
            --black-secondary: #0f0f0f;
            --black-card: rgba(15, 15, 15, 0.95);
            --gold-primary: #FFD700;
            --gold-secondary: #F4B400;
            --gold-accent: #FFC107;
            --gold-gradient: linear-gradient(135deg, #FFD700 0%, #F4B400 50%, #FFC107 100%);
            --gold-glass: rgba(255, 215, 0, 0.12);
            --text-light: #FFFFFF;
            --text-gray: #B0B0B0;
            --transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-border: rgba(255, 215, 0, 0.18);
            --shadow-glow: 0 8px 32px rgba(255, 215, 0, 0.15);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; overflow-x: hidden; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--black-primary);
            color: var(--text-light);
            line-height: 1.6;
            background: radial-gradient(circle at 20% 80%, rgba(255, 215, 0, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 80% 20%, rgba(255, 215, 0, 0.03) 0%, transparent 40%),
                        #0a0a0a;
            min-height: 100vh;
        }

        h1, h2, h3 { font-family: 'Clash Display', sans-serif; }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Background Animation */
        .bg-animation {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1; overflow: hidden;
        }
        .gold-grid {
            position: absolute; width: 200%; height: 200%;
            background-image: linear-gradient(rgba(255, 215, 0, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 215, 0, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridMove 100s linear infinite;
        }
        @keyframes gridMove { from { transform: translate(0,0); } to { transform: translate(-60px, -60px); } }

        .orbital-ring {
            position: absolute;
            border: 1px solid rgba(255, 215, 0, 0.08);
            border-radius: 50%;
            animation: rotateOrbit 80s infinite linear;
        }
        @keyframes rotateOrbit { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Navbar */
        .navbar {
            padding: 24px 0; border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            position: fixed; top: 0; width: 100%; z-index: 1000;
            background: rgba(10, 10, 10, 0.85); backdrop-filter: blur(20px);
        }
        .nav-content { display: flex; justify-content: space-between; align-items: center; }
        .logo {
            font-size: 28px; font-weight: 700; background: var(--gold-gradient);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            text-decoration: none; display: flex; align-items: center; gap: 8px;
        }

        /* Hero */
        .join-hero { padding: 160px 0 60px; text-align: center; }
        .join-hero h1 { font-size: 3.5rem; margin-bottom: 16px; background: var(--gold-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .join-hero p { color: var(--text-gray); font-size: 1.1rem; max-width: 600px; margin: 0 auto; }

        /* Summary Card */
        .summary-card {
            background: var(--black-card); border: 1px solid var(--glass-border);
            border-radius: 24px; padding: 40px; margin-bottom: 40px;
            box-shadow: var(--shadow-glow); position: relative; overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }
        .summary-title { text-align: center; color: var(--gold-primary); margin-bottom: 30px; letter-spacing: 2px; text-transform: uppercase; font-size: 0.9rem; font-family: 'Space Grotesk', sans-serif; }
        
        .benefits-vertical { display: flex; flex-direction: column; gap: 16px; margin-bottom: 40px; }
        .benefit-inline {
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 215, 0, 0.1);
            border-radius: 16px; padding: 20px 24px; display: flex; align-items: center; gap: 20px;
            transition: var(--transition);
        }
        .benefit-inline:hover { background: rgba(255, 215, 0, 0.05); border-color: var(--gold-primary); }
        .benefit-inline i { font-size: 24px; color: var(--gold-primary); width: 40px; text-align: center; }
        .benefit-inline h4 { font-size: 1.1rem; font-weight: 500; flex-grow: 1; }

        .price-display { text-align: center; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 30px; }
        .price-label { color: var(--text-gray); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .price-value { font-size: 3rem; font-weight: 700; color: var(--gold-primary); font-family: 'Clash Display', sans-serif; }

        /* Form Card */
        .form-card {
            background: var(--black-card); border: 1px solid var(--glass-border);
            border-radius: 24px; padding: 50px; position: relative; overflow: hidden;
            box-shadow: var(--shadow-glow); animation: slideUp 1s ease-out;
        }
        .form-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--gold-gradient); }
        .form-header { margin-bottom: 40px; text-align: center; }
        .form-header h2 { font-size: 2rem; margin-bottom: 10px; }
        .form-header p { color: var(--text-gray); font-size: 0.9rem; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .form-group { margin-bottom: 24px; }
        .form-group.full { grid-column: span 2; }
        .form-group label { display: block; margin-bottom: 10px; color: var(--gold-primary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-control {
            width: 100%; padding: 16px 20px; background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 215, 0, 0.15); border-radius: 12px; color: white;
            font-family: inherit; transition: var(--transition);
        }
        .input-control:focus { outline: none; border-color: var(--gold-primary); background: rgba(255, 215, 0, 0.05); }

        .checkbox-row { display: flex; align-items: flex-start; gap: 12px; margin-top: 20px; }
        .checkbox-row input { width: 18px; height: 18px; accent-color: var(--gold-primary); margin-top: 3px; cursor: pointer; }
        .checkbox-row label { font-size: 0.9rem; color: var(--text-gray); cursor: pointer; }
        .checkbox-row a { color: var(--gold-primary); text-decoration: none; }

        .submit-btn {
            width: 100%; padding: 20px; margin-top: 40px;
            background: var(--gold-gradient); color: var(--black-primary);
            border: none; border-radius: 12px; font-weight: 700; font-size: 1.1rem;
            cursor: pointer; transition: var(--transition); box-shadow: var(--shadow-glow);
            text-transform: uppercase; letter-spacing: 1px;
        }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3); }

        /* Trust Bar */
        .trust-bar { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin: 80px 0; }
        .trust-item { text-align: center; }
        .trust-icon { width: 60px; height: 60px; background: rgba(255, 215, 0, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; border: 1px solid rgba(255, 215, 0, 0.2); }
        .trust-icon i { color: var(--gold-primary); font-size: 24px; }
        .trust-item h5 { font-size: 1rem; margin-bottom: 5px; color: var(--text-light); }
        .trust-item p { font-size: 0.8rem; color: var(--text-gray); }

        /* FAQ */
        .faq-section { padding: 80px 0; }
        .faq-title { text-align: center; font-size: 2.5rem; margin-bottom: 50px; background: var(--gold-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .faq-list { max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px; }
        .faq-item { background: var(--black-card); border: 1px solid var(--glass-border); border-radius: 16px; padding: 24px; cursor: pointer; transition: var(--transition); }
        .faq-item:hover { border-color: var(--gold-primary); }
        .faq-q { display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 1.05rem; }
        .faq-a { max-height: 0; overflow: hidden; transition: 0.5s ease; color: var(--text-gray); font-size: 0.95rem; line-height: 1.8; }
        .faq-item.active .faq-a { max-height: 300px; padding-top: 15px; }
        .faq-item.active i { transform: rotate(180deg); color: var(--gold-primary); }

        /* Footer */
        .footer { padding: 100px 0 40px; background: #070707; border-top: 1px solid rgba(255,255,255,0.05); }
        .footer-grid { display: grid; grid-template-columns: 1.5fr repeat(3, 1fr); gap: 60px; margin-bottom: 80px; }
        .f-logo { font-size: 32px; font-weight: 700; background: var(--gold-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 24px; display: inline-block; }
        .f-desc { color: var(--text-gray); font-size: 0.95rem; margin-bottom: 30px; }
        .f-col h4 { margin-bottom: 30px; color: var(--gold-primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; }
        .f-links { list-style: none; }
        .f-links li { margin-bottom: 12px; }
        .f-links a { color: var(--text-gray); text-decoration: none; transition: 0.3s; font-size: 0.9rem; }
        .f-links a:hover { color: white; padding-left: 5px; }
        .f-bottom { border-top: 1px solid rgba(255,255,255,0.05); padding-top: 40px; text-align: center; color: var(--text-gray); font-size: 0.8rem; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 992px) {
            .trust-bar { grid-template-columns: 1fr 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
            .join-hero h1 { font-size: 2.5rem; }
            .trust-bar { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="bg-animation">
        <div class="gold-grid"></div>
        <div class="orbital-ring" style="width: 80vw; height: 80vw; top: -30vw; left: -30vw; animation-duration: 120s;"></div>
        <div class="orbital-ring" style="width: 60vw; height: 60vw; bottom: -20vw; right: -20vw; animation-duration: 80s; animation-direction: reverse;"></div>
    </div>

    <nav class="navbar">
        <div class="container nav-content">
            <a href="index.html" class="logo">MAATKA</a>
            <div class="nav-actions">
                <a href="index.html" class="btn-secondary" style="padding: 10px 20px; text-decoration: none; border-radius: 8px; font-size: 0.85rem;">BACK TO HOME</a>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <header class="join-hero">
            <h1>Join MAATKA</h1>
            <p>Begin your journey with a simple one-step verification and unlock premium digital assets today.</p>
        </header>

        <section class="summary-card">
            <div class="summary-title">What You'll Receive</div>
            <div class="benefits-vertical">
                <div class="benefit-inline">
                    <i class="fas fa-gem"></i>
                    <h4>100 KRITIKA Credits</h4>
                </div>
                <div class="benefit-inline">
                    <i class="fas fa-coins"></i>
                    <h4>1 Sudarshana Coin</h4>
                </div>
                <div class="benefit-inline">
                    <i class="fas fa-id-card"></i>
                    <h4>1 Unique Legacy ID</h4>
                </div>
            </div>
            <div class="price-display">
                <div class="price-label">One-Time Membership Fee</div>
                <div class="price-value">₹900</div>
            </div>
        </section>

        <section class="form-card">
            <div class="form-header">
                <h2>Complete Your Details</h2>
                <p>Personalize your membership card and verify your digital identity.</p>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(255, 50, 50, 0.1); border: 1px solid rgba(255, 50, 50, 0.3); color: #ff8080; padding: 15px; border-radius: 12px; margin-bottom: 24px; font-size: 0.9rem;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="input-control" placeholder="Enter your full name" required value="<?php echo $_POST['name'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="input-control" placeholder="john@example.com" required value="<?php echo $_POST['email'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="tel" name="phone" class="input-control" placeholder="+91 XXXXX XXXXX" required value="<?php echo $_POST['phone'] ?? ''; ?>">
                    </div>

                    <div class="form-group full">
                        <label>Postal Address *</label>
                        <textarea name="address" class="input-control" rows="3" placeholder="Enter your full residential address for identity verification" required><?php echo $_POST['address'] ?? ''; ?></textarea>
                    </div>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a> of MAATKA Membership operations.</label>
                </div>

                <button type="submit" name="submit_form" class="submit-btn" id="pay-btn">
                    Proceed to Payment
                </button>

                <p style="text-align: center; margin-top: 24px; color: var(--text-gray); font-size: 0.8rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-shield-alt" style="color: var(--gold-primary);"></i>
                    Payments are secured and encrypted via Razorpay
                </p>
            </form>
        </section>

        <section class="trust-bar">
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-lock"></i></div>
                <h5>100% Secure</h5>
                <p>Institutional grade encryption</p>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-bolt"></i></div>
                <h5>Instant Benefits</h5>
                <p>Credits available immediately</p>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-envelope"></i></div>
                <h5>Email Confirmation</h5>
                <p>Digital receipt on completion</p>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="fas fa-globe"></i></div>
                <h5>Trusted Gateway</h5>
                <p>Powered by Razorpay India</p>
            </div>
        </section>

        <section class="faq-section">
            <h2 class="faq-title">Questions Before Joining?</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-q">How long does the process take? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-a">The entire onboarding process takes less than 2 minutes. Once the form is filled and payment is successful, your benefits are activated instantly.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">What payment methods are accepted? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-a">We accept all major payment methods including UPI (Google Pay, PhonePe), Credit/Debit Cards (Visa, Mastercard, RuPay), Net Banking, and Digital Wallets via the secure Razorpay gateway.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">When will I receive my benefits? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-a">Your KRITIKA Credits and Sudarshana Coin are credited to your digital wallet instantly upon successful transaction. Your Unique Legacy ID is generated and sent via email within minutes.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Is my payment information safe? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-a">Yes. MAATKA does not store your card or bank details. All payments are processed through Razorpay's PCI-DSS compliant infrastructure using 256-bit AES encryption.</div>
                </div>
            </div>
        </section>
    </div>

    <footer class="footer">
        <div class="container footer-grid">
            <div class="f-brand">
                <div class="f-logo">MAATKA</div>
                <p class="f-desc">The future of premium digital membership. Institutional-grade security meets radical transparency for our distinguished community.</p>
                <div style="display: flex; gap: 15px;">
                    <a href="#" style="color: var(--text-gray); font-size: 1.2rem;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: var(--text-gray); font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: var(--text-gray); font-size: 1.2rem;"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="f-col">
                <h4>Platform</h4>
                <ul class="f-links">
                    <li><a href="index.html">Home</a></li>
                    <li><a href="how-it-works.html">How It Works</a></li>
                    <li><a href="benefits.html">Benefits</a></li>
                    <li><a href="trust.html">Trust & Security</a></li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Legal</h4>
                <ul class="f-links">
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Refund Policy</a></li>
                    <li><a href="#">Compliance</a></li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Support</h4>
                <ul class="f-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="mailto:support@maatka.com">support@maatka.com</a></li>
                </ul>
            </div>
        </div>
        <div class="f-bottom">
            &copy; 2026 MAATKA Operations. All rights reserved.
        </div>
    </footer>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        // FAQ Accordion
        document.querySelectorAll('.faq-item').forEach(item => {
            item.addEventListener('click', () => {
                const wasActive = item.classList.contains('active');
                document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
                if (!wasActive) item.classList.add('active');
            });
        });

        // Razorpay Trigger
        <?php if (isset($show_checkout) && $show_checkout): ?>
        var options = <?php echo json_encode($checkout_data); ?>;
        options.handler = function (response){
            window.location.href = 'payment_success.php?oid=' + response.razorpay_order_id + '&pid=' + response.razorpay_payment_id + '&sig=' + response.razorpay_signature;
        };
        options.modal = { "ondismiss": function(){ console.log('Checkout closed'); } };
        options.theme = { "color": "#FFD700" };
        var rzp1 = new Razorpay(options);
        rzp1.open();
        <?php endif; ?>
    </script>
</body>
</html>

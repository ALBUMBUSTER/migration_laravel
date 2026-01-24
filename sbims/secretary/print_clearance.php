<?php
require_once '../config/auth.php';
require_once '../config/connection.php';
Auth::checkAuth();
Auth::checkRole(['secretary']);

$cert_id = $_GET['id'] ?? 0;

if (!$cert_id) {
    die("Certificate not found.");
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
            c.id, 
            c.certificate_id, 
            c.certificate_type, 
            c.purpose, 
            c.status, 
            c.created_at,
            c.issued_date,
            r.first_name, 
            r.last_name, 
            r.address, 
            r.purok,
            r.gender,
            r.civil_status,
            r.birthdate,
            u1.full_name as issued_by_name, 
            u2.full_name as approved_by_name
          FROM certificates c 
          JOIN residents r ON c.resident_id = r.id 
          LEFT JOIN users u1 ON c.issued_by = u1.id 
          LEFT JOIN users u2 ON c.approved_by = u2.id
          WHERE c.id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$cert_id]);
$certificate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certificate) {
    die("Certificate not found.");
}

// Calculate age
$birthdate = new DateTime($certificate['birthdate']);
$today = new DateTime();
$age = $today->diff($birthdate)->y;

// Format dates
$issued_date = $certificate['issued_date'] ?? $certificate['created_at'];
$display_date = date('F j, Y', strtotime($issued_date));
$day_suffix = date('jS', strtotime($issued_date));
$valid_until = date('F j, Y', strtotime($issued_date . ' + 6 months'));

// Add headers to prevent browser URL display
header("Content-Type: text/html; charset=utf-8");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Clearance - <?php echo htmlspecialchars($certificate['certificate_id']); ?></title>
    <style>
        /* RESET EVERYTHING for print */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* HIDE EVERYTHING when printing except certificate */
        @media print {
            /* Hide browser headers and footers */
            @page {
                margin: 0 !important;
                size: letter portrait !important;
            }
            
            /* Hide all body content */
            body * {
                visibility: hidden;
                display: none;
            }
            
            /* Only show certificate container */
            .certificate-container,
            .certificate-container * {
                visibility: visible !important;
                display: block !important;
            }
            
            .certificate-container {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            
            /* Force print background colors */
            /* -webkit-print-color-adjust: economy;
            print-color-adjust: economy; */
        }

        /* Screen styles */
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f5f5f5;
            padding: 20px;
            margin: 0;
        }

        .certificate-container {
            width: 8.5in;
            height: 11in;
            margin: 0 auto;
            padding: 60px;
            background: white;
            position: relative;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .certificate-number {
            position: absolute;
            top: 30px;
            right: 60px;
            font-size: 14px;
            color: #666;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 22px;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: #006600;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 18px;
            margin-bottom: 4px;
            color: #006600;
        }

        .header h3 {
            font-size: 16px;
            font-style: italic;
            color: #006600;
        }

        .certificate-title {
            text-align: center;
            font-size: 32px;
            text-transform: uppercase;
            margin: 50px 0;
            font-weight: bold;
            text-decoration: underline;
            color: #000;
            letter-spacing: 2px;
        }

        .content {
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 50px;
            text-align: justify;
        }

        .resident-info {
            background: #f9fff9;
            padding: 25px;
            margin: 25px 0;
            border-left: 5px solid #006600;
            border-radius: 0 8px 8px 0;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.05);
        }

        .signatures {
            margin-top: 100px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-line {
            border-top: 2px solid #000;
            margin: 60px 0 10px;
            width: 100%;
        }

        .official-name {
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .official-position {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .valid-until {
            text-align: center;
            margin: 25px 0;
            font-weight: bold;
            color: #006600;
            font-size: 16px;
            padding: 10px;
            background: #f9fff9;
            border-radius: 5px;
        }

        .barangay-seal {
            position: absolute;
            top: 150px;
            right: 80px;
            width: 140px;
            height: 140px;
            border: 3px solid #006600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #006600;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .barangay-seal-inner {
            border: 2px solid #006600;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        /* Print buttons (only visible on screen) */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            display: flex;
            gap: 10px;
        }

        .print-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #006600;
            color: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #004400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .print-btn.secondary {
            background: #666;
        }

        .print-btn.secondary:hover {
            background: #444;
        }
    </style>
</head>
<body>
    <!-- Print buttons - will NOT appear in print -->
    <div class="print-actions" style="display: none;">
        <button class="print-btn" onclick="cleanPrint()">🖨️ Print Clean Copy</button>
        <button class="print-btn secondary" onclick="window.close()">Close Window</button>
    </div>

    <div class="certificate-container">
        <div class="certificate-number">
            <strong>Certificate No:</strong> <?php echo htmlspecialchars($certificate['certificate_id']); ?>
        </div>
        
        <!-- Barangay Seal -->
        <div class="barangay-seal">
            <div class="barangay-seal-inner">
                <div style="font-size: 16px; font-weight: bold;">OFFICIAL SEAL</div>
                <div style="margin: 5px 0; font-size: 14px;">BARANGAY</div>
                <div style="font-size: 18px; font-weight: bold;">LIBERTAD</div>
                <div style="font-size: 12px; margin-top: 5px;">ISABEL, LEYTE</div>
            </div>
        </div>
        
        <div class="header">
            <h1>Republic of the Philippines</h1>
            <h2>Province of Leyte</h2>
            <h2>Municipality of Isabel</h2>
            <h3>Barangay Libertad</h3>
        </div>
        
        <div class="certificate-title">
            Barangay Clearance
        </div>
        
        <div class="content">
            <p style="text-align: center; font-weight: bold; margin-bottom: 25px; font-size: 20px;">TO WHOM IT MAY CONCERN:</p>
            
            <div class="resident-info">
                <p>This is to certify that <strong style="text-transform: uppercase;"><?php echo htmlspecialchars($certificate['first_name'] . ' ' . $certificate['last_name']); ?></strong>, 
                <?php echo $age; ?> years of age, <?php echo htmlspecialchars($certificate['gender']); ?>, 
                <?php echo htmlspecialchars($certificate['civil_status']); ?>, 
                Filipino citizen, and a bona fide resident of 
                <?php echo htmlspecialchars($certificate['purok']); ?>, Barangay Libertad, Municipality of Isabel, Province of Leyte.</p>
            </div>
            
            <p>This further certifies that the above-named person is known to be a person of good moral character and has no derogatory records on file in this barangay.</p>
            
            <p>This certification is issued upon the request of the above-named person for <strong><?php echo htmlspecialchars($certificate['purpose']); ?></strong> and for whatever legal purpose it may serve.</p>
            
            <p style="margin-top: 25px;">Issued this <strong><?php echo $day_suffix; ?> day of <?php echo date('F Y', strtotime($issued_date)); ?></strong> at Barangay Libertad, Isabel, Leyte.</p>
        </div>
        
        <div class="valid-until">
            Valid until: <strong><?php echo $valid_until; ?></strong>
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="official-name">JUAN DELA CRUZ</div>
                <div class="official-position">Barangay Captain</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="official-name">MARIA SANTOS</div>
                <div class="official-position">Barangay Secretary</div>
            </div>
        </div>
        
        <div class="footer">
            <p><em>Note: This document is not valid without the official dry seal of the barangay.</em></p>
            <p>Date Issued: <?php echo $display_date; ?></p>
            <p><strong>OR Number:</strong> ___________ <strong>Amount Paid:</strong> ₱ 50.00</p>
        </div>
    </div>

    <script>
        // Function for clean printing without browser headers
        function cleanPrint() {
            // Show print buttons before printing
            document.querySelector('.print-actions').style.display = 'none';
            
            // Print the document
            window.print();
            
            // Show buttons again after printing
            setTimeout(() => {
                document.querySelector('.print-actions').style.display = 'flex';
            }, 1000);
        }
        
        // On page load
        window.onload = function() {
            // Show print buttons
            document.querySelector('.print-actions').style.display = 'flex';
            
            // Change document title to remove URL
            document.title = "Barangay Clearance Certificate - <?php echo htmlspecialchars($certificate['certificate_id']); ?>";
            
            // Check if we should auto-print
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoprint') === '1') {
                setTimeout(() => {
                    cleanPrint();
                }, 1000);
            }
            
            // Add message about Chrome settings
            console.log("To remove URL from print: In Chrome print dialog → More settings → UNCHECK 'Headers and footers' → SET Margins to 'None'");
        };
        
        // Add key shortcut for printing (Ctrl+P)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                cleanPrint();
            }
        });
    </script>
</body>
</html>
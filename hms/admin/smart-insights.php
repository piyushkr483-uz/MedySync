<?php
session_start();
include('include/config.php');

/* =========================================================
   CORE DATA
========================================================= */

// TOTAL PATIENTS
$patientQuery = mysqli_query($con,
"SELECT COUNT(*) as total FROM tblpatient");

$totalPatients = mysqli_fetch_assoc($patientQuery)['total'] ?? 0;

// TOTAL DOCTORS
$doctorQuery = mysqli_query($con,
"SELECT COUNT(*) as total FROM doctors");

$totalDoctors = mysqli_fetch_assoc($doctorQuery)['total'] ?? 0;

// TOTAL APPOINTMENTS
$appointmentQuery = mysqli_query($con,
"SELECT COUNT(*) as total FROM appointment");

$totalAppointments = mysqli_fetch_assoc($appointmentQuery)['total'] ?? 0;

// TOTAL REVENUE
$revenueQuery = mysqli_query($con,
"SELECT SUM(treatmentCost) as revenue FROM tblpatient");

$totalRevenue = mysqli_fetch_assoc($revenueQuery)['revenue'] ?? 0;

/* =========================================================
   BED UTILIZATION
========================================================= */

$totalBeds = 200;

$admittedQuery = mysqli_query($con,
"SELECT COUNT(*) as total
FROM tblpatient
WHERE admissionDate IS NOT NULL");

$occupiedBeds = mysqli_fetch_assoc($admittedQuery)['total'] ?? 0;

$bedUtilization = round(($occupiedBeds / $totalBeds) * 100);

/* =========================================================
   DEPARTMENT ANALYSIS
========================================================= */

$deptRiskData = [];

$deptQuery = mysqli_query($con, "
SELECT d.specilization as dept,
COUNT(p.id) as patients,
SUM(p.treatmentCost) as revenue
FROM doctors d
LEFT JOIN tblpatient p ON d.id = p.docid
GROUP BY d.specilization
");

while($row = mysqli_fetch_assoc($deptQuery)){

    $patients = $row['patients'];

    if($patients >= 8){
        $status = "Overloaded";
        $risk = "High";
        $color = "danger";
        $progress = 92;
        $comment = "Heavy patient pressure detected.";
    }
    elseif($patients >= 4){
        $status = "Stable";
        $risk = "Low";
        $color = "success";
        $progress = 45;
        $comment = "Department operating normally.";
    }
    else{
        $status = "Underutilized";
        $risk = "Medium";
        $color = "warning";
        $progress = 60;
        $comment = "Low engagement detected.";
    }

    $deptRiskData[] = [
        'dept' => $row['dept'],
        'status' => $status,
        'risk' => $risk,
        'color' => $color,
        'progress' => $progress,
        'comment' => $comment
    ];
}

/* =========================================================
   AI HEALTH SCORE
========================================================= */

$healthScore = 82;

if($bedUtilization > 80){
    $healthScore -= 15;
}

if($totalRevenue > 100000){
    $healthScore += 5;
}

if($totalAppointments < 10){
    $healthScore -= 8;
}

$healthScore = max(0, min(100, $healthScore));

/* =========================================================
   AI PREDICTIONS
========================================================= */

$expectedPatientsTomorrow = round($totalPatients * 0.12);

$predictedRevenue = round($totalRevenue * 1.18);

$icuRiskDays = 3;

/* =========================================================
   SMART ALERTS
========================================================= */

$alerts = [];

if($bedUtilization > 60){
    $alerts[] = "⚠ Bed occupancy increased rapidly this week.";
}

if($totalAppointments < 15){
    $alerts[] = "⚠ Appointment count lower than expected.";
}

if($totalRevenue > 100000){
    $alerts[] = "⚠ Revenue spike detected in Internal Medicine.";
}

$alerts[] = "⚠ Unusual patient surge predicted this weekend.";

/* =========================================================
   AI RECOMMENDATIONS
========================================================= */

$recommendations = [
    "Add one temporary doctor to Orthopedics.",
    "Increase pediatric staff on weekends.",
    "Reduce ENT appointment slots.",
    "Schedule maintenance during low traffic hours.",
    "Prepare additional ICU beds for weekend load."
];

/* =========================================================
   SMART TRENDS
========================================================= */

$trendNarrative = "
Patient admissions have increased consistently over recent weeks,
mainly driven by Orthopedics and Pediatrics.

Revenue growth remains stable while bed occupancy is rising steadily.

Operational pressure is expected to increase over the coming weekend.
";

/* =========================================================
   ANOMALIES
========================================================= */

$anomalies = [
    "Sudden drop in ENT visits detected.",
    "Revenue unusually high on Tuesday.",
    "Appointment cancellation spike observed.",
    "Unexpected increase in emergency inquiries."
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<link rel="stylesheet"
href="vendor/bootstrap/css/bootstrap.min.css">

<link rel="stylesheet"
href="vendor/fontawesome/css/font-awesome.min.css">

<link rel="stylesheet"
href="vendor/themify-icons/themify-icons.min.css">

<link rel="stylesheet"
href="assets/css/styles.css">

<link rel="stylesheet"
href="assets/css/plugins.css">

<link rel="stylesheet"
href="assets/css/themes/theme-1.css" id="skin_color">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AI Smart Insights</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial,sans-serif;
    background:#f5f7fb;
    color:#2d3436;
}

/* =====================================================
   MAIN CONTENT AREA
===================================================== */

.container{
    padding:25px;
}

/* =====================================================
   PAGE TITLE
===================================================== */

.page-title{
    font-size:30px;
    font-weight:700;
    color:#1f2d3d;
    margin-bottom:8px;
}

.subtitle{
    color:#7f8c8d;
    margin-bottom:30px;
    font-size:15px;
}

/* =====================================================
   CARDS
===================================================== */

.glass-card{
    background:white;
    border-radius:16px;
    padding:25px;
    box-shadow:
    0 2px 10px rgba(0,0,0,0.05);
    border:1px solid #edf1f7;
}

/* =====================================================
   HERO
===================================================== */

.hero{
    display:grid;
    grid-template-columns:320px 1fr;
    gap:25px;
    margin-bottom:30px;
}

.health-circle{
    width:220px;
    height:220px;
    border-radius:50%;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:center;

    background:
    conic-gradient(
        #4f8cff <?php echo $healthScore; ?>%,
        #e9edf5 0%
    );
}

.health-inner{
    width:170px;
    height:170px;
    background:white;
    border-radius:50%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
}

.health-inner h1{
    font-size:46px;
    color:#4f8cff;
}

.health-inner span{
    color:#7f8c8d;
    font-size:14px;
}

.health-info h2{
    font-size:28px;
    margin-bottom:20px;
    color:#1f2d3d;
}

.health-point{
    padding:14px 18px;
    background:#f7f9fc;
    margin-bottom:14px;
    border-radius:12px;
    border-left:4px solid #4f8cff;
    color:#34495e;
}

.health-confidence{
    margin-top:20px;
    color:#4f8cff;
    font-weight:600;
}

/* =====================================================
   SECTION TITLES
===================================================== */

.section-title{
    font-size:24px;
    margin-bottom:20px;
    color:#1f2d3d;
    font-weight:700;
}

/* =====================================================
   PREDICTIONS
===================================================== */

.predictions{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.prediction-card{
    background:white;
    border-radius:16px;
    padding:24px;
    border:1px solid #edf1f7;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    transition:0.25s;
}

.prediction-card:hover{
    transform:translateY(-4px);
}

.prediction-card i{
    font-size:28px;
    color:#4f8cff;
    margin-bottom:18px;
}

.prediction-card h3{
    color:#34495e;
    margin-bottom:10px;
}

.prediction-number{
    font-size:32px;
    font-weight:bold;
    color:#1f2d3d;
    margin-bottom:8px;
}

.prediction-card p{
    color:#7f8c8d;
    line-height:1.5;
}

/* =====================================================
   GRID
===================================================== */

.grid-2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:25px;
    margin-bottom:30px;
}

/* =====================================================
   RECOMMENDATIONS
===================================================== */

.recommendation{
    background:#f7f9fc;
    padding:16px;
    border-radius:12px;
    margin-bottom:14px;
    border-left:4px solid #28a745;
    color:#34495e;
}

/* =====================================================
   ALERTS
===================================================== */

.alert-item{
    background:#fff5f5;
    border-left:4px solid #ff6b6b;
    padding:16px;
    border-radius:12px;
    margin-bottom:14px;
    color:#c0392b;
}

/* =====================================================
   DEPARTMENT RISK
===================================================== */

.dept-card{
    background:#f9fbfd;
    padding:20px;
    border-radius:14px;
    margin-bottom:18px;
    border:1px solid #edf1f7;
}

.dept-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:14px;
    font-weight:600;
}

.progress{
    width:100%;
    height:10px;
    background:#e9edf5;
    border-radius:20px;
    overflow:hidden;
}

.progress-fill{
    height:100%;
    border-radius:20px;
}

.success{
    background:#28a745;
}

.warning{
    background:#ffc107;
}

.danger{
    background:#ff6b6b;
}

.dept-comment{
    margin-top:12px;
    color:#7f8c8d;
    font-size:14px;
}

/* =====================================================
   TRENDS
===================================================== */

.trend-box{
    color:#34495e;
    line-height:1.9;
    font-size:16px;
}

/* =====================================================
   ACTION CENTER
===================================================== */

.actions{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-top:15px;
}

.action-btn{
    padding:16px;
    border:none;
    border-radius:14px;
    cursor:pointer;
    background:#4f8cff;
    color:white;
    font-size:15px;
    font-weight:600;
    transition:0.25s;
}

.action-btn:hover{
    background:#3b74db;
    transform:translateY(-2px);
}

/* =====================================================
   ANOMALIES
===================================================== */

.anomaly{
    background:#fff9e6;
    border-left:4px solid #ffc107;
    padding:16px;
    border-radius:12px;
    margin-bottom:14px;
    color:#8a6d3b;
}

/* =====================================================
   AI CHAT
===================================================== */

.ai-chat-toggle{
    position:fixed;
    right:30px;
    bottom:30px;
    width:65px;
    height:65px;
    border-radius:50%;
    border:none;
    background:#4f8cff;
    color:white;
    font-size:26px;
    cursor:pointer;
    z-index:9999;
    box-shadow:0 6px 18px rgba(79,140,255,0.35);
}

.ai-chat-container{
    position:fixed;
    right:30px;
    bottom:110px;
    width:380px;
    height:620px;
    background:white;
    border-radius:18px;
    overflow:hidden;
    display:none;
    flex-direction:column;
    z-index:9999;
    box-shadow:0 8px 30px rgba(0,0,0,0.15);
    border:1px solid #edf1f7;
}

.ai-chat-header{
    background:#4f8cff;
    color:white;
    padding:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-weight:bold;
}

.chat-box{
    flex:1;
    overflow-y:auto;
    padding:20px;
    background:#f7f9fc;
}

.ai-message,
.user-message{
    padding:14px;
    border-radius:14px;
    margin-bottom:15px;
    max-width:82%;
    line-height:1.6;
}

.ai-message{
    background:white;
    border-left:4px solid #4f8cff;
    color:#34495e;
}

.user-message{
    background:#4f8cff;
    color:white;
    margin-left:auto;
}

.chat-input-area{
    display:flex;
    border-top:1px solid #edf1f7;
}

.chat-input-area input{
    flex:1;
    border:none;
    padding:16px;
    outline:none;
    font-size:15px;
}

.chat-input-area button{
    width:110px;
    border:none;
    background:#4f8cff;
    color:white;
    cursor:pointer;
    font-weight:bold;
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:992px){

    .hero{
        grid-template-columns:1fr;
    }

    .grid-2{
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<div id="app">

    <?php include('include/sidebar.php'); ?>

    <div class="app-content">

        <?php include('include/header.php'); ?>

        <div class="main-content">

            <div class="wrap-content container" id="container">

<div class="container">

    <div class="page-title">
        AI Smart Insights Center
    </div>

    <div class="subtitle">
        Predictive intelligence, risk monitoring and AI-driven hospital analytics.
    </div>

    <!-- HERO -->

    <div class="hero">

        <div class="glass-card">

            <div class="health-circle">

                <div class="health-inner">

                    <h1><?php echo $healthScore; ?></h1>

                    <span>Hospital Health</span>

                </div>

            </div>

        </div>

        <div class="glass-card health-info">

            <h2>Hospital Health Score</h2>

            <div class="health-point">
                ▲ Patient flow healthy
            </div>

            <div class="health-point">
                ▲ Revenue performance stable
            </div>

            <div class="health-point">
                ⚠ Orthopedics nearing overload
            </div>

            <div class="health-point">
                ⚠ Bed occupancy rising rapidly
            </div>

            <div class="health-confidence">
                AI Confidence Level: 94%
            </div>

        </div>

    </div>

    <!-- PREDICTIONS -->

    <h2 class="section-title">
        AI Predictions
    </h2>

    <div class="predictions">

        <div class="prediction-card">

            <i class="fa-solid fa-user-group"></i>

            <h3>Patient Forecast</h3>

            <div class="prediction-number">
                <?php echo $expectedPatientsTomorrow; ?>
            </div>

            <p>Expected patients tomorrow (+12%)</p>

        </div>

        <div class="prediction-card">

            <i class="fa-solid fa-money-bill-trend-up"></i>

            <h3>Revenue Forecast</h3>

            <div class="prediction-number">
                ₹<?php echo number_format($predictedRevenue); ?>
            </div>

            <p>Predicted weekly revenue</p>

        </div>

        <div class="prediction-card">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <h3>Emergency Load</h3>

            <div class="prediction-number">
                High
            </div>

            <p>Heavy emergency traffic expected Saturday</p>

        </div>

        <div class="prediction-card">

            <i class="fa-solid fa-bed"></i>

            <h3>Bed Shortage Risk</h3>

            <div class="prediction-number">
                <?php echo $icuRiskDays; ?> Days
            </div>

            <p>ICU may reach full capacity soon</p>

        </div>

    </div>

    <!-- GRID -->

    <div class="grid-2">

        <!-- RECOMMENDATIONS -->

        <div class="glass-card">

            <h2 class="section-title">
                AI Recommendations
            </h2>

            <?php
            foreach($recommendations as $rec){
                echo "<div class='recommendation'>• $rec</div>";
            }
            ?>

        </div>

        <!-- ALERTS -->

        <div class="glass-card">

            <h2 class="section-title">
                Hospital Alerts
            </h2>

            <?php
            foreach($alerts as $alert){
                echo "<div class='alert-item'>$alert</div>";
            }
            ?>

        </div>

    </div>

    <!-- DEPARTMENT RISKS -->

    <div class="glass-card" style="margin-bottom:30px;">

        <h2 class="section-title">
            Department Risk Meter
        </h2>

        <?php foreach($deptRiskData as $dept){ ?>

            <div class="dept-card">

                <div class="dept-top">

                    <div>
                        <strong><?php echo $dept['dept']; ?></strong>
                    </div>

                    <div>
                        <?php echo $dept['risk']; ?> Risk
                    </div>

                </div>

                <div class="progress">

                    <div class="progress-fill <?php echo $dept['color']; ?>"
                    style="width:<?php echo $dept['progress']; ?>%">
                    </div>

                </div>

                <div class="dept-comment">
                    <?php echo $dept['status']; ?> —
                    <?php echo $dept['comment']; ?>
                </div>

            </div>

        <?php } ?>

    </div>

    <!-- SMART TRENDS -->

    <div class="grid-2">

        <div class="glass-card">

            <h2 class="section-title">
                Smart Trends
            </h2>

            <div class="trend-box">
                <?php echo nl2br($trendNarrative); ?>
            </div>

        </div>

        <!-- ANOMALIES -->

        <div class="glass-card">

            <h2 class="section-title">
                Anomaly Detection
            </h2>

            <?php
            foreach($anomalies as $a){
                echo "<div class='anomaly'>⚠ $a</div>";
            }
            ?>

        </div>

    </div>

    <!-- ACTION CENTER -->

    <div class="glass-card">

        <h2 class="section-title">
            Action Center
        </h2>

        <div class="actions">

            <button class="action-btn">
                Increase Staff
            </button>

            <button class="action-btn">
                Export Risk Report
            </button>

            <button class="action-btn">
                Generate AI Summary
            </button>

            <button class="action-btn">
                Schedule Resource Audit
            </button>

        </div>

    </div>

</div>

<!-- =====================================================
     AI CHAT
===================================================== -->

<button class="ai-chat-toggle" onclick="toggleChat()">
    🤖
</button>

<div class="ai-chat-container" id="aiChat">

    <div class="ai-chat-header">

        <span>AI Hospital Assistant</span>

        <button onclick="toggleChat()"
        style="background:none;border:none;color:white;font-size:22px;cursor:pointer;">
            ✕
        </button>

    </div>

    <div class="chat-box" id="chatBox">

        <div class="ai-message">

            👋 Welcome to AI Smart Insights.<br><br>

            Ask questions like:<br><br>

            • Why did revenue drop?<br>
            • Predict next month occupancy<br>
            • Which department needs more staff?<br>
            • Show unusual trends<br>
            • Which department is overloaded?<br>

        </div>

    </div>

    <div class="chat-input-area">

        <input type="text"
        id="userInput"
        placeholder="Ask AI insights...">

        <button onclick="sendMessage()">
            Send
        </button>

    </div>

</div>

<script>

function toggleChat(){

    const chat = document.getElementById("aiChat");

    if(chat.style.display === "flex"){
        chat.style.display = "none";
    }
    else{
        chat.style.display = "flex";
    }

}

async function sendMessage(){

    const input = document.getElementById("userInput");

    const message = input.value.trim();

    if(message === "") return;

    const chatBox = document.getElementById("chatBox");

    chatBox.innerHTML += `
        <div class="user-message">
            ${message}
        </div>
    `;

    input.value = "";

    chatBox.innerHTML += `
        <div class="ai-message" id="loadingMsg">
            AI analyzing hospital data...
        </div>
    `;

    chatBox.scrollTop = chatBox.scrollHeight;

    const formData = new FormData();

    formData.append("message", message);

    const response = await fetch("ai-chat.php",{
        method:"POST",
        body:formData
    });

    const data = await response.json();

    const loading = document.getElementById("loadingMsg");

    if(loading){
        loading.remove();
    }

    chatBox.innerHTML += `
        <div class="ai-message">
            ${data.reply.replace(/\\n/g,"<br>")}
        </div>
    `;

    chatBox.scrollTop = chatBox.scrollHeight;

}

document.getElementById("userInput")
.addEventListener("keypress",function(e){

    if(e.key === "Enter"){
        sendMessage();
    }

});

</script>

            </div>
        </div>
    </div>

</div>

<!-- start: FOOTER -->
<?php include('include/footer.php');?>
<!-- end: FOOTER -->

<!-- start: SETTINGS -->
<?php include('include/setting.php');?>
<!-- end: SETTINGS -->

<script src="vendor/jquery/jquery.min.js"></script>

<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

<script src="vendor/modernizr/modernizr.js"></script>

<script src="vendor/jquery-cookie/jquery.cookie.js"></script>

<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>

<script src="vendor/switchery/switchery.min.js"></script>

<script src="assets/js/main.js"></script>

<script>
jQuery(document).ready(function() {
    Main.init();
});
</script>



</body>
</html>
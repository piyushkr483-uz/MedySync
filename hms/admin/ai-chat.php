<?php
session_start();

include('include/config.php');

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "reply" => "Invalid request."
    ]);

    exit;
}

$userMessage = trim($_POST['message'] ?? '');

if (empty($userMessage)) {

    echo json_encode([
        "reply" => "Please enter a message."
    ]);

    exit;
}


// FETCH HOSPITAL DATA

// Total Patients

$pq = mysqli_query($con,
"SELECT COUNT(*) as total FROM tblpatient");

$patients = mysqli_fetch_assoc($pq)['total'];

// Total Doctors

$dq = mysqli_query($con,
"SELECT COUNT(*) as total FROM doctors");

$doctors = mysqli_fetch_assoc($dq)['total'];

// Total Appointments

$aq = mysqli_query($con,
"SELECT COUNT(*) as total FROM appointment");

$appointments = mysqli_fetch_assoc($aq)['total'];

// Revenue

$rq = mysqli_query($con,
"SELECT SUM(treatmentCost) as revenue FROM tblpatient");

$revenue = mysqli_fetch_assoc($rq)['revenue'] ?? 0;

// Department Revenue

$deptData = [];

$deptQuery = mysqli_query($con, "
SELECT d.specilization as dept,
SUM(p.treatmentCost) as revenue
FROM doctors d
JOIN tblpatient p ON d.id = p.docid
GROUP BY d.specilization
");

while($row = mysqli_fetch_assoc($deptQuery)) {

    $deptData[] = $row;
}

// =======================================
// BUILD CONTEXT
// =======================================

$hospitalData = [
    "total_patients" => $patients,
    "total_doctors" => $doctors,
    "total_appointments" => $appointments,
    "total_revenue" => $revenue,
    "department_revenue" => $deptData
];

$context = json_encode($hospitalData, JSON_PRETTY_PRINT);

// =======================================
// GROQ API KEY
// =======================================

$apiKey = "YOUR_API_HERE";

// =======================================
// PROMPT
// =======================================

$prompt = "
You are an AI assistant for a Hospital Management System dashboard.

Hospital Data:
$context

User Question:
$userMessage

Instructions:
- Answer professionally
- Give analytical insights
- Keep answers concise
- Use hospital/business terminology
- Sound like a real analytics assistant
";

// =======================================
// GROQ API REQUEST
// =======================================

$url = "YOUT_API_HERE";

// =======================================
// REQUEST DATA
// =======================================

$data = [
    "model" => "llama-3.3-70b-versatile",

    "messages" => [

        [
            "role" => "system",
            "content" => "You are a hospital analytics AI assistant."
        ],

        [
            "role" => "user",
            "content" => $prompt
        ]
    ],

    "temperature" => 0.7,
    "max_tokens" => 500
];

$payload = json_encode($data);

// =======================================
// CURL
// =======================================

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// OPTIONAL FIX FOR LOCALHOST SSL ISSUES

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

// =======================================
// CURL ERROR
// =======================================

if(curl_errno($ch)) {

    echo json_encode([
        "reply" => "cURL Error: " . curl_error($ch)
    ]);

    exit;
}

curl_close($ch);

// =======================================
// DECODE RESPONSE
// =======================================

$result = json_decode($response, true);

// =======================================
// DEBUG ERRORS
// =======================================

if(isset($result['error'])) {

    echo json_encode([
        "reply" => "Groq Error: " . $result['error']['message']
    ]);

    exit;
}

// =======================================
// EXTRACT AI RESPONSE
// =======================================

$reply = $result['choices'][0]['message']['content']
    ?? "AI could not generate a response.";

// =======================================
// RETURN RESPONSE
// =======================================

echo json_encode([
    "reply" => $reply
]);

?>
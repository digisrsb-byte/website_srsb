<?php
// SRSB Workforce Solutions - Hostinger PHP form handler with file upload attachments
// Upload this file in the same folder as contact.html inside public_html.

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.html");
    exit;
}

// Change these two emails if needed.
$to_email = "info@srsbworkforcesolutions.com";      // Email where form details will be received
$from_email = "info@srsbworkforcesolutions.com";    // Must be a domain email for better delivery
$company_name = "SRSB Workforce Solutions";

$allowed_extensions = ["pdf", "doc", "docx"];
$max_file_size = 5 * 1024 * 1024; // 5MB

// Simple anti-spam honeypot field. Real users won't fill this.
if (!empty($_POST["website"] ?? "")) {
    header("Location: thank-you.html");
    exit;
}

function clean_input($value) {
    $value = trim((string)$value);
    $value = stripslashes($value);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function mail_value($key) {
    return clean_input($_POST[$key] ?? "");
}

function validate_uploaded_file($file, $required, $allowed_extensions, $max_file_size) {
    if (!isset($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return !$required;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file["size"] <= 0 || $file["size"] > $max_file_size) {
        return false;
    }

    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions, true)) {
        return false;
    }

    return is_uploaded_file($file["tmp_name"]);
}

function uploaded_file_or_null($file) {
    if (isset($file) && $file["error"] === UPLOAD_ERR_OK && is_uploaded_file($file["tmp_name"])) {
        return $file;
    }
    return null;
}

function send_mail_with_attachment($to, $subject, $message, $from, $reply_to, $company_name, $file = null) {
    $eol = "\r\n";
    $boundary = "srsb_" . md5((string)time());

    $headers = "From: {$company_name} <{$from}>" . $eol;
    $headers .= "Reply-To: {$reply_to}" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;

    if ($file !== null) {
        $safe_filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file["name"]));
        $file_content = chunk_split(base64_encode(file_get_contents($file["tmp_name"])));
        $mime_type = function_exists('mime_content_type') ? mime_content_type($file["tmp_name"]) : "application/octet-stream";
        if (!$mime_type) {
            $mime_type = "application/octet-stream";
        }

        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"" . $eol;

        $body = "--{$boundary}" . $eol;
        $body .= "Content-Type: text/plain; charset=UTF-8" . $eol;
        $body .= "Content-Transfer-Encoding: 7bit" . $eol . $eol;
        $body .= $message . $eol . $eol;

        $body .= "--{$boundary}" . $eol;
        $body .= "Content-Type: {$mime_type}; name=\"{$safe_filename}\"" . $eol;
        $body .= "Content-Transfer-Encoding: base64" . $eol;
        $body .= "Content-Disposition: attachment; filename=\"{$safe_filename}\"" . $eol . $eol;
        $body .= $file_content . $eol;
        $body .= "--{$boundary}--";

        return mail($to, $subject, $body, $headers);
    }

    $headers .= "Content-Type: text/plain; charset=UTF-8" . $eol;
    return mail($to, $subject, $message, $headers);
}

$form_type = mail_value("form_type");
$sent = false;

if ($form_type === "candidate") {
    $name = mail_value("candidate_name");
    $email = filter_var($_POST["candidate_email"] ?? "", FILTER_SANITIZE_EMAIL);
    $phone = mail_value("candidate_phone");
    $location = mail_value("current_location");
    $role = mail_value("preferred_role");
    $experience = mail_value("experience");
    $skills = mail_value("skills");
    $resume = $_FILES["resume"] ?? null;

    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($phone)) {
        header("Location: form-error.html");
        exit;
    }

    if (!validate_uploaded_file($resume, true, $allowed_extensions, $max_file_size)) {
        header("Location: form-error.html");
        exit;
    }

    $subject = "New Candidate Application - SRSB Website";
    $message = "New candidate application received from SRSB website.\n\n";
    $message .= "Full Name: {$name}\n";
    $message .= "Email: {$email}\n";
    $message .= "Phone: {$phone}\n";
    $message .= "Current Location: {$location}\n";
    $message .= "Preferred Job Role: {$role}\n";
    $message .= "Experience: {$experience}\n";
    $message .= "Skills: {$skills}\n\n";
    $message .= "Resume is attached with this email.\n";

    $sent = send_mail_with_attachment($to_email, $subject, $message, $from_email, $email, $company_name, uploaded_file_or_null($resume));

} elseif ($form_type === "client") {
    $company = mail_value("company_name");
    $contact_person = mail_value("contact_person");
    $email = filter_var($_POST["official_email"] ?? "", FILTER_SANITIZE_EMAIL);
    $phone = mail_value("client_phone");
    $location = mail_value("hiring_location");
    $job_role = mail_value("job_role");
    $openings = mail_value("openings");
    $service = mail_value("service_required");
    $requirement = mail_value("requirement");
    $jd_file = $_FILES["jd_file"] ?? null;

    if (empty($company) || empty($contact_person) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($phone)) {
        header("Location: form-error.html");
        exit;
    }

    if (!validate_uploaded_file($jd_file, false, $allowed_extensions, $max_file_size)) {
        header("Location: form-error.html");
        exit;
    }

    $subject = "New Hiring Requirement - SRSB Website";
    $message = "New hiring requirement received from SRSB website.\n\n";
    $message .= "Company Name: {$company}\n";
    $message .= "Contact Person: {$contact_person}\n";
    $message .= "Official Email: {$email}\n";
    $message .= "Phone: {$phone}\n";
    $message .= "Hiring Location: {$location}\n";
    $message .= "Job Role / Position: {$job_role}\n";
    $message .= "Number of Openings: {$openings}\n";
    $message .= "Service Required: {$service}\n";
    $message .= "Hiring Requirement: {$requirement}\n\n";

    $attached_jd = uploaded_file_or_null($jd_file);
    if ($attached_jd !== null) {
        $message .= "JD / requirement file is attached with this email.\n";
    } else {
        $message .= "No JD / requirement file was uploaded.\n";
    }

    $sent = send_mail_with_attachment($to_email, $subject, $message, $from_email, $email, $company_name, $attached_jd);

} else {
    header("Location: form-error.html");
    exit;
}

if ($sent) {
    header("Location: thank-you.html");
    exit;
}

header("Location: form-error.html");
exit;
?>

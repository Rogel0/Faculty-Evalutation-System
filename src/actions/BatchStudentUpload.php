<?php
// Load Composer autoloader (path relative to this file)
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    echo json_encode(['success' => false, 'error' => 'Composer autoload not found at ' . $vendorAutoload]);
    exit;
}
require_once $vendorAutoload;

// Load database config (provides $conn)
$dbConfig = __DIR__ . '/../config/database.php';
if (!file_exists($dbConfig)) {
    echo json_encode(['success' => false, 'error' => 'Database config not found at ' . $dbConfig]);
    exit;
}
require_once $dbConfig;

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();
header('Content-Type: application/json');

// Start output buffering to capture any unexpected output
ob_start();

// Convert PHP warnings/notices to exceptions so we can return JSON errors
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    // Determine if this is a preview or actual upload
    $isPreview = isset($_POST['preview']) && $_POST['preview'] === 'true';
    $fileKey = $isPreview ? 'file' : 'batch_file';

    // Validate file upload
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Please select a valid Excel file');
    }

    // Validate file type
    $fileExtension = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));

    // For preview: be lenient and handle CSV without PhpSpreadsheet to avoid dependency/mime issues
    if ($isPreview) {
        if ($fileExtension === 'csv') {
            $rows = [];
            if (($handle = fopen($_FILES[$fileKey]['tmp_name'], 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            } else {
                $buffer = ob_get_clean();
                $msg = 'Failed to open CSV file for preview.';
                if (!empty($buffer)) $msg .= ' | Output: ' . strip_tags($buffer);
                echo json_encode(['success' => false, 'error' => $msg]);
                exit;
            }
        } else {
            // Try using PhpSpreadsheet for xls/xlsx previews but catch errors
            try {
                $spreadsheet = IOFactory::load($_FILES[$fileKey]['tmp_name']);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            } catch (Exception $e) {
                $buffer = ob_get_clean();
                $msg = 'Failed to read spreadsheet for preview: ' . $e->getMessage();
                if (!empty($buffer)) $msg .= ' | Output: ' . strip_tags($buffer);
                echo json_encode(['success' => false, 'error' => $msg]);
                exit;
            }
        }
    } else {
        // For actual upload: perform basic validation then use PhpSpreadsheet for any format
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes) && !in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
            throw new Exception('Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file');
        }

        try {
            $spreadsheet = IOFactory::load($_FILES[$fileKey]['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (Exception $e) {
            $buffer = ob_get_clean();
            $msg = 'Failed to read spreadsheet: ' . $e->getMessage();
            if (!empty($buffer)) $msg .= ' | Output: ' . strip_tags($buffer);
            echo json_encode(['success' => false, 'error' => $msg]);
            exit;
        }
    }

    // Remove header row
    array_shift($rows);

    // Process rows
    $students = [];
    foreach ($rows as $row) {
        // Skip empty rows
        if (empty($row[0])) continue;

        $student = [
            'student_id' => trim($row[0] ?? ''),
            'firstname' => trim($row[1] ?? ''),
            'lastname' => trim($row[2] ?? ''),
            'middle_name' => trim($row[3] ?? ''),
            'email' => trim($row[4] ?? ''),
            'birthdate' => trim($row[5] ?? ''),
            'strand' => trim($row[6] ?? ''),
            'grade_level' => trim($row[7] ?? ''),
            'subject_codes' => trim($row[8] ?? '')
        ];

        // Validate required fields
        if (
            empty($student['student_id']) || empty($student['firstname']) ||
            empty($student['lastname']) || empty($student['email'])
        ) {
            continue;
        }

        if ($isPreview) {
            // For preview, convert subject_codes to array
            $student['subjects'] = !empty($student['subject_codes']) ?
                array_map('trim', explode(',', $student['subject_codes'])) : [];
            unset($student['subject_codes']);
            $students[] = $student;
        } else {
            $students[] = $student;
        }
    }

    // Check if we have valid data
    if (empty($students)) {
        throw new Exception('No valid student data found in the file');
    }

    // If preview, return data
    if ($isPreview) {
        echo json_encode(['success' => true, 'preview' => $students]);
        exit;
    }

    // Process actual upload using the mysqli connection from config/database.php ($conn)
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('Database connection not available');
    }

    // collect warnings (e.g., subject codes not found) to return to the client
    $all_warnings = [];

    $conn->begin_transaction();

    try {
        foreach ($students as $student) {
            // Generate username and password
            $username = strtolower(preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                $student['firstname'] . $student['lastname']
            ));
            $plainPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            // Insert or update student
            $stmt = $conn->prepare(
                "INSERT INTO users (student_id, firstname, lastname, middlename, email, 
                                   username, password, birthdate, strand, grade_level, user_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')
                 ON DUPLICATE KEY UPDATE
                 firstname=VALUES(firstname),
                 lastname=VALUES(lastname),
                 middlename=VALUES(middlename),
                 email=VALUES(email),
                 birthdate=VALUES(birthdate),
                 strand=VALUES(strand),
                 grade_level=VALUES(grade_level)"
            );

            $stmt->bind_param(
                "ssssssssss",
                $student['student_id'],
                $student['firstname'],
                $student['lastname'],
                $student['middle_name'],
                $student['email'],
                $username,
                $hashedPassword,
                $student['birthdate'],
                $student['strand'],
                $student['grade_level']
            );

            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception("Error inserting student: " . $stmt->error);
            }
            $stmt->close();

            // Get numeric user id for this student (users.id) by student_id value
            $student_user_id = null;
            $idStmt = $conn->prepare("SELECT id FROM users WHERE student_id = ? LIMIT 1");
            if ($idStmt) {
                $idStmt->bind_param('s', $student['student_id']);
                $idStmt->execute();
                $idStmt->bind_result($uid);
                if ($idStmt->fetch()) {
                    $student_user_id = (int)$uid;
                }
                $idStmt->close();
            }
            if ($student_user_id === null) {
                throw new Exception('Failed to determine user id for student: ' . $student['student_id']);
            }

            // Handle subject assignments using existing `student_enrollments` and `subjects` tables
            // Map submitted subject codes to subjects.id, insert/reactivate enrollments, and deactivate old ones
            if (isset($student['subject_codes'])) {
                $submitted = array_filter(array_map('trim', explode(',', $student['subject_codes'])));
                // Normalize to unique non-empty codes (uppercased for comparison)
                $submitted = array_values(array_unique(array_filter(array_map(function ($v) {
                    return strtoupper(trim($v));
                }, $submitted), function ($v) {
                    return $v !== '';
                })));

                // Determine current school year id (use active, fallback to latest)
                $school_year_id = null;
                $syRes = $conn->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1");
                if ($syRes && $row = $syRes->fetch_assoc()) {
                    $school_year_id = (int)$row['id'];
                } else {
                    $syRes2 = $conn->query("SELECT id FROM school_years ORDER BY id DESC LIMIT 1");
                    if ($syRes2 && $row2 = $syRes2->fetch_assoc()) $school_year_id = (int)$row2['id'];
                }

                // Prepare subject lookup statement (try by subject_code, then by subject_name)
                $subjectLookup = $conn->prepare("SELECT id FROM subjects WHERE UPPER(subject_code) = ? LIMIT 1");
                $subjectLookupName = $conn->prepare("SELECT id FROM subjects WHERE UPPER(subject_name) = ? LIMIT 1");

                // Map codes to subject ids; collect warnings if a code is not found
                $codeToId = [];
                $warnings = [];
                foreach ($submitted as $code) {
                    $sid = null;
                    if ($subjectLookup) {
                        $subjectLookup->bind_param('s', $code);
                        $subjectLookup->execute();
                        $subjectLookup->bind_result($foundId);
                        if ($subjectLookup->fetch()) {
                            $sid = (int)$foundId;
                        }
                        // reset for next fetch
                        $subjectLookup->free_result();
                    }
                    if ($sid === null && $subjectLookupName) {
                        $subjectLookupName->bind_param('s', $code);
                        $subjectLookupName->execute();
                        $subjectLookupName->bind_result($foundId2);
                        if ($subjectLookupName->fetch()) {
                            $sid = (int)$foundId2;
                        }
                        $subjectLookupName->free_result();
                    }

                    if ($sid === null) {
                        $warnings[] = "Subject code/name not found: {$code}";
                        continue;
                    }
                    $codeToId[$code] = $sid;
                }

                if ($subjectLookup) $subjectLookup->close();
                if ($subjectLookupName) $subjectLookupName->close();

                // If nothing resolved to subject ids, skip further processing for this student
                if (empty($codeToId)) {
                    // store warnings in session or collect for response - we'll include in final response
                    // continue to next student (no enrollments to process)
                } else {
                    // Fetch existing enrollments for this student (keyed by subject_id)
                    $existing = [];
                    $sel_stmt = $conn->prepare("SELECT se.subject_id, se.is_active FROM student_enrollments se WHERE se.student_id = ?");
                    if ($sel_stmt) {
                        $sel_stmt->bind_param('i', $student_user_id);
                        $sel_stmt->execute();
                        $res = $sel_stmt->get_result();
                        while ($r = $res->fetch_assoc()) {
                            $existing[(int)$r['subject_id']] = (int)$r['is_active'];
                        }
                        $sel_stmt->close();
                    }

                    // Prepare statements for enrollments
                    $updateActivateStmt = $conn->prepare("UPDATE student_enrollments SET is_active = 1, school_year_id = ? WHERE student_id = ? AND subject_id = ?");
                    $updateDeactivateStmt = $conn->prepare("UPDATE student_enrollments SET is_active = 0 WHERE student_id = ? AND subject_id = ?");
                    // prepare insert with teacher_id param (we will attempt to find a teacher for the subject)
                    $insertStmt = $conn->prepare("INSERT INTO student_enrollments (student_id, subject_id, teacher_id, school_year_id, is_active) VALUES (?, ?, ?, ?, 1)");

                    // Activate or insert submitted codes (by subject id)
                    foreach ($codeToId as $code => $sid) {
                        if (isset($existing[$sid])) {
                            if ($existing[$sid] === 0) {
                                if ($updateActivateStmt === false) throw new Exception('Prepare failed: ' . mysqli_error($conn));
                                // bind: school_year_id (i), student_id (i), subject_id (i)
                                $updateActivateStmt->bind_param('iii', $school_year_id, $student_user_id, $sid);
                                if (!$updateActivateStmt->execute()) {
                                    $updateActivateStmt->close();
                                    throw new Exception('Error activating enrollment: ' . $updateActivateStmt->error);
                                }
                            }
                            // remove from existing tracking so we don't deactivate it later
                            unset($existing[$sid]);
                        } else {
                            // new enrollment -> insert
                            if ($insertStmt === false) throw new Exception('Prepare failed: ' . mysqli_error($conn));
                            // attempt to find a teacher assigned to this subject in the current school year
                            $teacher_id_for_insert = 0;
                            $tStmt = $conn->prepare("SELECT teacher_id FROM teacher_subjects WHERE subject_id = ? AND school_year_id = ? LIMIT 1");
                            if ($tStmt) {
                                $tStmt->bind_param('ii', $sid, $school_year_id);
                                $tStmt->execute();
                                $tStmt->bind_result($foundTeacher);
                                if ($tStmt->fetch()) {
                                    $teacher_id_for_insert = (int)$foundTeacher;
                                }
                                $tStmt->close();
                            }
                            // fallback: any teacher assigned to the subject in any year
                            if ($teacher_id_for_insert === 0) {
                                $tStmt2 = $conn->prepare("SELECT teacher_id FROM teacher_subjects WHERE subject_id = ? LIMIT 1");
                                if ($tStmt2) {
                                    $tStmt2->bind_param('i', $sid);
                                    $tStmt2->execute();
                                    $tStmt2->bind_result($foundTeacher2);
                                    if ($tStmt2->fetch()) {
                                        $teacher_id_for_insert = (int)$foundTeacher2;
                                    }
                                    $tStmt2->close();
                                }
                            }

                            // if no teacher found, try to locate or create a placeholder teacher account
                            if ($teacher_id_for_insert === 0) {
                                $placeholderUsername = 'unassigned_teacher';
                                $phStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND user_type = 'teacher' LIMIT 1");
                                if ($phStmt) {
                                    $phStmt->bind_param('s', $placeholderUsername);
                                    $phStmt->execute();
                                    $phStmt->bind_result($foundPhId);
                                    if ($phStmt->fetch()) {
                                        $teacher_id_for_insert = (int)$foundPhId;
                                    }
                                    $phStmt->close();
                                }

                                if ($teacher_id_for_insert === 0) {
                                    // create placeholder teacher user
                                    $plain = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
                                    $hashed = password_hash($plain, PASSWORD_DEFAULT);
                                    $createStmt = $conn->prepare("INSERT INTO users (username, password, firstname, lastname, user_type) VALUES (?, ?, ?, ?, 'teacher')");
                                    if ($createStmt) {
                                        $first = 'Unassigned';
                                        $last = 'Teacher';
                                        $createStmt->bind_param('ssss', $placeholderUsername, $hashed, $first, $last);
                                        if ($createStmt->execute()) {
                                            $teacher_id_for_insert = (int)$conn->insert_id;
                                        }
                                        $createStmt->close();
                                    }
                                }
                            }

                            // bind: student_id (i), subject_id (i), teacher_id (i), school_year_id (i)
                            $insertStmt->bind_param('iiii', $student_user_id, $sid, $teacher_id_for_insert, $school_year_id);
                            if (!$insertStmt->execute()) {
                                $insertStmt->close();
                                throw new Exception('Error inserting enrollment: ' . $insertStmt->error);
                            }
                        }
                    }

                    // Any remaining entries in $existing are previous enrollments not present in submitted list
                    // Deactivate them (but keep the row for history)
                    foreach ($existing as $oldSid => $oldActive) {
                        if ($oldActive === 1) {
                            if ($updateDeactivateStmt === false) throw new Exception('Prepare failed: ' . mysqli_error($conn));
                            // bind: student_id (i), subject_id (i)
                            $updateDeactivateStmt->bind_param('ii', $student_user_id, $oldSid);
                            if (!$updateDeactivateStmt->execute()) {
                                $updateDeactivateStmt->close();
                                throw new Exception('Error deactivating old enrollment: ' . $updateDeactivateStmt->error);
                            }
                        }
                    }

                    if ($updateActivateStmt) $updateActivateStmt->close();
                    if ($updateDeactivateStmt) $updateDeactivateStmt->close();
                    if ($insertStmt) $insertStmt->close();
                }
                // collect warnings globally for session message (do not attach to student payload)
                if (!empty($warnings)) {
                    $all_warnings[] = ['student_id' => $student['student_id'], 'warnings' => $warnings];
                }
            }
        }

        $conn->commit();
        // set session messages so the existing layout toasts pick them up on next page load
        $_SESSION['success'] = 'Students uploaded successfully';
        if (!empty($all_warnings)) {
            // Persist structured warnings in session so we can show a detailed modal on the next page
            $_SESSION['upload_warnings'] = $all_warnings;
            // Also set a short warning message for the toast
            $_SESSION['warning'] = 'Upload completed with warnings. Click to view details.';
        }

        // For actual upload requests, don't return JSON — redirect back to referrer and use session to show messages/toasts
        $redirect = isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/faculty_evaluation/src/';
        header('Location: ' . $redirect);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    // Restore previous error handler
    restore_error_handler();

    // Capture and append any output buffer content for debugging
    $buffer = ob_get_clean();
    $msg = $e->getMessage();
    if (!empty($buffer)) {
        $msg .= ' | Output: ' . strip_tags($buffer);
    }

    // If this was a preview request, return JSON (preview uses AJAX). Otherwise set session error and redirect back.
    if (isset($isPreview) && $isPreview) {
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    } else {
        // set session error and redirect back to referrer
        $_SESSION['error'] = $msg;
        $redirect = isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/faculty_evaluation/src/';
        header('Location: ' . $redirect);
        exit;
    }
}

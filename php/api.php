<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$allowedStatuses = ['Active', 'Remote', 'On Leave'];

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $department = trim($_GET['department'] ?? '');

    $sql = "SELECT id, full_name, email, department, role_title, status, salary, joined_date FROM employees WHERE 1=1";
    $params = [];
    $types = '';

    if ($search !== '') {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR role_title LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= 'sss';
    }

    if ($department !== '') {
        $sql .= " AND department = ?";
        $params[] = $department;
        $types .= 's';
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $stmt->close();

    $statsResult = $conn->query("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'Active') AS active_count,
            SUM(status = 'Remote') AS remote_count,
            SUM(status = 'On Leave') AS leave_count
        FROM employees
    ");
    $stats = $statsResult ? $statsResult->fetch_assoc() : [];

    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'stats' => [
            'total' => (int)($stats['total'] ?? 0),
            'active' => (int)($stats['active_count'] ?? 0),
            'remote' => (int)($stats['remote_count'] ?? 0),
            'on_leave' => (int)($stats['leave_count'] ?? 0)
        ]
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

if ($method === 'POST') {
    $fullName = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $department = trim($input['department'] ?? '');
    $roleTitle = trim($input['role_title'] ?? '');
    $status = trim($input['status'] ?? 'Active');
    $salary = (float)($input['salary'] ?? 0);
    $joinedDate = trim($input['joined_date'] ?? '');

    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'Active';
    }

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $department === '' || $roleTitle === '' || $joinedDate === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter valid details for all required fields.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO employees (full_name, email, department, role_title, status, salary, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssds', $fullName, $email, $department, $roleTitle, $status, $salary, $joinedDate);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee added successfully.']);
    } else {
        http_response_code($conn->errno === 1062 ? 409 : 500);
        $msg = $conn->errno === 1062 ? 'An employee with this email already exists.' : 'Failed to add employee.';
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    $stmt->close();
    exit;
}

if ($method === 'PUT') {
    $id = (int)($input['id'] ?? 0);
    $fullName = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $department = trim($input['department'] ?? '');
    $roleTitle = trim($input['role_title'] ?? '');
    $status = trim($input['status'] ?? 'Active');
    $salary = (float)($input['salary'] ?? 0);
    $joinedDate = trim($input['joined_date'] ?? '');

    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'Active';
    }

    if ($id <= 0 || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $department === '' || $roleTitle === '' || $joinedDate === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid employee data provided.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE employees SET full_name=?, email=?, department=?, role_title=?, status=?, salary=?, joined_date=? WHERE id=?");
    $stmt->bind_param('sssssdsi', $fullName, $email, $department, $roleTitle, $status, $salary, $joinedDate, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully.']);
    } else {
        http_response_code($conn->errno === 1062 ? 409 : 500);
        $msg = $conn->errno === 1062 ? 'Another employee already uses this email.' : 'Failed to update employee.';
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    $stmt->close();
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete employee.']);
    }
    $stmt->close();
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
?>

<?php
require_once 'models/AccountModel.php';

class AccountController {
    private $model;

    public function __construct($pdo) {
        $this->model = new AccountModel($pdo);
    }

    // Kiểm tra quyền truy cập
    private function checkAdminAccess() {
        if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?action=login");
            exit();
        }
    }

    // Hiển thị danh sách tài khoản
    public function index() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập
        $accounts = [];
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        if (!empty($searchTerm)) {
            $accounts = $this->model->searchAccounts($searchTerm);
        } else {
            $accounts = $this->model->getAllAccounts();
        }

        require_once 'views/admin/account/index.php';
    }

    // Thêm tài khoản mới
    public function create() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Thu thập dữ liệu từ form
            $data = [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'firstName' => $_POST['firstName'] ?: null,
                'lastName' => $_POST['lastName'] ?: null,
                'dateOfBirth' => $_POST['dateOfBirth'] ?: null,
                'gender' => $_POST['gender'] ?: null,
                'identityNumber' => $_POST['identityNumber'] ?: null,
                'identityIssuedDate' => $_POST['identityIssuedDate'] ?: null,
                'identityIssuedPlace' => $_POST['identityIssuedPlace'] ?: null,
                'email' => $_POST['email'] ?: null,
                'phoneNumber' => $_POST['phoneNumber'] ?: null,
                'maritalStatus' => $_POST['maritalStatus'] ?: null,
                'hometown' => $_POST['hometown'] ?: null,
                'placeOfBirth' => $_POST['placeOfBirth'] ?: null,
                'nationality' => $_POST['nationality'] ?: null,
                'role' => $_POST['role']
            ];

            var_dump($data);



            // Kiểm tra dữ liệu hợp lệ (có thể thêm các kiểm tra bổ sung)
            if (!empty($data['username']) && !empty($data['password']) && !empty($data['role'])) {
                $result = $this->model->createAccount($data);
                if ($result) {
                    header('Location: index.php?action=account');
                    exit;
                } else {
                    $error = "Đã xảy ra lỗi khi thêm tài khoản.";
                }
            } else {
                $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
            }
        }
        require_once 'views/admin/account/create.php';
    }

    // Sửa tài khoản
    public function edit() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        if (!isset($_GET['id'])) {
            echo "Không tìm thấy ID.";
            exit;
        }
        $id = $_GET['id'];
        $account = $this->model->getAccountById($id);

        if (!$account) {
            echo "Tài khoản không tồn tại.";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Thu thập dữ liệu từ form
            $data = [
                'username' => $_POST['username'],
                'password' => $_POST['password'], // Có thể để trống nếu không đổi mật khẩu
                'firstName' => $_POST['firstName'] ?: null,
                'lastName' => $_POST['lastName'] ?: null,
                'dateOfBirth' => $_POST['dateOfBirth'] ?: null,
                'gender' => $_POST['gender'] ?: null,
                'identityNumber' => $_POST['identityNumber'] ?: null,
                'identityIssuedDate' => $_POST['identityIssuedDate'] ?: null,
                'identityIssuedPlace' => $_POST['identityIssuedPlace'] ?: null,
                'email' => $_POST['email'] ?: null,
                'phoneNumber' => $_POST['phoneNumber'] ?: null,
                'maritalStatus' => $_POST['maritalStatus'] ?: null,
                'hometown' => $_POST['hometown'] ?: null,
                'placeOfBirth' => $_POST['placeOfBirth'] ?: null,
                'nationality' => $_POST['nationality'] ?: null,
                'role' => $_POST['role']
            ];

            // Kiểm tra dữ liệu hợp lệ
            if (!empty($data['username']) && !empty($data['role'] && !empty($data['password']))) {
                $result = $this->model->updateAccount($id, $data);
                if ($result) {
                    header('Location: index.php?action=account');
                    exit;
                } else {
                    $error = "Đã xảy ra lỗi khi cập nhật tài khoản.";
                }
            } else {
                $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
            }
        }
        require 'views/admin/account/edit.php';
    }

    // Xóa tài khoản
    public function delete() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        if (!isset($_GET['id'])) {
            echo "Không tìm thấy ID.";
            exit;
        }
        $id = $_GET['id'];
        $result = $this->model->deleteAccount($id);
        if ($result) {
            header('Location: index.php?action=account');
            exit;
        } else {
            echo "Đã xảy ra lỗi khi xóa tài khoản.";
        }
    }

    public function getAllOTHistory() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        try {
            $otHistory = $this->model->getAllOTHistory();
            require_once 'views/admin/account/all_ot_history.php';
        } catch (PDOException $e) {
            // Log lỗi hoặc hiển thị thông báo
            echo "Error: " . $e->getMessage();
        }
    }

    public function getAllErrorReports() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        $errorReports = $this->model->getAllErrorReports();
        require_once 'views/admin/account/all_error_reports.php';
    }

    public function getAllLeaveHistory() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        $leaveHistory = $this->model->getAllLeaveHistory();
        require_once 'views/admin/account/all_leave_history.php';
    }

    public function getAllAttendanceHistory() {
        $this->checkAdminAccess(); // Kiểm tra quyền truy cập

        $attendanceHistory = $this->model->getAllAttendanceHistory();
        require_once 'views/admin/account/all_attendance_history.php';
    }
}
?>
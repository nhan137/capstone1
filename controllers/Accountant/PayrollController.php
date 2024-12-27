<?php
require_once 'models/Accountant/CheckinCheckoutModel.php';

class PayrollController {
    private $model;
    private $pdo;
    private $itemsPerPage = 10; // Số nhân viên mỗi trang

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new CheckinCheckoutModel($pdo);
    }

    public function index() {
        if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'ke toan') {
            header("Location: index.php?action=login");
            exit();
        }
        
        // Lấy tháng và năm từ POST hoặc GET
        $month = null;
        $year = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $month = filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT);
            $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
            // Lưu vào session để giữ giá trị khi chuyển trang
            $_SESSION['payroll_month'] = $month;
            $_SESSION['payroll_year'] = $year;
        } else if (isset($_GET['page'])) {
            // Lấy từ session khi chuyển trang
            $month = $_SESSION['payroll_month'] ?? null;
            $year = $_SESSION['payroll_year'] ?? null;
        }
        
        if ($month && $year) {
            $salaries = $this->calculateMonthlySalaries($month, $year);
            
            // Xử lý phân trang
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $totalItems = count($salaries);
            $totalPages = ceil($totalItems / $this->itemsPerPage);
            
            // Đảm bảo trang hiện tại hợp lệ
            if ($currentPage < 1) $currentPage = 1;
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            
            // Lấy dữ liệu cho trang hiện tại
            $start = ($currentPage - 1) * $this->itemsPerPage;
            $salariesOnPage = array_slice($salaries, $start, $this->itemsPerPage);
            
            // Truyền thêm thông tin phân trang
            $pagination = [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'month' => $month,
                'year' => $year
            ];
        } else {
            $error = "Dữ liệu không hợp lệ";
        }
        
        include 'views/Accountant_BE/payroll_view.php';
    }

    public function calculateMonthlySalaries($month, $year) {
        if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'ke toan') {
            return [];
        }

        $employees = $this->model->getEmployees();
        $workingDays = $this->model->getWorkingDaysInMonth($month, $year);
        $results = [];

        foreach ($employees as $employee) {
            $checkins = $this->model->getEmployeeCheckins($employee['id'], $month, $year);
            $totalHours = $this->model->calculateWorkingHours($checkins);
            
            // Lấy dữ liệu OT
            $overtimeRecords = $this->model->getEmployeeOvertime($employee['id'], $month, $year);
            $overtimeSalary = 0;

            foreach ($overtimeRecords as $record) {
                // Kiểm tra xem ngày có nằm trong tháng và năm đã chọn không
                $overtimeDate = new DateTime($record['date']);
                if ($overtimeDate->format('m') == $month && $overtimeDate->format('Y') == $year) {
                    // Kiểm tra ngày trong tuần
                    $dayOfWeek = $overtimeDate->format('w'); // 0 = Chủ nhật, 6 = Thứ 7
                    $shift = $record['shift'];
                    $overtimeHours = $record['time']; // Giả sử 'time' là số giờ OT

                    // Kiểm tra nếu là ngày lễ
                    $isHoliday = $this->checkIfHoliday($overtimeDate); // Phương thức kiểm tra ngày lễ

                    if ($isHoliday) {
                        // Nếu là ngày lễ
                        $overtimeSalary += $this->model->calculateOvertimeSalary($overtimeHours, $employee['BaseSalary'] / ($workingDays * 8), 'Holiday');
                    } else {
                        // Nếu không phải là ngày lễ
                        if ($dayOfWeek == 0) { // Chủ nhật
                            $overtimeSalary += $this->model->calculateOvertimeSalary($overtimeHours, $employee['BaseSalary'] / ($workingDays * 8), 'Weekend');
                        } elseif ($dayOfWeek == 6) { // Thứ 7
                            $overtimeSalary += $this->model->calculateOvertimeSalary($overtimeHours, $employee['BaseSalary'] / ($workingDays * 8), 'Weekend');
                        } else { // Ngày làm việc bình thường
                            $overtimeSalary += $this->model->calculateOvertimeSalary($overtimeHours, $employee['BaseSalary'] / ($workingDays * 8), 'Weekday');
                        }
                    }
                }
            }

            // Tính lương theo công thức mới
            $salary = $this->model->calculateSalary(
                $employee['BaseSalary'], 
                $totalHours, 
                $workingDays,
                $overtimeSalary
            );
            
            $note = '';
            $expectedHours = $workingDays * 8;
            if ($totalHours == 0) {
                $note = 'Không có dữ liệu chấm công';
            } else if ($totalHours < $expectedHours) {
                $note = 'Làm việc dưới thời gian quy định';
            }

            // Lưu vào bảng payroll
            $this->model->saveOrUpdatePayroll(
                $employee['id'], 
                $month, 
                $year, 
                $totalHours,
                $employee['BaseSalary'] / ($workingDays * 8),
                $salary // Lưu tổng lương vào ActualSalary
            );

            $results[] = [
                'EmployeeID' => $employee['id'],
                'FullName' => $employee['FullName'],
                'TotalHours' => $totalHours,
                'BaseSalary' => $employee['BaseSalary'],
                'WorkingDays' => $workingDays,
                'Salary' => $salary,
                'OvertimeSalary' => $overtimeSalary,
                'Note' => $note
            ];
        }

        return $results;
    }

    // Phương thức kiểm tra ngày lễ
    private function checkIfHoliday($date) {
        // Giả sử bạn có một bảng hoặc danh sách các ngày lễ
        $holidays = [
            // Năm 2020
            '2020-01-01', // Ngày Tết Dương Lịch
            '2020-01-23', // Tết Nguyên Đán (29 tháng Chạp)
            '2020-01-24', // Tết Nguyên Đán (30 tháng Chạp)
            '2020-01-25', // Tết Nguyên Đán (Mùng 1)
            '2020-01-26', // Tết Nguyên Đán (Mùng 2)
            '2020-01-27', // Tết Nguyên Đán (Mùng 3)
            '2020-01-28', // Tết Nguyên Đán (Mùng 4)
            '2020-04-02', // Giỗ Tổ Hùng Vương
            '2020-04-30', // Ngày Giải Phóng Miền Nam
            '2020-05-01', // Ngày Quốc Tế Lao Động
            '2020-09-02', // Ngày Quốc Khánh

            // Năm 2021
            '2021-01-01', // Ngày Tết Dương Lịch
            '2021-02-10', // Tết Nguyên Đán (29 tháng Chạp)
            '2021-02-11', // Tết Nguyên Đán (30 tháng Chạp)
            '2021-02-12', // Tết Nguyên Đán (Mùng 1)
            '2021-02-13', // Tết Nguyên Đán (Mùng 2)
            '2021-02-14', // Tết Nguyên Đán (Mùng 3)
            '2021-02-15', // Tết Nguyên Đán (Mùng 4)
            '2021-04-21', // Giỗ Tổ Hùng Vương
            '2021-04-30', // Ngày Giải Phóng Miền Nam
            '2021-05-01', // Ngày Quốc Tế Lao Động
            '2021-09-02', // Ngày Quốc Khánh

            // Năm 2022
            '2022-01-01', // Ngày Tết Dương Lịch
            '2022-01-29', // Tết Nguyên Đán (29 tháng Chạp)
            '2022-01-30', // Tết Nguyên Đán (30 tháng Chạp)
            '2022-01-31', // Tết Nguyên Đán (Mùng 1)
            '2022-02-01', // Tết Nguyên Đán (Mùng 2)
            '2022-02-02', // Tết Nguyên Đán (Mùng 3)
            '2022-02-03', // Tết Nguyên Đán (Mùng 4)
            '2022-04-10', // Giỗ Tổ Hùng Vương
            '2022-04-30', // Ngày Giải Phóng Miền Nam
            '2022-05-01', // Ngày Quốc Tế Lao Động
            '2022-09-02', // Ngày Quốc Khánh

            // Năm 2023
            '2023-01-01', // Ngày Tết Dương Lịch
            '2023-01-20', // Tết Nguyên Đán (29 tháng Chạp)
            '2023-01-21', // Tết Nguyên Đán (30 tháng Chạp)
            '2023-01-22', // Tết Nguyên Đán (Mùng 1)
            '2023-01-23', // Tết Nguyên Đán (Mùng 2)
            '2023-01-24', // Tết Nguyên Đán (Mùng 3)
            '2023-01-25', // Tết Nguyên Đán (Mùng 4)
            '2023-04-29', // Giỗ Tổ Hùng Vương
            '2023-04-30', // Ngày Giải Phóng Miền Nam
            '2023-05-01', // Ngày Quốc Tế Lao Động
            '2023-09-02', // Ngày Quốc Khánh

            // Năm 2024
            '2024-01-01', // Ngày Tết Dương Lịch
            '2024-02-09', // Tết Nguyên Đán (29 tháng Chạp)
            '2024-02-10', // Tết Nguyên Đán (30 tháng Chạp)
            '2024-02-11', // Tết Nguyên Đán (Mùng 1)
            '2024-02-12', // Tết Nguyên Đán (Mùng 2)
            '2024-02-13', // Tết Nguyên Đán (Mùng 3)
            '2024-02-14', // Tết Nguyên Đán (Mùng 4)
            '2024-04-18', // Giỗ Tổ Hùng Vương
            '2024-04-30', // Ngày Giải Phóng Miền Nam
            '2024-05-01', // Ngày Quốc Tế Lao Động
            '2024-09-02', // Ngày Quốc Khánh

        ];

        return in_array($date->format('Y-m-d'), $holidays);
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/Employee/ViewAttendanceHistory.css">
    
</head>
<body>
    <?php include 'views/layouts/sidebar_accountant.php'; ?>
    <div class="main-content">
        <div class="attendance-container">
            <div class="page-header">
                <div class="header-left">
                    <h2>Employee Attendance History</h2>
                </div>
                <div class="header-right">
                    <form method="GET" action="index.php" class="filter-form">
                        <input type="hidden" name="action" value="viewAttendanceHistory">
                        <div class="filter-group">
                            <select name="employee_id" class="form-select">
                                <option value="">All Employees</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['EmployeeID'] ?>" 
                                        <?= $selectedEmployee == $employee['EmployeeID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control">
                            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control">
                            <button type="submit" class="filter-button">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Content -->
            <div class="records-table">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Total Hours</th>
                            <th>Check In Location</th>
                            <th>Check Out Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceData as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['FirstName'] . ' ' . $record['LastName']) ?></td>
                                <td><?= date('m/d/Y', strtotime($record['CheckinTime'])) ?></td>
                                <td><?= date('H:i:s', strtotime($record['CheckinTime'])) ?></td>
                                <td><?= $record['CheckoutTime'] ? date('H:i:s', strtotime($record['CheckoutTime'])) : 'N/A' ?></td>
                                <td><?= $record['TotalWorkHours'] ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars($record['GPSLocation']) ?></td>
                                <td><?= htmlspecialchars($record['CheckoutLocation'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($record['IsAbsent']) && $record['IsAbsent']) : ?>
                                        <span class="status-absent">Absent</span>
                                    <?php else : ?>
                                        <?php 
                                            // Hiển thị trạng thái check-in
                                            if ($record['CheckinStatus'] === 'On Time') {
                                                echo '<span class="badge badge-success">Check-in On Time</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Late Check-in</span>';
                                            }

                                            echo ' '; // Khoảng cách giữa các badge

                                            // Hiển thị trạng thái check-out
                                            if (!empty($record['CheckoutTime'])) {
                                                if ($record['CheckoutStatus'] === 'On Time') {
                                                    echo '<span class="badge badge-success">Check-out On Time</span>';
                                                } else {
                                                    echo '<span class="badge badge-warning">Early Check-out</span>';
                                                }
                                            }
                                        ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Navigation Arrows -->
            <div class="nav-arrows">
                <?php if ($totalPages > 1): ?>
                    <a href="?action=viewAttendanceHistory&page=<?= max(1, $page-1) ?>&employee_id=<?= $selectedEmployee ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" 
                       class="nav-arrow <?= $page <= 1 ? 'disabled' : '' ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
                    <a href="?action=viewAttendanceHistory&page=<?= min($totalPages, $page+1) ?>&employee_id=<?= $selectedEmployee ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" 
                       class="nav-arrow <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 
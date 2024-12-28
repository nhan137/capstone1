<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="assets/css/Director/view_employee_list.css">
    
    <script>
        function showNotificationModal(employeeId) {
            // Lấy thông báo từ server bằng AJAX
            fetch(`index.php?action=getNotifications&employeeId=${employeeId}`)
                .then(response => response.json())
                .then(notifications => {
                    const modalContent = document.getElementById('notificationContent');
                    modalContent.innerHTML = '';

                    if (notifications.length === 0) {
                        modalContent.innerHTML = '<p>Không có thông báo mới</p>';
                    } else {
                        notifications.forEach(notification => {
                            const notificationDiv = document.createElement('div');
                            notificationDiv.className = 'notification-item';
                            notificationDiv.onclick = () => window.location.href = notification.url;
                            notificationDiv.innerHTML = notification.message;
                            modalContent.appendChild(notificationDiv);
                        });
                    }

                    // Hiển thị modal
                    document.getElementById('modalOverlay').style.display = 'block';
                    document.getElementById('notificationModal').style.display = 'block';
                });
        }

        function closeModal() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('notificationModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        document.getElementById('modalOverlay').onclick = closeModal;
    </script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <?php include 'views/layouts/sidebar_director.php'; ?>
    </div>
    <div class="content" id="content">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-users"></i> List of Employees
            </h1>
            <!-- Thêm form tìm kiếm và lọc -->
            <div class="search-filter-container">
                <form method="GET" action="index.php" class="search-filter-form">
                    <input type="hidden" name="action" value="viewEmployeeList">
                    
                    <!-- Thanh tìm kiếm (màu xanh) -->
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               placeholder="Tìm kiếm theo tên ..."
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        >
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Identity Number</th>
                            <th>Identity Issued Date</th>
                            <th>Identity Issued Place</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Marital Status</th>
                            <th>Hometown</th>
                            <th>Place of Birth</th>
                            <th>Nationality</th>
                            <th>Role</th>
                            <th>Notifications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Số bản ghi trên mỗi trang
                        $itemsPerPage = 10;

                        // Tổng số bản ghi
                        $totalEmployees = count($employees); // Giả sử bạn đã có mảng này
                        $totalPages = ceil($totalEmployees / $itemsPerPage);

                        // Lấy trang hiện tại từ GET
                        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

                        // Đảm bảo trang hiện tại hợp lệ
                        if ($currentPage < 1) $currentPage = 1;
                        if ($currentPage > $totalPages) $currentPage = $totalPages;

                        // Tính chỉ số bắt đầu cho truy vấn
                        $start = ($currentPage - 1) * $itemsPerPage;

                        // Lấy dữ liệu cho trang hiện tại
                        $employeesOnPage = array_slice($employees, $start, $itemsPerPage);
                        ?>

                        <?php if (!empty($employeesOnPage)): ?>
                            <?php foreach ($employeesOnPage as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['EmployeeID']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['FirstName']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['DateOfBirth']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['Gender']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['IdentityNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['IdentityIssuedDate']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['IdentityIssuedPlace']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['PhoneNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['MaritalStatus']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['Hometown']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['PlaceOfBirth']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['Nationality']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['Role']); ?></td>
                                    <td style="text-align: center;">
                                        <a href="javascript:void(0);" class="notification-button" onclick="showNotificationModal(<?php echo $employee['EmployeeID']; ?>)">
                                            <img src="assets/images/anhthongbao.jpg" alt="Notification" class="notification-icon">
                                            <?php 
                                            $notificationCount = $employeeModel->getNotificationCount($employee['EmployeeID']);
                                            if ($notificationCount > 0): ?>
                                                <span class="notification-count"><?php echo $notificationCount; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Hiển thị phân trang -->
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?action=viewEmployeeList&page=1<?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>">First</a>
                    <a href="?action=viewEmployeeList&page=<?= $currentPage - 1 ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?action=viewEmployeeList&page=<?= $i ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?action=viewEmployeeList&page=<?= $currentPage + 1 ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>">Next</a>
                    <a href="?action=viewEmployeeList&page=<?= $totalPages ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>">Last</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for notifications -->
    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="notification-modal" id="notificationModal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2>Thông báo</h2>
        <div id="notificationContent"></div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Requests</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            text-align: center;
        }
        .status.approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .btn-outline-warning:hover {
            color: #212529;
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-outline-success:hover {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-outline-danger:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .active-filter {
            font-weight: bold;
        }

        .table {
            border: 2px solid #007bff; /* Màu xanh */
        }
        .table th, .table td {
            border: 1px solid #007bff; /* Màu xanh cho các ô */
        }
        .table th {
            background-color: #007bff; /* Màu nền cho tiêu đề */
            color: white; /* Màu chữ cho tiêu đề */
        }
    </style>
</head>
<body>
    <?php include 'views/layouts/sidebar.php'; ?>
    
    <div class="container my-5">
        <h1 class="text-center mb-4">My Leave Requests</h1>
        
        <div class="text-center mb-4">
            <form method="GET" action="">
                <input type="hidden" name="action" value="viewPendingRequests">
                <button type="submit" name="filter" value="all" 
                        class="btn <?= (!isset($_GET['filter']) || $_GET['filter'] === 'all') ? 'btn-dark' : 'btn-secondary' ?> mx-2">
                    All
                </button>
                <button type="submit" name="filter" value="pending" 
                        class="btn <?= (isset($_GET['filter']) && $_GET['filter'] === 'pending') ? 'btn-warning' : 'btn-outline-warning' ?> mx-2">
                    Pending
                </button>
                <button type="submit" name="filter" value="approved" 
                        class="btn <?= (isset($_GET['filter']) && $_GET['filter'] === 'approved') ? 'btn-success' : 'btn-outline-success' ?> mx-2">
                    Approved
                </button>
                <button type="submit" name="filter" value="rejected" 
                        class="btn <?= (isset($_GET['filter']) && $_GET['filter'] === 'rejected') ? 'btn-danger' : 'btn-outline-danger' ?> mx-2">
                    Rejected
                </button>
            </form>
        </div>

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Leave Type</th>
                    <th>Shift</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Submit Date</th>
                    <th>Approval Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($leave_requests)): ?>
                    <?php foreach ($leave_requests as $request): ?>
                        <tr>
                            <!-- <td><?= htmlspecialchars($request['LeaveRequestID']) ?></td> -->
                            <td><?= htmlspecialchars($request['StartDate']) ?></td>
                            <td><?= htmlspecialchars($request['EndDate']) ?></td>
                            <td><?= htmlspecialchars($request['Reason']) ?></td>
                            <td><?= htmlspecialchars($request['Shift']) ?></td>
                            <td><?= htmlspecialchars($request['Description']) ?></td>
                            <td class="status <?= strtolower(htmlspecialchars($request['Status'])) ?>">
                                <?= htmlspecialchars($request['Status']) ?>
                            </td>
                            <td><?= htmlspecialchars($request['SubmitDate']) ?></td>
                            <td><?= htmlspecialchars($request['ApprovedDate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">You don't have any leave requests yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?action=viewPendingRequests&page=<?= $page - 1 ?>&filter=<?= htmlspecialchars($filter) ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?action=viewPendingRequests&page=<?= $i ?>&filter=<?= htmlspecialchars($filter) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?action=viewPendingRequests&page=<?= $page + 1 ?>&filter=<?= htmlspecialchars($filter) ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

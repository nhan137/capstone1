<?php
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'ke toan') {
    header("Location: index.php?action=login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Salary Calculation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #007BFF;
        }
        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        label, select, button {
            margin-right: 10px;
        }
        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        button:hover {
            background-color: #218838;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #007BFF;
        }
        
        .pagination .active {
            background-color: #007BFF;
            color: white;
            border: 1px solid #007BFF;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <?php include 'views/layouts/sidebar_accountant.php'; ?>
    <h1>Employee Salary Calculation</h1>
    <form method="POST" action="index.php?action=payroll">
        <label for="month">Select Month:</label>
        <select name="month" id="month" required>
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= (isset($pagination['month']) && $pagination['month'] == $i) ? 'selected' : '' ?>>
                    <?= $i ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="year">Select Year:</label>
        <select name="year" id="year" required>
            <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                <option value="<?= $y ?>" <?= (isset($pagination['year']) && $pagination['year'] == $y) ? 'selected' : '' ?>>
                    <?= $y ?>
                </option>
            <?php endfor; ?>
        </select>

        <button type="submit">Calculate Salary</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="text-align: center; color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="summary" style="text-align: center; margin: 20px 0;">
        <?php if (!empty($salaries)): ?>
            <p>Total Employees: <?= count($salaries) ?></p>
            <p>Total Salary Cost: <?= number_format(array_sum(array_column($salaries, 'Salary')), 2) ?> VND</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($salariesOnPage)): ?>
        <h2 style="text-align: center;">Salary Calculation Results</h2>
        <table>
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Base Salary</th>
                <th>Working Days</th>
                <th>Total Hours</th>
                <th>Overtime Salary</th>
                <th>Total Salary</th>
            </tr>
            <?php foreach ($salariesOnPage as $salary): ?>
                <tr>
                    <td><?= htmlspecialchars($salary['EmployeeID']) ?></td>
                    <td><?= htmlspecialchars($salary['FullName']) ?></td>
                    <td><?= number_format($salary['BaseSalary'], 0) ?> VND</td>
                    <td><?= $salary['WorkingDays'] ?> days</td>
                    <td><?= number_format($salary['TotalHours'], 2) ?> hours</td>
                    <td><?= number_format($salary['OvertimeSalary'], 0) ?> VND</td>
                    <td><?= number_format($salary['Salary'], 0) ?> VND</td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['currentPage'] > 1): ?>
                    <a href="?action=payroll&page=1"><<</a>
                    <a href="?action=payroll&page=<?= $pagination['currentPage']-1 ?>"><</a>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['currentPage']-2); $i <= min($pagination['totalPages'], $pagination['currentPage']+2); $i++): ?>
                    <a href="?action=payroll&page=<?= $i ?>" class="<?= ($i == $pagination['currentPage']) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                    <a href="?action=payroll&page=<?= $pagination['currentPage']+1 ?>">></a>
                    <a href="?action=payroll&page=<?= $pagination['totalPages'] ?>">>></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p style="text-align: center; color: red;">No salary data for the selected month and year.</p>
    <?php endif; ?>
</body>
</html>

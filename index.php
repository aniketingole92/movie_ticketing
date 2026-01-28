<?php
include('admin/includes/config.php');
$bill_id = '';
$customer_name = '';
$bill_data = null;
$items = [];

if (isset($_GET['bill_id']) && isset($_GET['customer_name'])) {
    $bill_id = $_GET['bill_id'];
    $customer_name = $_GET['customer_name'];

    $stmt = $con->prepare("SELECT * FROM bills  WHERE id = ? AND customer_name = ?");
    $stmt->bind_param("is", $bill_id, $customer_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $bill_data = $result->fetch_assoc();

    if ($bill_data) {
        $stmt = $con->prepare("SELECT * FROM bill_items join products on products.id=bill_items.product_id join categories on categories.id=products.category_id  WHERE bill_id = ?");
        $stmt->bind_param("i", $bill_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        while ($row = $items_result->fetch_assoc()) {
            $items[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing System | Check Your Bill </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Billing System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Check Bill</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin/index.php" target="_blank">Admin Panel</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <div class="container">
 
        <form class="row g-3 mb-4 no-print" method="get">
            <div class="col-md-5">
                <label for="bill_id" class="form-label">Bill ID</label>
                <input type="text" class="form-control" name="bill_id" value="<?= htmlspecialchars($bill_id) ?>" required>
            </div>
            <div class="col-md-5">
                <label for="customer_name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" name="customer_name" value="<?= htmlspecialchars($customer_name) ?>" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Check Bill</button>
            </div>
        </form>

        <?php if ($bill_data): ?>
            <!-- ✅ Bill Details -->
            <div id="billArea">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Bill #<?= $bill_data['id'] ?> - <?= htmlspecialchars($bill_data['customer_name']) ?></h5>
                        <small>Date: <?= $bill_data['created_at'] ?></small>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Tax (₹)</th>
                                    <th>Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                                    <td>₹<?= $item['price'] ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>₹<?= $item['tax_amount'] ?></td>
                                    <td>₹<?= $item['total_amount'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <h5 class="text-end">Grand Total: ₹<?= $bill_data['total_amount'] ?></h5>
                    </div>
                </div>
            </div>

            <!-- ✅ Print Button -->
            <div class="mt-3 no-print text-end">
                <button onclick="window.print()" class="btn btn-outline-primary">🖨️ Print Bill</button>
            </div>

        <?php elseif ($bill_id && $customer_name): ?>
            <div class="alert alert-danger">Bill not found for ID <strong><?= htmlspecialchars($bill_id) ?></strong> and customer <strong><?= htmlspecialchars($customer_name) ?></strong>.</div>
        <?php endif; ?>
    </div>
</body>
</html>

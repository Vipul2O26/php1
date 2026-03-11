<?php
session_start();

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__.'/../utility/db.php';

if(!isset($_SESSION['email'])){
    header('Location: ../auth/login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);

try{

/* =========================
   1) ADD PRODUCT TO CART
   ========================= */
if(isset($_GET['product_id'])){
    $product_id = intval($_GET['product_id']);

    // check if already in cart
    $check = "SELECT cart_id, qty FROM cart WHERE user_id=:user_id AND product_id=:product_id";
    $stmt = $connect->prepare($check);
    $stmt->execute([
        ':user_id'=>$user_id,
        ':product_id'=>$product_id
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row){
        // increase qty
        $update = "UPDATE cart SET qty = qty + 1 WHERE cart_id=:cart_id";
        $stmt = $connect->prepare($update);
        $stmt->execute([':cart_id'=>$row['cart_id']]);
    }else{
        // insert new
        $insert = "INSERT INTO cart(product_id,user_id,qty) VALUES(:product_id,:user_id,1)";
        $stmt = $connect->prepare($insert);
        $stmt->execute([
            ':product_id'=>$product_id,
            ':user_id'=>$user_id
        ]);
    }

    header("Location: cart.php");
    exit;
}

/* =========================
   2) UPDATE QTY
   ========================= */
if(isset($_POST['update_qty'])){
    $cart_id = intval($_POST['cart_id']);
    $qty = max(1, intval($_POST['qty']));

    $sql = "UPDATE cart SET qty=:qty WHERE cart_id=:cart_id AND user_id=:user_id";
    $stmt = $connect->prepare($sql);
    $stmt->execute([
        ':qty'=>$qty,
        ':cart_id'=>$cart_id,
        ':user_id'=>$user_id
    ]);

    header("Location: cart.php");
    exit;
}

/* =========================
   3) REMOVE ITEM
   ========================= */
if(isset($_GET['remove_id'])){
    $cart_id = intval($_GET['remove_id']);

    $sql = "DELETE FROM cart WHERE cart_id=:cart_id AND user_id=:user_id";
    $stmt = $connect->prepare($sql);
    $stmt->execute([
        ':cart_id'=>$cart_id,
        ':user_id'=>$user_id
    ]);

    header("Location: cart.php");
    exit;
}

/* =========================
   4) CLEAR CART
   ========================= */
if(isset($_GET['clear'])){
    $sql = "DELETE FROM cart WHERE user_id=:user_id";
    $stmt = $connect->prepare($sql);
    $stmt->execute([':user_id'=>$user_id]);

    header("Location: cart.php");
    exit;
}

/* =========================
   5) FETCH CART ITEMS
   ========================= */
$sql = "
SELECT c.cart_id, c.qty,
p.product_id,
p.product_name,
p.product_detail,
p.product_price,
p.product_image
FROM cart c
JOIN products p ON c.product_id=p.product_id
WHERE c.user_id=:user_id
ORDER BY c.cart_id ASC
";

$stmt = $connect->prepare($sql);
$stmt->execute([':user_id'=>$user_id]);
$cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
    echo "Error: ".$e->getMessage();
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-5">

<h3 class="mb-4">Shopping Cart</h3>

<?php if(!empty($cart)){ ?>

<div class="d-flex justify-content-end mb-3">
<a href="cart.php?clear=1" class="btn btn-danger btn-sm"
onclick="return confirm('Clear entire cart?')">
Empty Cart
</a>
</div>

<?php foreach($cart as $item){

$subtotal = $item['product_price'] * $item['qty'];
$total += $subtotal;

?>

<div class="row border rounded p-3 mb-3 align-items-center">

<div class="col-md-2">
<img src="../asset/uploads/<?= htmlspecialchars($item['product_image']) ?>"
class="img-fluid">
</div>

<div class="col-md-4">
<h5><?= htmlspecialchars($item['product_name']) ?></h5>
<p><?= htmlspecialchars($item['product_detail']) ?></p>
<p class="text-muted">Price : ₹<?= $item['product_price'] ?></p>
</div>

<div class="col-md-2">

<form method="POST" action="cart.php">

<input type="hidden" name="cart_id"
value="<?= $item['cart_id'] ?>">

<input type="number"
name="qty"
value="<?= $item['qty'] ?>"
min="1"
class="form-control mb-2">

<button type="submit"
name="update_qty"
class="btn btn-primary btn-sm w-100">
Update
</button>

</form>

</div>

<div class="col-md-2">
<strong>Subtotal</strong><br>
₹<?= $subtotal ?>
</div>

<div class="col-md-2">
<a href="cart.php?remove_id=<?= $item['cart_id'] ?>"
class="text-danger"
onclick="return confirm('Remove this item?')">
Remove
</a>
</div>

</div>

<?php } ?>

<div class="card p-3">

<h4>Total : ₹ <?= $total ?></h4>

<a href="../order/shipping.php"
class="btn btn-success mt-3">
Proceed To Shipping
</a>

</div>

<?php }else{ ?>

<div class="alert alert-warning">
No products in cart.
<a href="../product/products.php" class="btn btn-warning btn-sm ms-2">
Start Shopping
</a>
</div>

<?php } ?>

</div>

</body>
</html>
ALTER TABLE cart ADD qty INT DEFAULT 1;
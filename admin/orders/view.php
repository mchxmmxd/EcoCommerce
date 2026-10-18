<?php require __DIR__.'/../_init.php';
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT); $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
checkCsrf();
try {
$pdo->beginTransaction(); $s=$pdo->prepare('SELECT * FROM orders WHERE id=? FOR UPDATE'); $s->execute([$id]); $r=$s->fetch();
if (!$r) throw new RuntimeException('Order not found.');
$next=$_POST['status']??'';
$allowed=['pending'=>['processing','cancelled'],'processing'=>['shipped','cancelled'],'shipped'=>['delivered'],'delivered'=>[],'cancelled'=>[]];
if (!in_array($next,$allowed[$r['status']]??[],true)) throw new RuntimeException('Invalid order transition.');
if ($next!=='cancelled' && $r['payment_method']==='card' && $r['payment_status']!=='paid') throw new RuntimeException('Card payment must be completed first.');
if ($next==='cancelled') {
$s=$pdo->prepare('SELECT product_id,quantity FROM order_items WHERE order_id=? ORDER BY product_id'); $s->execute([$id]);
$restore=$pdo->prepare('UPDATE products SET stock=stock+? WHERE id=?'); foreach($s->fetchAll() as $item) $restore->execute([$item['quantity'],$item['product_id']]);
}
$payment=$r['payment_status'];
if ($next==='cancelled') $payment=$payment==='paid'?'refunded':'failed';
if ($next==='delivered' && $r['payment_method']==='cod') $payment='paid';
$s=$pdo->prepare('UPDATE orders SET status=?,payment_status=? WHERE id=?'); $s->execute([$next,$payment,$id]); $pdo->commit(); message('Order updated. Any refund shown is demo bookkeeping only.','view.php?id='.$id);
} catch(Throwable $ex) { if($pdo->inTransaction()) $pdo->rollBack(); $error=$ex instanceof RuntimeException?$ex->getMessage():'Could not update order.'; }
}
$s=$pdo->prepare('SELECT o.*,u.email FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?'); $s->execute([$id]); $r=$s->fetch(); if(!$r){http_response_code(404);exit('Order not found.');}
adminHeader('Order #'.$id); ?><p><?= e($error) ?></p><div class="card"><h2><?= e($r['shipping_name']) ?></h2><p><?= e($r['email']) ?> · <?= e($r['shipping_phone']) ?></p><p><?= nl2br(e($r['shipping_address'])) ?>, <?= e($r['shipping_city']) ?></p><p>Status: <?= e($r['status']) ?> | <?= e($r['payment_method'].' / '.$r['payment_status']) ?></p><strong>৳<?= e($r['total']) ?></strong></div><table><tr><th>Product</th><th>Quantity</th><th>Unit price</th></tr><?php $s=$pdo->prepare('SELECT i.*,p.name FROM order_items i JOIN products p ON i.product_id=p.id WHERE order_id=?'); $s->execute([$id]); foreach($s as $item): ?><tr><td><?= e($item['name']) ?></td><td><?= (int)$item['quantity'] ?></td><td>৳<?= e($item['price']) ?></td></tr><?php endforeach ?></table><form method="post"><?= csrf() ?><label>Next status<select name="status"><option value="processing">Processing</option><option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select></label><button>Update status</button></form><p><small>Transitions proceed forward. Cancellation restores stock once. Delivered COD orders are marked paid. All payments and refunds are demo records.</small></p><?php adminFooter(); ?>
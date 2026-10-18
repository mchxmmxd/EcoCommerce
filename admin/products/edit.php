<?php require __DIR__.'/../_init.php';
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT);
$r = array_fill_keys(['name', 'slug', 'category_id', 'description', 'price', 'stock'],''); $error='';
if ($id) { $s=$pdo->prepare('SELECT * FROM products WHERE id=?'); $s->execute([$id]); $r=$s->fetch(); if (!$r) { http_response_code(404); exit('Item not found.'); } }
if ($_SERVER['REQUEST_METHOD']==='POST') { checkCsrf();
foreach(array_keys($r) as $key) if (isset($_POST[$key]) && is_string($_POST[$key])) $r[$key]=trim($_POST[$key]);
try {
if (empty($r['name']) || strlen($r['name'])>200 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$r['slug']) || strlen($r['slug'])>220) throw new RuntimeException('Enter a name and a lowercase slug such as wireless-mouse.');
if (!is_numeric($r['price']) || $r['price']<0 || $r['price']>99999999.99 || filter_var($r['stock'],FILTER_VALIDATE_INT)===false || $r['stock']<0 || $r['stock']>2147483647) throw new RuntimeException('Enter a valid nonnegative price and whole stock quantity.');
$r['category_id']=$r['category_id']==='' ? null : $r['category_id'];
$values=[]; foreach(['name', 'slug', 'category_id', 'description', 'price', 'stock'] as $key) $values[]=$r[$key];
if ($id) { $values[]=$id; $s=$pdo->prepare('UPDATE products SET name=?,slug=?,category_id=?,description=?,price=?,stock=? WHERE id=?'); } else $s=$pdo->prepare('INSERT INTO products (name,slug,category_id,description,price,stock) VALUES (?,?,?,?,?,?)');
$s->execute($values); message('Saved successfully.','index.php');
} catch (PDOException $ex) { $error='Could not save. Check that the slug is unique and the category exists.'; } catch (RuntimeException $ex) { $error=$ex->getMessage(); }
}
adminHeader(($id?'Edit':'Add').' Product'); ?><p><?= e($error) ?></p><form method="post"><?= csrf() ?><label>Name<input name="name" value="<?= e($r['name']) ?>" required ></label><label>Slug<input name="slug" value="<?= e($r['slug']) ?>" required ></label><label>Category<select name="category_id"><option value="">Uncategorized</option><?php foreach($pdo->query('SELECT * FROM categories ORDER BY name') as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $r['category_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach ?></select></label><label>Description<textarea name="description"><?= e($r['description']) ?></textarea></label><label>Price<input name="price" value="<?= e($r['price']) ?>" required type="number" min="0" step="0.01"></label><label>Stock<input name="stock" value="<?= e($r['stock']) ?>" required type="number" min="0" step="1"></label><button>Save</button> <a href="index.php">Cancel</a></form><?php adminFooter(); ?>
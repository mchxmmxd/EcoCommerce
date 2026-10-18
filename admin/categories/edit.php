<?php require __DIR__.'/../_init.php';
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT);
$r = array_fill_keys(['name', 'slug'],''); $error='';
if ($id) { $s=$pdo->prepare('SELECT * FROM categories WHERE id=?'); $s->execute([$id]); $r=$s->fetch(); if (!$r) { http_response_code(404); exit('Item not found.'); } }
if ($_SERVER['REQUEST_METHOD']==='POST') { checkCsrf();
foreach(array_keys($r) as $key) if (isset($_POST[$key]) && is_string($_POST[$key])) $r[$key]=trim($_POST[$key]);
try {
if (empty($r['name']) || strlen($r['name'])>100 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$r['slug']) || strlen($r['slug'])>120) throw new RuntimeException('Enter a name and a lowercase slug such as wireless-mouse.');
$values=[]; foreach(['name', 'slug'] as $key) $values[]=$r[$key];
if ($id) { $values[]=$id; $s=$pdo->prepare('UPDATE categories SET name=?,slug=? WHERE id=?'); } else $s=$pdo->prepare('INSERT INTO categories (name,slug) VALUES (?,?)');
$s->execute($values); message('Saved successfully.','index.php');
} catch (PDOException $ex) { $error='Could not save. Check that the slug is unique and the category exists.'; } catch (RuntimeException $ex) { $error=$ex->getMessage(); }
}
adminHeader(($id?'Edit':'Add').' Category'); ?><p><?= e($error) ?></p><form method="post"><?= csrf() ?><label>Name<input name="name" value="<?= e($r['name']) ?>" required ></label><label>Slug<input name="slug" value="<?= e($r['slug']) ?>" required ></label><button>Save</button> <a href="index.php">Cancel</a></form><?php adminFooter(); ?>
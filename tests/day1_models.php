<?php
// Isolated SQLite only: never boot Laravel or load .env.
require __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\QueryException;
use App\Models\User;

$db = new Capsule;
$db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]);
$db->setAsGlobal();
$db->bootEloquent();
$db->getContainer()->instance('db', $db->getDatabaseManager());
$db->getContainer()->bind('db.schema', fn () => $db->schema());
Facade::setFacadeApplication($db->getContainer());
foreach (glob(__DIR__.'/../database/migrations/*.php') as $file) {
    (require $file)->up();
}
$count = 0;
function check(bool $condition, string $message): void {
    global $count;
    if (!$condition) { throw new RuntimeException($message); }
    $count++;
}
function rejects(callable $operation, string $message): void {
    try { $operation(); } catch (QueryException $e) { check(true, $message); return; }
    throw new RuntimeException($message);
}
check($db->schema()->hasColumn('users', 'role'), 'users.role missing');
check($db->schema()->hasColumn('users', 'is_active'), 'users.is_active missing');
$id = $db->table('users')->insertGetId(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'not-a-real-password']);
$user = User::findOrFail($id);
check($user->role === 'ADMIN', 'default role must be ADMIN');
check($user->is_active === true, 'is_active boolean cast');
check(!array_key_exists('password', $user->toArray()), 'password hidden');
check(!(new User)->isFillable('role') && !(new User)->isFillable('is_active'), 'privileges cannot be mass assigned');
rejects(fn () => $db->table('users')->where('id', $id)->update(['role' => 'VISITOR']), 'invalid role rejected');
$db->table('users')->where('id', $id)->update(['role' => 'SUPER_ADMIN', 'is_active' => false]);
check(User::findOrFail($id)->is_active === false, 'inactive user cast');
echo "PASS: {$count} checks; isolated SQLite :memory:\n";

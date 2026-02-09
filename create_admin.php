<?php
// create_admin.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::create([
        'name' => 'Администратор',
        'email' => 'admin@example.com', 
        'password' => Hash::make('12345678'),
        'role' => 'admin',
        'email_verified_at' => now(),
        'is_active' => true,
    ]);
    
    echo "Администратор создан!\n";
    echo "Email: admin@example.com\n";
    echo "Пароль: 12345678\n";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$compiler = app('blade.compiler');
$string1 = '@forelse($ordenesRecientes as $orden)';
$string2 = '@forelse ($ordenesRecientes as $orden)';

echo "String 1 compiled:\n" . $compiler->compileString($string1) . "\n\n";
echo "String 2 compiled:\n" . $compiler->compileString($string2) . "\n\n";

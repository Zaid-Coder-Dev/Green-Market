<?php
$producerSections = [
    'dashboard',
    'profile',
    'product-add',
    'product-edit',
    'product-view',
    'order-view',
    'categorie-view',
    'products',
    'orders',
    'payments',
    'reviews',
    'categories',
];

foreach ($producerSections as $producerSection) {
    require __DIR__ . '/sections/' . $producerSection . '.php';
}
?>

<?php
$formType = strtolower(trim($_REQUEST['form_type'] ?? 'contact'));

$routeMap = [
    'contact' => '/api/forms/contact',
    'mosaic' => '/api/forms/mosaic',
    'prospera' => '/api/forms/prospera',
    'sports' => '/api/forms/sports',
    'business-loans' => '/api/forms/financing',
    'sba-loans' => '/api/forms/financing',
    'equipment-loans' => '/api/forms/financing',
    'bridge-loans' => '/api/forms/financing',
    'working-capital' => '/api/forms/financing',
    'financing' => '/api/forms/financing'
];

$target = $routeMap[$formType] ?? '/api/forms/contact';
header("Location: " . $target, true, 307);
exit();

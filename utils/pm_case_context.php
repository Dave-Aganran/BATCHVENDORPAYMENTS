<?php
function getCaseVars($mockDataFile = null) {
    $vars = [
        'BATCH_ID' => 101,
        'VENDORS' => [
            ['id' => 'V001', 'name' => 'Alpha Ltd', 'amount' => 250000],
            ['id' => 'V002', 'name' => 'Beta Co', 'amount' => 175000]
        ],
        'APPROVER' => 'finance_manager',
        'STATUS' => 'APPROVED'
    ];

    if ($mockDataFile && file_exists($mockDataFile)) {
        $fileVars = json_decode(file_get_contents($mockDataFile), true);
        return array_merge($vars, $fileVars);
    }
    
    return $vars;
}

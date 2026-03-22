<?php

$fields = [
'bank_account_holder_name' => ['type'=>'varchar','len'=>'50'],
'bank_account_number' => ['type'=>'varchar','len'=>'50'],
'employer_name' => ['type'=>'varchar','len'=>'100'],
'employee_payroll_number' => ['type'=>'varchar','len'=>'50'],
'employer_mobile' => ['type'=>'varchar','len'=>'50'],
];

foreach ($fields as $name => $def) {

    $dictionary['Account']['fields'][$name]['name'] = $name;
    $dictionary['Account']['fields'][$name]['vname'] = 'LBL_' . strtoupper($name);
    $dictionary['Account']['fields'][$name]['type'] = $def['type'];
    $dictionary['Account']['fields'][$name]['inline_edit'] = '';
    $dictionary['Account']['fields'][$name]['audited'] = true;
    $dictionary['Account']['fields'][$name]['required'] = false;

    if (isset($def['len'])) {
        $dictionary['Account']['fields'][$name]['len'] = $def['len'];
    }

    if (isset($def['options'])) {
        $dictionary['Account']['fields'][$name]['options'] = $def['options'];
    }

    if (isset($def['dbType'])) {
        $dictionary['Account']['fields'][$name]['dbType'] = $def['dbType'];
    }

    if (isset($def['default'])) {
        $dictionary['Account']['fields'][$name]['default'] = $def['default'];
    }
}
<?php

$fields = [
    'middle_name' => ['type' => 'varchar', 'len' => '100'],
    'alternate_name' => ['type' => 'varchar', 'len' => '100'],

    'date_of_birth' => ['type' => 'datetimecombo'],
    'gender' => ['type' => 'enum', 'options' => 'gender_list'],
    'nationality' => ['type' => 'varchar', 'len' => '50'],
    'other_nationality' => ['type' => 'varchar', 'len' => '50'],

    'marital_status' => ['type' => 'enum', 'options' => 'marital_status'],
    'identification_type' => ['type' => 'enum', 'options' => 'identification_type'],
    'identification_number' => ['type' => 'varchar', 'len' => '50'],

    'fatca_social_security_number' => ['type' => 'varchar', 'len' => '50'],
    'tax_country' => ['type' => 'varchar', 'len' => '50'],
    'tax_identification_number' => ['type' => 'varchar', 'len' => '50'],

    'epos_email' => ['type' => 'varchar', 'len' => '255'],

    'mobile_country_code' => ['type' => 'varchar', 'len' => '10'],
    'alt_mobile_country_code' => ['type' => 'varchar', 'len' => '10'],

    'primary_address_full_address' => ['type' => 'varchar', 'len' => '255'],
    'primary_address_unit_number' => ['type' => 'varchar', 'len' => '100'],
    'primary_address_building_number' => ['type' => 'varchar', 'len' => '100'],
    'primary_address_district' => ['type' => 'varchar', 'len' => '100'],

    'employer_name' => ['type' => 'varchar', 'len' => '100'],
    'employer_email' => ['type' => 'varchar', 'len' => '100'],
    'employer_mobile' => ['type' => 'phone', 'len' => '100', 'dbType' => 'varchar'],
    'employer_full_address' => ['type' => 'varchar', 'len' => '100'],

    // 'occupation' => ['type'=>'varchar','len'=>'100'],
    'country_of_birth' => ['type' => 'varchar', 'len' => '50'],

    'bank_account_holder' => ['type' => 'varchar', 'len' => '100'],
    'bank_account_number' => ['type' => 'varchar', 'len' => '50'],
    'payroll_number' => ['type' => 'varchar', 'len' => '50'],

    'is_smoker' => ['type' => 'bool', 'default' => '0'],
    'age' => ['type' => 'int'],

    'ext_tenant_id' => ['type' => 'varchar', 'len' => '50'],
    'ext_channel_id' => ['type' => 'varchar', 'len' => '50'],
    'ext_assigned_to' => ['type' => 'varchar', 'len' => '50'],
    'ext_record_id' => ['type' => 'varchar', 'len' => '50'],
    'ext_party_type' => ['type' => 'varchar', 'len' => '50'],

    'verification_data' => ['type' => 'text'],
];
;

foreach ($fields as $name => $def) {

    $dictionary['Contact']['fields'][$name]['name'] = $name;
    $dictionary['Contact']['fields'][$name]['vname'] = 'LBL_' . strtoupper($name);
    $dictionary['Contact']['fields'][$name]['type'] = $def['type'];
    $dictionary['Contact']['fields'][$name]['inline_edit'] = '';
    $dictionary['Contact']['fields'][$name]['audited'] = true;
    $dictionary['Contact']['fields'][$name]['required'] = false;

    if (isset($def['len'])) {
        $dictionary['Contact']['fields'][$name]['len'] = $def['len'];
    }

    if (isset($def['options'])) {
        $dictionary['Contact']['fields'][$name]['options'] = $def['options'];
    }

    if (isset($def['dbType'])) {
        $dictionary['Contact']['fields'][$name]['dbType'] = $def['dbType'];
    }

    if (isset($def['default'])) {
        $dictionary['Contact']['fields'][$name]['default'] = $def['default'];
    }
}
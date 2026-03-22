<?php
/**
 * Add custom endpoint to search party.
 * @author erick.pham@360f.com
 */
$app->post('/searchParty', 'PartyController:searchParty');
$app->post('/bulkAddParty', 'PartyController:bulkAddParty');
?>
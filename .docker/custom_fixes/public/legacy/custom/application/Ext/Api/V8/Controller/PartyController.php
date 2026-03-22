<?php
/**
 * Add custom endpoint to search party.
 * @author erick.pham@360f.com
 */
use Api\V8\BeanDecorator\BeanManager;
use Api\V8\Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use Api\V8\JsonApi\Response\MetaResponse;
use Api\V8\JsonApi\Response\DataResponse;
use Api\V8\JsonApi\Helper\AttributeObjectHelper;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class PartyController extends BaseController
{
    /**
     * @var BeanManager
     */
    private BeanManager $beanManager;

    public function __construct(BeanManager $beanManager)
    {
        $this->beanManager = $beanManager;
    }

    /**
     * @param SugarBean $bean
     * @param array|null $fields
     * @param string|null $path
     *
     * @return DataResponse
     */
    private function getDataResponse(SugarBean $bean, ?array $fields = null, ?string $path = null): DataResponse
    {
        $dataResponse = new DataResponse($bean->getObjectName(), $bean->id);
        $attributeObjectHelper = new AttributeObjectHelper($this->beanManager);
        $dataResponse->setAttributes($attributeObjectHelper->getAttributes($bean, $fields));
        return $dataResponse;
    }

    /**
     * Build SQL WHERE Like conditions from filter array
     */
    private function buildWhereLikeConditions(array $filter, $db): string
    {
        $where_filter_arr = [];
        foreach ($filter as $field => $condition) {
            foreach ($condition as $operator => $value) {
                switch (strtolower($operator)) {
                    case 'like':
                        $where_filter_arr[] = "contacts.{$field} LIKE '" . $db->quote($value) . "'";
                        break;
                    case 'in':
                        if (is_array($value) && !empty($value)) {
                            $quotedValues = array_map(fn($v) => "'" . $db->quote($v) . "'", $value);
                            $where_filter_arr[] = "contacts.{$field} IN (" . implode(",", $quotedValues) . ")";
                        }
                        break;
                }
            }
        }
        return empty($where_filter_arr) ? "" : implode(' OR ', $where_filter_arr);
    }

    /**
     * Build SQL WHERE conditions from filter array
     */
    private function buildWhereConditions(array $filter, $db, string $filter_user): string
    {
        $where_filter_arr = [];
        if ($filter_user) {
            $where_filter_arr[] = $filter_user;
        }
        foreach ($filter as $field => $condition) {
            if ($field === 'deleted') {
                continue;
            }
            foreach ($condition as $operator => $value) {
                $operator = strtolower($operator);
                switch ($operator) {
                    case 'neq':
                        $where_filter_arr[] = "contacts.{$field} != '" . $db->quote($value) . "'";
                        break;
                    case 'eq':
                        $where_filter_arr[] = "contacts.{$field} = '" . $db->quote($value) . "'";
                        break;
                    case 'in':
                        if (is_array($value) && !empty($value)) {
                            $quotedValues = array_map(fn($v) => "'" . $db->quote($v) . "'", $value);
                            $where_filter_arr[] = "contacts.{$field} IN (" . implode(",", $quotedValues) . ")";
                        }
                        break;
                }
            }
        }
        return empty($where_filter_arr) ? "" : implode(' AND ', $where_filter_arr);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function searchParty(Request $request, Response $response): Response
    {
        try {
            $jsonResponse = ['data' => []];
            $oauth2Token = new OAuth2Tokens;
            $oauth2Token->retrieve_by_string_fields([
                'access_token' => $request->getAttribute('oauth_access_token_id')
            ]);
            $user = new User();
            $user->retrieve($oauth2Token->assigned_user_id);

            $module = 'Contacts';
            $requestBody = $request->getParsedBody();
            $fields = $requestBody['data']['field'] ?? [];
            $searchParam = $requestBody['data']['search'] ?? [];
            $filter = $requestBody['data']['filter'] ?? [];
            $size = $requestBody['data']['page']['size'] ?? 10;
            $number = $requestBody['data']['page']['number'] ?? 1;
            $orderBy = $requestBody['data']['sort'] ?? '';

            $bean = $this->beanManager->newBeanSafe($module);

            if (!$bean->ACLAccess('view')) {
                throw new AccessDeniedException();
            }

            $user_list = [$oauth2Token->assigned_user_id];
            $filter_user = $user->is_admin ? "" : "contacts.assigned_user_id IN ('" . implode("','", $user_list) . "')";
            $db = $bean->db;
            $where = $this->buildWhereConditions($filter, $db, $filter_user);
            $where_search = $this->buildWhereLikeConditions($searchParam, $db);
            $deleted = isset($filter['deleted']) ? $db->quote($filter['deleted']['eq']) : "0";

            $offset = $number !== 0 ? ($number - 1) * $size : $number;
            if ($where_search) {
                $where = $where ? $where . " AND (" . $where_search . ")" : $where_search;
            }

            $where_count = $where ? $where . " AND contacts.deleted = '" . $deleted . "'" : "";
            $realRowCount = $this->beanManager->countRecords($module, $where_count);
            $limit = $size === BeanManager::DEFAULT_ALL_RECORDS ? BeanManager::DEFAULT_LIMIT : $size;

            if (empty($fields)) {
                $fields = $this->beanManager->getDefaultFields($bean);
            }

            $beanListResponse = $this->beanManager->getList($module)
                ->orderBy($orderBy)
                ->where($where)
                ->offset($offset)
                ->limit($limit)
                ->max($size)
                ->deleted($deleted)
                ->fields($this->beanManager->filterAcceptanceFields($bean, $fields))
                ->fetch();

            $data = [];
            foreach ($beanListResponse->getBeans() as $beanItem) {
                $beanSafe = $this->beanManager->getBeanSafe($module, $beanItem->id);
                $data[] = $this->getDataResponse(
                    $beanSafe,
                    $fields,
                    $request->getUri()->getPath() . '/' . $beanSafe->id
                );
            }

            $jsonResponse['data'] = $data;

            if ($data && $limit !== BeanManager::DEFAULT_LIMIT) {
                $totalPages = ceil($realRowCount / $size);
                $jsonResponse['meta'] = new MetaResponse([
                    'total-records' => $realRowCount,
                    'total-pages' => $totalPages,
                    'records-on-this-page' => count($data)
                ]);
            }

            if (!empty($requestBody['debug'])) {
                $jsonResponse["where"] = $where;
                $jsonResponse["where_count"] = $where_count;
            }
            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (\Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function bulkAddParty(Request $request, Response $response): Response
    {
        try {
            $jsonResponse = ['data' => []];
            $oauth2Token = new OAuth2Tokens;
            $oauth2Token->retrieve_by_string_fields([
                'access_token' => $request->getAttribute('oauth_access_token_id')
            ]);
            $user = new User();
            $user->retrieve($oauth2Token->assigned_user_id);

            $module = 'Contacts';
            $requestBody = $request->getParsedBody();
            $createPartyList = $requestBody['data'] ?? [];

            $bean = $this->beanManager->newBeanSafe($module);

            if (!$bean->ACLAccess('save')) {
                throw new AccessDeniedException('You do not have permission to create records in this module.');
            }

            $results = [];

            foreach ($createPartyList as $leadData) {
                try {
                    $partyBean = $this->beanManager->newBeanSafe($module);

                    foreach ($leadData as $field => $value) {
                        if ($partyBean->field_defs[$field] ?? false) {
                            $partyBean->$field = $value;
                        }
                    }

                    $partyBean->save();

                    $results[] = [
                        'success' => true,
                        'id' => $partyBean->id,
                        'ext_record_id' => $leadData['ext_record_id']
                    ];

                } catch (\Exception $e) {
                    $results[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'ext_record_id' => $leadData['ext_record_id']
                    ];
                }
            }

            $jsonResponse['data'] = $results;
            return $this->generateResponse($response, $jsonResponse, 200);
        } catch (\Exception $exception) {
            return $this->generateErrorResponse($response, $exception, 400);
        }
    }
}

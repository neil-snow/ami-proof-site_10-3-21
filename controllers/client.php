<?PHP
include_once(__DIR__."/../inc/db.inc.php");
include_once(__DIR__."/../inc/ErrorManager.class.php");
include_once(__DIR__."/../inc/db.class.php");
include_once(__DIR__."/../inc/classes.inc.php");
include_once(__DIR__."/../inc/jobs.conf.php");

$action =  '';
$post = !empty($_POST)?$_POST:array();
if(!empty($post)){
	$action = isset($post['action'])?$post['action']:'';
}

$mysqlObj = new MySQL();
$mysqlObj->connect(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD);

if(isset($action) && $action!=''){
	switch($action){
		case 'client_list':
			//$client_list = "SELECT * FROM "
			$sqlParam = "select * from " . $CLIENTS_TABLE ;

			//This is for search
			if(isset($post['search']['value']) && $post['search']['value']!=''){
				$search_text = $post['search']['value'];
				$sqlParam .= " WHERE sName LIKE '%".$search_text."%'";
				$sqlParam .= " OR sCity LIKE '%".$search_text."%'";
				$sqlParam .= " OR sState LIKE '%".$search_text."%'";
				$sqlParam .= " OR sContact LIKE '%".$search_text."%'";
				$sqlParam .= " OR sContactPhone LIKE '%".$search_text."%'";
				$sqlParam .= " OR sFloridaContactEmail LIKE '%".$search_text."%'";
				$sqlParam .= " OR sContactEmail LIKE '%".$search_text."%'";
				$sqlParam .= " OR sContactFax LIKE '%".$search_text."%'";
				$sqlParam .= " OR sTrackingEmail LIKE '%".$search_text."%'";
				$sqlParam .= " OR bReseller LIKE '%".$search_text."%'";
			}

			//This is for count
			$count = $mysqlObj->count($sqlParam);

			//This is for pagination
			if(isset($post['order'][0]['column']) && $post['order'][0]['column'] > 0 && isset($post['order'][0]['dir'])){
				$columnOrder =  array("", "sName", "sCity", "sState", "sContact", "sContactPhone", "sFloridaContactEmail", "sContactEmail", "sContactFax", "sTrackingEmail", "bReseller");
				$sqlParam .= " order by ".$columnOrder[$post['order'][0]['column']]." ".$post['order'][0]['dir'];
			} else {
				$sqlParam .= " order by sName ASC";
			}
			if(isset($post['start']) && isset($post['length']) && $post['length'] > 0){
				$sqlParam .= " LIMIT ".$post['start'].", ".$post['length'];
			}
			
			
			$clientList = $mysqlObj->queryFetchAll($sqlParam);

                        $action = '';
			if(!empty($clientList)){
				foreach($clientList as $key => $client){

					/*if(isset($post['ref']) && $post['ref']!=''){

						$arr = explode('?', $post['ref']);
						$routes = explode('=', $arr[1]);
						if(isset($routes[1]) && $routes[1] == 'jobs.add'){
							$action = "<a href='javascript:void(0);' onClick='selectClient(".$client['id'].")'>Select</a>";
						} else {
                                                    $action = "<a href='javascript:void(0);' onClick='editClient(".$client['id'].")'>Select</a>";
                                                }
					} else {
						$action = "<a href='javascript:void(0);' onClick='editClient(".$client['id'].")'>Select</a>";
						//$action .= " | <a href='javascript:void(0);' onClick='deleteClient(".$client['id'].")'>Delete</a>";
					}*/
                                        $action = "<a href='javascript:void(0);' onClick='selectClient(".$client['id'].")'>Select</a>";
					
					$clientList[$key]['action'] = $action;
					$clientList[$key]['Reseller'] = isset($client['bReseller']) && $client['bReseller'] == 1?"Yes":"No";
				}
			}
                        
                        $response = array(
				"draw" => isset($post['draw'])?intval($post['draw']):'',
				"iTotalRecords" => $count,
				"iTotalDisplayRecords" => $count,
				"aaData" => $clientList
			);

			echo json_encode($response);
			exit;
			 

		break;
	}
}

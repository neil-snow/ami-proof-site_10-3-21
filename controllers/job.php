<?PHP
include_once(__DIR__."/../inc/db.inc.php");
include_once(__DIR__."/../inc/ErrorManager.class.php");
include_once(__DIR__."/../inc/db.class.php");
include_once(__DIR__."/../inc/classes.inc.php");
include_once(__DIR__."/../inc/jobs.conf.php");

session_start();
$logged_in_wUsername = isset($_SESSION['sesUser']->fields['wUsername'])?$_SESSION['sesUser']->fields['wUsername']:'';
$logged_in_email = isset($_SESSION['sesUser']->fields['sEmail'])?$_SESSION['sesUser']->fields['sEmail']:'';



$action =  '';
$post = !empty($_POST)?$_POST:array();
if(!empty($post)){
	$action = isset($post['action'])?$post['action']:'';
}

$mysqlObj = new MySQL();
$mysqlObj->connect(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD);

if(isset($action) && $action!=''){
	switch($action){
		case 'job_list':
			//$client_list = "SELECT * FROM "
			//$sqlParam = "select * from " . $CLIENTS_TABLE ;
                        $sqlParam = "select t1.*, "
                                . "DATE_FORMAT(t1.dtDate,'%M %e,%Y %l:%i %p') as dtStart, "
                                . "DATE_FORMAT(t1.dtStamp,'%M %e,%Y %l:%i %p') as dtRevised, t2.sName as clientName "
                                . "from " . $JOBS_TABLE . " as t1, " 
                                . $CLIENTS_TABLE . " as t2 "
                                . "where t2.id=t1.iCal ";
                                //. $alphaLimit . " order by " . $sqo . " DESC";
                        
			//This is for search
			if(isset($post['search']['value']) && $post['search']['value']!=''){
				$search_text = $post['search']['value'];
				$sqlParam .= " AND (t2.sName LIKE '%".mysql_real_escape_string($search_text)."%'";
				$sqlParam .= " OR t1.sName LIKE '%".mysql_real_escape_string($search_text)."%'";
				$sqlParam .= " OR DATE_FORMAT(t1.dtDate,'%M %e,%Y %l:%i %p') LIKE '%".mysql_real_escape_string($search_text)."%'";
				$sqlParam .= " OR DATE_FORMAT(t1.dtStamp,'%M %e,%Y %l:%i %p') LIKE '%".mysql_real_escape_string($search_text)."%')";
			}
                        
                        //This is for alphabet wildsearch
                        if(isset($post['alpha']) && $post['alpha']!=''){
                            if($post['alpha'] == 'num'){
                                $sqlParam .= " AND t1.sName REGEXP '^[0-9]+$'";
                            } else {
                                $sqlParam .= " AND t1.sName LIKE '".mysql_real_escape_string($post['alpha'])."%'";
                            }
                        }
                        
                        //This is for job type
                        if(isset($post['job_type']) && ($post['job_type'] == '000000' || $post['job_type'] == '339900')){
                            $sqlParam .= " AND t1.sColor = '#".mysql_real_escape_string($post['job_type'])."'";
                        }
                        
                        //This is for archive
                        if(isset($post['isArchive']) && $post['isArchive'] == true){
                            $sqlParam .= " AND (DATE_FORMAT(t1.dtStamp,'%Y') < '2011') ";
                        } else {
                            $sqlParam .= " AND (DATE_FORMAT(t1.dtStamp,'%Y') > '2011') ";
                        }
                        
                        //This is for my jobs
                        if(isset($post['myJob']) && $post['myJob'] == true){
                            $sqlParam .= "AND (";
                            if(isset($logged_in_wUsername) && $logged_in_wUsername!=''){
                                $sqlParam .= " LOWER(t1.wOwner)= '".mysql_real_escape_string(strtolower($logged_in_wUsername))."'";
                            }
                            
                            if(isset($logged_in_email) && $logged_in_email!=''){
                                $sqlParam .= " OR LOWER(t2.sContactEmail) = '".mysql_real_escape_string(strtolower($logged_in_email))."' ";
                            }
                            $sqlParam .= " ) ";
                        }
                        
                        
                        //This is for client
                        if(isset($post['calId']) && $post['calId'] > 0){
                            $sqlParam .= "and t1.iCal=" . mysql_real_escape_string($post['calId']) . "";
                            $columnOrder =  array("", "sColor", "sName", "dtDate", "dtStamp");
                        } else {
                            $columnOrder =  array("", "clientName", "sColor", "sName", "dtDate", "dtStamp");
                        }
                        //echo $sqlParam;exit;
                        //This is for count
			$count = $mysqlObj->count($sqlParam);

			//This is for pagination
			if(isset($post['order'][0]['column']) && $post['order'][0]['column'] > 0 && isset($post['order'][0]['dir'])){
				//$columnOrder =  array("", "clientName", "sColor", "sName", "dtStart", "dtRevised");
				$sqlParam .= " order by ".mysql_real_escape_string($columnOrder[$post['order'][0]['column']])." ".mysql_real_escape_string($post['order'][0]['dir']);
			} else {
				$sqlParam .= " order by t1.dtStamp desc";
			}
			if(isset($post['start']) && isset($post['length']) && $post['length'] > 0){
				$sqlParam .= " LIMIT ".mysql_real_escape_string($post['start']).", ".mysql_real_escape_string($post['length']);
			}
			
			$jobList = $mysqlObj->queryFetchAll($sqlParam);

                        $action = '';
			if(!empty($jobList)){
				foreach($jobList as $key => $job){
                                    
                                        $edit_url = ami_interface_url('jobs.edit')."&id=".$job['id']."&cId=".$job['iCal'];
                                        $action = "<a href='".$edit_url."' >View | Edit</a>";
					
//                                        $jobList[$key]['dtStart'] = date("F, d Y H:i:s", strtotime($job['dtStart']));
//                                        $jobList[$key]['dtRevised'] = date("F, d Y H:i:s", strtotime($job['dtRevised']));
					$jobList[$key]['action'] = $action;
                                        $jobList[$key]['sColor'] = '<span style="width:12px;height:12px;font-size:12px;background-color:'.$job['sColor'].'">&nbsp;</span>';
				}
			}
                        
                        $response = array(
				"draw" => isset($post['draw'])?intval($post['draw']):'',
				"iTotalRecords" => $count,
				"iTotalDisplayRecords" => $count,
				"aaData" => $jobList
			);

			echo json_encode($response);
			exit;
			 

		break;
	}
}

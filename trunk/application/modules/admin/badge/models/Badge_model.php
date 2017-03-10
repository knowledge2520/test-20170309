<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Badge_model extends MY_Model{

    protected $table_name	= "pet_badge";
    protected $key			= "id";
    protected $soft_deletes	= FALSE;
    protected $date_format	= "datetime";

    protected $log_user 	= FALSE;

    protected $set_created	= false;
    protected $set_modified = false;
    protected $created_field = "created_date";
    protected $modified_field = "modified_date";

    /*
        Customize the operations of the model without recreating the insert, update,
        etc methods by adding the method names to act as callbacks here.
     */
    protected $before_insert 	= array();
    protected $after_insert 	= array();
    protected $before_update 	= array();
    protected $after_update 	= array();
    protected $before_find 		= array();
    protected $after_find 		= array();
    protected $before_delete 	= array();
    protected $after_delete 	= array();

    /*
        For performance reasons, you may require your model to NOT return the
        id of the last inserted row as it is a bit of a slow method. This is
        primarily helpful when running big loops over data.
     */
    protected $return_insert_id 	= TRUE;

    // The default type of element data is returned as.
    protected $return_type 			= "object";

    // Items that are always removed from data arrays prior to
    // any inserts or updates.
    protected $protected_attributes = array();

    /*
        You may need to move certain rules (like required) into the
        $insert_validation_rules array and out of the standard validation array.
        That way it is only required during inserts, not updates which may only
        be updating a portion of the data.
     */
    protected $validation_rules         = array();
    protected $insert_validation_rules  = array();
    protected $skip_validation          = false;
    protected $empty_validation_rules   = array();

    /**
     *
     * @param string $option
     * @param number $status
     * @param string $keyword
     * @param string $order_field
     * @param string $sort
     * @param string $limit
     * @param string $offset
     * @return Ambigous <number, mixed, boolean, string>
     *file_name
     */
    public function getItems($option = 'total' , $status = 0 , $cate_id, $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){

        if($status){
            $this->where('active',$status);
        }
        if($keyword !='')
        {
            $this->like('id',$keyword);
            $this->or_like('code',$keyword);
        }
        if(!empty($cate_id)){
            $this->where('category_id',$cate_id);
        }

        if($option == 'total'){
            $results = $this->find_all();
            if($results) {
                $return  = count($results);
            }
            else{
                $return  = 0;
            }  
        }
        elseif($option == 'count_list'){
            $this->order_by($order_field,$sort);
            $this->limit($limit,$offset);
            $return  = count($this->find_all());
        }
        else{
            $this->order_by($order_field,$sort);
            $this->limit($limit,$offset);
            $return  = $this->find_all();
        }
        return $return;
    }

    public function detail($id)
    {
        if(!$id)
        {
            return false;
        }
        $this->select('*');
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');

        return  $this->find_by('id',$id);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table_name, array('id' => $id));
    }
    
    public function deletePhoto($id){
        $banner = $this->detail($id);
        unlink($_SERVER['DOCUMENT_ROOT'].'/'.$banner->path);
        $data = array(
            'path' => '',
        );
        $this->update($id,$data);
        return TRUE;
    }
    
    public function update_status($id, $status){
        $data = array(
            'status' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    public function check_relationship($id){
        return $this->find_by('category_id', $id);
    }

    public function checkGenerated($badge_id){
        $result = $this->db->query("
            SELECT p.id, p.badge_id, pet_badge_category.name , p.code_id, p.created_date, p.code
                    FROM `pet_qrcode`  p
                    left join pet_badge on pet_badge.id = p.badge_id
                    left join pet_badge_category on pet_badge_category.id = pet_badge.category_id
                    WHERE p.`badge_id` = '$badge_id'
        ");
        if($result->num_rows()>0){
            return $result->result_array();
        }else{
            return 0;
        }
    }

    public function checkGeneratedWitSort($badge_id,$order_field, $sort){
        $result = $this->db->query("
            SELECT p.id, p.badge_id, pet_badge_category.name , p.code_id, p.created_date, p.code
                    FROM `pet_qrcode`  p
                    left join pet_badge on pet_badge.id = p.badge_id
                    left join pet_badge_category on pet_badge_category.id = pet_badge.category_id
                    WHERE p.`badge_id` = '$badge_id' ORDER BY $order_field $sort
        ");
        if($result->num_rows()>0){
            return $result->result_array();
        }else{
            return 0;
        }
    }

    public function createCodes($item){
        $this->load->helper(array('util'));
        $dataReturn = array();
        if($item->quantity > 0){
            for($i=1; $i<=$item->quantity;$i++){
                $dataInsert = array();
                $dataInsert['badge_id'] = $item->id;
                $dataInsert['created_date'] = NOW();
                // Check unique ID
                do{
                    $code = md5($item->id);
                    $codeChar = substr($code, 0,2);
                    $uniqueId = $codeChar . UtilHelper::generateRandomString(4);
                    $result = $this->db->query(" 
                    SELECT *
                            FROM `pet_qrcode`  p
                            WHERE p.`code_id` = '$uniqueId'
                    ");
                    $count = $result->num_rows();
                }
                while($count > 0);
                $dataInsert['code_id'] = $uniqueId;
                $qr_code = $uniqueId;
                $dataInsert['code'] = URL_BADGE_ID . $qr_code;
                $this->db->insert('pet_qrcode',$dataInsert);
                $id = $this->db->insert_id();
                if($id > 0){
                    $dataInsert['id'] = $id;
                    $dataReturn[] = $dataInsert;
                }  
            }
            // Update status , not generate anymore
            $dataUpdate = array(
                'status' => 1,
            );
            $this->db->where('id',$item->id);
            $this->db->update('pet_badge',$dataUpdate);
            $this->exportData($item->id,$dataReturn);

        }
        return $dataReturn;
    }

    public function check_relationship_codes($id){
        $result = $this->db->query("
            SELECT *
                    FROM `pet_qrcode`  p
                    WHERE p.`badge_id` = '$id'
        ");
        if($result->num_rows()>0){
            return true;
        }
        return false;
    }

    public function exportData($badgeId,$others)
    {
        $headers = "";
        $data_header = array(
            "S/N",   
            "Badge ID URL",   
            "Badge ID", 
            );
        if (!$others) {
            exit;
        } else {
             foreach ($data_header as $field) {
                $headers .= $field . ",";
            }
        }
        $dataExport="";
        $file = "Badge ID List SN ".$badgeId;
        $i = 0;
        foreach($others as $other){
            $i++;
            $line = '';

            $id = $i;
            $id = str_replace('"', '""', $id);
            $id = '"' . $id . '"' . ",";
            $line .=    $id;

            $qr_code = URL_BADGE_ID . $other['code_id'];
            $qr_code = str_replace('"', '""', $qr_code);
            $qr_code = '"' . $qr_code . '"' . ",";
            $line .=    $qr_code;

            $unique_id = $other['code_id'];
            $unique_id = str_replace('"', '""', $unique_id);
            $unique_id = '"' . $unique_id . '"' . ",";
            $line .=    $unique_id;
            
            $dataExport .= trim($line)."\n";                                      
        }
        $dataExport = str_replace("\r","",$dataExport);   
        $filePath = "../themes/public/download/" . $file . ".csv";
        if(!file_exists('../themes/public/download/')){
            mkdir('../themes/public/download/', 0777, true);
        }      
        $fileName = "Badge_" . date("YmdHis") . "_" . $badgeId . ".csv";
        $fp = fopen($filePath, 'w');
        $dataExport = "$headers\n$dataExport";
        fwrite($fp,$dataExport);    
        fclose($fp);
        $url = $this->putToS3($filePath,$fileName);
        if(!isset($url['code'])){
             $dataUpdate = array(
                'url' => realpath($filePath),
            );
            $this->db->where('id',$badgeId);
            $this->db->update('pet_badge',$dataUpdate);
            //unlink($filePath);
        }
    }

     /**
     * @param $filePath
     * @param $fileName
     * @return string
     * @description: Put the actual file into S3 AWS
     */
    protected function putToS3( $filePath, $fileName ) {
        $this->config->load('s3');
        $this->load->library('s3', array(
            "access_key"    => $this->config->item('access_key'),
            "secret_key"    => $this->config->item('secret_key'),
            "use_ssl"       => false,
            "verify_peer"   => true
        ));
        if ($this->s3->putObjectFile($filePath, $this->config->item('s3-bucket'), $this->config->item('s3-path') . "/" . $fileName, S3::ACL_PUBLIC_READ)) {
            return $this->config->item('s3-uri') . $fileName;
        } else {
            $err['code']    = 15;
            $err['msg']     = lang('Error: Something went wrong while uploading your file... sorry!');
            return $err;
        }
    }

    public function getDashboard(){
        $this->table_name = 'pet_qrcode';
        
        $date = getdate();
        $this->db->where($this->modified_field .' >=', CMSHelper::dayAdd('day', 0, $date));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['today']                 = $this->db->count_all_results();

        $this->db->where($this->modified_field .' <', CMSHelper::dayAdd('day', 0, $date));
        $this->db->where($this->modified_field .' >=', CMSHelper::dayAdd('day', -1, $date));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['yesterday']             = $this->db->count_all_results();

        $this->db->where($this->modified_field .' <', CMSHelper::dayAdd('month', +1, $date));
        $this->db->where($this->modified_field .' >=', CMSHelper::dayAdd('month', 0, $date));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['this_month']             = $this->db->count_all_results();

        $this->db->where($this->modified_field .' <', CMSHelper::dayAdd('month', 0, $date));
        $this->db->where($this->modified_field .' >=', CMSHelper::dayAdd('month', -1, $date));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['last_month']             = $this->db->count_all_results();

        $this->db->where($this->modified_field .' <', CMSHelper::dayAdd('month', -1, $date));
        $this->db->where($this->modified_field .' >=', CMSHelper::dayAdd('month', -2, $date));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['month_before_last']      = $this->db->count_all_results();

        $this->db->where($this->modified_field .' >=', strtotime($date['year'] . '-1'));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['this_year']              = $this->db->count_all_results();

        $this->db->where($this->modified_field .' <', CMSHelper::dayAdd('year', 0, $date));
        $this->db->where($this->modified_field .' >=', CMSHelper::dayAdd('year', -1, $date));
        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['last_year']      = $this->db->count_all_results();

        $data['change_from_last_year'] = 0;
        if($data['last_year'] == 0){
            if($data['this_year'] == 0){
                $data['change_from_last_year'] = 0;
            }else{
                $data['change_from_last_year'] = 100;
            }
        }else{
            $data['change_from_last_year'] = round( ( ( $data['this_year']*100 ) / $data['last_year'] ) - 100, 2);
        }

        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['total']                  = $this->db->count_all_results();
        return $data;
    }

    public function getOverall(){
        // get top country badge
        $query = "SELECT u.last_country_id as country_id, c.countryName as country_name, count(u.last_country_id) as total
                    FROM pet_qrcode qr
                    LEFT JOIN pets p on p.id = qr.pet_id
                    LEFT JOIN users u on u.id = p. user_id
                    LEFT JOIN countries c on c.id = u.last_country_id
                    WHERE u.last_country_id IS NOT NULL AND qr.pet_id IS NOT NULL
                    GROUP BY u.last_country_id
                    ORDER BY count(u.last_country_id) DESC, c.countryName ASC
                    limit 3";
        $results = $this->db->query($query);
        $data['top_countries'] = $results->num_rows() > 0 ? $results->result() : array();

        // get top brand badge
        $query = "SELECT COALESCE(c.name, 'N/A') as name, COALESCE(c.id, '0') as id, COUNT(COALESCE(c.id, '0')) as total
                    FROM pet_qrcode qr
                    LEFT JOIN pet_badge b ON b.id = qr.badge_id
                    LEFT JOIN pet_badge_category c ON c.id = b.category_id
                    WHERE qr.pet_id IS NOT NULL
                    GROUP BY COALESCE(c.id, '0')
                    ORDER BY COUNT(COALESCE(c.id, '0')) DESC
                    LIMIT 3";
        $results = $this->db->query($query);
        $data['top_brands'] = $results->num_rows() > 0 ? $results->result() : array();

        $this->db->where('pet_id!=', NULl);
        $this->db->from($this->table_name);
        $data['total']                  = $this->db->count_all_results();

        return $data;
    }
}
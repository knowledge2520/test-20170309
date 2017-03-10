<?php
class Cron_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->helper(array('util'));
    }

    public function run() {
        $result = $this->db->query("
            SELECT *
                    FROM `pet_badge`  p
                    WHERE p.`status` = '0'
        ");
        if($result->num_rows()>0){
            $data = $result->result();
            foreach($data as $item){
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
                        'modified_date'=>strtotime(date("Y-m-d H:i:s"))
                    );
                    $this->db->where('id',$item->id);
                    $this->db->update('pet_badge',$dataUpdate);
                    $this->exportData($item->id,$dataReturn);
                }
            }
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

    public function genSubscribed(){
        $result = $this->db->query("
            SELECT *
                    FROM `users`  p
        ");
        if($result->num_rows()>0){
            $data = $result->result();
            foreach($data as $item){
                $email = $item->email;
                $firstName = $item->first_name;
                $lastName = $item->last_name;
                $code = UtilHelper::syncMailchimp($email,$firstName,$lastName);
            }
        }        
    }

    public function updateCountry(){
        $result = $this->db->query("
            SELECT *
                    FROM `user_locations`  p
        ");
        if($result->num_rows()>0){
            $data = $result->result();
            foreach($data as $item){
		sleep(1);
                $lat = $item->lat;
                $lng = $item->lng;
                $userId = $item->user_id;
                $geocode=file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&sensor=false');
                $output= json_decode($geocode);
                for($j=0;$j<count($output->results[0]->address_components);$j++){
                    $cn=array($output->results[0]->address_components[$j]->types[0]);
                    if(in_array("country", $cn)){
                        $country= $output->results[0]->address_components[$j]->long_name;
                        if(!empty($country)){
                            $result = $this->db->query("
                                SELECT *
                                        FROM `countries`  where countryName like '%$country%'
                            ");
                            if($result->num_rows()>0){
                                $data = $result->row(0);
                                $countryId = $data->id;
                                $this->db->update('users',['last_country_id'=>$countryId],['id'=>$userId]);
                            }
                        }
                    }
                }
            }
        } 
    }
}
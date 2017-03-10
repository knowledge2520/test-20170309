<?php
class Nearbystatus_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function items() {

        $this->db->select('id, title, content')->from('user_nearby_status')->where('status', 1)->order_by('ordering');

        $query = $this->db->get();

        return $query;
    }

    public function item($id) {
        $this->db->select('id, title, content')->from('user_nearby_status')->where('status', 1)->where('id', $id);

        $query = $this->db->get();

        return $query;
    }

    public function setUserLocation( $userObj, $gpsLat = false, $gpsLong = false ) {

        $userLocationQuery = $this->db->get_where("user_locations", array("user_id" => $userObj->id));

        if( $userLocationQuery->num_rows() > 0 ) {
            $this->db->update("user_locations", array("lat" => $gpsLat, "lng" => $gpsLong, "updated_at" => now()), array("user_id" => $userObj->id));
        } else {
            $this->db->insert("user_locations", array("lat" => $gpsLat, "lng" => $gpsLong, "user_id" => $userObj->id, "created_at" => now(), "updated_at" => now()));
        }

        // Update last country new location
        $this->updateLastCountry($userObj->id);
    }

    public function getUserLocation($userId) {

        $query = $this->db->get_where("user_locations", array("user_id" => $userId));

        return $query->row();
    }

    /**
     * @param $userId
     * @param $gpsLat
     * @param $gpsLong
     * @param string $option
     * @param $limit
     * @param $start
     * @return bool
     * @description: Get user list nearby. Sorting from closest. We also get user info and
     * friend status
     */
    public function getUsersNearby( $userId, $gpsLat, $gpsLong, $option = 'all', $limit, $start ) {

        $userId = (int) $userId;

        if( !$gpsLat || $gpsLong ) {
            $userLocation = $this->getUserLocation($userId);

            if( !$userLocation || !isset($userLocation->lat) || !isset($userLocation->lng) ) {
                return false;
            }

            $gpsLat     = $userLocation->lat;
            $gpsLong    = $userLocation->lng;
        }

        $sql = "SELECT ( 6371 * acos( cos( radians($gpsLat) ) * cos( radians( ul.lat ) ) * cos( radians( ul.lng ) - radians($gpsLong) ) + sin( radians($gpsLat) ) * sin( radians( ul.lat ) ) ) )  AS distance,
        ul.lat AS latitude, ul.lng AS longitude, ul.user_id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, uc.status AS isFriend, us.id AS statusId, us.title AS statusTitle
        FROM user_locations ul
        INNER JOIN users u ON u.id = ul.user_id AND u.active = 1
        INNER JOIN user_options uo ON u.id = uo.user_id AND uo.user_nearby_status <> " . INVISIBLE_MODE . "
        LEFT JOIN user_contact uc ON uc.user_id = $userId AND uc.registed = ul.user_id AND uc.status = 1
        LEFT JOIN user_nearby_status us ON us.id = uo.user_nearby_status
        WHERE ul.user_id <> $userId
        GROUP BY ul.id
        HAVING distance <=" . DISTANCE_LIMIT . "
        ORDER BY distance ASC";

        if($option == ALL) {
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);
            return $query;
        }
    }

     /**
     * @param $userId
     * @param string $option
     * @param $limit
     * @param $start
     * @return bool
     * @description: Get user list users. Sorting from closest. We also get user info and
     * friend status
     */
    public function getUsersByKeyword( $userId, $keyword, $option = 'all', $limit, $start ) {
        $userId = (int) $userId;
        $query_keyword ="(
            concat_ws(' ',u.first_name,u.last_name) LIKE '%" . $this->db->escape_like_str(trim($keyword)) . "%'
            )";  
        $sql = "SELECT
        u.id, u.first_name,u.last_name,u.email,u.profile_photo,u.profile_photo_thumb, (CASE WHEN u.facebook_id IS NOT NULL THEN 1 ELSE 0 END) as social_type, u.id as registed, (CASE WHEN uc.status IS NOT NULL THEN uc.status ELSE 0 END) as friend_status
        FROM users u
        LEFT JOIN user_locations ul ON u.id = ul.user_id AND u.active = 1
        INNER JOIN user_options uo ON u.id = uo.user_id
        LEFT JOIN user_contact uc  ON uc.user_id = $userId AND  uc.registed = ul.user_id
        LEFT JOIN user_nearby_status us ON us.id = uo.user_nearby_status
        WHERE 1 = 1 AND $query_keyword group by u.id  order by u.first_name";
        if($option == ALL) {
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);
            return $query;
        }
    }

    function updateLastCountry($userId){
        $userLocation = $this->getUserLocation($userId);
        if($userLocation){
            if(isset($userLocation->lat) && !empty($userLocation->lat)){
                $lat = $userLocation->lat;
                $lng = $userLocation->lng;
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
/*
 * user a add friend user b, có 2 records
 * 1. user_id của user a, email, first_name, lastname, phone, của user b, registed là user_id của user b, status là 2 (sender pending)
2. user_id của user b, email, first_name, lastname, phone, của user a, registed là user_id của user a, status là 3 (approve request)
 */
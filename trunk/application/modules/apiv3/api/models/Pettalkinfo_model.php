<?php
class Pettalkinfo_model extends CI_Model
{
    protected $dataResults = array();
    protected $pettalkInfo;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function items( $arrFilters = array(), $option = ALL, $start, $limit ) {

        $where = "";

        $arrConds = array();

        $sql = "SELECT p.*, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.newsFeedType, ua.id AS newsFeedId, cat.title AS catTitle
        FROM pet_talk_info p
        INNER JOIN users u ON u.id = p.user_id
        INNER JOIN pet_talk_category_info cat ON cat.id = p.catId
        INNER JOIN user_newsfeed_activities ua ON ua.pettalk_info_id = p.id";

        if( isset($arrFilters["userId"]) && !empty($arrFilters["userId"]) ) {

            $sql .= count($arrConds) ? " AND " : " WHERE ";
            $sql .= "p.user_id = ?";
            $arrConds["user_id"] = (int)$arrFilters["userId"];
        }

        if( isset($arrFilters["catId"]) && !empty($arrFilters["catId"]) ) {
            $sql .= count($arrConds) ? " AND " : " WHERE ";
            $sql .= "p.catId = ?";
            $arrConds["catId"] = (int)$arrFilters["catId"];
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_date DESC LIMIT $start, $limit";

        return $this->db->query($sql, $arrConds);
    }

    function item( $newFeedId = false, $newsFeedItemId = false, $userId = false ) {

        $sql = "SELECT i.id, i.catId, i.name AS title, '' AS content, cat.name AS catName, i.photo,
		i.type AS petType, breed, color, age, sex, contact, lat, lng, location, i.when AS petWhen, i.where AS petWhere,
		rewardCurrency, currency, microchip, infoType, additionalInfo,
        $newFeedId AS newsFeedId, i.infoType AS newsFeedType
        FROM pet_talk_info i
        INNER JOIN pet_talk_category cat ON cat.id = i.catId
        WHERE i.user_id = ? AND i.id = ?";

        $query = $this->db->query($sql, array("user_id" => $userId, "id" => $newsFeedItemId));
        $this->pettalkInfo = $query->num_rows() > 0 ? $query->row() : array();
        return $this;
    }

    public function getPetalkList( $params = false, $option = 'total', $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {

        $distanceLimit  = NEWSFEED_DISTANCE_LIMIT;

        $arrUserLocation = getUserOptions($params["userLoginId"]);

        if( !isset($arrUserLocation[LATITUDE]) || !isset($arrUserLocation[LONGITUDE])
            || empty($arrUserLocation[LATITUDE]) || empty($arrUserLocation[LONGITUDE]) ) {
            $arrUserLocation = array(LATITUDE => 0, LONGITUDE => 0);
        }
        $userLat = $arrUserLocation[LATITUDE];
        $userLong = $arrUserLocation[LONGITUDE];

        $where = ""; $whereInfo = "";

        if($params["userId"]) {
            $where .= empty($where) ? " WHERE " : " AND ";
            $where .= " t.created_by = " . (int)$params["userId"];
            /*$where .= " t.created_by = ?";
            array_push($arr, $params["userId"]);*/

            $whereInfo .= empty($whereInfo) ? " WHERE " : " AND ";
            $whereInfo .= " i.user_id = " . (int)$params["userId"];
            /*$whereInfo .= " i.user_id = ?";
            array_push($arr, $params["userId"]);*/
        }

        if($params["catId"]) {
            $where .= empty($where) ? " WHERE " : " AND ";
            $where .= " t.category_id = " . (int)$params["catId"];
            /*$where .= " t.category_id = ?";
            array_push($arr, $params["catId"]);*/

            $whereInfo .= empty($whereInfo) ? " WHERE " : " AND ";
            $whereInfo .= " i.catId = " . (int)$params["catId"];
            /*$where .= " i.catId = ?";
            array_push($arr, $params["catId"]);*/
        }

        if($params["keyword"]) {
            $keyword =  $this->db->escape("%" . $params["keyword"] . "%") ;
            $where .= empty($where) ? " WHERE " : " AND ";
            $where .= " t.title LIKE $keyword";
            //array_push($arr, $keyword);

            $whereInfo .= empty($whereInfo) ? " WHERE " : " AND ";
            $whereInfo .= " i.name LIKE $keyword";
            //array_push($arr, $keyword);
        }

        //check pet_talk_category status is 1
        $where .= empty($where) ? " WHERE " : " AND ";
        $where .= " c.status = 1";

        $whereInfo .= empty($whereInfo) ? " WHERE " : " AND ";
        $whereInfo .= " cat.status = 1";

        $sql = "SELECT * FROM(
			SELECT t.id, category_id AS catId, title, content, c.name AS catName, '' AS photo,
			'' AS petType, '' AS breed, '' AS color, '' AS age, '' AS sex, '' AS contact, '' AS lat, '' AS lng, '' AS location, '' AS petWhen, '' AS petWhere,
			'' AS rewardCurrency, '' AS currency, '' AS microchip, '' AS infoType, t.content AS additionalInfo,
			u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb,
			ua.id AS newsFeedId, ua.newsFeedType, ua.created_date
			FROM pet_talk_topics t
			INNER JOIN pet_talk_category c ON c.id = t.category_id
			INNER JOIN users u ON u.id = t.created_by
			INNER JOIN user_newsfeed_activities ua ON ua.topic_id = t.id
			$where 
			GROUP BY t.id

			UNION

			SELECT i.id, i.catId, i.name AS title, '' AS content, cat.name AS catTitle, i.photo,
            i.type AS petType, breed, color, age, sex, contact, lat, lng, location, i.when AS petWhen, i.where AS petWhere,
            rewardCurrency, currency, microchip, infoType, additionalInfo,
            u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb,
            ua.id AS newsFeedId, ua.newsFeedType, ua.created_date
            FROM (
              SELECT *, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( lat ) ) ) )  AS distance
              FROM pet_talk_info
              GROUP BY id
              HAVING distance <= $distanceLimit
            ) AS i
			INNER JOIN pet_talk_category cat ON cat.id = i.catId
			INNER JOIN users u ON u.id = i.user_id
			INNER JOIN user_newsfeed_activities ua ON ua.pettalk_info_id = i.id
			$whereInfo
			GROUP BY i.id
		) AS pettalk ORDER BY created_date DESC";

        if( $option == 'total' ) {
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {//print_r($arr);print_r($arrInfo);die();
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);//echo $this->db->last_query();die();
            $this->dataResults = $query->num_rows() > 0 ? $query->result() : array();
            return $this;
        }
    }

    public function pettalkListTransformer($loggedInUserId) {

        $arrData = array();

        if( count($this->dataResults) ) {

            foreach($this->dataResults as $item) {

                format_output_data($item);

                $petType    = empty($item->infoType) ? TOPIC_INFO : PETTALK_INFO;
                $petData    = empty($item->infoType) ? $this->pettalk_model->pettalkTopicTransformer($item) : $this->pettalkInfoTransformer($item);
                $infoPhoto  = $this->newsfeed_model->getNewsFeedMedia( $item->newsFeedId )->getNewsfeedMediaTransformer();
                $petData[PHOTOS] = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                if($petType == PETTALK_INFO) {
                    $petData[COVER_PHOTO] = isset($infoPhoto[COVER_PHOTO]) ? $infoPhoto[COVER_PHOTO] : new stdClass();
                }

                $arrData[] = array(
                    NEWSFEED_ID     => $item->newsFeedId,
                    NEWSFEED_TYPE   => $item->newsFeedType,
                    NEWSFEED_ITEM_ID=> $item->id,
                    USER_TAG        => $this->usertag_model->getUserTag( $item->newsFeedId, NEWSFEED_USER_TAG )->transformer(),
                    TOTAL_LIKES     => getNewsfeedTotalLike( $item->id, $item->newsFeedType ),
                    TOTAL_COMMENTS  => getNewsfeedTotalReview( $item->id, $item->newsFeedType ),
                    TOTAL_SHARING   => getNewsfeedTotalSharing( $item->id, $item->newsFeedType ),
                    HAS_LIKED       => hasLikedNewsfeed( $item->id, $loggedInUserId, $item->newsFeedType ),
                    CREATED_TIME    => $item->created_time,
                    CREATED_DATE    => $item->created_date,
                    USER_INFO       => array(
                        ID => $item->userId,
                        FIRST_NAME => $item->first_name,
                        LAST_NAME => $item->last_name,
                        PROFILE_PHOTOS => array(
                            $item->profile_photo, $item->profile_photo_thumb
                        ),
                        TOTAL_FRIEND  => getTotalUserFriends($item->userId),
                        TOTAL_PHOTO   => getTotalUserListingPhotos($item->userId),
                        TOTAL_REVIEW  => getTotalUserReviews($item->userId),
                    ),
                    $petType => $petData
                );
            }
        }
        return $arrData;
    }

    public function pettalkInfoTransformer($item = false) {

        if($this->pettalkInfo) {
            $item = $this->pettalkInfo;
        }

        if($item) {

            format_output_data($item);
            return array(
                ID => $item->id,
                NAME => $item->title,
                TYPE => $item->petType,
                BREED => $item->breed,
                COLOR => $item->color,
                AGE => $item->age,
                SEX => ucfirst($item->sex),
                CONTACT => $item->contact,
                LATITUDE => $item->lat,
                LONGITUDE => $item->lng,
                LOCATION => $item->location,
                WHEN => $item->petWhen,
                WHERE => $item->petWhere,
                REWARD => $item->rewardCurrency,
                CURRENCY => $item->currency,
                MICROCHIP => $item->microchip,
                ADDITIONAL_INFO => $item->additionalInfo,
                //COVER_PHOTO => $item->photo,
                CATEGORY => array(
                    ID => $item->catId,
                    TITLE => $item->catName,
                ),
                //PHOTOS      =>    $this->ci->petnewsfeed->getNewsFeedMedia($item->newsFeedId),
            );
        } else {
            return array();
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @description: Add new data for pet info (adoptions, found and lost report)
     */
    public function saveNew($params = array()) {
        if( count($params) ) {
            $this->db->insert("pet_talk_info", $params);
            return $this->db->insert_id();
        }
    }

    /**
     * @param array $params
     * @param array $conditions
     * @description: update Pettalk info
     */
    public function save( $params = array(), $conditions = array() ) {
        $this->db->update("pet_talk_info", $params, $conditions);
    }

    /**
     * @return mixed
     * @description: get pettalk info without structure
     */
    public function get() {
        return $this->pettalkInfo;
    }

    public function deletePettalkInfo($id = null, $userId = null) {
        $this->db->delete("pet_talk_info", array("id" => $id, "user_id" => $userId));
    }

    /**
     *
     * @param string $option
     * @param number $start
     * @param string $limit
     * @param unknown $cat_id
     * @param unknown $user_location
     * @return unknown
     *file_name
     */
    public function searchPetTalk($member, $keyword, $option = 'total', $start = 0, $limit = API_NUM_RECORD_PER_PAGE,$user_location ) {

        $distanceLimit  = $user_location['search_distance'];
        $userLat = $user_location['latitude'];
        $userLong = $user_location['longitude'];

        $where = ""; $whereInfo = "";

        if($keyword) {
            $keyword =  $this->db->escape("%" . $keyword . "%") ;
            $where .= empty($where) ? " WHERE " : " AND ";
            $where .= " (t.title LIKE $keyword OR t.content LIKE $keyword)";
            //array_push($arr, $keyword);

            $whereInfo .= empty($whereInfo) ? " WHERE " : " AND ";
            $whereInfo .= " i.name LIKE $keyword";
            //array_push($arr, $keyword);
        }

        //check pet_talk_category status is 1
        $where .= empty($where) ? " WHERE " : " AND ";
        $where .= " c.status = 1";

        $whereInfo .= empty($whereInfo) ? " WHERE " : " AND ";
        $whereInfo .= " cat.status = 1";
        if($userLat && $userLong){
            $sql = "SELECT * FROM(
            SELECT t.id, category_id AS catId, title, content, c.name AS catName, '' AS photo,
            '' AS petType, '' AS breed, '' AS color, '' AS age, '' AS sex, '' AS contact, '' AS lat, '' AS lng, '' AS location, '' AS petWhen, '' AS petWhere,
            '' AS rewardCurrency, '' AS currency, '' AS microchip, '' AS infoType, t.content AS additionalInfo,
            u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb,
            ua.id AS newsFeedId, ua.newsFeedType, ua.created_date
            FROM pet_talk_topics t
            INNER JOIN pet_talk_category c ON c.id = t.category_id
            INNER JOIN users u ON u.id = t.created_by
            INNER JOIN user_newsfeed_activities ua ON ua.topic_id = t.id
            $where 
            GROUP BY t.id

            UNION

            SELECT i.id, i.catId, i.name AS title, '' AS content, cat.name AS catTitle, i.photo,
            i.type AS petType, breed, color, age, sex, contact, lat, lng, location, i.when AS petWhen, i.where AS petWhere,
            rewardCurrency, currency, microchip, infoType, additionalInfo,
            u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb,
            ua.id AS newsFeedId, ua.newsFeedType, ua.created_date
            FROM (
              SELECT *, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( lat ) ) ) )  AS distance
              FROM pet_talk_info
              GROUP BY id
              HAVING distance <= $distanceLimit
            ) AS i
            INNER JOIN pet_talk_category cat ON cat.id = i.catId
            INNER JOIN users u ON u.id = i.user_id
            INNER JOIN user_newsfeed_activities ua ON ua.pettalk_info_id = i.id
            $whereInfo
            GROUP BY i.id
            ) AS pettalk ORDER BY created_date DESC";
        }
        else{
            $sql = "SELECT * FROM(
            SELECT t.id, category_id AS catId, title, content, c.name AS catName, '' AS photo,
            '' AS petType, '' AS breed, '' AS color, '' AS age, '' AS sex, '' AS contact, '' AS lat, '' AS lng, '' AS location, '' AS petWhen, '' AS petWhere,
            '' AS rewardCurrency, '' AS currency, '' AS microchip, '' AS infoType, t.content AS additionalInfo,
            u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb,
            ua.id AS newsFeedId, ua.newsFeedType, ua.created_date
            FROM pet_talk_topics t
            INNER JOIN pet_talk_category c ON c.id = t.category_id
            INNER JOIN users u ON u.id = t.created_by
            INNER JOIN user_newsfeed_activities ua ON ua.topic_id = t.id
            $where 
            GROUP BY t.id

            UNION

            SELECT i.id, i.catId, i.name AS title, '' AS content, cat.name AS catTitle, i.photo,
            i.type AS petType, breed, color, age, sex, contact, lat, lng, location, i.when AS petWhen, i.where AS petWhere,
            rewardCurrency, currency, microchip, infoType, additionalInfo,
            u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb,
            ua.id AS newsFeedId, ua.newsFeedType, ua.created_date
            FROM (
              SELECT *
              FROM pet_talk_info
              GROUP BY id
            ) AS i
            INNER JOIN pet_talk_category cat ON cat.id = i.catId
            INNER JOIN users u ON u.id = i.user_id
            INNER JOIN user_newsfeed_activities ua ON ua.pettalk_info_id = i.id
            $whereInfo
            GROUP BY i.id
            ) AS pettalk ORDER BY created_date DESC";
        }

        if( $option == 'total' ) {
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {//print_r($arr);print_r($arrInfo);die();
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);//echo $this->db->last_query();die();
            $this->dataResults = $query->num_rows() > 0 ? $query->result() : array();
            return $this;
        }
    }
}
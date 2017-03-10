<?php
class Checkin_model extends CI_Model {

    protected $dataResults = array();
    protected $checkin;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->model('listing_model');
    }

    public function getTotalReviews($listingId) {

        $query = $this->db->get_where("user_reviews", array("business_id" => $listingId, "status" => 1));

        return $query->num_rows();
    }

    public function getTotalRating($listingId) {

        if(!$listingId) {
            return 0;
        }

        $sql = "select count(*) as total_review, SUM(rate) as total_point  from user_reviews where status = 1 and business_id = ?";

        $result = $this->db->query($sql, array($listingId));

        $total_review = ($result->num_rows() > 0) ? $result->first_row()->total_review : 0;

        $total_point = ($result->num_rows() > 0) ? $result->first_row()->total_point : 0;

        if($total_review && $total_point > 0)  {
            $average_rating = round($total_point/$total_review, 0, PHP_ROUND_HALF_UP);
        }
        else {
            $average_rating = 0;
        }

        return $average_rating;
    }

    public function item( $newFeedId = false, $newsFeedItemId = false ) {

        $sqlCheckin   = "SELECT bi.id AS businessId, bi.name, bi.address, bi.country, bi.latitude, bi.longitude, bi.photo, checkin.comment, checkin.id AS checkinId
        FROM business_items bi
        LEFT JOIN user_checkins checkin ON bi.id = checkin.business_id
        WHERE checkin.id = ?
        GROUP BY checkin.id";

        $checkinQuery = $this->db->query($sqlCheckin, array($newFeedId));

        $this->checkin = $checkinQuery->num_rows() > 0 ? $checkinQuery->row() : array();

        return $this;
    }

    public function itemTransformer( $newsFeedId = false, $item = false )
    {
        if ($this->checkin) {
            $item = $this->checkin;
        }

        if ($item) {

            $comment        = !empty($item->comment) ? $item->comment : "";
            $shortContent   =  !empty($comment) ? giveShortContent($comment) : "";
            $catName        = $this->listing_model->getListingCategories($item->businessId)->getListingCategoryName();

            $arrCheckin = array(
                ID              => $item->checkinId,
                PHOTOS          => array(),
                VISIT           => "",
                SHORT_CONTENT   => $shortContent,
                CHECKIN_TEXT    => $comment,
            );

            format_output_data($item);

            $arrListingInfo = array(
                ID              => $item->businessId,
                LATITUDE        => $item->latitude,
                LONGITUDE       => $item->longitude,
                COUNTRY         => $item->country,
                ADDRESS         => $item->address,
                NAME            => $item->name,
                WEBSITE         => !empty($item->website) ? $item->website : "",
                CATEGORY        => $catName,
                PHOTO           => $item->photo,
                TOTAL_REVIEW    => $this->getTotalReviews($newsFeedId),
                TOTAL_RATING    => $this->getTotalRating($newsFeedId),
                VISIT           => getTotalVisit((int)$newsFeedId)
            );

            return array(
                CHECKIN_INFO => $arrCheckin,
                LISTING_INFO => $arrListingInfo,
            );

        } else {
            return array(
                CHECKIN_INFO => array(),
                LISTING_INFO => array(),

            );
        }
    }

    public function delete( $checkinId, $userId ) {
        $this->db->delete("user_checkins", array("id" => $checkinId, "user_id" => $userId));
    }
}
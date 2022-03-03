<?php
namespace Vectorserver\Odnoklassniki;
class OdnoklassnikiApi
{

    /**
     * https://apiok.ru/dev/methods/rest/group/
     */

    const API_AUTH_URL  = 'https://api.ok.ru/oauth/token.do';
    const API_LOGIN_URL = 'https://connect.ok.ru/oauth/authorize';
    const API_BASE_URL  = 'https://api.ok.ru/fb.do';

    private $app_id;
    private $public_key;
    private $secret;
    private $accessToken;
    private $scope;

    /**
     * @param $app_id
     * @param $public_key
     * @param $secret
     * @param $accessToken
     * @param $scope
     */
    public function __construct($app_id, $public_key, $secret, $accessToken, $scope)
    {
        $this->app_id = $app_id;
        $this->public_key = $public_key;
        $this->secret = $secret;
        $this->scope = $scope;

        $this->accessToken = $accessToken;
    }


    /**
     * Получает историю счетчиков статистики по дням
     * @param $group_id
     * @param $date_from
     * @param $date_to
     * @return mixed|void
     */
    public function group_getStatTrends($group_id, $date_from="2021-01-01", $date_to="2021-05-01"){
        $params = [
            'gid'=>$group_id,
            'fields'=>'COMMENTS,COMPLAINTS,CONTENT_OPENS,ENGAGEMENT,FEEDBACK,HIDES_FROM_FEED,LEFT_MEMBERS,LIKES,LINK_CLICKS,MEMBERS_COUNT,MEMBERS_DIFF,MUSIC_PLAYS,NEGATIVES,NEW_MEMBERS,NEW_MEMBERS_TARGET,PAGE_VISITS,PHOTO_OPENS,REACH,REACH_EARNED,REACH_MOB,REACH_MOBWEB,REACH_OWN,REACH_WEB,RENDERINGS,RESHARES,TOPIC_OPENS,VIDEO_PLAYS,VOTES',
            'start_time'=>strtotime($date_from)*1000,
            'end_time'=>strtotime($date_to)*1000,
        ];
        return $this->call('group.getStatTrends',$params);
    }

    /**
     * Получение информации о группах
     * @param $uids
     * @param $move_to_top
     * @return mixed|void
     */
    public function group_getInfo($uids, $move_to_top = false){

        $params = [
            'uids'=>$uids,
            'move_to_top'=>$move_to_top,
            'fields'=>'ABBREVIATION,ACCESS_TYPE,ADDRESS,ADD_CHANNEL_ALLOWED,ADD_PAID_THEME_ALLOWED,ADD_PHOTOALBUM_ALLOWED,ADD_THEME_ALLOWED,ADD_VIDEO_ALLOWED,ADMIN_ID,ADS_MANAGER_ALLOWED,ADVANCED_PUBLICATION_ALLOWED,AGE_RESTRICTED,BLOCKED,BOOKMARKED,BUSINESS,CALL_ALLOWED,CATALOG_CREATE_ALLOWED,CATEGORY,CHANGE_AVATAR_ALLOWED,CHANGE_TYPE_ALLOWED,CITY,COMMENT_AS_OFFICIAL,COMMUNITY,CONTENT_AS_OFFICIAL,COUNTRY,COVER,COVER_BUTTONS,COVER_SERIES,CREATED_MS,CREATE_ADS_ALLOWED,DELETE_ALLOWED,DESCRIPTION,DISABLE_PHOTO_UPLOAD,DISABLE_REASON,EDIT_ALLOWED,EDIT_APPS_ALLOWED,END_DATE,FEED_SUBSCRIPTION,FOLLOWERS_COUNT,FOLLOW_ALLOWED,FRIENDS_COUNT,GRADUATE_YEAR,GROUP_AGREEMENT,GROUP_CHALLENGE_CREATE_ALLOWED,GROUP_JOURNAL_ALLOWED,GROUP_NEWS,HAS_GROUP_AGREEMENT,HOMEPAGE_NAME,HOMEPAGE_URL,INVITATIONS_COUNT,INVITATION_SENT,INVITE_ALLOWED,INVITE_FREE_ALLOWED,JOIN_ALLOWED,JOIN_REQUESTS_COUNT,LEAVE_ALLOWED,LINK_CAROUSEL_ALLOWED,LINK_POSTING_ALLOWED,LOCATION_ID,LOCATION_LATITUDE,LOCATION_LONGITUDE,LOCATION_ZOOM,MAIN_PAGE_TAB,MAIN_PHOTO,MANAGE_MEMBERS,MANAGE_MESSAGING_ALLOWED,MEMBERS_COUNT,MEMBER_STATUS,MENTIONS_SUBSCRIPTION,MENTIONS_SUBSCRIPTION_ALLOWED,MESSAGES_ALLOWED,MESSAGING_ALLOWED,MESSAGING_ENABLED,MIN_AGE,MOBILE_COVER,NAME,NEW_ADVERTS_ALLOWED,NEW_CHATS_COUNT,NOTIFICATIONS_SUBSCRIPTION,ONLINE_PAYMENT_ALLOWED,PAID_ACCESS,PAID_ACCESS_DESCRIPTION,PAID_ACCESS_PRICE,PAID_CONTENT,PAID_CONTENT_DESCRIPTION,PAID_CONTENT_PRICE,PARTNER_LINK_CREATE_ALLOWED,PARTNER_PROGRAM_ALLOWED,PARTNER_PROGRAM_STATUS,PENALTY_POINTS_ALLOWED,PHONE,PHOTOS_TAB_HIDDEN,PHOTO_ID,PIC_AVATAR,PIN_NOTIFICATIONS_OFF,POSSIBLE_MEMBERS_COUNT,PREMIUM,PRIVATE,PRODUCTS_TAB_HIDDEN,PRODUCT_CREATE_ALLOWED,PRODUCT_CREATE_SUGGESTED_ALLOWED,PRODUCT_CREATE_ZERO_LIFETIME_ALLOWED,PROFILE_BUTTONS,PROMO_THEME_ALLOWED,PUBLISH_DELAYED_THEME_ALLOWED,REF,REQUEST_SENT,REQUEST_SENT_DATE,RESHARE_ALLOWED,ROLE,SCOPE_ID,SHOP_VISIBLE_ADMIN,SHOP_VISIBLE_PUBLIC,SHORTNAME,START_DATE,STATS_ALLOWED,STATUS,SUBCATEGORY_ID,SUGGEST_THEME_ALLOWED,TAGS,TRANSFERS_ALLOWED,UID,UNFOLLOW_ALLOWED,USER_PAID_ACCESS,USER_PAID_ACCESS_TILL,USER_PAID_CONTENT,USER_PAID_CONTENT_TILL,VIDEO_TAB_HIDDEN,VIEW_MEMBERS_ALLOWED,VIEW_MODERATORS_ALLOWED,VIEW_PAID_THEMES_ALLOWED,YEAR_FROM,YEAR_TO'
        ];

        return $this->call('group.getInfo',$params);

    }


    /**
     * @param $accessToken
     * @return void
     */
    public function setAccessToken($accessToken){
        $this->accessToken = $accessToken;
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed|void
     */
    private function call($method, array $params ){

        $ok_secret_key = md5($this->accessToken . $this->secret);


        /*Сортируем и склеиваем параметры запроса и secret_key*/
        $gen_sig = array(
            'method' => $method,
            'application_key' => $this->public_key,
            'format' => 'json',
        );
        $gen_sig = array_merge($gen_sig,$params);

        ksort($gen_sig);

        $imploded = urldecode(http_build_query($gen_sig,'',''));


        $sig = md5($imploded . $ok_secret_key);

        $build_query = $gen_sig;
        $build_query['sig'] = $sig;
        $build_query['access_token'] = $this->accessToken;

        $url = $this->rest(self::API_BASE_URL."?".http_build_query($build_query));

        return $url;
    }


    /**
     * @param $url
     * @return mixed|void
     */
    private function rest($url){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Authority: api.ok.ru';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {

            die ('OdnoklassnikiApi Error:' . curl_error($ch));
        }
        curl_close($ch);
        return json_decode($result);
    }



}

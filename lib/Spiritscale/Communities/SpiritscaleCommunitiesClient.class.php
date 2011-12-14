<?php
    namespace Spiritscale\Communities;
    
    require_once 'CommunityMembershipProfile.class.php';
    require_once 'UnexpectedResponseException.class.php';
    
    use Guzzle\Service\Client;
    

    
    
    
    /**
     * This class is strongly discouraged to be subclassed, as its internals are subject to change without notice.
     * Yet, its public contract will not change.
     */
    class SpiritscaleCommunitiesClient {

        /**
         * The underlying Guzzle client
         * @var Client
         */
        private $client;

        /**
         * @var string, has the following syntax: http://host:port
         * Currently only non SSL http traffic is supported in the PHP implementation of Spiritscale API.
         */
        private $hostPortConfig;
        
        /**
         * @var int 
         */
        private $apiKey;
        
        /**
         * @var string 
         */
        private $apiSecret;

        
        /**
         * @param $hostPortConfig string, has the following syntax: http://host:port
         * Currently only non SSL http traffic is supported in the PHP implementation of Spiritscale API.
         * @param $apiKey int - the API key
         * @param $apiSecret string - the API secret
         */
        public function __construct($hostPortConfig, $apiKey, $apiSecret) {
            $this->hostPortConfig = $hostPortConfig;
            $this->apiKey = $apiKey;
            $this->apiSecret = $apiSecret;
            $this->client = new Client($hostPortConfig.'/api/communities/'.$apiKey.'/');
        }
        
        
        ////////////////////////////////////////////////////////////////////
        // Member functions
        ////////////////////////////////////////////////////////////////////
        

        /**
         * Creates connection between one person and another.
         * 
         * @return void.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/members/{i}/{t}/{secret}
         * Method:       PUT
         * Http codes:   201 Created, 401 Unauthorized
         */
        public function putMemberConnection($institutorId, $targetId){
            $response = $this->client->put('members/'.$institutorId.'/'.$targetId.'/'.$this->apiSecret)->send();
            self::assertCreated($response);
            // no return value
        }
        
        
        /**
         * Creates member connections in bulk.
         * 
         * @param $connectionsArray a two dimensional array with two columns - 1st one is institutor id, 2nd one is target id. Every row describes one connection.
         * @return void.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/members/{secret}
         * Method:       PUT
         * Http codes:   201 Created, 401 Unauthorized
         */
        public function putMemberConnections($connectionsArray){
            $importBody = "<import>";
            foreach ($connectionsArray as $connection){
                $importBody .= "<item from=\"$connection[0]\">".$connection[1]."</item>";
            }
            $importBody .= "</import>";

            $response = $this->client->put('members/'.$this->apiSecret, array('Content-Type' => 'application/xml', 'Accept' => 'text/plain'), $importBody)->send();
            self::assertCreated($response);
            // no return value
        }
        
        
        /**
         * Removes connection between one person and another.
         * 
         * @return void.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/members/{i}/{t}/{secret}
         * Method:       DELETE
         * Http codes:   204 No content, 401 Unauthorized
         */
        public function removeMemberConnection($institutorId, $targetId){
            $response = $this->client->delete('members/'.$institutorId.'/'.$targetId.'/'.$this->apiSecret)->send();
            self::assertNoContent($response);
            // no return value
        }
        
        
        /**
         * @return true if connection exists between institutor and target, false otherwise.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/members/{i}/{t}/{secret}
         * Method:       GET
         * Http codes:   200 OK, 401 Unauthorized
         * 
         * Should return response body - text/plain: 't' or 'f' otherwise it is an 'unexpected' error.
         */
        public function getMemberConnection($institutorId, $targetId){
            $response = $this->client->get('members/'.$institutorId.'/'.$targetId.'/'.$this->apiSecret, array('Accept' => 'text/plain'))->send();
            self::assertOk($response);
            $responseBody = $response->getBody(true);
            if ($responseBody == "t") {
                return true;
            } else if($responseBody == "f") {
                return false;
            } else {
                throw new UnexpectedResponseException($responseBody);
            }
        }
        
        
        /**
         * @return A map from the "integer" target id to the boolean values of whether each of the targets has been connected to by the institutor.
         * 
         * @param $targetIdsArray must have no duplicate values.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/members/{i}/{secret}
         * Method:       PUT
         * Http codes:   200 OK, 400 Bad request, 401 Unauthorized
         */
        public function getMemberConnections($institutorId, $targetIdsArray){
            $spaceDelimitedMemberIds = implode(" ", $targetIdsArray);

            $response = $this->client->put('members/'.$institutorId.'/'.$this->apiSecret, array('Content-Type' => 'text/plain', 'Accept' => 'text/plain'), $spaceDelimitedMemberIds)->send();
            self::assertOk($response);

            $responseBody = $response->getBody(true);
            
            
            if(count($targetIdsArray) != strlen($responseBody)){
                throw new UnexpectedResponseException($responseBody);
            }

            $map = array();

            for($i = 0; $i < count($targetIdsArray); $i++){
                $c = $responseBody{$i};
                if($c == 't'){
                    $map[(string)$targetIdsArray[$i]] = true;
                } else if($c == 'f'){
                    $map[(string)$targetIdsArray[$i]] = false;
                } else {
                    throw new UnexpectedResponseException($responseBody);
                }
            }

            return $map;
        }
        
        
        /**
         * Retrieves rating.
         * @return The existing member's rating value ranging from 0 to 100. 0 means that either the rating is not yet available or that it's its actual value.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/rating/{member}/{secret}
         * Method:       GET
         * Http codes:   200 OK, 401 Unauthorized
         */
        public function getRating($memberId){
            $response = $this->client->get('rating/'.$memberId.'/'.$this->apiSecret, array('Accept' => 'application/xml'))->send();
            self::assertOk($response);
            return $response->getBody(true);
        }
        
        
        /**
         * Retrieves ratings in bulk.
         * Only existing subjects' ratings are returned.
         * 
         * @return A map from the member id to the rating value. The existing member's rating value range from 0 to 100.
         * 
         * Implementation details:
         * 
         * Relative URL: {apiKey}/rating/{secret}
         * Method:       PUT
         * Http codes:   200 OK, 400 Bad request, 401 Unauthorized
         * 
         * Internal format is defined to be as in:
         * <report><rating id="2">0</rating><rating id="345">90</rating><rating id="positiveInt">int(0-100)</rating></report>
         */
        public function getRatings($memberIdsArray){
            $spaceDelimitedMemberIds = implode(" ", $memberIdsArray);
//            echo "Member ids: ".$spaceDelimitedMemberIds;
            $response = $this->client->put('rating/'.$this->apiSecret, array(
                'Content-Type' => 'text/plain', 
                'Accept' => 'application/xml',
                'Accept-Encoding' => 'gzip, deflate'
            ), $spaceDelimitedMemberIds)->send();
            self::assertOk($response);

            $map = array();

            $report = simplexml_load_string($response->getBody(true));
            foreach ($report->children() as $rating) {
                $map[(string)$rating['id']] = (string)$rating;
            }
            
            return $map;
        }
        
        
        
        
        
        /**
         * Retrieves a community membership profile.
         * 
         * @return a CommunityMembershipProfile instance
         * 
         * @param $institutorId an anonymous subject user if non-positive
         * 
         * Relative URL: as of now, none
         * Method: as of now, none
         */
        public function getCommunityMembershipProfile($institutorId, $targetId){
            return new CommunityMembershipProfile($this->getRating($targetId), self::isLoggedIn($institutorId) ? $this->getMemberConnection($institutorId, $targetId) : false);
        }


        /**
         * Retrieves community membership profiles in bulk.
         * 
         * @return a list of CommunityMembershipProfile
         * 
         * @param $fetchConnections use case is e.g. when the subject user has logged in. Default is false.
         * 
         * Relative URL: as of now, none
         * Method: as of now, none
         */
        public function getCommunityMembershipProfiles($institutorId, $targetIdsArray){
            $ratings = $this->getRatings($targetIdsArray);
            $memberConnections = self::isLoggedIn($institutorId) ? $this->getMemberConnections($institutorId, $targetIdsArray) : null;
            
            $map = array();
            
            for ($i = 0; $i < count($targetIdsArray); $i++) {
                $targetId = $targetIdsArray[$i];
                if (isset($ratings[(string)$targetId])) {
                    $rating = $ratings[(string)$targetId];
                } else {
                    $rating = 0;
                }
                $connection = self::isLoggedIn($institutorId) ? $memberConnections[(string)$targetId] : null;
                
                $map[(string)$targetIdsArray[$i]] = new CommunityMembershipProfile($rating, $connection);
            }
            
            return $map;
        }
        
        
        
        
        ///////////////////////////////////////////////////////
        // Helper functions
        ///////////////////////////////////////////////////////
        
        static function assertOk($response){
            $statusCode = $response->getStatusCode();
            if($statusCode != 200){
                throw new UnexpectedResponseException("Expected a 200 OK response, it was ".$response->getStatusCode()." instead.");
            }
        }

        static function assertCreated($response){
            $statusCode = $response->getStatusCode();
            if($statusCode != 201){
                throw new UnexpectedResponseException("Expected a 201 Created response, it was ".$response->getStatusCode()." instead.");
            }
        }

        static function assertNoContent($response){
            $statusCode = $response->getStatusCode();
            if($statusCode != 204){
                throw new UnexpectedResponseException("Expected a 204 No content response, it was ".$response->getStatusCode()." instead.");
            }
        }
        
        // custom semantics are supported
        static function isLoggedIn($institutorId){
            return (int)$institutorId > 0;
        }
    }
?>
<?php
    namespace Spiritscale\Communities;
    
    class CommunityMembershipProfile {
        
        /**
         * @var int 
         */
        private $rating;
        
        /**
         * @var boolean
         */
        private $connectedTo;
        
        
        /**
         * @param $rating int
         * @param $connectedTo boolean
         */
        public function __construct($rating, $connectedTo) {
            $this->rating = $rating;
            $this->connectedTo = $connectedTo;
        }
        
        public function getRating(){
            return $this->rating;
        }
        
        public function getConnectedTo(){
            return $this->connectedTo;
        }
    }
?>
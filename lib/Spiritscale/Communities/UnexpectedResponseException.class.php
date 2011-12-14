<?php
    namespace Spiritscale\Communities;
    
    class UnexpectedResponseException extends \Exception {
        
        public function __construct($response) {
            parent::__construct($response, 9000, null);
        }
        
        public function __toString() {
            return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        }
    }
?>
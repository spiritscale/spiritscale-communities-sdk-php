<?php
    require_once '/Guzzle/guzzle.phar';
    require_once '../lib/Spiritscale/Communities/SpiritscaleCommunitiesClient.class.php';
    
    
    // !NB: make sure you have obtained a valid Spiritscale Communities account.
    $client = new Spiritscale\Communities\SpiritscaleCommunitiesClient('http://spiritscale.com', 9797, '9JWafz6lGdKiBnHZGJdr9WPA-57HdEOVdF84mM_kadah_InQ870');
    
    
    
    $CONNECTION_INSTITUTOR_ID = 50;
    $CONNECTION_TARGET_ID = 100;
    
    
    ///////////////////////////////
    
    
    echo "Creating member connection ($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID) ..<br/>";
    $client->putMemberConnection($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID);
    
    
    ///////////////////////////////
    
    
    echo "Member connection ($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID) exists: ";
    $memberConnection = $client->getMemberConnection($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID);
    echo ($memberConnection ? "true" : "false")."<br/>";
    
    
    ///////////////////////////////
    
    
    echo "Removing member connection ($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID) ..<br/>";
    $client->removeMemberConnection($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID);
    
    
    ///////////////////////////////
    
    
    echo "Member connection ($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID) exists: ";
    $memberConnection = $client->getMemberConnection($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID);
    echo ($memberConnection ? "true" : "false")."<br/>";
    
    
    ///////////////////////////////
    
    
    echo "Creating member connections ($CONNECTION_INSTITUTOR_ID, 100), ($CONNECTION_INSTITUTOR_ID, 101), ($CONNECTION_INSTITUTOR_ID, 102), (60, 102), ($CONNECTION_INSTITUTOR_ID, 110) ..<br/>";
    $memberConnections = array(
        array($CONNECTION_INSTITUTOR_ID, 100),
        array($CONNECTION_INSTITUTOR_ID, 101),
        array($CONNECTION_INSTITUTOR_ID, 102),
        array(60, 102),
        array($CONNECTION_INSTITUTOR_ID, 110)
    );
    $client->putMemberConnections($memberConnections);
    
    
    ///////////////////////////////
    
    
    echo "Member connections ($CONNECTION_INSTITUTOR_ID, 1000), ($CONNECTION_INSTITUTOR_ID, 100), ($CONNECTION_INSTITUTOR_ID, 101), ($CONNECTION_INSTITUTOR_ID, 102), ($CONNECTION_INSTITUTOR_ID, 110) exist: ";
    $targetIds = array(1000, 100, 101, 102, 110);
    $memberConnections = $client->getMemberConnections($CONNECTION_INSTITUTOR_ID, $targetIds);
    foreach ($memberConnections as $key => $value) {
        echo "<br/>".$key.": ".($value ? "true" : "false");
    }
    echo "<br/>";

    
    ///////////////////////////////
    
    
    echo "Rating ($CONNECTION_INSTITUTOR_ID): ".$client->getRating($CONNECTION_INSTITUTOR_ID)."<br/>";
    
    
    ///////////////////////////////
    
    
    $BULK_RATING_MEMBER_IDS = array($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID, 2345);
    $ratingMap = $client->getRatings($BULK_RATING_MEMBER_IDS);
    echo "Bulk rating ($CONNECTION_INSTITUTOR_ID, $CONNECTION_TARGET_ID): $ratingMap[$CONNECTION_INSTITUTOR_ID], $ratingMap[$CONNECTION_TARGET_ID]<br/>";
    
    
    ///////////////////////////////
    ///////////////////////////////
    ///////////////////////////////
    ///////////////////////////////
    
    
    
    
    
    
    
    /*
     * This is the code that you should typically use.
     */
    
    
    $BULK_PROFILE_MEMBER_IDS = array($CONNECTION_TARGET_ID, 3455, 101, 1000, 102, 103);
    
    echo "<br/><br/>The user has logged in";
    $communityProfileMap = $client->getCommunityMembershipProfiles($CONNECTION_INSTITUTOR_ID, $BULK_PROFILE_MEMBER_IDS);
    foreach ($communityProfileMap as $key => $profile) {
        echo "<br/>".$key.":  (rating: ".$profile->getRating()."    connected to: ".($profile->getConnectedTo() ? "true" : "false").")";
    }
    
    ///////////////////////////////
    
    // not logged in anymore
    $CONNECTION_INSTITUTOR_ID = 0;
    
    echo "<br/><br/>Now the user has logged out";
    $communityProfileMap = $client->getCommunityMembershipProfiles($CONNECTION_INSTITUTOR_ID, $BULK_PROFILE_MEMBER_IDS);
    foreach ($communityProfileMap as $key => $profile) {
        echo "<br/>".$key.":  (rating: ".$profile->getRating()."    connected to: ".($profile->getConnectedTo() ? "true" : "not-available").")";
    }
?>
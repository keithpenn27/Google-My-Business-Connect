<?php 

namespace GMBConnect;

/**
 * Handles backend Location API functionality.
 */
class Locations
{

  /**
   * Get the locations managed by the current account.
   *
   * @return ListAccountsLocationsResponse
   */
    public static function sync_locations() {

    $creds = \get_option( 'gmbc_credentials' );

    if ( ( ! empty($creds['client_id']) && $creds['client_id'] != '') && (! empty($creds['client_secret']) && $creds['client_secret'] != '') ) {
      
      $client = Loader::get_client();

       // Get the client from the Loader instance
       $client = Loader::get_client();

       $account_management = new \Google_Service_MyBusinessAccountManagement($client);
       $business_info = new \Google_Service_MyBusinessBusinessInformation($client);
 
       // Get the current Google account
       $gmb_accounts = $account_management->accounts->listAccounts()->getAccounts();

       // Get the locations the current has access to
       $gmb_locations = $business_info->accounts_locations->listAccountsLocations(
           $gmb_accounts[0]->name,
           [
       'readMask' => [
       "name",
       "labels",
       "languageCode",
       "storeCode",
       "title",
       "websiteUri",
       "metadata",
       "latlng",
       "categories",
       "storefrontAddress",
       "regularHours",
       "specialHours",
       "serviceArea",
       "openInfo",
       "profile",
       "relationshipData",
       "moreHours",
       "serviceItems" ],
       'pageSize' => 100,
       'orderBy' =>
       'title'
       ]
       );


       $locations = [];

       while (count($locations) < $gmb_locations->totalSize) {
           $locations = array_merge($locations, $gmb_locations->locations);
 
           $gmb_locations =     $gmb_locations = $business_info->accounts_locations->listAccountsLocations(
               $gmb_accounts[0]->name,
               [
             'readMask' => [
             "name",
             "labels",
             "languageCode",
             "storeCode",
             "title",
             "websiteUri",
             "metadata",
             "latlng",
             "categories",
             "storefrontAddress",
             "regularHours",
             "specialHours",
             "serviceArea",
             "openInfo",
             "profile",
             "relationshipData",
             "moreHours",
             "serviceItems" ],
             'pageSize' => 100,
             'orderBy' =>
             'title'
             ]
           );
       }
       
       // Loop through the returned reviews.
       foreach ($locations as $key => $location) {
           list($path, $location_id) = (explode('/', $location->name));
 
           $location_exists = DBTables::check_synced_locations($location_id);

 
           // If the review already exists in our database, update it. If not, add it.
           if ($location_exists) {
 
           // Review exists, so update it.
               DBTables::update_locations_table(
                   $location->name,
                   $location->title,
                   $location_id,
                   $location->metadata->newReviewUri,
                   $location->metadata->mapsUri
               );
           } else {
 
           // Add the review to the database.
               DBTables::insert_locations_table(
                   $location->name,
                   $location->title,
                   $location_id,
                   $location->metadata->newReviewUri,
                   $location->metadata->mapsUri
               );
           }
       }

       return $locations;
      }
    }
   
}

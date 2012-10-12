<?php

/************************************************************/
/************************************************************
 * CLASS:         CBAPI
 * DESCRIPTION:   Master class responsible for making all API calls to CareerBuilder.com this is the
 *                primary interface for performing CareerBuilder related activities.
 *
 * FUNCTIONS:     getJobCount, getJobDetails, getJobResults, getRecommendationsForJob, etc.
 *
 */
class CBAPI {
	private static $APIKey = "YOURDEVELOPERKEYHERE";  /* your private CareerBuilder API Developer Key */
	public static $perPage = 10; /* the number of job results to pull back for a results search */
	public static $siteID = ''; /* optional string that can be used to track engagement */
	
	
	/*
	 *  FUNCTION:     getJobCount
	 *  DESCRIPTION:  Function that runs job search and returns the number of jobs found that match
	 *  INPUT:        (keywords:string) string for the keywords of the search
	 *  INPUT:        (location:string) string for the location of the search
	 *  RETURNS:      an integer of the number of jobs found
	 *  EXAMPLE CODE: 
	 *  			require_once('../classes/cb.php'); // load library
	 *				$num_jobs = CBAPI::getJobCount("sales","atlanta"); // make the function call
	 *
	 */	
	public static function getJobCount($keywords, $location) {
		$siteIDValue = CBAPI::$siteID;
		$location = urlencode($location);
		$keywords = urlencode($keywords);
		$key = CBAPI::$APIKey;
		$url = "http://api.careerbuilder.com/V1/jobsearch?DeveloperKey=$key&ExcludeNational=True&Keywords=$keywords&siteid=$siteIDValue&Location=$location&PerPage=1";
		try {
			$xml = simplexml_load_file($url);
		}catch(Exception $e){
			print_r($e);
		}
		
		$count = $xml->TotalCount;
		return $count;
	}
	
	
	/*
	 *  FUNCTION:     getJobCountSinceDate
	 *  DESCRIPTION:  Function to get the number of jobs since a date
	 *  INPUT:        (keywords:string) string for the keywords of the search
	 *  INPUT:        (location:string) string for the location of the search
	 *  INPUT:        (daysBackToLook:number) number of days back to look, values from 1-30
	 *  INPUT:        (country:string:optional) country code in which to run the search
	 *  RETURNS:      an integer of the number of jobs found
	 *  EXAMPLE CODE: 
	 *  			require_once('../classes/cb.php'); // load library
	 *				$num_jobs = CBAPI::getJobCountSinceDate("sales","atlanta", 7); // make the function call
	 *
	 */	
	public static function getJobCountSinceDate($keywords, $location, $daysBackToLook, $country = null) {
		$siteIDValue = CBAPI::$siteID;
		$location = urlencode($location);
		$keywords = urlencode($keywords);
		$key = CBAPI::$APIKey;
		$url = "http://api.careerbuilder.com/V1/jobsearch?DeveloperKey=$key&ExcludeNational=True&Keywords=$keywords&PostedWithin=$daysBackToLook&siteid=$siteIDValue&Location=$location";
		if($country != "" && $country != null) {
			$url = $url . "&CountryCode=$country";
		}
		
		try {
			$xml = simplexml_load_file($url);
		}catch(Exception $e){
			print_r($e);
		}
		
		$count = $xml->TotalCount;
		return $count;
	}
	
	
	
	/*
	 *  FUNCTION:     getJobResults
	 *  DESCRIPTION:  Function to run a job search on careerbuilder and return back an array of the
	 *                results that matched the search.
	 *  INPUT:        (keywords:string) string for the keywords of the search
	 *  INPUT:        (location:string) string for the location of the search
	 *  INPUT:        (country:string) country code in which to search
	 *  INPUT:        (pagenumber:number) page of results to retrieve, start at page 0
	 *  INPUT:        (daysBackToLook:number:optional) values from 1-30, used to add a time constraint
	 *                to the search
	 *  RETURNS:      an array of job objects that matches the search
	 *  EXAMPLE CODE: 
	 *  			require_once('../classes/cb.php'); // load library
	 *				$results = CBAPI::getJobResults("sales","Atlanta", "", 0); // request a job object
	 *
	 */	
	public static function getJobResults($keywords, $location, $country, $pagenumber, $daysBackToLook = null) {
		$siteIDValue = CBAPI::$siteID;
		$location = urlencode($location);
		$keywords = urlencode($keywords);
		$key = CBAPI::$APIKey;
		$perPage = CBAPI::$perPage;
		$url = "http://api.careerbuilder.com/v1/jobsearch?DeveloperKey=$key&ExcludeNational=True&Location=".$location."&siteid=$siteIDValue&Keywords=$keywords&PerPage=$perPage&PageNumber=$pagenumber";
		if($daysBackToLook != null)
		{
			$url .= "&PostedWithin=$daysBackToLook";
		}
		if($country != "") {
			$url = $url . "&CountryCode=$country";
		}
		$xml = simplexml_load_file($url);
		$jobsCollection = Array();
		$currItem = 0;
		foreach($xml->Results->JobSearchResult as $result) {
	    $currJob = new Job();
	    $currJob->did = (string)$result->DID;
	    $currJob->title = (string)$result->JobTitle;
	    $currJob->companyName = (string)$result->Company;
	    $currJob->city = (string)$result->LocationCity;
	    $currJob->state = (string)$result->LocationState;
	    $currJob->latitude = (string)$result->LocationLatitude;
	    $currJob->longitude = (string)$result->LocationLongitude;
	    $currJob->description = (string)$result->DescriptionTeaser; 
	    $currJob->posted = (string)$result->PostedDate;
			$jobsCollection[$currItem] = $currJob;
			$currItem ++;
		}
		return $jobsCollection;
	}
	
	
	/*
	 *  FUNCTION:     getJobDetails
	 *  DESCRIPTION:  Method to get the data related to one specific job posting
	 *  INPUT:        (jobdid:string) a careerbuilder job document identifier
	 *  RETURNS:      a job object instance
	 *  EXAMPLE CODE: 
	 *  			require_once('../classes/cb.php'); // load library
	 *				$job = CBAPI::getJobDetails("J1234567890ABCDEF"); // request a job object
	 *				$title = $job->JobTitle;                        // pull off the job title from the job
	 *
	 */	
	public static function getJobDetails($jobdid) {
		$key = CBAPI::$APIKey;
		$jobdid = urlencode($jobdid);
		$url = "http://api.careerbuilder.com/v1/job?DeveloperKey=$key&siteid=$siteIDValue&DID=$jobdid&CoBrand=$cobrandcode";
		$xml = simplexml_load_file($url);

	  $retJob = new Job();
	  $retJob->did = (string)$xml->Job->DID;
	  $retJob->title = (string)$xml->Job->JobTitle;
		$retJob->companyName = (string)$xml->Job->Company;
	  $retJob->city = (string)$xml->Job->LocationCity;
	  $retJob->state = (string)$xml->Job->LocationState;
	  $retJob->latitude = (string)$xml->Job->LocationLatitude;
	  $retJob->longitude = (string)$xml->Job->LocationLongitude;
	  $retJob->description = (string)$xml->Job->JobDescription; 
	  $retJob->posted = (string)$xml->Job->PostedDate;
	  $retJob->categories = explode(',',(string)$xml->Job->Categories);
	  $retJob->categoryCodes = explode(',',(string)$xml->Job->CategoriesCodes);
	  $retJob->applyURL = (string)$xml->Job->ApplyURL;
		$retJob->companyDetailsURL = (string)$xml->Job->CompanyDetailsURL;
		
		return $retJob;
	}
	
	
	
	/*
	 *  FUNCTION:     getRecommendationsForJob
	 *  DESCRIPTION:  Method to find jobs similiar to the job passed in
	 *  INPUT:        (jobdid:string) a careerbuilder job document identifier to match
	 *  RETURNS:      an array of job recommendation objects
	 *  EXAMPLE CODE: 
	 *  			require_once('../classes/cb.php'); // load library
	 *				$job_matches = CBAPI::getRecommendationsForJob("J1234567890ABCDEF"); // request a job object
	 *
	 */	
	public static function getRecommendationsForJob($JobDID){
		global $bDebug;
		$JobDID = urlencode($JobDID);
		$key = CBAPI::$APIKey;
		$url = "http://api.careerbuilder.com/v1/recommendations/forjob/?DeveloperKey=$key&JobDID=$JobDID";

		$xml = simplexml_load_file($url);
		$jobsCollection = Array();
		$currItem = 0;
		if ($xml->RecommendJobResults->RecommendJobResult) {
			foreach($xml->RecommendJobResults->RecommendJobResult as $result) {
		    $currJob = new jobRecommendation();
				
				$currJob->did = (string)$result->JobDID;
				$currJob->title = (string)$result->Title;
				$currJob->relevancy = (string)$result->Relevancy;
				$currJob->companyName = (string)$result->Company->CompanyName;
				$currJob->companyLink = (string)$result->Company->CompanyDetailsURL;
				$currJob->locationCity = (string)$result->Location->City;
				$currJob->locationState = (string)$result->Location->State;
				$currJob->detailsLink = (string)$result->JobDetailsURL;
				$currJob->apiDetailsLink = (string)$result->JobServiceURL;
				$currJob->similarJobsLink = (string)$result->SimilarJobsURL;
	
				$jobsCollection[$currItem] = $currJob;
				$currItem ++;
			}
			return $jobsCollection;
		} else {
			return false;
		}
	}
	
	
	// returns a collection of JobRecommendation Objects
	// returns null if there are no recommendations available
	// 10 is the default but the range can be between 1 and 100
	public static function getRecommendationsForUser($ExternalID, $countLimit = 10){
		$siteIDValue = $this->siteIDParam;
		$ExternalID = urlencode($ExternalID);
		$key = CBAPI::$APIKey;
		$url = "http://api.careerbuilder.com/v1/recommendations/foruser/?DeveloperKey=$key&ExternalID=$ExternalID";
		
		if($countLimit > 0){
			if($countLimit <= 100){
				$url .= "&CountLimit=$countLimit";
			}else{
				$url .= "&CountLimit=100";
			}
		}else{
			$url .= "&CountLimit=10";
		}

		if($bDebug){echo$url;}

		$xml = simplexml_load_file($url);
		
		// FIRST WE DETERMINE  IF WE HAVE RECOMMENDATIONS
		// if not we will return null
		if(count($xml->Errors->children()) > 0)
		{
			//print_r($xml->Errors->children());
			return null;
		}
		
		$jobsCollection = Array();
		$currItem = 0;
		$this->totalJobResultsFromLastQuery = -1;
		foreach($xml->RecommendJobResults->RecommendJobResult as $result) {
	    $currJob = new jobRecommendation();
			
			$currJob->did = $result->JobDID;
			$currJob->title = $result->Title;
			$currJob->relevancy = $result->Relevancy;
			$currJob->companyName = $result->Company->CompanyName;
			$currJob->companyLink = $result->Company->CompanyDetailsURL;
			$currJob->locationCity = $result->Location->City;
			$currJob->locationState = $result->Location->State;
			$currJob->detailsLink = $result->JobDetailsURL;
			$currJob->apiDetailsLink = $result->JobServiceURL;
			$currJob->similarJobsLink = $result->SimilarJobsURL;

			$this->jobsParsed[$this->itemNumber] = $currJob;
			$this->itemnumber ++;
			$jobsCollection[$currItem] = $currJob;
			$currItem ++;
		}
		//print_r($jobsCollection);
		return $jobsCollection;
	}
	
}





/************************************************************/
/************************************************************
 * CLASS:         Job
 * DESCRIPTION:   Class representing a job posting on careerbuilder.com.
 *
 * ATTRIBUTES:    did, title, company, city, state, etc.
 *
 * FUNCTIONS:     getJobCount, getJobDetails, getJobResults, getRecommendationsForJob, etc.
 *
 * SUGGESTED IMPROVMENTS: expand attributes to reflect all job properties, create a constructor
 *                        that will initialize from passed in XML or SimpleXML object
 */
class Job {
	public $did = "";
	public $title = "";
	public $companyName = "";
	public $city = "";
	public $state = "";
	public $description = "";
	public $posted = "";
	public $relevancy = -1;
	public $applyURL = "";
	public $categories = nil;
	public $categoryCodes = nil;
	public $companyDetailsURL = "";
	

	/*
	 *  FUNCTION:     getJobTitle
	 *  DESCRIPTION:  More advanced way to return the job title
	 *  INPUT:        (maxLength:number:optional) the maximum length before trimming the title
	 *  RETURNS:      the job title as a string (truncated if specified)
	 */	
	public function getJobTitle($maxLength = null) {
		if($this->title != null && $maxLength != null && strlen($this->title) > $maxLength) {
			return substr($this->title, 0, $maxLength-3)."...";
		} else {
			return $this->title;
		}
	}

	/*
	 *  FUNCTION:     getCompanyName
	 *  DESCRIPTION:  More advanced way to return the job company Name
	 *  INPUT:        (maxLength:number:optional) the maximum length before trimming the title
	 *  RETURNS:      the job title as a string (truncated if specified)
	 */	
	public function getCompanyName($maxLength = null) {
		if($this->companyName != null && $maxLength != null && strlen($this->companyName) > $maxLength) {
			return substr($this->companyName, 0, $maxLength-3)."...";
		} else {
			return $this->companyName;
		}
	}

	/*
	 *  FUNCTION:     getLocation
	 *  DESCRIPTION:  More advanced way to return the jobs location
	 *  INPUT:        (maxLength:number:optional) the maximum length before trimming the title
	 *  RETURNS:      the city/state as one location string (truncated if specified)
	 */	
	public function getLocation($maxLength = null) {
		$location = "" . $this->city;
		if($this->state != "" && $this->state != null){
			if($location != ""){ $location .= ", "; }
			$location .= $this->state;
		}
		if($location != null && $maxLength != null && strlen($location) > $maxLength) {
			return substr($location, 0, $maxLength-3)."...";
		} else {
			return $location;
		}
	}
	
	/*
	 *  FUNCTION:     getJobTitle
	 *  DESCRIPTION:  More advanced way to return the job title
	 *  INPUT:        (maxLength:number:optional) the maximum length before trimming the title
	 *  RETURNS:      the job title as a string (truncated if specified)
	 */	
	public function getJobDescription() {
		return $this->description;
	}
	
}


/************************************************************/
/************************************************************
 * CLASS:         jobRecommendation
 * DESCRIPTION:   Class representing a job recomendation (very similar to Job Detail)
 *
 * KEY ATTRIBUTES: did, title, companyName, locationCity, locationSate, relevancy, etc.
 *
 * FUNCTIONS:     getJobCount, getJobDetails, getJobResults, getRecommendationsForJob, etc.
 *
 * SUGGESTED IMPROVMENTS: expand attributes to reflect all job properties, create a constructor
 *                        that will initialize from passed in XML or SimpleXML object
 */
class jobRecommendation {
	public $did = "";
	public $title = "";
	public $companyName = "";
	public $companyLink = "";
	public $locationCity = "";
	public $locationState = "";
	public $relevancy = -1;
	public $detailsLink = "";
	public $apiDetailsLink = "";
	public $similarJobsLink = "";
	public $posted = "";
	
	public function getJobTitle() {
		return $this->title;
	}

	public function getJobCompany() {
		return $this->companyName;
	}

	public function getJobLocation() {
		$retStr = $this->locationState;

		if($retStr != "" && $this->locationCity != ""){
			$retStr = $this->locationCity . ", " . $retStr;
		}
		
		return $retStr;
	}

}
?>
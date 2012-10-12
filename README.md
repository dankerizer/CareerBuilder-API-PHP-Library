CareerBuilder API PHP Library
=============================

Library to provide basic CareerBuilder API functionality for PHP developers.  Job search, Job Details, Recommendations.  Requires a valid API Developer Key.

Classes
-------

### CBAPI

Wrapper class for core operations against the CareerBuilder API.


    $job = CBAPI::getJobDetails('J12345ABCDE');
    $results_arr = CBAPI::getJobResults('sales','atlanta','',0);


### Job

Class representing a single job entry from CareerBuilder.

### JobRecommendation

Class representing a single job recommendation from CareerBuiler

Further Reading
-------

Visit http://api.careerbuilder.com to learn more about obtaining a developer key as well as learning about what API calls are possible and how to interface with them.

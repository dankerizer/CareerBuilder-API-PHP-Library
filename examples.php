<?
	require_once("cb.php");
?>
<html>
	<head>
	</head>
	<body>
		<div>
			<b>Job Count:</b> <?= CBAPI::getJobCount("sales","atlanta")?>
		</div>
		<br>
		
		<div>
			<b>Job Count Since Date:</b> <?= CBAPI::getJobCountSinceDate("sales","atlanta", 7)?>
		</div>
		<br>
		
		<div>
			<?
				$job = CBAPI::getJobDetails("JHT5C35XTD3RGR97QQF"); // request a job object
				$title = $job->JobTitle;                        // pull off the job title from the job
			?>
			<b>Job Details:</b> (<?= $job->getJobTitle() ?>/<?= $job->getJobTitle(10) ?>) <br><?= $job->getCompanyName() ?><br><?= $job->getLocation() ?><br><? print_r($job) ?>
		</div>
		<br>
		
		<div>
			<?
				$results = CBAPI::getJobResults("sales", "atlanta", "", 0);
			?>
			<b>Job Results:</b> <? print_r($results) ?>
		</div>
		<br>
		
		<div>
			<?
				$matches = CBAPI::getRecommendationsForJob("JHT5C35XTD3RGR97QQF");
			?>
			<b>Job Recommendations for Job: </b>  <? print_r($matches) ?>
		</div>
		<br>
		
	</body>
</html>
<?php 
	//import config file
	$dbUser = "Collaboratum";
	$dbPass = "Collaboratum";
	$dbNameGeneral = "collaboratum";
	$dbNameNetwork = "parsingdata";
	
	$baseURL = "http://binf1.memphis.edu/Collaboratum";
	
	$lsiQueryHost = "localhost";
	$lsiQueryPort = "50005";
	
	$keywdQueryHost = "localhost";
	$keywdQueryPort = "50004";    	

	/*
	 * Get the search query and the type of query.
	 * 
	 * $query: The search string to use against the database 
	 * $searchType: The filter to use for search results. 
	 * 		searchType possible values 
	 * 		0 - Do not filter, return both investigators and grants.
	 * 		1 - Return only related grant information.
	 * 		2 - Return only related investigators. 
	 * $exactSearch: Whether or not we want to use Keyword search or LSI search.
	 * 		exactSearch possible values
	 * 		'true'  - Use Keyword search
	 * 		'false' - Use LSI search
	 */ 
	$query = $_POST['searchBox'];
	if( isset($_POST['searchBox']) ) {
		$query = $_POST['searchBox'];
	}
	else if ( isset($_GET['searchBox']) ) {
		$query = $_GET['searchBox'];
	}
	if( isset($_POST['searchType']) ) {
		$searchType = $_POST['searchType'];
	}
	else if( isset($_GET['searchType']) ) {
		$searchType = $_GET['searchType'];
	}

 	$investigatorArray = array( "Randall", "Bayer",
 "Melvin", "Beck",
 "Charles", "Biggers",
 "King", "Thom", "Chung",
 "Judith", "Cole",
 "Lewis", "Coons",
 "Michael", "Ferkin",
 "David", "Freeman",
 "Kent", "Gartner",
 "Shirlean", "Goodwin",
 "Ramin", "Homayouni",
 "Michael", "Kennedy",
 "Charles", "Lessman",
 "Andrew", "Liu",
 "Carlos", "Lopez-Estrano",
 "Duane", "McKenna",
 "Takuya", "Nakazato",
 "Donald", "Ourth",
 "Matthew", "Parris",
 "Reza", "Pezeshki",
 "Stephan", "Schoech",
 "Steve", "Schwartzbach",
 "Bill", "Simco",
 "Omar", "Skalli",
 "Stanley", "Stevens",
 "Thomas", "Sutter",
 "Barbara", "Taller",
 "Tit-Yee", "Wong",
 "Joel", "Bumgardner",
 "Amy", "de", "Jongh", "Curry",
 "Eugene", "Eckstein",
 "Warren", "Haggard",
 "Jessica", "Jennings",
 "Erno", "Lindner",
 "Bradford", "Pendley",
 "Esra", "Roan",
 "John", "Williams",
 "Michael", "Yen",
 "William", "Alexander",
 "Daniel", "Baker",
 "Peter", "Bridson",
 "Theodore", "Burkey",
 "Gary", "Emmert",
 "Mark", "Freilich",
 "Tomoko", "Fujiwara",
 "Larry", "Houk",
 "Xiaohua", "Huang",
 "Henry", "Kurtz",
 "Ying", "Sing", "Li",
 "Abby", "Parrill",
 "Richard", "Petersen",
 "Evgueni", "Pinkhassik",
 "Paul", "Simone",
 "Yongmei", "Wang",
 "Charles", "Webster",
 "Jeremy", "Wessel",
 "Xuan", "Zhao");
	foreach( $investigatorArray as $name ) {
		if( strpos(strtolower($query), strtolower($name)) !== false) {
			$searchType = 2;
			break;
		}
	}

	$largestSimilarity = 0;
	$unresolved;
	$histogram;
	
	if( isset($_POST['exactSearch']) )
	{
		$exactSearch = $_POST['exactSearch'];
	}
	else if( isset($_GET['exactSearch']) ) 
	{
		$exactSearch = $_GET['exactSearch'];
	}
	else
	{
		$exactSearch = "false";
	}
	
	// if we have a search query to use
    if(isset($query))
    {
    	// If we want to use LSI search
        if($exactSearch === "false")
        {
        	// connect to the LSI query service on port 50005 and query it.
					$queryResult = querySearchService($lsiQueryHost, $lsiQueryPort, $query, $searchType);
			  	$queryResult = explode("\n", $queryResult);  
					$largestSimilarity = 1;
					$unresolved = $queryResult;
					$queryResult = resolveIDs( $queryResult, $dbHost, $dbUser, $dbPass );
        }
		// TODO finish implementing histogram widget
		// Otherwise we want to use Keyword search
        else
        {
        	// connect to the Keyword query service on port 50004 and query it.
					$queryResult = querySearchService($lsiQueryHost, $keywdQueryPort, $query, $searchType);
          $queryResult = explode("\n", $queryResult);
					$largestSimilarity = findLargestSimilarity( $queryResult );
					
					//echo "finish similar";
					if($exactSearch === "true" )
					{
						// pass the largest similarity from the keyword search
						$histogram = generateHistogramData($queryResult, 0, $largestSimilarity);
					}
					else {
						// the range of similarity scores for LSI is -1 to 1.
						$histogram = generateHistogramData($queryResult, -1, 1);
					}
					$unresolved = $queryResult;
					$queryResult = resolveIDs( $queryResult, $dbHost, $dbUser, $dbPass );
        }
		
		
    }
	// There was no search query to use.
    else
    {
        echo "There was an error performing the search";
    }
	
	/*
	 * $data is the array that contains scores.
	 * $min is the minimum score that can be encountered.
	 * $max is the maximum score that can be encountered.
	 */ 
	function generateHistogramData( $data, $min, $max )
	{
		$histogram[0] = 0;
		$histogram[1] = 0;
		$histogram[2] = 0;
		$histogram[3] = 0;
		$histogram[4] = 0;
		$histogram[5] = 0;
		$histogram[6] = 0;
		$histogram[7] = 0;
		$histogram[8] = 0;
		$histogram[9] = 0;
		$histogram[10] = 0;
		$histogram[11] = 0;
		$histogram[12] = 0;
		$histogram[13] = 0;
		$histogram[14] = 0;
		$histogram[15] = 0;
		$histogram[16] = 0;
		$histogram[17] = 0;
		$histogram[18] = 0;
		$histogram[19] = 0;
		// for each similarity score.
		for($i = 0; $i < count($data) - 1; $i++)
	    {
	    	// get the similarity
			$entry = explode(" ", $data[$i]);
		    $name = "";
			$entry = explode(" ", $data[count($entry)]);
			$similarity = $entry[count($entry) - 1 ];
			
			// update the count depending on the range that $similarity falls into.
			if($similarity >= -1 && $similarity <= -.9) {
				$histogram[0] = $histogram[0] + 1;
			}
			else if($similarity > -.9 && $similarity <= -.8){
				$histogram[1] = $histogram[1] + 1;
			}
			else if($similarity > -.8 && $similarity <= -.7){
				$histogram[2] = $histogram[2] + 1;
			}
			else if($similarity > -.7 && $similarity <= -.6){
				$histogram[3] = $histogram[3] + 1;
			}
			else if($similarity > -.6 && $similarity <= -.5){
				$histogram[4] = $histogram[4] + 1;
			}
			else if($similarity > -.5 && $similarity <= -.4){
				$histogram[5] = $histogram[5] + 1;
			}
			else if($similarity > -.4 && $similarity <= -.3){
				$histogram[6] = $histogram[6] + 1;
			}
			else if($similarity > -.3 && $similarity <= -.2){
				$histogram[7] = $histogram[7] + 1;
			}
			else if($similarity > -.2 && $similarity <= -.1){
				$histogram[8] = $histogram[8] + 1;
			}
			else if($similarity > -.1 && $similarity <= 0){
				$histogram[9] = $histogram[9] + 1;
			}
			else if($similarity > 0 && $similarity <= .1){
				$histogram[10] = $histogram[10] + 1;
			}
			else if($similarity > .1 && $similarity <= .2){
				$histogram[11] = $histogram[11] + 1;
			}
			else if($similarity > .2 && $similarity <= .3){
				$histogram[12] = $histogram[12] + 1;
			}
			else if($similarity > .3 && $similarity <= .4){
				$histogram[13] = $histogram[13] + 1;
			}
			else if($similarity > .4 && $similarity <= .5){
				$histogram[14] = $histogram[14] + 1;
			}
			else if($similarity > .5 && $similarity <= .6){
				$histogram[15] = $histogram[15] + 1;
			}
			else if($similarity > .6 && $similarity <= .7){
				$histogram[16] = $histogram[16] + 1;
			}
			else if($similarity > .7 && $similarity <= .8){
				$histogram[17] = $histogram[17] + 1;
			}
			else if($similarity > .8 && $similarity <= .9){
				$histogram[18] = $histogram[18] + 1;
			}
			else if($similarity > .9 && $similarity <= 1){
				$histogram[19] = $histogram[19] + 1;
			}
	    }
		return $histogram;
	}
    
	function findLargestSimilarity( $data )
	{
		$largest = 0;
		for($i = 0; $i < count($data) - 1; $i++)
	    {
	    	/*
			 *  convert the line into an array, where each index is a word separated by a space.
			 *  For example say, $unresolvedIDs[0] = "1531 1.312"
			 *  The string "1531 1.312" is composed of two parts. The first part is an ID that can 
			 *  be resolved against the Collaboratum database. The second part is a similarity or 
			 * 	ranking score used to determine how relevant this result is to the search query.
			 * 
			 *  When we explode(" ", "1531 1.312") we get an array $entry where
			 *  $entry[0] = "1531"
			 *  $entry[1] = "1.312"
			 */ 
			 $entry = explode(" ", $data[$i]);
		     $name = "";
			 $entry = explode(" ", $data[count($entry)]);
			 
			
			$similarity = $entry[count($entry) - 1 ];
			
			if ($similarity > $largest)
			{
				$largest = $similarity;
			}
	    }
		return $largest + 0.5;
	}
	
	/**
	 * Description: This function takes IDs returned by query services from their database and resolves them
	 * to an investigator or grant name.  
	 * 
	 * Parameters:
	 * $unresolvedIDs: This is an array that contains all of the IDs from the query service
	 * that need to be resolved into names using the Collaboratum database.
	*/
	function resolveIDs( $unresolvedIDs, $dbHost, $dbUser, $dbPass )
	{
		// Now we connect to the Collaboratum database
		
	    $con = mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
			
		$resolvedIDs = array();
		// for each line in $unresolvedIDs
	    for($i = 0; $i < count($unresolvedIDs) - 1; $i++)
	    {
	    	/*
			 *  convert the line into an array, where each index is a word separated by a space.
			 *  For example say, $unresolvedIDs[0] = "1531 1.312"
			 *  The string "1531 1.312" is composed of two parts. The first part is an ID that can 
			 *  be resolved against the Collaboratum database. The second part is a similarity or 
			 * 	ranking score used to determine how relevant this result is to the search query.
			 * 
			 *  When we explode(" ", "1531 1.312") we get an array $entry where
			 *  $entry[0] = "1531"
			 *  $entry[1] = "1.312"
			 */ 
	        $entry = explode(" ", $unresolvedIDs[$i]);
		// When we explode this tring there will be more array entries than we need. This is because the sever inserts multiple spaces
		// to visually align the id and similiarity scores in output.

		// TODO modify the lsi and keyword python servers to only have one space in between the id and sim score.	
	        // Then we query the Collaboratum database with the ID($entry[0]) from our exploded array, $entry.
		$entry[1] = $entry[count($entry)-1];
		$size = count($entry);
		for( $j = 4; $j < $size; $j++)
		{
			unset($entry[$j]);
		}
		$entry = array_values($entry);

	        $resolvedID = mysql_query("SELECT collaboratum.investigator.first_name, collaboratum.investigator.type FROM collaboratum.investigator WHERE collaboratum.investigator.investigator_id = ".$entry[0]."")
	        or die(mysql_error());  
	
			// Then we take the results from the database and store them in
	        $row = mysql_fetch_array( $resolvedID);
	        // Then we overwrite the id with the textual name from the database.
		$entry[2] = $entry[0];  
	        $entry[0] = $row['first_name'];
		// also store the type
		$entry[3] = $row['type'];
	        $resolvedIDs[$i] = implode("`", $entry);
	    }
		return $resolvedIDs;
	}
	
	/**
	 * Description: This function connects to the given query service, queries it, and returns the results from 
	 * the query service.
	 * 
	 * Parameters:
	 * $hostname: The hostname is the machine on which the query service is running. Ex. 'localhost', '127.0.0.1', '75.66.31.45', etc.
	 * $port: The port on which the query service is running on the given host.
	 * $query: The search string to query the service with.
	 * $type: The type of results that are desired from the query service.
	 * 		type possible values
	 * 		0 - return results containing both grants and investigators
	 * 		1 - return results containing only grants
	 * 		2 - return results containing only investigators.
	 */
    function querySearchService($hostname, $port, $query, $type)
    {     //echo "enter";
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
        $message = $type." | ".$query;
        
		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }
        //echo "creation";
        if(!socket_connect($sock , $hostname, $port))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Could not connect: [$errorcode] $errormsg \n");
        }
                
        //Send the message to the server
        if( ! socket_send ( $sock , $message , strlen($message) , 0))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Could not send data: [$errorcode] $errormsg \n");
        }   
         
        //Now receive reply from server into 
	$buffer = "";
	$in = "";

	//while( ( $res = socket_recv($sock, $buffer, 1, MSG_PEEK) ) != FALSE && $buffer != "\0" )
	{
		
		socket_recv($sock, $buffer, 4000, MSG_PEEK);
		$in .= $buffer;
		//echo $buffer . "  ";
	}
	$message = "ack";
        if( !socket_send( $sock, $message, strlen($message), 0 ) )
        {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                die("Could not send acknowledgement: [$errorcode] $errrormsg \n");
        }

	
        //return the search results
        return $in;

        
    }
    
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
		?>
		<title>Collaboratum Home</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../res/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="../res/css/jquery-ui.css" rel="stylesheet">
		<style type="text/css">
			body {
				padding: 40px;
			}
			
			#explorerTabContent {
				height: 100% !important;
			}
		</style>
		<link href="../res/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link rel="stylesheet" href="../res/css/index.css">
		<link rel="stylesheet" href="../res/css/bootstrap-switch.css">
	</head>
	<body>
		<!-- Begin Body Scaffolding -->
	<div class="row-fluid">
			<div class="span12">
				<!-- Begin Nav -->
				<div class="navbar navbar-fixed-top">
					<div class="navbar-inner">
						<a class="brand" href="/Collaboratum/index.php">Collaboratum</a>
						<ul class="nav">
							<li>
								<a href="/Collaboratum/index.php">Home</a>
							</li>
							<li class="divider-vertical"></li>
							<li>
								<a href="#aboutModal" data-toggle="modal">About</a>
							</li>
							<li class="divider-vertical"></li>
							<li>
								<a href="#helpModal" data-toggle="modal">Help</a>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
							    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
							    	Browse Faculty Networks	
							    	<i class="icon-share-alt"></i>
							    	
							    </a>
							    <ul class="dropdown-menu">
							    	<li>
							    		<a href="http://binf1.memphis.edu/Collaboratum/views/subnet.php?startId=1&endId=28&title=Biology">
							    			Biology 
							    		</a>
							    	</li>
							    	<li>
							    		<a href="http://binf1.memphis.edu/Collaboratum/views/subnet.php?startId=38&endId=57&title=Chemistry">
							    			Chemistry
							    		</a>
							    	</li>
							    	<li>
							    		<a href="http://binf1.memphis.edu/Collaboratum/views/subnet.php?startId=29&endId=38&title=Biomedical%20Engineering">
							    			Biomedical Engineering
							    		</a>
							    	</li>
							    </ul>
						    </li>
						    <li class="divider-vertical"></li>
								<li>
									<div id="mySwitch" class="make-switch" data-text-label="" data-animated="true" data-on-label="Concept" data-off-label="Keyword" data-on="success" data-off="success">
										<input type="checkbox" <?php if($exactSearch === "false"){echo 'checked="checked"';}?>>
									</div>
								</li>
								<li class="divider-vertical"></li>
								</li>
						</ul>
					</div>
				</div>
				<!-- End Nav -->
			</div>
			<!-- Main Content -->
			<div class="span12">
				<div class="span9 offset1 text-center">
                        <div id="cytoscapeweb" style="height: 475px;">
                            Cytoscape Web will replace the contents of this div with your graph.
                        </div>
						<p>
							<label for="amount"> </label>
							<input type="text" id="amount" style="border: 0; color: #f6931f; font-weight: bold;" />
						</p>
						<div id="slider-vertical" style="height: 20px;"></div>
            	</div>
				<div class="span2">
					<img src="http://i.imgur.com/z0pptVF.jpg">
				</div>
			</div>
			<!-- End Main Content -->
			<div class="row-fluid">
				<div class="span12">
					<!-- Begin Explorer UI -->
					<div class="nav navbar-fixed-bottom">
						<div class="explorerUI" id="explorer1">
							<div class="explorer-group">
						    	<div id="collapsibleDiv" class="explorer-heading navbar-inner"  data-toggle="collapse" data-parent="#explorer1" href="#collapseOne">
						        	<a class="explorer-toggle offset6">
						        		<i class="icon-chevron-up"></i>
						        	</a>
						    	</div>
						    	<div id="collapseOne" class="explorer-body collapse in">
						      		<div class="accordion-inner" style="background-color: #fff;">
						        		<ul class="nav nav-tabs">
											<li>
												<a href="#search" data-toggle="tab">Search</a>
											</li>
											<li class="active">
												<a href="#results" data-toggle="tab">Results</a>
											</li>
											<li>
												<!-- a href="#statistics" data-toggle="tab">Statistics</a -->
											</li>
										</ul>
										<div id="explorerTabContent" class="tab-content">
											
											<div class="tab-pane fade" id="search">
												<div class="span12 well">
													<!-- Begin search widget -->
													<form class="form-inline" action="/Collaboratum/views/results.php">
														<div class="span11">
															<div class="input-prepend input-append text-left">
																<div class="btn-group">
															    	<button id="searchTypeButton" type="button" class="btn dropdown-toggle" data-toggle="dropdown">
															      		Keyword
															      		<span class="caret"></span>
															    	</button>
															    	<ul class="dropdown-menu">
															   
															      		<li><a tabindex="-1" href="#" onclick="selectSearch(0);" data-toggle="tooltip" data-placement="right" title="Conceptual search is a more abstract search that provides results which are conceptually similar">Conceptual Search</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectSearch(1);" data-toggle="tooltip" data-placement="right" title="Keyword search provides more 'concrete' results than Conceptual search">Keyword Search(Default)</a></li>
															      		
															    	</ul>
															    </div>
															    
																<input name="searchBox" type="text" class="input-xlarge" placeholder="Enter your Query..">
																
																<div class="btn-group">
															    	<button id="filterButton" type="button" class="btn dropdown-toggle" data-toggle="dropdown">
															      		Filter
															      		<span class="caret"></span>
															    	</button>
															    	<ul class="dropdown-menu">
															    		<li><a tabindex="-1" href="#" onclick="selectFilter(0);">Everything(Default)</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(1);">Grants Only</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(2);">Researchers Only</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(3);">Classes Only</a></li>
															      		<li class="divider"></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(4);" data-toggle="modal" data-target="#customFilterModal">Build Custom Filter</a></li>
															    	</ul>
															    </div>
																<input id="searchType" type="hidden" name="exactSearch" value="true"> Keyword Search
								                    			<input id="filterType" type="hidden" name="searchType" value="0"> 
								                    			<input id="isFlashEnabled" name="isFlashEnabled" type="hidden" value="">
															</div>
														    <button type="submit" class="btn btn-primary">Search!</button> 
														</div>
													</form>
													<!-- End Search Widget -->
												</div>
											</div>
											
											<div class="tab-pane fade active in" id="results">
												<div id="searchResults">
							                        <table class="table table-striped table-hover table-condensed">
							                            <thead>
							                                <tr>
							                                    <td>#</td>
							                                    <td>Name</td>
											    				<td>Similiarity</td>
							                                </tr>
							                            </thead>
							                                <tbody>
							                                    <?php
													               // the number of results
								                                   	$numResults = 0;
																	$id = "";
							                                        for($i = 0; $i < count($queryResult); $i++)
							                                        {
							                                           $id = "";
							                                           $entry = explode("`", $queryResult[$i]);
																	  
																	   
																	   $name = $entry[0];
																	   $score = $entry[1]; 
																	   $id = $entry[2];
																	   $type = $entry[3];
																	   $tempName = trim($name);
																	   if($tempName == "")
																	   {
																	   	$name = "No Title Found";
																	   }
								                    				   if($score > 0)
												     				   {
																			$numResults++;
																			if($id <= 57)
																			{
																				echo '<tr>
																				<td>'.($numResults).'</td>
																				<td>
																				<a href="/Collaboratum/views/investigatorInfo.php?id='.$id.'&searchString='.$query.'">
																					'.$name.'
																				</a>
																				</td>
																				<td>
																					'.( $score ).'
																				</td>
																				</tr>';
																			}
																			else if( $id >= 289)
																			{
																				echo '<tr>
																				<td>'.($numResults).'</td>
																				<td>
																				<a href="/Collaboratum/views/grantInfo.php?id='.$id.'&searchString='.$query.'">
																					'.$name.'
																				</a>
																				</td>
																				<td>
																					'.( $score ).'
																				</td>
																				</tr>';
																			}
else {

echo '<tr><td>'.($numResults).'</td><td><a href="/Collaboratum/views/classInfo.php?id='.$id.'">'.$name.'</a></td><td>'.($score).'</td></tr>';
}
												 					   }
							                                        }
							                                    ?>
							                                </tbody>
							                                <tfoot>
							                                    <tr>
							                                        <td>
							                                            <!-- Empty Column -->
							                                        </td>
							                                        <td>
							                                                # results found:
							                                        </td>
							                                        <td>
							                                                <?php echo $numResults; ?>
							                                        </td>
							                                    </tr>
							                                </tfoot>
							                            </table>
						                		</div>
						
						           			</div><!-- End results -->
											<div class="tab-pane fade" id="statistics">
												<script type="text/javascript" src="https://www.google.com/jsapi"></script>
												<script type="text/javascript">
											      google.load("visualization", "1", {packages:["corechart"]});
											      google.setOnLoadCallback(drawChart);
											      function drawChart() {
											        var data = new google.visualization.DataTable();
											        data.addColumn('string', 'Similarity Range'); // Implicit domain label col.
													data.addColumn('number', '# Entities');
													data.addColumn({type: 'string', role: 'tooltip'});
													
													
											        data.addRows([ 
											          <?php
											          	// if we are using LSI. Use 20 columns from -1 to 1
											          	if($exactSearch === "false")
														{
												          	echo "['>=-1',  ".$histogram[0].", '>= -1 Similarity \u000D\u000A Percent: %".($histogram[0]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[0]."'],
														          ['>-.9',  ".$histogram[1].", '> -.9 Similarity \u000D\u000A Percent: %".($histogram[1]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[1]."'],
														          ['>-.8',  ".$histogram[2].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[2]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[2]."'],
														          ['>-.7',  ".$histogram[3].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[3]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[3]."'],
														          ['>-.6',  ".$histogram[4].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[4]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[4]."'],
														          ['>-.5',  ".$histogram[5].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[5]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[5]."'],
														          ['>-.4',  ".$histogram[6].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[6]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[6]."'],
														          ['>-.3',  ".$histogram[7].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[7]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[7]."'],
														          ['>-.2',  ".$histogram[8].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[8]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[8]."'],
														          ['>-.1',  ".$histogram[9].", '<0.1 Similarity \u000D\u000A Percent: %".($histogram[9]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[9]."'],
														          ['<0.1',  ".$histogram[10].", '<0.2 Similarity \u000D\u000A Percent: %".($histogram[10]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[10]."'],
														          ['<0.2',  ".$histogram[11].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[11]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[11]."'],
														          ['<0.3',  ".$histogram[12].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[12]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[12]."'],
														          ['<0.4',  ".$histogram[13].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[13]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[13]."'],
														          ['<0.5',  ".$histogram[14].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[14]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[14]."'],
														          ['<0.6',  ".$histogram[15].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[15]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[15]."'],
														          ['<0.7',  ".$histogram[16].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[16]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[16]."'],
														          ['<0.8',  ".$histogram[17].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[17]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[17]."'],
														          ['<0.9',  ".$histogram[18].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[18]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[18]."'],
														          ['<=1', ".$histogram[19].", '<=1.0 Similarity \u000D\u000A Percent: %".($histogram[19]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[19]."'],";
														}
														// Otherwise using Keyword, use 20 columns from 0 to max.
														else {
															echo "['>=-1',  ".$histogram[0].", '>= -1 Similarity \u000D\u000A Percent: %".($histogram[0]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[0]."'],
														          ['>-.9',  ".$histogram[1].", '> -.9 Similarity \u000D\u000A Percent: %".($histogram[1]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[1]."'],
														          ['>-.8',  ".$histogram[2].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[2]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[2]."'],
														          ['>-.7',  ".$histogram[3].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[3]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[3]."'],
														          ['>-.6',  ".$histogram[4].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[4]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[4]."'],
														          ['>-.5',  ".$histogram[5].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[5]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[5]."'],
														          ['>-.4',  ".$histogram[6].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[6]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[6]."'],
														          ['>-.3',  ".$histogram[7].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[7]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[7]."'],
														          ['>-.2',  ".$histogram[8].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[8]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[8]."'],
														          ['>-.1',  ".$histogram[9].", '<0.1 Similarity \u000D\u000A Percent: %".($histogram[9]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[9]."'],
														          ['<0.1',  ".$histogram[10].", '<0.2 Similarity \u000D\u000A Percent: %".($histogram[10]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[10]."'],
														          ['<0.2',  ".$histogram[11].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[11]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[11]."'],
														          ['<0.3',  ".$histogram[12].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[12]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[12]."'],
														          ['<0.4',  ".$histogram[13].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[13]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[13]."'],
														          ['<0.5',  ".$histogram[14].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[14]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[14]."'],
														          ['<0.6',  ".$histogram[15].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[15]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[15]."'],
														          ['<0.7',  ".$histogram[16].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[16]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[16]."'],
														          ['<0.8',  ".$histogram[17].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[17]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[17]."'],
														          ['<0.9',  ".$histogram[18].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[18]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[18]."'],
														          ['<=1', ".$histogram[19].", '<=1.0 Similarity \u000D\u000A Percent: %".($histogram[19]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[19]."'],";
														}
											          ?>
											        ]);
											
											        var options = {
											          title: 'Distribution of Related Entities',
											          bar:  {groupWidth: "100%"},
											          width: 600,
											          height: 450,
											          backgroundColor: {strokeWidth: 2, stroke: "#000"},
											          hAxis: {title: 'Similarity',  titleTextStyle: {color: 'red'}}
											        };
											
											        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
											        chart.draw(data, options);
											      }
											    </script>
												<div id="chart_div" class="span11" style="width: 600px; height: 450px;"></div>
											</div>
										</div>
						      		</div>
						    	</div>
						  	</div>
						</div>
					</div>
					<!-- End Nav -->
				</div>
			</div>		
		</div>
		<!-- End Body Scaffolding -->
		
		<!-- Begin Modals -->
		
		<!-- Modal that provides information about Collaboratum --> 
		<div id="aboutModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="aboutModalLabel">About Collaboratum</h3>
			</div>
			<ul class="thumbnails">
  				<li class="span4 center vspace-small">
				</li>
			</ul>
			<div class="modal-body">
				<div class="media">
                <p class="lead">
					Collaboratum provides principal investigators with the tools they need to find suitable collaborators and funding relevant to their
					research. 
				</p>
				<p>
					<em>
						We accomplish this through clever application of new and traditional information retrieval methods: A basic keyword search 
						and Conceptual search via LSI. 
						Typical keyword searches are provided to give you "directly associated" results relative to your queries. 
						However, through conceptual search we provide you with the ability to find previously invisible implied associations.
					</em>
				</p>
				
				<h3>Who's Behind This?</h3>
					<div class="media">
						<a class="pull-left" href="#">
							<img class="media-object" data-src="res/images/um.png">
						</a>
						<div class="media-body">
							<h4 class="media-heading">University of Memphis</h4>
						</div>
					</div>
</div>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
		
		<!-- Modal that provides help information for the current page -->
		<div id="helpModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="helpModalLabel">Help</h3>
			</div>
			<div class="modal-body">
				<b>Help Index</b>
				<ul>
					<li><a href="#help1">Search Interface - Overview</a></li>
					<li><a href="#help2">Search Interface - Search Methods</a></li>
					<li><a href="#help3">Search Interface - Filters</a></li>
				</ul>
				<br>
				<br>
				
				<h3><a name="help1">Search Interface(Overview):</a></h3>
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface.png"><br>
				
				<p style="text-indent: 3em;">The search interface is the first way in which you'll interact with CollaboratUM. It has several components that allow you to specify what datasets you'd like to search and how you'd like to search them. The interface is composed of 2 drop down lists, a text input for entering queries, and a submit button. The first drop down list, labeled as "Keyword" by default, allows you to select your search method. The second drop down list, labeled as "Filter" by default, allows you to specify the datasets you'd like to search within. 
				<br>
				<br>
				<h3><a name="help2">Search Interface (Search Methods):</a></h3>
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface2.png"><br>
				<p>The search methods available at this time are a keyword search algorithm, and an LSI search algorithm.  What’s the difference between the two?</p>
				<ul>
					<li>Keyword Search - A keyword search is what most users will be familiar with. It looks for direct associations between a datum and the given keywords. This is done by measuring how frequently a keyword is seen within that datum. </li>
					<li>Conceptual Search - A LSI(Latent Semantic Indexing) search differs from keyword search in that it looks for implied associations in a dataset using the given keyword(s). </li>
				</ul>





				<h3><a name="help3">Search Interface (Filters):</a></h3>
				
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface3.png"><br>
				<p style="text-indent: 3em;">As well as several searching methods there are several "filters" that can be applied. Filters basically allow you to specify what datasets you would like to limit your search to. Our current datasets include a list of Grants automatically pulled from the NIH, Biology classes at the University of Memphis, and a set of 57 Investigators and Faculty at University of Memphis. </p>
				<p style="text-indent: 3em;">When clicking the filter button, you can elect to search all datasets by selecting "Everything." You can also select to search just for Grants, Researchers, or Classes that are relevant to your query. </p>
				<p style="text-indent: 3em;">While selecting a single dataset or all datasets would be preferable in most cases, sometimes it will be desired to search a "mix-and-match" of different datasets. </p>
				<p style="text-indent: 3em;">By clicking "Build Custom Filter" you can select any and all datasets you wish to search within. </p>
				<br>
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface4.png">
				<br>
				<br>
				<p style="text-indent: 3em;">Simply check the checkboxes next to the datasets you wish to include in your results and click "Close." The filter will then be applied and you can begin searching with it. </p>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
		
		<!-- Modal that provides an interface for building a custom filter -->
		<div id="customFilterModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="customFilterModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="verifyFilter();">&times;</button>
				<h3 id="customFilterModalLabel">Build your custom filter!</h3>
			</div>
			<div class="modal-body">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<td>
								Filters:
							</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="checkbox" id="customFilterGrant" value="grants" onchange="customFilter(this, 1);"> Grants
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" id="customFilterCollaborator" value="collaborators" onchange="customFilter(this, 2);"> Reseachers
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" id="customFilterClasses" value="classes" onchange="customFilter(this, 3);"> Classes
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true" onclick="verifyFilter();">Close</button>
			</div>
		</div>
		<!-- End Modals -->
		
		<!-- Begin load JS -->
		<script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/json2.min.js"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/AC_OETags.min.js"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/cytoscapeweb.min.js"></script>
		<script src="http://code.jquery.com/jquery.js"></script>
		<script src="../res/bootstrap/js/bootstrap.min.js"></script>   
		<script src="../res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
		<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
		<script src="../res/js/bootstrap-switch.min.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(function(){ $("label").inFieldLabels(); });
			$(function(){
				var availableTags = [
					"cancer",
					"breast",
					"reelin"
				];
				$( "#searchBox" ).autocomplete({
					source: availableTags
				});
			});
		</script>
		<script type="text/javascript">
	        /*
	        * This script determines if flash is installed
	        * if not it passes a hidden parameter instructing
	        * the search page to generate a png image of the search's graph
	        * and display that instead of the cytoscape web swf graph
	        */
	
	        if(FlashDetect.installed)
	        {
	                // If flash is installed
	                $("#isFlashEnabled").val("true");
	        }
	        else
	        {
	                // If flash isn't installed
	                $("#isFlashEnabled").val("false");
	        }
        </script>
        <!-- start of Cytoscape Graph Data -->
        <script type="text/javascript"> 
            window.onload = function() {
         
                // id of Cytoscape Web container div
                var div_id = "cytoscapeweb";
				
                var network_json;
				$.ajax({
					type: 'POST',
					url: "../res/scripts/getGraph.php",
					data: <?php echo "{threshHold: \"0.0\", queryTerm: \"".$query."\", queryResult: \"".preg_replace('/\s+/', ' ', trim(implode("~", $queryResult)))."\"}"; ?>, 
					dataType: "json",
					async: false,
					success: function(data) {
						console.log(data);
						network_json = data;
						//try{ 
				       // alert($data);
				         
					},
					error: function() {
						alert("Sorry, There was an error loading the graph"); // + "<?php echo preg_replace('/\s+/', ' ', trim(implode("~", $queryResult))); ?>");
					}
				});

  var nodeColorMapper = {
                          "attrName": "type",
                          "entries": [ { "attrValue": "investigator", "value": "#0000ff" }, { "attrValue": "queryTerm", "value": "#ff0000"}, {"attrValue": "grant", "value":"#00ff00"} ]
                  };

                  // NOTE the "compound" prefix in some visual properties
                  var visual_style = {
                      "nodes": {
                          "shape": "RECTANGLE",
                          "label": { "passthroughMapper": { "attrName": "label" } },
                          //"label": {"  "},
                          "borderColor": "#83959d",
                          "color": { "discreteMapper": nodeColorMapper  },
                      },
											"edges": {
												"tooltipText": "Common Terms: "
											}
                  };

                // initialization options
                var options = {
                    swfPath: "/Collaboratum/res/cytoscape/swf/CytoscapeWeb",
                    flashInstallerPath: "/Collaboratum/res/cytoscape/swf/playerProductInstall"
                };

                var vis = new org.cytoscapeweb.Visualization(div_id, options);
                // 2. Add a context menu item any time after the network is ready:
  							vis.ready(function () {
 							 });

		vis.ready(function() {
                    // set the style programmatically
			
                });

                var draw_options = {
                    // your data goes here
                    network: network_json,
                    // set the style at initialisation
                    visualStyle: visual_style,
                    // hide pan zoom
                    panZoomControlVisible: true,
										nodeTooltipsEnabled: true,
										edgeTooltipsEnabled: true
                };

                vis.draw(draw_options);
            };
        </script> <!-- End of Cytoscape graph data -->
        <script type="text/javascript"> <!-- start of scale bar script -->
		$(function() {
			$( "#slider-vertical" ).slider({
				orienatation: "vertical",
				range: "min",
				min: 0,
				max: <?php echo $largestSimilarity; ?>, 
				value: 0, 
				step: 0.05,
				
				slide: function( event, ui ) {
					$( "#amount" ).val( ui.value );
				},
				change: function( event, ui ) {
							$.ajax({
								type: 'POST',
								url: "../res/scripts/getGraph.php",
								data: <?php echo "{ threshHold: ui.value, queryTerm: \"".$query."\", queryResult: \"".preg_replace('/\s+/', ' ', trim(implode("~", $queryResult)))."\"}"; ?>,
								dataType: "json",
								async: false,
								success: function(data) {
									network_json = data;
									try{ 
										var div_id = "cytoscapeweb";
										var options = {
											swfPath: "/Collaboratum/res/cytoscape/swf/CytoscapeWeb",
											flashInstallerPath: "/Collaboratum/res/cytoscape/swf/playerProductInstall"
										};



 var nodeColorMapper = {
                         "attrName": "type",
                         "entries": [ { "attrValue": "investigator", "value": "#0000ff" }, { "attrValue": "queryTerm", "value": "#ff0000"}, {"attrValue": "grant", "value":"#00ff00"} ]
                 };

                 // NOTE the "compound" prefix in some visual properties
                 var visual_style = {
                     "nodes": {
                         "shape": "RECTANGLE",
                         "label": { "passthroughMapper": { "attrName": "label" } },
                        //"label": {"  "},
                         "borderColor": "#83959d",
                         "color": { "discreteMapper": nodeColorMapper  },
                     }
                     
                     	
                 };


										var vis = new org.cytoscapeweb.Visualization(div_id, options);
// 2. Add a context menu item any time after the network is ready:
  vis.ready(function () {
			vis.addContextMenuItem("View More Information", "nodes", 
				function(evt) {
					
					var node = evt.target;
					var id = node.data.id;
					if(id <= 57) {
						window.location.href = "http://binf1.memphis.edu/Collaboratum/views/investigatorInfo.php?id="+id;
					}
					else if(id > 285) {
						window.location.href = "http://binf1.memphis.edu/Collaboratum/views/grantInfo.php?id="+id;
					}
					else {
						window.location.href = "http://binf1.memphis.edu/Collaboratum/views/classInfo.php?id="+id;
					}

					});
 });


										var draw_options = {
											// your data goes here
											network: network_json,
											// set the style at initialisation
											visualStyle: visual_style,
											// hide pan zoom
											panZoomControlVisible: true,
											nodeTooltipsEnabled: true,
											edgeTooltipsEnabled: true 
										};

										vis.draw(draw_options);
										
									}
									catch(err)
									{
										alert(err);
									}
								},
								error: function() {
									alert("sorry, there's no qualified data "); // + "<?php echo preg_replace('/\s+/', ' ', trim(implode("~", $queryResult))); ?>");
								}
							});
				}
			});
			$( "#amount" ).val( $( "#slider-vertical" ).slider( "value" ) );
		});
	</script> <!-- End of scale bar script -->		
	<script type="text/javascript">
        	/*
        	 * This script updates the search type to be used
        	 */
        	function selectSearch( searchType )
        	{
        		// Do LSI Search if search type is 0
        		if(searchType == 0)
        		{
        			$("#searchType").val('false');
        			$("#searchTypeButton").text("Conceptual");
        		}
        		//If search type is 1 do Keyword search
        		else if(searchType == 1)
        		{
        			$("#searchType").val('true');
        			$("#searchTypeButton").text("Keyword");
        		}
        	}
        
        	/*
        	 * This script updates the filter to be applied to the search.
        	 * It does this by modifying a hidden input on the search <form>
        	 * 
        	 * filterType = 0 : Everything will be returned in search results
        	 * filterType = 1 : Only grants will be returned in search results
        	 * filterType = 2 : Only collaborators will be returned in search results
        	 * filterType = 3 : Only classes will be returned in search results
        	 * filterType = 4 : A custom search filter has been applied to search results
        	 */
        	function selectFilter( filterType )
        	{
        		if(filterType == 0)
        		{
        			$("#filterButton").text("Everything");
        			$("#filterType").val('0');
				}
        		if(filterType == 1)
        		{
        			$("#filterButton").text("Grants");
        			$("#filterType").val('1');
        		}
        		if(filterType == 2)
        		{
        			$("#filterButton").text("Researchers");
        			$("#filterType").val('2');
        		}
        		if(filterType == 3)
        		{
        			$("#filterButton").text("Classes");
        			$("#filterType").val('3');
        		}
        		if(filterType == 4)
        		{
        			$("#filterButton").text("Custom");
        			$('#customFilterModal').modal('show');
        		}
        	}
        </script>
        <script type="text/javascript">
        	/*
        	 * This script updates the search type to be used
        	 */
        	function selectSearch( searchType )
        	{
        		// Do LSI Search if search type is 0
        		if(searchType == 0)
        		{
        			$("#searchType").val('false');
        			$("#searchTypeButton").text("Conceptual");
        		}
        		//If search type is 1 do Keyword search
        		else if(searchType == 1)
        		{
        			$("#searchType").val('true');
        			$("#searchTypeButton").text("Keyword");
        		}
        	}
        
        	/*
        	 * This script updates the filter to be applied to the search.
        	 * It does this by modifying a hidden input on the search <form>
        	 * 
        	 * filterType = 0 : Everything will be returned in search results
        	 * filterType = 1 : Only grants will be returned in search results
        	 * filterType = 2 : Only collaborators will be returned in search results
        	 * filterType = 3 : Only classes will be returned in search results
        	 * filterType = 4 : A custom search filter has been applied to search results
        	 */
        	function selectFilter( filterType )
        	{
        		if(filterType == 0)
        		{
        			
        			$("#filterButton").text("Everything");
        			$("#filterType").val('0');
        		}
        		if(filterType == 1)
        		{
        			
        			$("#filterButton").text("Grants");
        			$("#filterType").val('1');
        		}
        		if(filterType == 2)
        		{
        			
        			$("#filterButton").text("Researchers");
        			$("#filterType").val('2');
        		}
        		if(filterType == 3)
        		{
        			
        			$("#filterButton").text("Classes");
        			$("#filterType").val('3');
        		}
        		if(filterType == 4)
        		{
        			
        			$("#filterButton").text("Custom");
					// Wipe the current filter and set all checkboxes to unchecked.
					$("#filterType").val("");
					// clear checboxes
					clearCheckboxes();
					// Show the modal to allow the client to build a new custom filter.				 
        			$("#customFilterModal").modal({
					show: true,
					keyboard: true
				});
        		}
			verifyFilter();
        	}

		function clearCheckboxes()
		{
			$("#customFilterGrant").prop("checked", false);
			$("#customFilterCollaborator").prop("checked", false);
			$("#customFilterClasses").prop("checked", false);
		}

		// create a customFilter
		function customFilter( obj, additionalFilter )
		{

			// Initialize the current filter.
			var curFilter = "";
			// append each checkbox if checked
			if( $("#customFilterGrant").is(":checked") )
			{
				curFilter = curFilter + '1';
			}
			if( $("#customFilterCollaborator").is(":checked") )
			{
				if(curFilter === "")
				{
					curFilter = curFilter + '2';
				}
				else
				{
					curFilter = curFilter + ',' + '2';
				}
			}
			if( $("#customFilterClasses").is(":checked") )
			{
				if(curFilter === "")
				{
					curFilter = curFilter + '3';
				}
				else
				{
					curFilter = curFilter + ',' + '3';
				}
			}
			$("#filterType").val(curFilter);
		}

		// verify that the current filter is valid, and if not attempt to fix it.
		function verifyFilter()
		{
			// TODO do validation checking using a finite state machine.
			var curFilter = $("#filterType").val();
			
			// the filter is not allowed to be empty, default to 0, update UI, and issue warning.
			if( curFilter === "" )
			{
				$("#filterType").val("0");	
				$("#filterButton").val("Everything");			
				alert("An invalid search filter has been detected. This search has been reset to search Everything.");	
			}
			// do a basic check to see if there are any invalid characters present in the filter string
			for( var i = 0; i < curFilter.length(); i++)
			{
				// if an error is detected default to everything and break out of loop.
				if( curFilter.charat(i) != '0' || curFilter.charAt(i) != '1' || curFilter.charAt(i) != '2' || curFilter.charAt(i) != '3' || curFilter.charAt!= ',') 
				{
					$("#filterType").val("0");
					$("#filterButton").val("Everything");
					alert("An invalid search filter has been detected. This search has been reset to search Everything.");
				}
			}	
		}
        </script>
	<script type="text/javascript">
		/*
			This script handles the lsi/keyword switch toggle at the top
			of the page
		*/
		$('#mySwitch').on('switch-change', function (e, data) {
    	var value = data.value;
			if(value == true) {
				//Concept Search
				window.location.href = "<?php echo "http://binf1.memphis.edu/Collaboratum/views/results.php?searchBox=".$_GET['searchBox']."&exactSearch=false&searchType=0"//.$searchType; ?>";
			}
			else {
				//Keyword Search
				window.location.href = "<?php echo "http://binf1.memphis.edu/Collaboratum/views/results.php?searchBox=".$_GET['searchBox']."&exactSearch=true&searchType=1"//.$searchType; ?>";
			}
		});

		$('#nodeLabeling').on('switch-change', function(e, data) {
			var value = data.value;
			if(value == true) {
				//enable labeling
			
			}
			else {
				//disable labeling
				alert("2" + $('#amount').val());
			}
		});


	</script>
	</body>
</html>

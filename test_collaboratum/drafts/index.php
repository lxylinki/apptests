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
?> 

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Collaboratum Home</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="res/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="res/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link rel="stylesheet" href="res/css/index.css">
	</head>
	<body>
		
		<!-- Begin Body Scaffolding -->
		<div class="row-fluid">
			<div class="span10">
				<!-- Begin Nav -->
				<div class="navbar navbar-static-top">
					<div class="navbar-inner">
						<a class="brand" href="#">Collaboratum</a>
						<ul class="nav">
							<li class="active">
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
							    		<a href="<?php echo $baseURL; ?>/views/subnet.php?startId=1&endId=28&title=Biology">
							    			Biology 
							    		</a>
							    	</li>
							    	<li>
							    		<a href="<?php echo $baseURL; ?>/views/subnet.php?startId=38&endId=57&title=Chemistry">
							    			Chemistry
							    		</a>
							    	</li>
							    	<li>
							    		<a href="<?php echo $baseURL; ?>/views/subnet.php?startId=29&endId=38&title=Biomedical%20Engineering">
							    			Biomedical Engineering
							    		</a>
							    	</li>
							    </ul>
						    </li>
						    <li class="divider-vertical"></li>
						</ul>
					</div>
				</div>
				<!-- End Nav -->
			</div>
  			<div class="span12 alpha">
  				<div class="span8 center vspace-normal">
  					<div class="span10 center text-center">
  						<img src="/Collaboratum/res/images/collaboratum_logo_dark.png" alt="CollaboratUM">
  					</div>
  					<div class="span10 center text-center well vspace-small alpha">
						<form class="form-inline" action="views/results.php" method="POST">
							<div class="span11">
								<div class="input-prepend input-append text-left">
									<div class="btn-group">
								    	<button id="searchTypeButton" type="button" class="btn dropdown-toggle" data-toggle="dropdown">
								      		Keyword
								      		<span class="caret"></span>
								    	</button>
								    	<ul class="dropdown-menu">
								   
								      		<li><a tabindex="-1" href="#" onclick="selectSearch(0);" data-toggle="tooltip" data-placement="right" title="Conceptual is a more abstract search that provides results which are conceptually similar">Conceptual Search</a></li>
								      		<li><a tabindex="-1" href="#" onclick="selectSearch(1);" data-toggle="tooltip" data-placement="right" title="Keyword search provides more 'concrete' results than Conceptual">Keyword Search(Default)</a></li>
								      		
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
					</div>
  				</div>
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

			<div class="modal-body">
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
				<p>The search methods available at this time are a keyword search algorithm, and a conceptual  search algorithm.  What’s the difference between the two?</p>
				<ul>
					<li>Keyword Search - A keyword search is what most users will be familiar with. It looks for direct associations between a datum and the given keywords. This is done by measuring how frequently a keyword is seen within that datum. </li>
					<li>Conceptual Search - A conceptual search differs from keyword search in that it looks for implied associations in a dataset using the given keyword(s). </li>
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
								<input type="checkbox" id="customFilterCollaborator" value="collaborators" onchange="customFilter(this, 2);"> Researchers 
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
		<script src="http://code.jquery.com/jquery.js"></script>
		<script src="res/bootstrap/js/bootstrap.min.js"></script>   
		<script src="res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
		<script src="res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
		<script src="res/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
		<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
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
        			
        			$("#filterButton").text("Researcher");
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
	</body>
</html>

<?php

##################################################################
# Configuration Begin
#     The README has more instructions on how to use this script
#     Flurry API: http://support.flurry.com/index.php?title=API
##################################################################

$apiAccessCode  = ""; # Your Flurry API access code. See API url above.
$apiKey         = ""; # Flurry API Key for the app you want to track. See API url above.
$daysToShow     = 7; # Number of days you want to view
$graphTitle     = ""; # The title for the graph
$graphType      = "line"; # Set to 'line' or 'bar'
$hideTotals     = false; # Set to true if you want to hide totals on the y-axis, false otherwise
$displayTotal   = false; # Set to true if you want the totals for each metric listed at the end of the graph.
$dateFormat     = "M j"; # Date format for x-axis. Default format is "Apr 20". See http://php.net/manual/en/function.date.php

# This array comtains a hash for each Flurry metric you want to track.
# See http://support.flurry.com/index.php?title=API/Code for other metrics.
$metrics = array(
    array(
        'metric'    => 'ActiveUsers',
        'title'     => 'Daily Active Users',
        'color'     => 'green'
    ),
    array(
        'metric'    => 'NewUsers',
        'title'     => 'Daily New Users',
        'color'     => 'blue'
    )
);

##################################################################
# Configuration Ends
##################################################################

$endDate = date("Y-m-d", time() - 60 * 60 * 24);
$startDate = date("Y-m-d", time() - 60 * 60 * 24 * $daysToShow);

$dataSequences = array();
foreach ($metrics as $metric) {
    $metricMethod = $metric['metric'];
    $url = "http://api.flurry.com/appMetrics/$metricMethod?apiAccessCode=$apiAccessCode&apiKey=$apiKey&startDate=$startDate&endDate=$endDate";
    
    $options = array(
        'http' => array(
            'header' => array(
              'Accept: application/json',
            ),
        ),
    );
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $result = json_decode($result, true);
    
    $days = $result['day'];
        
    $data = array();
    foreach ($days as $day) {
        $dateString = date($dateFormat, strtotime($day['@date']));
        $value = $day['@value'];
        $data[] = array('title' => $dateString, 'value' => $value);
    }
    
    $dataSequences[] = array('title' => $metric['title'], 'color' => $metric['color'], 'datapoints' => $data);
    
    // Sleep for a sec, since the Flurry API only allows one call per second
    sleep(1);
}

$graph = array(
    'graph' => array(
        'title' => $graphTitle,
        'type' => $graphType,
        'total' => $displayTotal,
        'yAxis' => array(
            'hide' => $hideTotals
        ),
        'datasequences' => $dataSequences,
    ),
);

echo json_encode($graph);

?>
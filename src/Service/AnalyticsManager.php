<?php

namespace App\Service;

class AnalyticsManager{

    private $manager;

    public function __construct(){
        
    }

   

    /**
     * Initializes an Analytics Reporting API V4 service object.
     *
     * @return An authorized Analytics Reporting API V4 service object.
     */
    public function initializeAnalytics()
    {

    // Use the developers console and download your service account
    // credentials in JSON format. Place them in this directory or
    // change the key file location if necessary.
    $KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';

    // Create and configure a new client object.
    $client = new \Google_Client();
    $client->setApplicationName("Hello Analytics Reporting");
    $client->setAuthConfig($KEY_FILE_LOCATION);
    $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    $analytics = new \Google_Service_AnalyticsReporting($client);

    return $analytics;
    }


    /**
     * Queries the Analytics Reporting API V4.
     *
     * @param service An authorized Analytics Reporting API V4 service object.
     * @return The Analytics Reporting API V4 response.
     */
    public function getReport($analytics,$startDate,$endDate,$viewId) {

    // Replace with your view ID, for example XXXX.
    $VIEW_ID = $viewId;

    // Create the DateRange object.
    $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate($startDate);
    $dateRange->setEndDate($endDate);

    // Create the Metrics object.
    $sessions = new \Google_Service_AnalyticsReporting_Metric();
    $sessions->setExpression("ga:users");
    $sessions->setAlias("users");

    // Create the ReportRequest object.
    $request = new \Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    $request->setDateRanges($dateRange);
    $request->setMetrics(array($sessions));

    $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $request) );
    return $analytics->reports->batchGet( $body );
    }


    /**
     * Parses and prints the Analytics Reporting API V4 response.
     *
     * @param An Analytics Reporting API V4 response.
     */
    public function printResults($reports) {
    for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
        $report = $reports[ $reportIndex ];
        $header = $report->getColumnHeader();
        $dimensionHeaders = $header->getDimensions();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $report->getData()->getRows();

        for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
        $row = $rows[ $rowIndex ];
        $dimensions = $row->getDimensions();
        $metrics = $row->getMetrics();
        for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
            return $dimensions[$i];
        }

        for ($j = 0; $j < count($metrics); $j++) {
            $values = $metrics[$j]->getValues();
            for ($k = 0; $k < count($values); $k++) {
            $entry = $metricHeaders[$k];
            return $values[$k];
            }
        }
        }
    }
    return 0;
    }

}

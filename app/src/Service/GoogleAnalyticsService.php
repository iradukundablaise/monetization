<?php

namespace App\Service;

use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_DimensionFilter;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_OrderBy;
use Google\Service\AnalyticsReporting\GetReportsResponse;

class GoogleAnalyticsService
{
    const VIEW_ID = "219011628";
    const APPLICATION_NAME = "Yegob Monetization";

    private Google_Client $client;

    private Google_Service_AnalyticsReporting_DateRange $dateRange;

    private array $dimensions = [];
    private array $metrics = [];
    private array $filters = [];
    private array $orderBys = [];

    public function __construct()
    {
        // Create a new Google_Client object
        $this->client = new Google_Client();
        // Set the OAuth 2.0 credentials for your Google Analytics account
        $this->client->setAuthConfig(__DIR__ . '/../../credentials.json');
        // Enable the Google Analytics API for the Google_Client object
        $this->client->addScope('https://www.googleapis.com/auth/analytics.readonly');
    }

    public function addMetric($expression , $alias): void
    {
        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression($expression);
        $metric->setAlias($alias);
        $this->metrics[] = $metric;
    }

    private function addDimension($dimensionName): void
    {
        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName($dimensionName);
        $this->dimensions[] = $dimension;
    }

    public function addDimensionsFilter($dimension, $operator,  $expressions = array()): void
    {
        $filter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $filter->setDimensionName($dimension);
        $filter->setExpressions($expressions);
        $filter->setOperator($operator);
        $this->filters[] = $filter;
    }

    public function addOrdering($fieldName, $orderType, $sortOrder = "DESCENDING"): void{
        $ordering = new Google_Service_AnalyticsReporting_OrderBy();
        $ordering->setFieldName($fieldName);
        $ordering->setOrderType($orderType);
        $ordering->setSortOrder($sortOrder);

        $this->orderBys[] = $ordering;
    }

    public function setDateRange($start, $end): void
    {
        $date = new \Google_Service_AnalyticsReporting_DateRange();
        $date->setStartDate($start);
        $date->setEndDate($end);
        $this->daterange = $date;
    }

    public function sendRequest(): GetReportsResponse
    {
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId(self::VIEW_ID);
        $request->setDateRanges($this->daterange);

        if($this->metrics != []){
            $request->setMetrics($this->metrics);
        }
        if($this->dimensions != []){
            $request->setDimensions($this->dimensions);
        }

        if($this->orderBys != []){
            $request->setOrderBys($this->orderBys);
        }

        if($this->filters != []){
            $filterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
            $filterClause->setFilters($this->filters);
            $request->setDimensionFilterClauses($filterClause);
        }

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );


        $reporting = new Google_Service_AnalyticsReporting($this->client);

        return $reporting->reports->batchGet($body);
    }

    public function getReportsByAuthor(string $startDate = '2022-12-19', string $endDate = 'today'): array
    {
        // metrics (pageviews and unique pageviews //
        $this->addMetric('ga:pageviews', 'pageviews');
        $this->addMetric('ga:uniquepageviews', 'uniquePageviews');

        // dimensions
        $this->addDimension('ga:dimension1');
        $this->addDimension('ga:date');
        // $this->addDimension('ga:utm_source');

        // dimensions filters
        // $this->addDimensionsFilter('utm_medium', 'EXACT', 'affiliate');

        $this->setDateRange($startDate, $endDate);

        return $this->parseReportsResponse($this->sendRequest());
    }

    public function parseReportsResponse(GetReportsResponse $response): array
    {
        $reports = $response->getReports();
        [$authorsReports] = $reports;
        $headers = array_map(fn($header) => $header->getName(),
                        $authorsReports
                            ->getColumnHeader()
                            ->getMetricHeader()
                            ->getMetricHeaderEntries());

        $rows = $authorsReports->getData()->getRows();
        $metricsByDimension = [];

        foreach($rows as $row){
            [$pageviews, $uniquePageviews] = $row->getMetrics()[0]->getValues();
            $metricsByDimension[$row->dimensions[0]][$row->dimensions[1]] = [
                'pageviews' => (int) $pageviews,
                'uniquePageviews' => (int) $uniquePageviews
            ];
        }
        return $metricsByDimension;
    }
}

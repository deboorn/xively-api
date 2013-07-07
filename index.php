<?php


	/**
	 * Xively API
	 * @author Daniel Boorn - daniel.boorn@gmail.com
	 * @copyright Daniel Boorn
	 * @license Creative Commons Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0)
	 * @namespace Xively
	 * @note Class uses a JSON api_path.js file that defines the API resources (endpoints and paths).
	 */

	/**
	 * The Xiviley API class is chainable. Use chains to trigger API resources.
	 * All API Resources are Available for chainging.
	 */

	require_once('vendors/com.rapiddigitalllc/xively/api.php');
	
	$xi = \Xively\Api::forge();
	//$xi = \Xively\Api::forge('your api key');
	
	# view api resource triggers
	//var_dump($xi->paths);

	# example - json get feeds
	/*
	$r = $xi->feeds()->list()->get();
	var_dump($r);
	die();
	*/
	
	# example - forge factory is chainable for one time use
	/*
	 $r = \Xively\Api::forge()->feeds()->list()->get();
	 */	
	
	# example - xml get feeds
	/*
	$xml = $xi->xml()->feeds()->list()->get();
	var_dump($xml);
	*/
	
	# example - csv get feeds
	/*
	$csv = $xi->csv()->feeds()->list()->get();
	var_dump($csv);
	*/
	
	# example - with custom exception
	/*
	try{
		$r = $xi->feeds()->list()->get();
	}catch(\Xively\Exception $e){
		die($e->getMessage());
	}
	*/
	
	# example - get feeds by criteria
	/*
	$r = $xi->feeds()->read(array(
		'per_page' => 10,
		'page' => 5,
		'tag' => 'temperature',
	))->get();
	var_dump($r);
	*/
	
	# example - same as above with iteration
	/*
	$r = $xi->feeds()->read(array('per_page' => 10,'page' => 5,'tag' => 'temperature',))->get();
	echo "<pre>Total Results: {$r->totalResults}\n\n";
	foreach($r->results as $row){
		print_r($row);
	}
	*/

	# example - feed used in examples below
	
	/*
	$r = $xi->feeds()->read(array('per_page' => 1))->get();
	$feed = current($r->results);
	*/
	
	# example - grab feed data by range
	/*
	$r = $xi->feeds($feed->id)->range(array(
		'start' => date('c',strtotime('-10 days')),
		'end' => date('c',strtotime('-1 hour')),
		'time_unit' => 'hours',
	))->get();
	var_dump($r);
	*/

	# example - grab feed's 1st data stream
	/*
	$datastream = current($feed->datastreams);
	*/
	
	# example - pull data from stream by range
	/*
	$r = $xi->feeds($feed->id)->datastreams($datastream->id)->range(array(
		'start' => date('c',strtotime('-10 days')),
		'end' => date('c',strtotime('-1 hour')),
		'time_unit' => 'hours',
	))->get();
	var_dump($r);
	*/
	
	# example - push data to stream
	/*
	$r = $xi->feeds()->read(array(
		'user' => 'deboorn',
	))->get();
	
	//feeds(id)->datastreams(id)->update(body)
	$feed = $r->results[1];
	$datastream = $feed->datastreams[0];
	$r = $xi->feeds($feed->id)->datastreams($datastream->id)->update(array(
		'version' => '1.0.0',
		'datastreams' => array(
			array(
				'id' => $datastream->id,
				'datapoints' => array(
					array('at' => date('c'), 'value' => rand(1,10)),
				),
			),
		),
	))->get();
	*/
	
	
	# ... create your own chain ... see paths in json file for complete triggers
	
	
	
	
	
	
	
	
	



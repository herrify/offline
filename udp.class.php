<?php

class StatsD {
	
	private $hostname;
	private $port_number;
	
	public function __construct($hostname, $port_number) {
		$this->hostname = $hostname;
		$this->port_number = $port_number;
	}
	
	private function __clone() { }
	//private function __destruct() { }
	
	public function getHostname() {
		return $this->hostname;
	}
	
	public function getPortNumber() {
		return $this->port_number;
	}
	
	/**
	 * Log timing information
	 *
	 * @param string $stats The metric to in log timing info for.
	 * @param float $time The ellapsed time (ms) to log
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 **/
	public function timing($stat, $time, $sample_rate = 1) {
		$this->send(array($stat => "$time|ms"), $sample_rate);
	}

	/**
	 * Increments one or more stats counters
	 *
	 * @param string|array $stats The metric(s) to increment.
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 * @return boolean
	 **/
	public function increment($stats, $sample_rate = 1) {
		$this->update_stats($stats, 1, $sample_rate);
	}

	/**
	 * Decrements one or more stats counters.
	 *
	 * @param string|array $stats The metric(s) to decrement.
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 * @return boolean
	 **/
	public function decrement($stats, $sample_rate = 1) {
		$this->update_stats($stats, -1, $sample_rate);
	}

	/**
	 * Updates one or more stats counters by arbitrary amounts.
	 *
	 * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
	 * @param int|1 $delta The amount to increment/decrement each metric by.
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 * @return boolean
	 */
	public function update_stats($stats, $delta = 1, $sample_rate = 1) {
		if (!is_array($stats)) {
			$stats = array($stats);
		}
		$data = array();
		foreach ($stats as $stat) {
			$data[$stat] = "$delta|c";
		}
		$this->send($data, $sample_rate);
	}

	/**
	 * Send the metrics over UDP
	 */
	public function send($data, $sample_rate = 1) {
		$sampled_data = array();
		if ($sample_rate < 1) {
			foreach ($data as $stat => $value) {
				if ((mt_rand() / mt_getrandmax()) <= $sample_rate) {
					$sampled_data[$stat] = "$value|@$sample_rate";
				}
			}
		} else {
			$sampled_data = $data;
		}
		if (empty($sampled_data)) {
			return;
		}
		try {
			$host = $this->hostname;
			$port = $this->port_number;
			$fp = fsockopen("udp://$host", $port, $errno, $errstr);
			if (!$fp) {
				return;
			}
			foreach ($sampled_data as $stat => $value) {
				var_dump($stat);
				echo '<br>';
				var_dump($value);
				echo "<hr/>";
				fwrite($fp, "$stat:$value");
			}
			$content = fread($fp,"100");
			var_dump($content);
			echo "<hr color='red'/>";
			fclose($fp);
		} catch (Exception $ex) {
			error_log("StatsD Exception: " . $ex->getMessage());
		}
	}
}

$hostname = "127.0.0.1";
$port_number = 11109;

$statsd = new StatsD($hostname, $port_number);

for($i = 0; $i < 180; $i++) {
	//<game>.<topic>.<counter_name>
	$statsd->increment('high_level_topic.test.test_counter');
	if ($i % 100 == 0) {
		printf("#");
		sleep(2);
	} else if ($i % 101 == 0) {
		printf("*");
		sleep(2);
	} else {
		printf(".");
		sleep(0.8);
	}
}

printf("\n");

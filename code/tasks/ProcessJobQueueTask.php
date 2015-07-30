<?php

/**
 * Task used to process the job queue
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/bsd-license/
 */
class ProcessJobQueueTask extends BuildTask {

	/**
	 * @var QueuedJobService
	 */
	public $queuedJobService;
	/**
	 * @return string
	 */
	public function getDescription() {
		return _t(
			'ProcessJobQueueTask.Description',
			'Used via a cron job to execute queued jobs that need to be run.'
		);
	}

	/**
	 * @param SS_HttpRequest $request
	 */
	public function run($request) {
		if($request->getVar('list')) {
			// List helper
			$this->listJobs();
			return;
		}

		// Check if there is a job to run
		if(($job = $request->getVar('job')) && strpos($job, '-')) {
			// Run from a isngle job
			$parts = explode('-', $job);
			$id = $parts[1];
			$this
				->getTaskRunner()
				->runJob($id);
			return;
		}

		// Run the queue
		$queue = $this->getQueue($request);
		$this->queuedJobService->runQueue($queue);
	}

	/**
	 * Resolves the queue name to one of a few aliases.
	 *
	 * @todo Solve the "Queued"/"queued" mystery!
	 *
	 * @param SS_HTTPRequest $request
	 *
	 * @return string
	 */
	protected function getQueue($request) {
		$queue = $request->getVar('queue');

		if(!$queue) {
			$queue = 'Queued';
		}

		switch(strtolower($queue)) {
			case 'immediate': {
				$queue = QueuedJob::IMMEDIATE;
				break;
			}
			case 'queued': {
				$queue = QueuedJob::QUEUED;
				break;
			}
			case 'large': {
				$queue = QueuedJob::LARGE;
				break;
			}
		}

		return $queue;
	}

}

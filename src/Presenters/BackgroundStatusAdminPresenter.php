<?php

namespace Crm\AdminModule\Presenters;

use Crm\ApplicationModule\Components\Graphs\GoogleLineGraphGroup\GoogleLineGraphGroupControlFactoryInterface;
use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\ApplicationModule\Hermes\RedisTasksQueue;
use Crm\ApplicationModule\Models\Graphs\Criteria;
use Crm\ApplicationModule\Models\Graphs\GraphDataItem;
use Crm\ApplicationModule\Repositories\HermesTasksRepository;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Tomaj\Hermes\Emitter;

class BackgroundStatusAdminPresenter extends AdminPresenter
{
    private $hermesTasksRepository;

    private $redisTasksQueue;

    private $hermesEmitter;

    public function __construct(
        HermesTasksRepository $hermesTasksRepository,
        RedisTasksQueue $redisTasksQueue,
        Emitter $hermesEmitter
    ) {
        parent::__construct();
        $this->hermesTasksRepository = $hermesTasksRepository;
        $this->redisTasksQueue = $redisTasksQueue;
        $this->hermesEmitter = $hermesEmitter;
    }

    /**
     * @admin-access-level read
     */
    public function renderDefault()
    {
        $errorDayRanges = [1,7,31];

        $errorCounts = [];
        foreach ($errorDayRanges as $dayRange) {
            $errorCounts[$dayRange] = $this->hermesTasksRepository
                ->getStateCounts(
                    DateTime::from("-{$dayRange} days"),
                    [
                        HermesTasksRepository::STATE_ERROR,
                    ]
                )
                ->fetchPairs('type', 'count');
        }

        $enqueuedTasks = $this->redisTasksQueue->getAllTask(100);

        $this->template->errorDayRanges = $errorDayRanges;
        $this->template->errorCounts = $errorCounts;
        $this->template->enqueuedTasks = $enqueuedTasks;

        $this->template->errorTasks = $this->hermesTasksRepository->getErrorTasks()->limit(100);
    }

    protected function createComponentBackgroundJobsGraph(GoogleLineGraphGroupControlFactoryInterface $factory)
    {
        $errorEvents = new GraphDataItem();
        $errorEvents->setCriteria((new Criteria())
            ->setTableName('hermes_tasks')
            ->setTimeField('created_at')
            ->setWhere('AND state="error"')
            ->setValueField('COUNT(*)')
            ->setStart('-1 month'))
            ->setName($this->translator->translate('admin.admin.background_jobs.default.states.error'));

        $control = $factory->create()
            ->setGraphTitle($this->translator->translate('admin.admin.background_jobs.default.graph.title'))
            ->setGraphHelp($this->translator->translate('admin.admin.background_jobs.default.graph.tooltip'))
            ->addGraphDataItem($errorEvents);

        return $control;
    }

    /**
     * @admin-access-level write
     */
    public function handleRetry($id)
    {
        $task = $this->hermesTasksRepository->find($id);
        $this->hermesEmitter->emit(new HermesMessage($task->type, Json::decode($task->payload, Json::FORCE_ARRAY)));
        // not sure if we would like to delete this task from table
        //$this->hermesTasksRepository->delete($task);
        $this->redirect('default');
    }
}

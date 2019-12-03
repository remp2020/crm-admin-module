<?php

namespace Crm\AdminModule\Presenters;

use Crm\ApplicationModule\Components\Graphs\GoogleLineGraphGroupControlFactoryInterface;
use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\GraphDataItem;
use Crm\ApplicationModule\Hermes\HermesTasksQueue;
use Crm\ApplicationModule\Repository\HermesTasksRepository;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

class BackgroundStatusAdminPresenter extends AdminPresenter
{
    private $hermesTasksRepository;

    private $hermesTasksQueue;

    public function __construct(HermesTasksRepository $hermesTasksRepository, HermesTasksQueue $hermesTasksQueue)
    {
        parent::__construct();
        $this->hermesTasksRepository = $hermesTasksRepository;
        $this->hermesTasksQueue = $hermesTasksQueue;
    }

    public function renderDefault()
    {
        $errorRanges = [1,7,31];
        $queuedCounts = array_filter(
            $this->hermesTasksQueue->getTypeCounts()
        );

        $errorCounts = [];
        foreach ($errorRanges as $range) {
            $errorCounts[$range] = $this->hermesTasksRepository
                ->getStateCounts(
                    DateTime::from("-{$range} days"),
                    [
                        HermesTasksRepository::STATE_ERROR,
                    ]
                )
                ->fetchPairs('type', 'count');
        }

        $tasks = $this->hermesTasksQueue->getAllTask();
        $enqueuedTasks = [];
        foreach ($tasks as $task => $processTime) {
            $task = Json::decode($task);
            $processAt = null;
            if ($processTime) {
                $processAt = DateTime::createFromFormat('U.u', number_format($processTime, 6, '.', ''));
            }

            $enqueuedTasks[] = [
                'id' => $task->message->id,
                'type' => $task->message->type,
                'payload' => Json::encode($task->message->payload, Json::PRETTY),
                'processAt' => $processAt,
            ];
        }

        $this->template->queuedCounts = $queuedCounts;
        $this->template->errorRanges = $errorRanges;
        $this->template->errorCounts = $errorCounts;
        $this->template->enqueuedTasks = $enqueuedTasks;
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
}

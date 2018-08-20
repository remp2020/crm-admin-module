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
        $typeCounts = $this->hermesTasksQueue->getTypeCounts();

        $stateCounts = [
            'enqueued' => array_sum($typeCounts)
        ];

        array_walk($typeCounts, function (&$item) {
            $item = [
                'enqueued' => $item,
                'processing' => 0,
                'error' => 0,
                'done' => 0,
            ];
        });

        $states = $this->hermesTasksRepository->getStateCounts();

        foreach ($states as $state) {
            if (isset($stateCounts[$state->state])) {
                $stateCounts[$state->state] += $state->count;
            } else {
                $stateCounts[$state->state] = $state->count;
            }

            if (!isset($typeCounts[$state->type])) {
                $typeCounts[$state->type] = [
                    'enqueued' => 0,
                    'processing' => 0,
                    'error' => 0,
                    'done' => 0,
                ];
            }
            $typeCounts[$state->type][$state->state] += $state->count;
        }
        ksort($stateCounts);

        $tasks = $this->hermesTasksQueue->getAllTask();
        $enqueuedTasks = [];
        foreach ($tasks as $task => $processAt) {
            $task = Json::decode($task);
            $processAt = DateTime::createFromFormat('U', $processAt)
                ->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            $enqueuedTasks[] = [
                'id' => $task->message->id,
                'type' => $task->message->type,
                'payload' => Json::encode($task->message->payload),
                'processAt' => $processAt->format('d.m.Y H:i:s'),
            ];
        }

        $this->template->typeCounts = $typeCounts;
        $this->template->stateCounts = $stateCounts;
        $this->template->enqueuedTasks = $enqueuedTasks;
    }

    protected function createComponentBackgroundJobsGraph(GoogleLineGraphGroupControlFactoryInterface $factory)
    {
        $graphDataItem1 = new GraphDataItem();
        $graphDataItem1->setCriteria((new Criteria())
            ->setTableName('hermes_tasks')
            ->setTimeField('created_at')
            ->setWhere('AND state="done"')
            ->setValueField('COUNT(*)')
            ->setStart('-1 month'))
            ->setName($this->translator->translate('admin.admin.background_jobs.default.states.done'));

        $graphDataItem2 = new GraphDataItem();
        $graphDataItem2->setCriteria((new Criteria())
            ->setTableName('hermes_tasks')
            ->setTimeField('created_at')
            ->setWhere('AND state="error"')
            ->setValueField('COUNT(*)')
            ->setStart('-1 month'))
            ->setName($this->translator->translate('admin.admin.background_jobs.default.states.error'));

        $control = $factory->create()
            ->setGraphTitle($this->translator->translate('admin.admin.background_jobs.default.graph.title'))
            ->setGraphHelp($this->translator->translate('admin.admin.background_jobs.default.graph.tooltip'))
            ->addGraphDataItem($graphDataItem1)
            ->addGraphDataItem($graphDataItem2);

        return $control;
    }
}

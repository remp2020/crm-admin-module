<?php

namespace Crm\AdminModule\Populators;

use Crm\ApplicationModule\Populator\AbstractPopulator;
use Crm\ApplicationModule\Repository\AuditLogRepository;
use Symfony\Component\Console\Helper\ProgressBar;

class AuditLogsPopulator extends AbstractPopulator
{
    /**
     * @param ProgressBar $progressBar
     */
    public function seed($progressBar)
    {
        $auditLogs = $this->database->table('audit_logs');
        $auditedUsers = [
            $this->getRecord('users'),
            $this->getRecord('users'),
            $this->getRecord('users'),
            $this->getRecord('users'),
        ];

        for ($i = 0; $i < $this->count; $i++) {
            $user = $this->getRecord('users');

            $oldValues = [
                'email' => $this->faker->email,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
            ];
            $newValues = [];

            if (random_int(0, 1)) {
                $newValues['email'] = $this->faker->email;
            } else {
                unset($oldValues['email']);
            }
            if (random_int(0, 1)) {
                $newValues['first_name'] = $this->faker->firstName;
            } else {
                unset($oldValues['first_name']);
            }
            if (random_int(0, 1)) {
                $newValues['last_name'] = $this->faker->lastName;
            } else {
                unset($oldValues['last_name']);
            }

            if (empty($oldValues)) {
                continue;
            }

            $data = [
                'table_name' => 'users',
                'user_id' => $user->id,
                'operation' => AuditLogRepository::OPERATION_UPDATE,
                'signature' => $auditedUsers[random_int(0, sizeof($auditedUsers)-1)],
                'data' => json_encode([
                    'version' => 1,
                    'from' => $oldValues,
                    'to' => $newValues,
                ]),
                'created_at' => $this->faker->dateTimeBetween('-1 years'),
            ];

            $auditLogs->insert($data);

            $progressBar->advance();
        }
    }
}

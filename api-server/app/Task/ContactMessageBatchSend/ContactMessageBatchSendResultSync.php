<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Task\ContactMessageBatchSend;

use App\Contract\ContactMessageBatchSendEmployeeServiceInterface;
use App\QueueService\ContactMessageBatchSend\SyncSendResultApply;
use Hyperf\Crontab\Annotation\Crontab;
use Psr\Container\ContainerInterface;

/**
 * @Crontab(name="contactMessageBatchSendResultSync", rule="*\/5 * * * *", callback="execute", singleton=true, memo="客户消息结果同步")
 */
class ContactMessageBatchSendResultSync
{
    const INTERVAL_MINUTES = 5; // 定时任务间隔分钟数

    const EXPIRE_MINUTES = 1440 * 30; // 过期时间，到期后不再同步消息结果

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(): void
    {
        $syncResult = $this->container->get(SyncSendResultApply::class);
        $service    = $this->container->get(ContactMessageBatchSendEmployeeServiceInterface::class);
        $rows       = $service->getContactMessageBatchSendEmployeesByResultSync(self::INTERVAL_MINUTES, self::EXPIRE_MINUTES);

        foreach ($rows as $item) {
            $syncResult->handle($item['id']);
        }

    }

}
